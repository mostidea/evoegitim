<?php
session_start();
include("../config/connection.php");
checkUnSession();

$groupId = (int)($_GET['id'] ?? 0);
if ($groupId <= 0) {
    header("location: groups.php");
    exit;
}

/* ── Gruptan Çıkar (POST + CSRF) ─────────────────────── */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'remove') {
    if (!hash_equals($_SESSION['csrf_token'] ?? '', $_POST['csrf_token'] ?? '')) {
        http_response_code(403); die('Geçersiz istek.');
    }

    $quotaId   = (int)($_POST['quota_id']   ?? 0);
    $studentId = (int)($_POST['student_id'] ?? 0);
    $gid       = (int)($_POST['group_id']   ?? 0);

    if ($quotaId && $studentId && $gid) {
        // Öğrenci ve grup bilgisini DB'den al (GET param değil)
        $infoStmt = $db->prepare("SELECT u.fullname, u.phone, g.title FROM users u, `groups` g WHERE u.id = ? AND g.id = ?");
        $infoStmt->execute([$studentId, $gid]);
        $info = $infoStmt->fetch(PDO::FETCH_ASSOC);

        // Veli telefonu
        $veliPhone = '';
        $vpStmt = $db->prepare("SELECT p.phone FROM invite_parent ip JOIN parent p ON ip.email = p.email WHERE ip.user_email = (SELECT email FROM users WHERE id = ?) LIMIT 1");
        $vpStmt->execute([$studentId]);
        $vp = $vpStmt->fetchColumn();
        if ($vp) $veliPhone = $vp;

        // groups_quota → reddedildi
        $db->prepare("UPDATE groups_quota SET status = -1 WHERE id = ? AND group_id = ?")->execute([$quotaId, $gid]);

        // Gelecekteki derslerin kredisi
        $refundStmt = $db->prepare("SELECT SUM(credit) FROM appointment WHERE student_id = ? AND group_id = ? AND start_date > NOW()");
        $refundStmt->execute([$studentId, $gid]);
        $refund = (int)$refundStmt->fetchColumn();

        // Gelecekteki dersleri sil
        $db->prepare("DELETE FROM appointment WHERE student_id = ? AND group_id = ? AND start_date > NOW()")->execute([$studentId, $gid]);

        // Kredi iadesi
        if ($refund > 0) {
            creditRefund($db, $studentId, $refund, 1);
        }

        // SMS
        if ($info) {
            $title    = $info['title'];
            $fullname = $info['fullname'];
            if (!empty($info['phone'])) {
                sendSms("Değerli Öğrencimiz, {$title} grubundan çıkarıldınız. İlgili krediler hesabınıza iade edildi.", [$info['phone']]);
            }
            if ($veliPhone) {
                sendSms("Değerli Velimiz, {$fullname} adlı öğrenciniz {$title} grubundan çıkarıldı. Kredisi iade edilmiştir.", [$veliPhone]);
            }
        }
    }

    header("Location: class-members.php?id={$groupId}&removed=1");
    exit;
}

/* ── Grup bilgisi ──────────────────────────────────────── */
$groupStmt = $db->prepare("SELECT * FROM `groups` WHERE id = ?");
$groupStmt->execute([$groupId]);
$group = $groupStmt->fetch(PDO::FETCH_ASSOC);
if (!$group) {
    header("location: groups.php");
    exit;
}

