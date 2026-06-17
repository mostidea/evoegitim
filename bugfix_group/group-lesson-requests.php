<?php
ini_set('session.cookie_path', '/');
ini_set('session.cookie_domain', '');
session_start();
include("../config/connection.php");
checkUnSession();

$tid = (int)$_SESSION['user_id'];

$flash = '';
$flashType = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'], $_POST['quota_id'], $_POST['group_id'])) {
    if (!hash_equals($_SESSION['csrf_token'] ?? '', $_POST['csrf_token'] ?? '')) {
        http_response_code(403); die('Gecersiz istek.');
    }
    $quotaId = (int)$_POST['quota_id'];
    $gid     = (int)$_POST['group_id'];

    $gChk = $db->prepare('SELECT id, title, credit, total_lesson_time, weekly_lesson_count, quota, lesson_id FROM `groups` WHERE id = ? AND teacher_id = ?');
    $gChk->execute([$gid, $tid]);
    $group = $gChk->fetch(PDO::FETCH_ASSOC);

    if ($group) {
        $reqCr = (int)$group['credit'] * (int)$group['total_lesson_time'] * (int)$group['weekly_lesson_count'];
        $action = $_POST['action'];

        $s = $db->prepare('SELECT user_id, status FROM groups_quota WHERE id = ? AND group_id = ?');
        $s->execute([$quotaId, $gid]);
        $rec = $s->fetch(PDO::FETCH_ASSOC);

        if ($rec && $action === 'approve' && $rec['status'] == 0) {
            $s2 = $db->prepare('SELECT COUNT(*) FROM groups_quota WHERE group_id = ? AND status = 1');
            $s2->execute([$gid]);
            if ((int)$s2->fetchColumn() >= (int)$group['quota']) {
                $flash = 'Kontenjan dolu, bu ogrenci onaylanamaz.';
                $flashType = 'warning';
            } else {
                $creditOk = true;
                if ($reqCr > 0) {
                    $s3 = $db->prepare('SELECT id, credit FROM active_credit WHERE user_id = ? AND type = 1 AND credit > 0 ORDER BY credit DESC');
                    $s3->execute([$rec['user_id']]);
                    $creditRows = $s3->fetchAll(PDO::FETCH_ASSOC);
                    $totalAvail = array_sum(array_column($creditRows, 'credit'));
                    if ($totalAvail < $reqCr) {
                        $creditOk  = false;
                        $flash     = "Ogrencinin kredisi yetersiz (Gereken: {$reqCr}, Mevcut: {$totalAvail}).";
                        $flashType = 'warning';
                    } else {
                        $rem = $reqCr;
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
                    $gtStmt->execute([$gid]);
                    $fl = 0;
                    foreach ($gtStmt->fetchAll(PDO::FETCH_ASSOC) as $gt) {
                        $dup = $db->prepare('SELECT id FROM appointment WHERE student_id = ? AND group_time_id = ?');
                        $dup->execute([$rec['user_id'], $gt['id']]);
                        if ($dup->fetch()) { $fl = 1; continue; }
                        $db->prepare('INSERT INTO appointment (student_id,teacher_id,lesson_id,group_id,group_time_id,room_id,first_lesson,start_date,end_date,status,credit,total_credit,type,revise,teacher_report,student_report,income) VALUES (?,?,?,?,?,?,?,?,?,0,?,?,1,0,0,0,0)')
                           ->execute([$rec['user_id'],$tid,$group['lesson_id'],$gid,$gt['id'],$gt['id'],$fl,$gt['start_date'],$gt['end_date'],$group['credit'],$reqCr]);
                        $fl = 1;
                    }

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
                            <p style='margin:0 0 24px;color:#334155;'>Ders programinizi panelinizden inceleyebilirsiniz.</p>
                            <a href='https://evoegitim.com/student/appointment.php' style='display:inline-block;background:#7c3aed;color:#fff;padding:12px 24px;border-radius:8px;text-decoration:none;font-weight:700;'>Randevularima Git</a>
                            <hr style='border:none;border-top:1px solid #e2e8f0;margin:24px 0;'>
                            <p style='margin:0;color:#64748b;font-size:.82rem;'>Evo Egitim Ekibi</p>
                          </div>
                        </div>";
                        sendEmail($stu['email'], $stu['fullname'], "Grup Ders Basvurunuz Onaylandi - Evo Egitim", $mailBody,
                            "Sayin {$stu['fullname']}, {$groupTitle} grup dersine basvurunuz onaylanmistir.");
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
}

$tab = in_array($_GET['tab'] ?? '', ['approved','rejected']) ? $_GET['tab'] : 'pending';
$statusMap = ['pending' => 0, 'approved' => 1, 'rejected' => -1];
$filterStatus = $statusMap[$tab];

$stmt = $db->prepare('
    SELECT gq.id AS quota_id, gq.status, gq.created_at,
           u.fullname, u.email, u.phone,
           g.id AS group_id, g.title AS group_title, g.quota,
           g.credit, g.total_lesson_time, g.weekly_lesson_count,
           (SELECT COUNT(*) FROM groups_quota gq2 WHERE gq2.group_id = g.id AND gq2.status = 1) AS enrolled
    FROM groups_quota gq
    JOIN users u    ON gq.user_id  = u.id
    JOIN `groups` g ON gq.group_id = g.id
    WHERE g.teacher_id = ? AND gq.status = ?
    ORDER BY gq.created_at DESC
');
$stmt->execute([$tid, $filterStatus]);
$applications = $stmt->fetchAll(PDO::FETCH_ASSOC);

$counts = [];
foreach ($statusMap as $k => $v) {
    $sc = $db->prepare('SELECT COUNT(*) FROM groups_quota gq JOIN `groups` g ON gq.group_id = g.id WHERE g.teacher_id = ? AND gq.status = ?');
    $sc->execute([$tid, $v]);
    $counts[$k] = (int)$sc->fetchColumn();
}

function glrInitials($n) {
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
  <title>Grup Ders Talepleri | Evo Egitim</title>
  <style>
    :root{--p:#7c3aed;--ps:#f5f3ff;--b:#2563eb;--bs:#eff6ff;--g:#059669;--gs:#ecfdf5;--a:#f59e0b;--as:#fffbeb;--r:#dc2626;--rs:#fef2f2;--tx:#0f172a;--mu:#64748b;--br:#e2e8f0;--bg:#f1f5f9;--card:#fff;--rad:16px;--shd:0 2px 12px rgba(0,0,0,.06);}
    .glr-page{background:var(--bg);min-height:100vh;padding:1.75rem;display:flex;flex-direction:column;gap:1.25rem;}
    .glr-head{background:linear-gradient(135deg,#4c1d95,#1e40af);border-radius:var(--rad);padding:1.6rem 1.8rem;display:flex;align-items:center;gap:1rem;flex-wrap:wrap;}
    .glr-head-icon{width:48px;height:48px;border-radius:14px;background:rgba(255,255,255,.15);display:flex;align-items:center;justify-content:center;font-size:1.4rem;color:#fff;flex-shrink:0;}
    .glr-head-title{font-size:1.15rem;font-weight:800;color:#fff;margin:0;}
    .glr-head-sub{font-size:.8rem;color:rgba(255,255,255,.7);margin:.2rem 0 0;}
    .glr-head-btn{margin-left:auto;display:inline-flex;align-items:center;gap:.4rem;padding:.55rem 1.1rem;background:rgba(255,255,255,.15);backdrop-filter:blur(4px);border-radius:10px;color:#fff;font-size:.8rem;font-weight:600;text-decoration:none;transition:background .15s;white-space:nowrap;}
    .glr-head-btn:hover{background:rgba(255,255,255,.25);color:#fff;text-decoration:none;}
    .glr-stats{display:grid;grid-template-columns:repeat(3,1fr);gap:1rem;}
    .glr-stat{background:var(--card);border:1px solid var(--br);border-radius:var(--rad);box-shadow:var(--shd);padding:1.1rem 1.3rem;display:flex;align-items:center;gap:.85rem;}
    .glr-stat-icon{width:42px;height:42px;border-radius:12px;display:flex;align-items:center;justify-content:center;font-size:1.2rem;flex-shrink:0;}
    .glr-stat-num{font-size:1.6rem;font-weight:800;color:var(--tx);line-height:1;}
    .glr-stat-lbl{font-size:.7rem;color:var(--mu);margin-top:.2rem;}
    .glr-flash{padding:.9rem 1.2rem;border-radius:12px;font-size:.85rem;font-weight:600;display:flex;align-items:center;gap:.6rem;}
    .glr-flash.success{background:var(--gs);color:#064e3b;border:1px solid #a7f3d0;}
    .glr-flash.warning{background:var(--as);color:#78350f;border:1px solid #fde68a;}
    .glr-flash.danger{background:var(--rs);color:#7f1d1d;border:1px solid #fecaca;}
    .glr-tabs{display:flex;gap:.5rem;flex-wrap:wrap;}
    .glr-tab{display:inline-flex;align-items:center;gap:.45rem;padding:.55rem 1.1rem;border-radius:10px;border:1.5px solid var(--br);background:var(--card);font-size:.82rem;font-weight:600;color:var(--mu);text-decoration:none;transition:all .15s;}
    .glr-tab:hover{border-color:var(--p);color:var(--p);text-decoration:none;}
    .glr-tab.active{background:var(--p);border-color:var(--p);color:#fff;}
    .glr-tab-cnt{padding:.05rem .45rem;border-radius:20px;font-size:.7rem;font-weight:700;}
    .glr-tab.active .glr-tab-cnt{background:rgba(255,255,255,.25);}
    .glr-tab:not(.active) .glr-tab-cnt{background:rgba(0,0,0,.07);}
    .glr-card{background:var(--card);border:1px solid var(--br);border-radius:var(--rad);box-shadow:var(--shd);}
    .glr-card-head{padding:1rem 1.4rem;border-bottom:1px solid var(--br);}
    .glr-card-body{padding:1.25rem 1.4rem;}
    .glr-list{display:flex;flex-direction:column;gap:.65rem;}
    .glr-item{border:1px solid var(--br);border-radius:14px;padding:1rem 1.2rem;display:flex;align-items:center;gap:1rem;flex-wrap:wrap;transition:box-shadow .2s,border-color .2s;}
    .glr-item:hover{box-shadow:0 4px 20px rgba(0,0,0,.08);border-color:#c4b5fd;}
    .glr-avatar{width:42px;height:42px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:.78rem;font-weight:800;color:#fff;flex-shrink:0;}
    .glr-info{flex:1;min-width:160px;}
    .glr-name{font-size:.9rem;font-weight:700;color:var(--tx);}
    .glr-meta{font-size:.74rem;color:var(--mu);margin-top:.25rem;display:flex;gap:.7rem;flex-wrap:wrap;align-items:center;}
    .glr-meta i{color:var(--p);}
    .glr-group-pill{display:inline-flex;align-items:center;gap:.3rem;background:var(--ps);color:var(--p);padding:.2rem .65rem;border-radius:20px;font-size:.7rem;font-weight:700;white-space:nowrap;}
    .glr-quota-mini{font-size:.7rem;color:var(--mu);white-space:nowrap;}
    .glr-date{font-size:.72rem;color:var(--mu);white-space:nowrap;flex-shrink:0;}
    .glr-acts{display:flex;gap:.4rem;flex-shrink:0;}
    .glr-btn{display:inline-flex;align-items:center;gap:.3rem;padding:.45rem .9rem;border-radius:9px;border:none;cursor:pointer;font-size:.75rem;font-weight:700;text-decoration:none;transition:opacity .15s;white-space:nowrap;}
    .glr-btn:hover{opacity:.85;text-decoration:none;}
    .glr-btn-ok{background:var(--g);color:#fff;}
    .glr-btn-no{background:var(--r);color:#fff;}
    .glr-btn-link{background:var(--bs);color:var(--b);}
    .glr-status-badge{display:inline-flex;align-items:center;gap:.3rem;padding:.28rem .75rem;border-radius:20px;font-size:.73rem;font-weight:700;}
    .badge-ok{background:var(--gs);color:#064e3b;}
    .badge-no{background:var(--rs);color:#7f1d1d;}
    .glr-empty{padding:3.5rem 1rem;text-align:center;color:var(--mu);}
    .glr-empty i{font-size:2.5rem;display:block;margin-bottom:.6rem;color:#cbd5e1;}
    .glr-empty p{font-size:.88rem;margin:0;}
    @media(max-width:640px){.glr-page{padding:1rem;}.glr-stats{grid-template-columns:1fr 1fr;}.glr-item{flex-direction:column;align-items:flex-start;}.glr-acts{width:100%;}}
  </style>
</head>
<body>
  <?php include 'includes/left-menu.php'; ?>
  <div class="dashboard-main-wrapper">
    <?php include 'includes/top-menu.php'; ?>
    <div class="dashboard-body glr-page">

      <div class="glr-head">
        <div class="glr-head-icon"><i class="ph-fill ph-clipboard-text"></i></div>
        <div>
          <h1 class="glr-head-title">Grup Ders Talepleri</h1>
          <p class="glr-head-sub">Tum grup derslerinize gelen ogrenci basvurulari</p>
        </div>
        <a href="teacher/group-request.php" class="glr-head-btn">
          <i class="ph ph-list-checks"></i> Grup Başvurularını Yönet
        </a>
      </div>

      <div class="glr-stats">
        <div class="glr-stat">
          <div class="glr-stat-icon" style="background:#fef9c3;color:#854d0e;"><i class="ph-fill ph-clock"></i></div>
          <div><div class="glr-stat-num"><?php echo $counts['pending']; ?></div><div class="glr-stat-lbl">Bekleyen Talep</div></div>
        </div>
        <div class="glr-stat">
          <div class="glr-stat-icon" style="background:var(--gs);color:var(--g);"><i class="ph-fill ph-check-circle"></i></div>
          <div><div class="glr-stat-num"><?php echo $counts['approved']; ?></div><div class="glr-stat-lbl">Onaylanan</div></div>
        </div>
        <div class="glr-stat">
          <div class="glr-stat-icon" style="background:var(--rs);color:var(--r);"><i class="ph-fill ph-x-circle"></i></div>
          <div><div class="glr-stat-num"><?php echo $counts['rejected']; ?></div><div class="glr-stat-lbl">Reddedilen</div></div>
        </div>
      </div>

      <?php if ($flash): ?>
      <div class="glr-flash <?php echo $flashType; ?>">
        <i class="ph <?php echo $flashType==='success' ? 'ph-check-circle' : ($flashType==='warning' ? 'ph-warning' : 'ph-x-circle'); ?>"></i>
        <?php echo htmlspecialchars($flash); ?>
      </div>
      <?php endif; ?>

      <div class="glr-card">
        <div class="glr-card-head">
          <div class="glr-tabs">
            <a href="teacher/group-lesson-requests.php?tab=pending" class="glr-tab <?php echo $tab==='pending' ? 'active' : ''; ?>">
              <i class="ph ph-clock"></i> Bekleyenler <span class="glr-tab-cnt"><?php echo $counts['pending']; ?></span>
            </a>
            <a href="teacher/group-lesson-requests.php?tab=approved" class="glr-tab <?php echo $tab==='approved' ? 'active' : ''; ?>">
              <i class="ph ph-check-circle"></i> Onaylananlar <span class="glr-tab-cnt"><?php echo $counts['approved']; ?></span>
            </a>
            <a href="teacher/group-lesson-requests.php?tab=rejected" class="glr-tab <?php echo $tab==='rejected' ? 'active' : ''; ?>">
              <i class="ph ph-x-circle"></i> Reddedilenler <span class="glr-tab-cnt"><?php echo $counts['rejected']; ?></span>
            </a>
          </div>
        </div>
        <div class="glr-card-body">
          <?php if (empty($applications)): ?>
            <div class="glr-empty">
              <i class="ph ph-clipboard-text"></i>
              <p>Bu kategoride herhangi bir basvuru bulunmuyor.</p>
            </div>
          <?php else: ?>
          <div class="glr-list">
            <?php foreach ($applications as $i => $app):
              $color    = $avatarColors[$i % count($avatarColors)];
              $ini      = glrInitials($app['fullname']);
              $reqCr    = (int)$app['credit'] * (int)$app['total_lesson_time'] * (int)$app['weekly_lesson_count'];
              $enrolled = (int)$app['enrolled'];
              $quota    = (int)$app['quota'];
            ?>
            <div class="glr-item">
              <div class="glr-avatar" style="background:<?php echo $color; ?>"><?php echo $ini; ?></div>
              <div class="glr-info">
                <div class="glr-name"><?php echo htmlspecialchars($app['fullname']); ?></div>
                <div class="glr-meta">
                  <span><i class="ph ph-envelope"></i> <?php echo htmlspecialchars($app['email']); ?></span>
                  <?php if (!empty($app['phone'])): ?><span><i class="ph ph-phone"></i> <?php echo htmlspecialchars($app['phone']); ?></span><?php endif; ?>
                </div>
                <div class="glr-meta" style="margin-top:.35rem;">
                  <span class="glr-group-pill"><i class="ph ph-users-three"></i><?php echo htmlspecialchars($app['group_title']); ?></span>
                  <span class="glr-quota-mini"><?php echo $enrolled; ?>/<?php echo $quota; ?> ogrenci &middot; <?php echo $reqCr; ?> kredi</span>
                </div>
              </div>
              <div class="glr-date"><i class="ph ph-calendar-blank"></i> <?php echo turkcetarih('j F Y H:i', $app['created_at']); ?></div>
              <div class="glr-acts">
                <?php if ($tab === 'pending'): ?>
                  <button class="glr-btn glr-btn-ok swal-approve"
                          data-quota="<?php echo $app['quota_id']; ?>"
                          data-gid="<?php echo $app['group_id']; ?>"
                          data-name="<?php echo htmlspecialchars($app['fullname'], ENT_QUOTES); ?>"
                          data-group="<?php echo htmlspecialchars($app['group_title'], ENT_QUOTES); ?>">
                    <i class="ph ph-check"></i> Onayla
                  </button>
                  <button class="glr-btn glr-btn-no swal-reject"
                          data-quota="<?php echo $app['quota_id']; ?>"
                          data-gid="<?php echo $app['group_id']; ?>"
                          data-name="<?php echo htmlspecialchars($app['fullname'], ENT_QUOTES); ?>">
                    <i class="ph ph-x"></i> Reddet
                  </button>
                  <a href="teacher/group-request.php?id=<?php echo $app['group_id']; ?>" class="glr-btn glr-btn-link"><i class="ph ph-arrow-square-out"></i></a>
                <?php elseif ($tab === 'approved'): ?>
                  <span class="glr-status-badge badge-ok"><i class="ph ph-check-circle"></i> Onaylandi</span>
                  <a href="teacher/group-request.php?id=<?php echo $app['group_id']; ?>&tab=approved" class="glr-btn glr-btn-link"><i class="ph ph-arrow-square-out"></i></a>
                <?php else: ?>
                  <span class="glr-status-badge badge-no"><i class="ph ph-x-circle"></i> Reddedildi</span>
                  <a href="teacher/group-request.php?id=<?php echo $app['group_id']; ?>&tab=rejected" class="glr-btn glr-btn-link"><i class="ph ph-arrow-square-out"></i></a>
                <?php endif; ?>
              </div>
            </div>
            <?php endforeach; ?>
          </div>
          <?php endif; ?>
        </div>
      </div>

      <form id="frm-approve" method="POST" action="teacher/group-lesson-requests.php?tab=<?php echo $tab; ?>" style="display:none;">
        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
        <input type="hidden" name="quota_id" id="fld-qid-ok">
        <input type="hidden" name="group_id" id="fld-gid-ok">
        <input type="hidden" name="action"   value="approve">
      </form>
      <form id="frm-reject" method="POST" action="teacher/group-lesson-requests.php?tab=<?php echo $tab; ?>" style="display:none;">
        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
        <input type="hidden" name="quota_id" id="fld-qid-no">
        <input type="hidden" name="group_id" id="fld-gid-no">
        <input type="hidden" name="action"   value="reject">
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
        html: `<b>${this.dataset.name}</b> adl\u0131 \u00f6\u011frenciyi <b>${this.dataset.group}</b> grubuna onaylamak istiyor musunuz?`,
        showCancelButton: true, confirmButtonText: 'Onayla', cancelButtonText: 'Vazge\u00e7',
        confirmButtonColor: '#059669'
      }).then(r => {
        if (r.isConfirmed) {
          document.getElementById('fld-qid-ok').value = this.dataset.quota;
          document.getElementById('fld-gid-ok').value = this.dataset.gid;
          document.getElementById('frm-approve').submit();
        }
      });
    });
  });
  document.querySelectorAll('.swal-reject').forEach(btn => {
    btn.addEventListener('click', function () {
      Swal.fire({
        title: 'Reddet', icon: 'warning',
        text: `${this.dataset.name} adl\u0131 \u00f6\u011frencinin ba\u015fvurusunu reddetmek istedi\u011finize emin misiniz?`,
        showCancelButton: true, confirmButtonText: 'Reddet', cancelButtonText: 'Vazge\u00e7',
        confirmButtonColor: '#dc2626'
      }).then(r => {
        if (r.isConfirmed) {
          document.getElementById('fld-qid-no').value = this.dataset.quota;
          document.getElementById('fld-gid-no').value = this.dataset.gid;
          document.getElementById('frm-reject').submit();
        }
      });
    });
  });
  </script>
</body>
</html>
