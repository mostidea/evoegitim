<?php
session_start();
include("../config/connection.php");
checkUnSession();

$groupId = (int)($_GET['id'] ?? 0);
if ($groupId <= 0) {
    header("location: /management/group-classes.php");
    exit;
}

// Grup bilgisi
$stmt = $db->prepare('SELECT id, title, credit, total_lesson_time, weekly_lesson_count, quota, teacher_id, lesson_id FROM `groups` WHERE id = ?');
$stmt->execute([$groupId]);
$group = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$group) {
    header("location: /management/group-classes.php");
    exit;
}

$requiredCredits = (int)$group['credit'] * (int)$group['total_lesson_time'] * (int)$group['weekly_lesson_count'];

// Onay / Red işlemi
$actionMsg = '';
$actionType = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'], $_POST['quota_id'])) {
    if (!hash_equals($_SESSION['csrf_token'] ?? '', $_POST['csrf_token'] ?? '')) {
        http_response_code(403);
        die('Geçersiz istek.');
    }
    $quotaId  = (int)$_POST['quota_id'];
    $action   = $_POST['action'];

    // Bu kaydın user_id'sini bul
    $s = $db->prepare('SELECT user_id, status FROM groups_quota WHERE id = ? AND group_id = ?');
    $s->execute([$quotaId, $groupId]);
    $rec = $s->fetch(PDO::FETCH_ASSOC);

    if ($rec && $action === 'approve' && $rec['status'] == 0) {
        // Kontenjan kontrolü
        $s2 = $db->prepare('SELECT COUNT(*) FROM groups_quota WHERE group_id = ? AND status = 1');
        $s2->execute([$groupId]);
        $approvedCount = (int)$s2->fetchColumn();

        if ($approvedCount >= (int)$group['quota']) {
            $actionMsg  = 'Kontenjan dolu, bu öğrenci onaylanamaz.';
            $actionType = 'danger';
        } else {
            // Kredi düş
            $creditOk = true;
            if ($requiredCredits > 0) {
                $s3 = $db->prepare('SELECT id, credit FROM active_credit WHERE user_id = ? AND type = ? AND credit > 0 ORDER BY credit DESC');
                $s3->execute([$rec['user_id'], '1']);
                $creditRows = $s3->fetchAll(PDO::FETCH_ASSOC);
                $totalAvail = array_sum(array_column($creditRows, 'credit'));

                if ($totalAvail < $requiredCredits) {
                    $creditOk   = false;
                    $actionMsg  = "Öğrencinin kredisi yetersiz (Gereken: {$requiredCredits}, Mevcut: {$totalAvail}). Yine de onaylamak için tekrar gönderin.";
                    $actionType = 'warning';
                } else {
                    $remaining = $requiredCredits;
                    foreach ($creditRows as $cr) {
                        if ($remaining <= 0) break;
                        $deduct = min($remaining, (int)$cr['credit']);
                        $upd = $db->prepare('UPDATE active_credit SET credit = credit - ? WHERE id = ?');
                        $upd->execute([$deduct, $cr['id']]);
                        $remaining -= $deduct;
                    }
                }
            }

            if ($creditOk) {
                $upd = $db->prepare('UPDATE groups_quota SET status = 1 WHERE id = ?');
                $upd->execute([$quotaId]);

                // group_time seanslarından appointment oluştur
                $gtStmt = $db->prepare('SELECT * FROM group_time WHERE group_id = ? AND end_date > NOW() ORDER BY start_date ASC');
                $gtStmt->execute([$groupId]);
                $groupTimes = $gtStmt->fetchAll(PDO::FETCH_ASSOC);
                $firstLesson = 0;
                foreach ($groupTimes as $gt) {
                    $dupCheck = $db->prepare('SELECT id FROM appointment WHERE student_id = ? AND group_time_id = ?');
                    $dupCheck->execute([$rec['user_id'], $gt['id']]);
                    if ($dupCheck->fetch()) { $firstLesson = 1; continue; }
                    $ins = $db->prepare('INSERT INTO appointment (student_id, teacher_id, lesson_id, group_id, group_time_id, room_id, first_lesson, start_date, end_date, status, credit, total_credit, type, revise, teacher_report, student_report, income) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 0, ?, ?, 1, 0, 0, 0, 0)');
                    $ins->execute([
                        $rec['user_id'],
                        $group['teacher_id'],
                        $group['lesson_id'],
                        $groupId,
                        $gt['id'],
                        $gt['id'],
                        $firstLesson,
                        $gt['start_date'],
                        $gt['end_date'],
                        $group['credit'],
                        $requiredCredits,
                    ]);
                    $firstLesson = 1;
                }

                $actionMsg  = 'Öğrenci başarıyla onaylandı.';
                $actionType = 'success';
            }
        }

    } elseif ($rec && $action === 'reject') {
        $upd = $db->prepare('UPDATE groups_quota SET status = -1 WHERE id = ?');
        $upd->execute([$quotaId]);
        $actionMsg  = 'Başvuru reddedildi.';
        $actionType = 'danger';
    }
}

// Aktif tab
$tab = $_GET['tab'] ?? 'pending';
$statusMap = ['pending' => 0, 'approved' => 1, 'rejected' => -1];
$filterStatus = $statusMap[$tab] ?? 0;