/* ── Üyeler (groups_quota, status=1) ──────────────────── */
$membersStmt = $db->prepare("
    SELECT gq.id AS quota_id, gq.created_at,
           u.id AS user_id, u.fullname, u.email, u.phone, u.profile_photo, u.level
    FROM groups_quota gq
    JOIN users u ON gq.user_id = u.id
    WHERE gq.group_id = ? AND gq.status = 1
    ORDER BY gq.created_at ASC
");
$membersStmt->execute([$groupId]);
$members = $membersStmt->fetchAll(PDO::FETCH_ASSOC);
$total   = count($members);

$avatarColors = ['#7c3aed','#2563eb','#059669','#d97706','#db2777','#0891b2'];

function initials($name) {
    $p = explode(' ', trim($name));
    $i = strtoupper(mb_substr($p[0], 0, 1));
    if (isset($p[1])) $i .= strtoupper(mb_substr($p[1], 0, 1));
    return $i;
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
  <?php include "../includes_panel/meta.php"; ?>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
  <title><?php echo htmlspecialchars($group['title']); ?> — Üyeler | Evo Eğitim</title>
  <style>
    :root {
      --gp:#7c3aed; --gp-s:#f5f3ff; --gb:#2563eb; --gb-s:#eff6ff;
      --gg:#059669; --gg-s:#ecfdf5; --gr:#dc2626; --gr-s:#fef2f2;
      --gt:#0f172a; --gm:#64748b;   --gbr:#e2e8f0; --gbg:#f1f5f9;
      --gc:#ffffff; --radius:16px;  --shadow:0 2px 12px rgba(0,0,0,.06);
    }
    .evo-page { background:var(--gbg); min-height:100vh; padding:1.75rem; display:flex; flex-direction:column; gap:1.25rem; }

    /* Header */
    .evo-ph { display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:1rem; }
    .evo-ph-left { display:flex; align-items:center; gap:.85rem; }
    .evo-ph-icon { width:48px; height:48px; border-radius:14px; background:var(--gp-s); display:flex; align-items:center; justify-content:center; font-size:1.4rem; color:var(--gp); }
    .evo-ph-title { font-size:1.15rem; font-weight:800; color:var(--gt); margin:0; }
    .evo-ph-sub   { font-size:.78rem; color:var(--gm); margin:.1rem 0 0; }
    .evo-back-btn { display:inline-flex; align-items:center; gap:.4rem; background:var(--gc); color:var(--gm); font-size:.82rem; font-weight:600; padding:.5rem 1rem; border-radius:10px; border:1px solid var(--gbr); text-decoration:none; transition:background .15s; }
    .evo-back-btn:hover { background:#f8fafc; color:var(--gt); }

    /* Stats */
    .evo-stats { display:grid; grid-template-columns:repeat(3,1fr); gap:1rem; }
    .evo-stat-card { background:var(--gc); border:1px solid var(--gbr); border-radius:var(--radius); box-shadow:var(--shadow); padding:1.1rem 1.25rem; display:flex; align-items:center; gap:.85rem; }
    .evo-stat-icon { width:42px; height:42px; border-radius:12px; display:flex; align-items:center; justify-content:center; font-size:1.2rem; flex-shrink:0; }
    .evo-stat-num { font-size:1.4rem; font-weight:800; color:var(--gt); line-height:1; }
    .evo-stat-lbl { font-size:.7rem; color:var(--gm); margin-top:.2rem; }

    /* Member Grid */
    .evo-member-grid { display:grid; grid-template-columns:repeat(auto-fill, minmax(280px,1fr)); gap:1rem; }

    /* Member Card */
    .evo-mc { background:var(--gc); border:1px solid var(--gbr); border-radius:var(--radius); box-shadow:var(--shadow); overflow:hidden; display:flex; flex-direction:column; transition:box-shadow .18s, transform .18s; }
    .evo-mc:hover { box-shadow:0 6px 24px rgba(0,0,0,.1); transform:translateY(-2px); }

    .evo-mc-top { padding:1.25rem 1.25rem .75rem; display:flex; align-items:center; gap:.85rem; }
    .evo-avatar { width:48px; height:48px; border-radius:50%; display:flex; align-items:center; justify-content:center; font-size:.85rem; font-weight:800; color:#fff; flex-shrink:0; }
    .evo-mc-name { font-size:.95rem; font-weight:700; color:var(--gt); }
    .evo-mc-level { font-size:.72rem; color:var(--gm); margin-top:.15rem; }

    .evo-mc-body { padding:.25rem 1.25rem .85rem; display:flex; flex-direction:column; gap:.4rem; flex:1; }
    .evo-mc-row { display:flex; align-items:center; gap:.45rem; font-size:.78rem; color:var(--gm); }
    .evo-mc-row i { color:var(--gp); width:14px; text-align:center; flex-shrink:0; }

    .evo-mc-foot { padding:.75rem 1.25rem; border-top:1px solid var(--gbr); display:flex; align-items:center; justify-content:space-between; }
    .evo-mc-date { font-size:.7rem; color:var(--gm); display:flex; align-items:center; gap:.3rem; }
    .evo-remove-btn { display:inline-flex; align-items:center; gap:.3rem; background:var(--gr-s); color:var(--gr); font-size:.75rem; font-weight:700; padding:.35rem .85rem; border-radius:8px; border:none; cursor:pointer; transition:background .15s; }
    .evo-remove-btn:hover { background:#fecaca; }

    /* Empty */
    .evo-empty { background:var(--gc); border:1px solid var(--gbr); border-radius:var(--radius); padding:4rem 2rem; text-align:center; color:var(--gm); }
    .evo-empty i { font-size:2.5rem; display:block; margin-bottom:.75rem; color:#cbd5e1; }
    .evo-empty p { margin:0; font-size:.9rem; }

    /* Alert */
    .evo-alert { padding:.85rem 1.25rem; border-radius:12px; font-size:.84rem; font-weight:600; display:flex; align-items:center; gap:.5rem; }
    .evo-alert-success { background:var(--gg-s); color:var(--gg); border:1px solid #a7f3d0; }

    @media (max-width:768px) { .evo-page { padding:1rem; } .evo-stats { grid-template-columns:1fr 1fr; } .evo-member-grid { grid-template-columns:1fr; } }
    @media (max-width:480px) { .evo-stats { grid-template-columns:1fr; } }
  </style>
</head>
<body>
  <?php include 'includes/left-menu.php'; ?>
  <div class="dashboard-main-wrapper">
    <?php include 'includes/top-menu.php'; ?>
    <div class="dashboard-body evo-page">

      <!-- Header -->
      <div class="evo-ph">
        <div class="evo-ph-left">
          <div class="evo-ph-icon"><i class="ph-fill ph-users"></i></div>
          <div>
            <h1 class="evo-ph-title"><?php echo htmlspecialchars($group['title']); ?></h1>
            <p class="evo-ph-sub">Onaylı grup üyeleri</p>
          </div>
        </div>
        <a href="group-class-detail.php?kurs=<?php echo htmlspecialchars($group['slug']); ?>" class="evo-back-btn">
          <i class="ph ph-arrow-left"></i> Gruba Dön
        </a>
      </div>

      <?php if (isset($_GET['removed'])): ?>
      <div class="evo-alert evo-alert-success">
        <i class="ph ph-check-circle"></i> Öğrenci gruptan çıkarıldı ve kredisi iade edildi.
      </div>
      <?php endif; ?>

      <!-- Stats -->
      <div class="evo-stats">
        <div class="evo-stat-card">
          <div class="evo-stat-icon" style="background:var(--gp-s);color:var(--gp);"><i class="ph-fill ph-users-three"></i></div>
          <div>
            <div class="evo-stat-num"><?php echo $total; ?></div>
            <div class="evo-stat-lbl">Onaylı Üye</div>
          </div>
        </div>
        <div class="evo-stat-card">
          <div class="evo-stat-icon" style="background:var(--gg-s);color:var(--gg);"><i class="ph-fill ph-chair"></i></div>
          <div>
            <div class="evo-stat-num"><?php echo max(0, (int)$group['quota'] - $total); ?></div>
            <div class="evo-stat-lbl">Boş Kontenjan</div>
          </div>
        </div>
        <div class="evo-stat-card">
          <div class="evo-stat-icon" style="background:var(--gb-s);color:var(--gb);"><i class="ph-fill ph-percent"></i></div>
          <div>
            <div class="evo-stat-num"><?php echo $group['quota'] > 0 ? round($total / $group['quota'] * 100) : 0; ?>%</div>
            <div class="evo-stat-lbl">Doluluk Oranı</div>
          </div>
        </div>
      </div>

      <!-- Members -->
      <?php if (empty($members)): ?>
        <div class="evo-empty">
          <i class="ph ph-users"></i>
          <p>Bu gruba henüz onaylı öğrenci bulunmuyor.</p>
        </div>
      <?php else: ?>
      <div class="evo-member-grid">
        <?php foreach ($members as $i => $m):
          $color = $avatarColors[$i % count($avatarColors)];
          $ini   = initials($m['fullname']);
          $avatarImg = !empty($m['profile_photo']) ? '/' . ltrim($m['profile_photo'], '/') : null;
        ?>
        <div class="evo-mc">
          <div class="evo-mc-top">
            <?php if ($avatarImg): ?>
              <img src="<?php echo htmlspecialchars($avatarImg); ?>" alt="" class="evo-avatar" style="object-fit:cover;">
            <?php else: ?>
              <div class="evo-avatar" style="background:<?php echo $color; ?>"><?php echo $ini; ?></div>
            <?php endif; ?>
            <div>
              <div class="evo-mc-name"><?php echo htmlspecialchars($m['fullname']); ?></div>
              <div class="evo-mc-level"><?php echo htmlspecialchars($m['level'] ?? ''); ?></div>
            </div>
          </div>

          <div class="evo-mc-body">
            <?php if (!empty($m['email'])): ?>
            <div class="evo-mc-row"><i class="ph ph-envelope-simple"></i><?php echo htmlspecialchars($m['email']); ?></div>
            <?php endif; ?>
            <?php if (!empty($m['phone'])): ?>
            <div class="evo-mc-row"><i class="ph ph-phone"></i><?php echo htmlspecialchars($m['phone']); ?></div>
            <?php endif; ?>
          </div>

          <div class="evo-mc-foot">
            <span class="evo-mc-date">
              <i class="ph ph-calendar-check"></i>
              <?php echo turkcetarih('j F Y', $m['created_at']); ?>
            </span>
            <button class="evo-remove-btn"
                    data-quota="<?php echo $m['quota_id']; ?>"
                    data-student="<?php echo $m['user_id']; ?>"
                    data-group="<?php echo $groupId; ?>"
                    data-name="<?php echo htmlspecialchars($m['fullname']); ?>">
              <i class="ph ph-user-minus"></i> Çıkar
            </button>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
      <?php endif; ?>

    </div>
    <?php include '../includes_panel/footer.php'; ?>
  </div>

  <!-- Remove form (gizli, JS ile submit edilir) -->
  <form id="removeForm" method="POST" action="class-members.php?id=<?php echo $groupId; ?>" style="display:none;">
    <input type="hidden" name="action"      value="remove">
    <input type="hidden" name="csrf_token"  value="<?php echo $_SESSION['csrf_token']; ?>">
    <input type="hidden" name="quota_id"    id="fQuotaId">
    <input type="hidden" name="student_id"  id="fStudentId">
    <input type="hidden" name="group_id"    id="fGroupId">
  </form>

  <?php include '../includes_panel/scripts.php'; ?>
  <?php include 'includes/teacher-scripts.php'; ?>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.js"></script>
  <script>
  document.querySelectorAll('.evo-remove-btn').forEach(btn => {
    btn.addEventListener('click', function () {
      const name = this.dataset.name;
      Swal.fire({
        title: 'Gruptan Çıkar',
        html: `<strong>${name}</strong> adlı öğrenciyi gruptan çıkarmak istiyor musunuz?<br><small class="text-muted">Gelecekteki dersleri silinecek ve kredisi iade edilecektir.</small>`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Evet, Çıkar',
        cancelButtonText: 'Vazgeç',
        confirmButtonColor: '#dc2626'
      }).then(r => {
        if (!r.isConfirmed) return;
        document.getElementById('fQuotaId').value   = this.dataset.quota;
        document.getElementById('fStudentId').value = this.dataset.student;
        document.getElementById('fGroupId').value   = this.dataset.group;
        document.getElementById('removeForm').submit();
      });
    });
  });
  </script>
</body>
</html>
