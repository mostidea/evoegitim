<?php
ini_set('session.cookie_path', '/');
ini_set('session.cookie_domain', '');
session_start();
include("../config/connection.php");
checkUnSession();

$tid     = (int)$_SESSION['user_id'];
$groupId = (int)($_GET['id'] ?? 0);

/* ── ID YOK: grup seçim ekranı ── */
if ($groupId <= 0) {
    $gList = $db->prepare('
        SELECT g.id, g.title, g.quota,
               (SELECT COUNT(*) FROM groups_quota gq WHERE gq.group_id = g.id AND gq.status = 0) AS pending,
               (SELECT COUNT(*) FROM groups_quota gq WHERE gq.group_id = g.id AND gq.status = 1) AS approved
        FROM `groups` g
        WHERE g.teacher_id = ?
        ORDER BY g.id DESC
    ');
    $gList->execute([$tid]);
    $myGroups = $gList->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="tr">
<head>
  <?php include "../includes_panel/meta.php"; ?>
  <title>Grup Seç | Evo Eğitim</title>
  <style>
    :root{--p:#7c3aed;--ps:#f5f3ff;--b:#2563eb;--bs:#eff6ff;--g:#059669;--gs:#ecfdf5;--tx:#0f172a;--mu:#64748b;--br:#e2e8f0;--bg:#f1f5f9;--card:#fff;--rad:16px;--shd:0 2px 12px rgba(0,0,0,.06);}
    .gr-page{background:var(--bg);min-height:100vh;padding:1.75rem;display:flex;flex-direction:column;gap:1.25rem;}
    .gr-head{background:linear-gradient(135deg,#4c1d95,#1e40af);border-radius:var(--rad);padding:1.6rem 1.8rem;display:flex;align-items:center;gap:1rem;}
    .gr-head-icon{width:48px;height:48px;border-radius:14px;background:rgba(255,255,255,.15);display:flex;align-items:center;justify-content:center;font-size:1.4rem;color:#fff;flex-shrink:0;}
    .gr-head-title{font-size:1.15rem;font-weight:800;color:#fff;margin:0;}
    .gr-head-sub{font-size:.8rem;color:rgba(255,255,255,.7);margin:.2rem 0 0;}
    .gr-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(260px,1fr));gap:1rem;}
    .gr-gc{background:var(--card);border:1px solid var(--br);border-radius:14px;box-shadow:var(--shd);padding:1.25rem 1.4rem;display:flex;flex-direction:column;gap:.75rem;transition:box-shadow .2s,border-color .2s;text-decoration:none;}
    .gr-gc:hover{box-shadow:0 6px 24px rgba(0,0,0,.1);border-color:#c4b5fd;text-decoration:none;}
    .gr-gc-title{font-size:.95rem;font-weight:800;color:var(--tx);}
    .gr-gc-meta{display:flex;gap:.6rem;flex-wrap:wrap;}
    .gr-badge{display:inline-flex;align-items:center;gap:.25rem;padding:.2rem .65rem;border-radius:20px;font-size:.7rem;font-weight:700;}
    .badge-pend{background:#fef9c3;color:#854d0e;}
    .badge-appr{background:var(--gs);color:#064e3b;}
    .badge-quota{background:var(--bs);color:#1e40af;}
    .gr-arrow{margin-left:auto;color:var(--p);font-size:1.1rem;}
    .gr-empty{background:var(--card);border:1px solid var(--br);border-radius:var(--rad);padding:3rem 1rem;text-align:center;color:var(--mu);}
    .gr-empty i{font-size:2.5rem;display:block;margin-bottom:.6rem;color:#cbd5e1;}
  </style>
</head>
<body>
  <?php include 'includes/left-menu.php'; ?>
  <div class="dashboard-main-wrapper">
    <?php include 'includes/top-menu.php'; ?>
    <div class="dashboard-body gr-page">

      <div class="gr-head">
        <div class="gr-head-icon"><i class="ph-fill ph-users-three"></i></div>
        <div>
          <h1 class="gr-head-title">Grup Başvuruları</h1>
          <p class="gr-head-sub">Başvuruları görmek istediğiniz grubu seçin</p>
        </div>
      </div>

      <?php if (empty($myGroups)): ?>
        <div class="gr-empty">
          <i class="ph ph-folder-open"></i>
          <p>Henüz grubunuz bulunmuyor.</p>
        </div>
      <?php else: ?>
      <div class="gr-grid">
        <?php foreach ($myGroups as $g): ?>
        <a href="teacher/group-request.php?id=<?php echo $g['id']; ?>" class="gr-gc">
          <div class="gr-gc-title"><?php echo htmlspecialchars($g['title']); ?></div>
          <div class="gr-gc-meta">
            <?php if ($g['pending'] > 0): ?>
              <span class="gr-badge badge-pend"><i class="ph ph-clock"></i> <?php echo $g['pending']; ?> Bekleyen</span>
            <?php endif; ?>
            <span class="gr-badge badge-appr"><i class="ph ph-check-circle"></i> <?php echo $g['approved']; ?> Onaylı</span>
            <span class="gr-badge badge-quota"><i class="ph ph-users"></i> Kont. <?php echo $g['quota']; ?></span>
          </div>
          <i class="ph ph-arrow-right gr-arrow"></i>
        </a>
        <?php endforeach; ?>
      </div>
      <?php endif; ?>

    </div>
    <?php include '../includes_panel/footer.php'; ?>
  </div>
  <?php include '../includes_panel/scripts.php'; ?>
  <?php include 'includes/teacher-scripts.php'; ?>
</body>
</html>
<?php
    exit;
}

/* ── ID VAR: grup doğrulaması ── */
$gStmt = $db->prepare('SELECT id, title, credit, total_lesson_time, weekly_lesson_count, quota, teacher_id, lesson_id FROM `groups` WHERE id = ? AND teacher_id = ?');
$gStmt->execute([$groupId, $tid]);
$group = $gStmt->fetch(PDO::FETCH_ASSOC);
if (!$group) { header("location: teacher/group-request.php"); exit; }

$requiredCredits = (int)$group['credit'] * (int)$group['total_lesson_time'] * (int)$group['weekly_lesson_count'];

/* ── Onay / Red ── */
$flash = '';
$flashType = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'], $_POST['quota_id'])) {
    if (!hash_equals($_SESSION['csrf_token'] ?? '', $_POST['csrf_token'] ?? '')) {
        http_response_code(403); die('Gecersiz istek.');
    }
    $quotaId = (int)$_POST['quota_id'];
    $action  = $_POST['action'];

    $s = $db->prepare('SELECT user_id, status FROM groups_quota WHERE id = ? AND group_id = ?');
    $s->execute([$quotaId, $groupId]);
    $rec = $s->fetch(PDO::FETCH_ASSOC);

    if ($rec && $action === 'approve' && $rec['status'] == 0) {
        $s2 = $db->prepare('SELECT COUNT(*) FROM groups_quota WHERE group_id = ? AND status = 1');
        $s2->execute([$groupId]);
        if ((int)$s2->fetchColumn() >= (int)$group['quota']) {
            $flash = 'Kontenjan dolu, bu ogrenci onaylanamaz.';
            $flashType = 'warning';
        } else {
            $creditOk = true;
            if ($requiredCredits > 0) {
                $s3 = $db->prepare('SELECT id, credit FROM active_credit WHERE user_id = ? AND type = 1 AND credit > 0 ORDER BY credit DESC');
                $s3->execute([$rec['user_id']]);
                $creditRows = $s3->fetchAll(PDO::FETCH_ASSOC);
                $totalAvail = array_sum(array_column($creditRows, 'credit'));
                if ($totalAvail < $requiredCredits) {
                    $creditOk  = false;
                    $flash     = "Ogrencinin kredisi yetersiz (Gereken: {$requiredCredits}, Mevcut: {$totalAvail}).";
                    $flashType = 'warning';
                } else {
                    $rem = $requiredCredits;
                    foreach ($creditRows as $cr) {
                        if ($rem <= 0) break;
                        $ded = min($rem, (int)$cr['credit']);
                        $db->prepare('UPDATE active_credit SET credit = credit - ? WHERE id = ?')->execute([$ded, $cr['id']]);
                        $rem -= $ded;
                    }
                }
            }
            if ($creditOk) {
                $db->prepare('UPDATE groups_quota SET status = 1 WHERE id = ?')->execute([$quotaId]);
                $gtStmt = $db->prepare('SELECT * FROM group_time WHERE group_id = ? AND end_date > NOW() ORDER BY start_date ASC');
                $gtStmt->execute([$groupId]);
                $fl = 0;
                foreach ($gtStmt->fetchAll(PDO::FETCH_ASSOC) as $gt) {
                    $dup = $db->prepare('SELECT id FROM appointment WHERE student_id = ? AND group_time_id = ?');
                    $dup->execute([$rec['user_id'], $gt['id']]);
                    if ($dup->fetch()) { $fl = 1; continue; }
                    $db->prepare('INSERT INTO appointment (student_id,teacher_id,lesson_id,group_id,group_time_id,room_id,first_lesson,start_date,end_date,status,credit,total_credit,type,revise,teacher_report,student_report,income) VALUES (?,?,?,?,?,?,?,?,?,0,?,?,1,0,0,0,0)')
                       ->execute([$rec['user_id'],$tid,$group['lesson_id'],$groupId,$gt['id'],$gt['id'],$fl,$gt['start_date'],$gt['end_date'],$group['credit'],$requiredCredits]);
                    $fl = 1;
                }

                /* ── Bildirimler ── */
                $stuStmt = $db->prepare('SELECT fullname, email, phone FROM users WHERE id = ?');
                $stuStmt->execute([$rec['user_id']]);
                $stu = $stuStmt->fetch(PDO::FETCH_ASSOC);
                $groupTitle = $group['title'];

                if (!empty($stu['phone'])) {
                    sendSms(
                        "Degerli Ogrencimiz, {$groupTitle} grup dersine basvurunuz onaylandi. Ders programinizi panelinizden inceleyebilirsiniz. Evo Egitim",
                        [$stu['phone']]
                    );
                }
                if (!empty($stu['email'])) {
                    $mailBody = "<div style='font-family:Arial,sans-serif;max-width:600px;margin:0 auto;'>
                      <div style='background:linear-gradient(135deg,#4c1d95,#1e40af);padding:28px 32px;border-radius:12px 12px 0 0;'>
                        <h2 style='color:#fff;margin:0;font-size:1.2rem;'>Grup Ders Basvurunuz Onaylandi</h2>
                      </div>
                      <div style='background:#f8fafc;padding:28px 32px;border:1px solid #e2e8f0;border-top:none;border-radius:0 0 12px 12px;'>
                        <p style='margin:0 0 16px;color:#0f172a;'>Sayin <strong>{$stu['fullname']}</strong>,</p>
                        <p style='margin:0 0 16px;color:#334155;'><strong>{$groupTitle}</strong> grup dersine basvurunuz onaylanmistir.</p>
                        <p style='margin:0 0 24px;color:#334155;'>Ders programinizi ve seans detaylarini panelinizden inceleyebilirsiniz.</p>
                        <a href='https://evoegitim.com/student/appointment.php' style='display:inline-block;background:#7c3aed;color:#fff;padding:12px 24px;border-radius:8px;text-decoration:none;font-weight:700;font-size:.9rem;'>Randevularima Git</a>
                        <hr style='border:none;border-top:1px solid #e2e8f0;margin:24px 0;'>
                        <p style='margin:0;color:#64748b;font-size:.82rem;'>Evo Egitim Ekibi</p>
                      </div>
                    </div>";
                    sendEmail(
                        $stu['email'], $stu['fullname'],
                        "Grup Ders Basvurunuz Onaylandi - Evo Egitim",
                        $mailBody,
                        "Sayin {$stu['fullname']}, {$groupTitle} grup dersine basvurunuz onaylanmistir."
                    );
                }

                $flash = 'Ogrenci basariyla onaylandi. SMS ve e-posta bildirimi gonderildi.';
                $flashType = 'success';
            }
        }
    } elseif ($rec && $action === 'reject') {
        $db->prepare('UPDATE groups_quota SET status = -1 WHERE id = ?')->execute([$quotaId]);
        $flash = 'Basvuru reddedildi.';
        $flashType = 'danger';
    }
}

$tab = in_array($_GET['tab'] ?? '', ['approved','rejected']) ? $_GET['tab'] : 'pending';
$statusMap = ['pending' => 0, 'approved' => 1, 'rejected' => -1];

$stmt = $db->prepare('SELECT gq.id AS quota_id, gq.status, gq.created_at, u.fullname, u.email, u.phone FROM groups_quota gq JOIN users u ON gq.user_id = u.id WHERE gq.group_id = ? AND gq.status = ? ORDER BY gq.created_at DESC');
$stmt->execute([$groupId, $statusMap[$tab]]);
$applications = $stmt->fetchAll(PDO::FETCH_ASSOC);

$counts = [];
foreach ($statusMap as $k => $v) {
    $sc = $db->prepare('SELECT COUNT(*) FROM groups_quota WHERE group_id = ? AND status = ?');
    $sc->execute([$groupId, $v]);
    $counts[$k] = (int)$sc->fetchColumn();
}

function nameInitials($n) {
    $p = explode(' ', trim($n));
    return strtoupper(mb_substr($p[0],0,1).(isset($p[1])?mb_substr($p[1],0,1):''));
}
$avatarColors = ['#7c3aed','#2563eb','#059669','#d97706','#db2777','#0891b2'];
?>
<!DOCTYPE html>
<html lang="tr">
<head>
  <?php include "../includes_panel/meta.php"; ?>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
  <title>Grup Başvuruları | Evo Eğitim</title>
  <style>
    :root{--p:#7c3aed;--ps:#f5f3ff;--b:#2563eb;--bs:#eff6ff;--g:#059669;--gs:#ecfdf5;--a:#f59e0b;--as:#fffbeb;--r:#dc2626;--rs:#fef2f2;--tx:#0f172a;--mu:#64748b;--br:#e2e8f0;--bg:#f1f5f9;--card:#fff;--rad:16px;--shd:0 2px 12px rgba(0,0,0,.06);}
    .gr-page{background:var(--bg);min-height:100vh;padding:1.75rem;display:flex;flex-direction:column;gap:1.25rem;}
    .gr-head{background:linear-gradient(135deg,#4c1d95,#1e40af);border-radius:var(--rad);padding:1.6rem 1.8rem;display:flex;align-items:center;gap:1rem;flex-wrap:wrap;}
    .gr-head-icon{width:48px;height:48px;border-radius:14px;background:rgba(255,255,255,.15);display:flex;align-items:center;justify-content:center;font-size:1.4rem;color:#fff;flex-shrink:0;}
    .gr-head-title{font-size:1.15rem;font-weight:800;color:#fff;margin:0;}
    .gr-head-sub{font-size:.8rem;color:rgba(255,255,255,.7);margin:.2rem 0 0;}
    .gr-head-back{margin-left:auto;display:inline-flex;align-items:center;gap:.4rem;padding:.55rem 1.1rem;background:rgba(255,255,255,.15);backdrop-filter:blur(4px);border-radius:10px;color:#fff;font-size:.8rem;font-weight:600;text-decoration:none;transition:background .15s;}
    .gr-head-back:hover{background:rgba(255,255,255,.25);color:#fff;text-decoration:none;}
    .gr-stats{display:grid;grid-template-columns:repeat(3,1fr);gap:1rem;}
    .gr-stat{background:var(--card);border:1px solid var(--br);border-radius:var(--rad);box-shadow:var(--shd);padding:1.1rem 1.3rem;display:flex;align-items:center;gap:.85rem;}
    .gr-stat-icon{width:42px;height:42px;border-radius:12px;display:flex;align-items:center;justify-content:center;font-size:1.2rem;flex-shrink:0;}
    .gr-stat-num{font-size:1.6rem;font-weight:800;color:var(--tx);line-height:1;}
    .gr-stat-lbl{font-size:.7rem;color:var(--mu);margin-top:.2rem;}
    .gr-flash{padding:.9rem 1.2rem;border-radius:12px;font-size:.85rem;font-weight:600;display:flex;align-items:center;gap:.6rem;}
    .gr-flash.success{background:var(--gs);color:#064e3b;border:1px solid #a7f3d0;}
    .gr-flash.warning{background:var(--as);color:#78350f;border:1px solid #fde68a;}
    .gr-flash.danger{background:var(--rs);color:#7f1d1d;border:1px solid #fecaca;}
    .gr-tabs{display:flex;gap:.5rem;flex-wrap:wrap;}
    .gr-tab{display:inline-flex;align-items:center;gap:.45rem;padding:.55rem 1.1rem;border-radius:10px;border:1.5px solid var(--br);background:var(--card);font-size:.82rem;font-weight:600;color:var(--mu);text-decoration:none;transition:all .15s;}
    .gr-tab:hover{border-color:var(--p);color:var(--p);text-decoration:none;}
    .gr-tab.active{background:var(--p);border-color:var(--p);color:#fff;}
    .gr-tab-cnt{padding:.05rem .45rem;border-radius:20px;font-size:.7rem;font-weight:700;}
    .gr-tab.active .gr-tab-cnt{background:rgba(255,255,255,.25);}
    .gr-tab:not(.active) .gr-tab-cnt{background:rgba(0,0,0,.07);}
    .gr-list{display:flex;flex-direction:column;gap:.6rem;}
    .gr-item{border:1px solid var(--br);border-radius:14px;padding:1rem 1.2rem;display:flex;align-items:center;gap:1rem;flex-wrap:wrap;transition:box-shadow .2s;}
    .gr-item:hover{box-shadow:0 4px 20px rgba(0,0,0,.08);}
    .gr-avatar{width:42px;height:42px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:.78rem;font-weight:800;color:#fff;flex-shrink:0;}
    .gr-info{flex:1;min-width:180px;}
    .gr-name{font-size:.9rem;font-weight:700;color:var(--tx);}
    .gr-meta{font-size:.75rem;color:var(--mu);margin-top:.2rem;display:flex;gap:.75rem;flex-wrap:wrap;}
    .gr-date{font-size:.72rem;color:var(--mu);white-space:nowrap;}
    .gr-acts{display:flex;gap:.45rem;flex-shrink:0;}
    .btn-ap{display:inline-flex;align-items:center;gap:.35rem;padding:.5rem 1rem;border-radius:9px;border:none;cursor:pointer;font-size:.78rem;font-weight:700;transition:opacity .15s;white-space:nowrap;}
    .btn-ap:hover{opacity:.85;}
    .btn-ap-ok{background:var(--g);color:#fff;}
    .btn-ap-no{background:var(--r);color:#fff;}
    .gr-status-badge{display:inline-flex;align-items:center;gap:.3rem;padding:.3rem .8rem;border-radius:20px;font-size:.75rem;font-weight:700;}
    .badge-ok{background:var(--gs);color:#064e3b;}
    .badge-no{background:var(--rs);color:#7f1d1d;}
    .gr-empty{padding:3rem 1rem;text-align:center;color:var(--mu);}
    .gr-empty i{font-size:2.5rem;display:block;margin-bottom:.6rem;color:#cbd5e1;}
    .gr-empty p{font-size:.88rem;margin:0;}
    @media(max-width:640px){.gr-page{padding:1rem;}.gr-stats{grid-template-columns:1fr 1fr;}.gr-item{flex-direction:column;align-items:flex-start;}.gr-acts{width:100%;}}
  </style>
</head>
<body>
  <?php include 'includes/left-menu.php'; ?>
  <div class="dashboard-main-wrapper">
    <?php include 'includes/top-menu.php'; ?>
    <div class="dashboard-body gr-page">

      <div class="gr-head">
        <div class="gr-head-icon"><i class="ph-fill ph-users-three"></i></div>
        <div>
          <h1 class="gr-head-title"><?php echo htmlspecialchars($group['title']); ?></h1>
          <p class="gr-head-sub">Grup Ders Başvuruları · <?php echo $requiredCredits; ?> kredi gereken</p>
        </div>
        <a href="teacher/group-request.php" class="gr-head-back">
          <i class="ph ph-arrow-left"></i> Tüm Gruplar
        </a>
      </div>

      <div class="gr-stats">
        <div class="gr-stat">
          <div class="gr-stat-icon" style="background:#fef9c3;color:#854d0e;"><i class="ph-fill ph-clock"></i></div>
          <div><div class="gr-stat-num"><?php echo $counts['pending']; ?></div><div class="gr-stat-lbl">Bekleyen</div></div>
        </div>
        <div class="gr-stat">
          <div class="gr-stat-icon" style="background:var(--gs);color:var(--g);"><i class="ph-fill ph-check-circle"></i></div>
          <div><div class="gr-stat-num"><?php echo $counts['approved']; ?> / <?php echo $group['quota']; ?></div><div class="gr-stat-lbl">Onaylı / Kontenjan</div></div>
        </div>
        <div class="gr-stat">
          <div class="gr-stat-icon" style="background:var(--rs);color:var(--r);"><i class="ph-fill ph-x-circle"></i></div>
          <div><div class="gr-stat-num"><?php echo $counts['rejected']; ?></div><div class="gr-stat-lbl">Reddedilen</div></div>
        </div>
      </div>

      <?php if ($flash): ?>
      <div class="gr-flash <?php echo $flashType; ?>">
        <i class="ph <?php echo $flashType==='success' ? 'ph-check-circle' : ($flashType==='warning' ? 'ph-warning' : 'ph-x-circle'); ?>"></i>
        <?php echo htmlspecialchars($flash); ?>
      </div>
      <?php endif; ?>

      <div style="background:var(--card);border:1px solid var(--br);border-radius:var(--rad);box-shadow:var(--shd);">
        <div style="padding:1rem 1.4rem;border-bottom:1px solid var(--br);">
          <div class="gr-tabs">
            <a href="teacher/group-request.php?id=<?php echo $groupId; ?>&tab=pending"
               class="gr-tab <?php echo $tab==='pending'?'active':''; ?>">
              <i class="ph ph-clock"></i> Bekleyenler
              <span class="gr-tab-cnt"><?php echo $counts['pending']; ?></span>
            </a>
            <a href="teacher/group-request.php?id=<?php echo $groupId; ?>&tab=approved"
               class="gr-tab <?php echo $tab==='approved'?'active':''; ?>">
              <i class="ph ph-check-circle"></i> Onaylananlar
              <span class="gr-tab-cnt"><?php echo $counts['approved']; ?></span>
            </a>
            <a href="teacher/group-request.php?id=<?php echo $groupId; ?>&tab=rejected"
               class="gr-tab <?php echo $tab==='rejected'?'active':''; ?>">
              <i class="ph ph-x-circle"></i> Reddedilenler
              <span class="gr-tab-cnt"><?php echo $counts['rejected']; ?></span>
            </a>
          </div>
        </div>

        <div style="padding:1.25rem 1.4rem;">
          <?php if (empty($applications)): ?>
            <div class="gr-empty">
              <i class="ph ph-clipboard-text"></i>
              <p>Bu kategoride başvuru bulunmuyor.</p>
            </div>
          <?php else: ?>
          <div class="gr-list">
            <?php foreach ($applications as $i => $app):
              $color = $avatarColors[$i % count($avatarColors)];
              $ini   = nameInitials($app['fullname']);
            ?>
            <div class="gr-item">
              <div class="gr-avatar" style="background:<?php echo $color; ?>"><?php echo $ini; ?></div>
              <div class="gr-info">
                <div class="gr-name"><?php echo htmlspecialchars($app['fullname']); ?></div>
                <div class="gr-meta">
                  <span><i class="ph ph-envelope"></i> <?php echo htmlspecialchars($app['email']); ?></span>
                  <?php if (!empty($app['phone'])): ?>
                  <span><i class="ph ph-phone"></i> <?php echo htmlspecialchars($app['phone']); ?></span>
                  <?php endif; ?>
                </div>
              </div>
              <div class="gr-date">
                <i class="ph ph-calendar-blank"></i>
                <?php echo turkcetarih('j F Y H:i', $app['created_at']); ?>
              </div>
              <div class="gr-acts">
                <?php if ($tab === 'pending'): ?>
                  <button class="btn-ap btn-ap-ok swal-approve"
                          data-quota="<?php echo $app['quota_id']; ?>"
                          data-name="<?php echo htmlspecialchars($app['fullname'], ENT_QUOTES); ?>">
                    <i class="ph ph-check"></i> Onayla
                  </button>
                  <button class="btn-ap btn-ap-no swal-reject"
                          data-quota="<?php echo $app['quota_id']; ?>"
                          data-name="<?php echo htmlspecialchars($app['fullname'], ENT_QUOTES); ?>">
                    <i class="ph ph-x"></i> Reddet
                  </button>
                <?php elseif ($tab === 'approved'): ?>
                  <span class="gr-status-badge badge-ok"><i class="ph ph-check-circle"></i> Onaylandı</span>
                <?php else: ?>
                  <span class="gr-status-badge badge-no"><i class="ph ph-x-circle"></i> Reddedildi</span>
                <?php endif; ?>
              </div>
            </div>
            <?php endforeach; ?>
          </div>
          <?php endif; ?>
        </div>
      </div>

      <form id="frm-approve" method="POST" action="teacher/group-request.php?id=<?php echo $groupId; ?>&tab=<?php echo $tab; ?>" style="display:none;">
        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
        <input type="hidden" name="quota_id"   id="fld-quota-ok">
        <input type="hidden" name="action"     value="approve">
      </form>
      <form id="frm-reject" method="POST" action="teacher/group-request.php?id=<?php echo $groupId; ?>&tab=<?php echo $tab; ?>" style="display:none;">
        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
        <input type="hidden" name="quota_id"   id="fld-quota-no">
        <input type="hidden" name="action"     value="reject">
      </form>

    </div>
    <?php include '../includes_panel/footer.php'; ?>
  </div>
  <?php include '../includes_panel/scripts.php'; ?>
  <?php include 'includes/teacher-scripts.php'; ?>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.js"></script>
  <script>
  document.querySelectorAll('.swal-approve').forEach(btn => {
    btn.addEventListener('click', function () {
      Swal.fire({
        title: 'Onayla', icon: 'question',
        text: `${this.dataset.name} adlı öğrenciyi gruba onaylamak istiyor musunuz?`,
        showCancelButton: true, confirmButtonText: 'Onayla', cancelButtonText: 'Vazgeç',
        confirmButtonColor: '#059669'
      }).then(r => {
        if (r.isConfirmed) {
          document.getElementById('fld-quota-ok').value = this.dataset.quota;
          document.getElementById('frm-approve').submit();
        }
      });
    });
  });
  document.querySelectorAll('.swal-reject').forEach(btn => {
    btn.addEventListener('click', function () {
      Swal.fire({
        title: 'Reddet', icon: 'warning',
        text: `${this.dataset.name} adlı öğrencinin başvurusunu reddetmek istediğinize emin misiniz?`,
        showCancelButton: true, confirmButtonText: 'Reddet', cancelButtonText: 'Vazgeç',
        confirmButtonColor: '#dc2626'
      }).then(r => {
        if (r.isConfirmed) {
          document.getElementById('fld-quota-no').value = this.dataset.quota;
          document.getElementById('frm-reject').submit();
        }
      });
    });
  });
  </script>
</body>
</html>