// Başvuruları çek
$stmt = $db->prepare('
    SELECT gq.id AS quota_id, gq.status, gq.created_at,
           u.fullname, u.email, u.phone
    FROM groups_quota gq
    JOIN users u ON gq.user_id = u.id
    WHERE gq.group_id = ? AND gq.status = ?
    ORDER BY gq.created_at DESC
');
$stmt->execute([$groupId, $filterStatus]);
$applications = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Sayılar (tab badge için)
$counts = [];
foreach ($statusMap as $key => $val) {
    $sc = $db->prepare('SELECT COUNT(*) FROM groups_quota WHERE group_id = ? AND status = ?');
    $sc->execute([$groupId, $val]);
    $counts[$key] = (int)$sc->fetchColumn();
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
  <?php include "../includes_panel/meta.php"; ?>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
  <title>Grup Ders Başvuruları | Evo Eğitim</title>
</head>
<body>
  <?php include 'includes/left-menu.php'; ?>
  <div class="dashboard-main-wrapper">
    <?php include 'includes/top-menu.php'; ?>
    <div class="dashboard-body">
      <div class="card mt-24">
        <div class="card-header border-bottom d-flex align-items-center justify-content-between">
          <div>
            <h4 class="mb-1"><?php echo htmlspecialchars($group['title']); ?> — Başvurular</h4>
            <small class="text-muted">Kontenjan: <?php echo $counts['approved']; ?> / <?php echo $group['quota']; ?> onaylı öğrenci</small>
          </div>
          <a href="/management/group-class-detail.php?kurs=<?php echo $_GET['id']; ?>" class="btn btn-sm btn-outline-secondary">Gruba Dön</a>
        </div>
        <div class="card-body">

          <?php if ($actionMsg): ?>
            <div class="alert alert-<?php echo $actionType; ?> mb-3"><?php echo htmlspecialchars($actionMsg); ?></div>
          <?php endif; ?>

          <!-- Tablar -->
          <ul class="nav nav-tabs mb-3">
            <li class="nav-item">
              <a class="nav-link <?php echo $tab === 'pending' ? 'active' : ''; ?>"
                 href="/management/group-request.php?id=<?php echo $groupId; ?>&tab=pending">
                Bekleyenler <span class="badge bg-warning text-dark ms-1"><?php echo $counts['pending']; ?></span>
              </a>
            </li>
            <li class="nav-item">
              <a class="nav-link <?php echo $tab === 'approved' ? 'active' : ''; ?>"
                 href="/management/group-request.php?id=<?php echo $groupId; ?>&tab=approved">
                Onaylananlar <span class="badge bg-success ms-1"><?php echo $counts['approved']; ?></span>
              </a>
            </li>
            <li class="nav-item">
              <a class="nav-link <?php echo $tab === 'rejected' ? 'active' : ''; ?>"
                 href="/management/group-request.php?id=<?php echo $groupId; ?>&tab=rejected">
                Reddedilenler <span class="badge bg-danger ms-1"><?php echo $counts['rejected']; ?></span>
              </a>
            </li>
          </ul>

          <?php if (empty($applications)): ?>
            <p class="text-muted">Bu kategoride başvuru yok.</p>
          <?php else: ?>
            <div class="table-responsive">
              <table class="table table-bordered table-hover align-middle">
                <thead class="table-light">
                  <tr>
                    <th>#</th>
                    <th>Ad Soyad</th>
                    <th>E-posta</th>
                    <th>Telefon</th>
                    <th>Başvuru Tarihi</th>
                    <?php if ($tab === 'pending'): ?>
                    <th>İşlem</th>
                    <?php endif; ?>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach ($applications as $i => $app): ?>
                  <tr>
                    <td><?php echo $i + 1; ?></td>
                    <td><?php echo htmlspecialchars($app['fullname']); ?></td>
                    <td><?php echo htmlspecialchars($app['email']); ?></td>
                    <td><?php echo htmlspecialchars($app['phone'] ?? '-'); ?></td>
                    <td><?php echo turkcetarih('j F Y H:i', $app['created_at']); ?></td>
                    <?php if ($tab === 'pending'): ?>
                    <td>
                      <form method="POST" action="/management/group-request.php?id=<?php echo $groupId; ?>&tab=pending" style="display:inline">
                        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                        <input type="hidden" name="quota_id" value="<?php echo $app['quota_id']; ?>">
                        <input type="hidden" name="action" value="approve">
                        <button type="submit" class="btn btn-sm btn-success" onclick="return confirm('Bu öğrenciyi onaylıyor musunuz?')">Onayla</button>
                      </form>
                      <form method="POST" action="/management/group-request.php?id=<?php echo $groupId; ?>&tab=pending" style="display:inline">
                        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                        <input type="hidden" name="quota_id" value="<?php echo $app['quota_id']; ?>">
                        <input type="hidden" name="action" value="reject">
                        <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Bu başvuruyu reddetmek istediğinize emin misiniz?')">Reddet</button>
                      </form>
                    </td>
                    <?php endif; ?>
                  </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            </div>
          <?php endif; ?>

        </div>
      </div>
    </div>
    <?php include '../includes_panel/footer.php'; ?>
  </div>
  <?php include '../includes_panel/scripts.php'; ?>
</body>
</html>
