<?php
ini_set('session.cookie_path', '/');
ini_set('session.cookie_domain', '');
session_start();
date_default_timezone_set('Europe/Istanbul');
include("../config/connection.php");
checkUnSession();

$sid = (int)$_SESSION["user_id"];

$guncelQ = $db->prepare("
    SELECT a.*, l.title AS lit, l.level AS leslevel, ut.fullname AS teacher_name
    FROM appointment a
    INNER JOIN lessons l  ON a.lesson_id  = l.id
    INNER JOIN users   ut ON a.teacher_id = ut.id
    WHERE (
        a.student_id = :sid
        OR (a.type = 1 AND a.group_id IN (
            SELECT group_id FROM groups_quota WHERE user_id = :sid2 AND status = 1
        ))
    )
    AND a.end_date > DATE_SUB(NOW(), INTERVAL 1 HOUR)
    ORDER BY a.start_date ASC
");
$guncelQ->execute([':sid' => $sid, ':sid2' => $sid]);
$guncelRows = $guncelQ->fetchAll(PDO::FETCH_ASSOC);

$gecmisQ = $db->prepare("
    SELECT a.*, l.title AS lit, l.level AS leslevel, ut.fullname AS teacher_name
    FROM appointment a
    INNER JOIN lessons l  ON a.lesson_id  = l.id
    INNER JOIN users   ut ON a.teacher_id = ut.id
    WHERE (
        a.student_id = :sid
        OR (a.type = 1 AND a.group_id IN (
            SELECT group_id FROM groups_quota WHERE user_id = :sid2 AND status = 1
        ))
    )
    AND a.end_date <= DATE_SUB(NOW(), INTERVAL 1 HOUR)
    ORDER BY a.start_date DESC
");
$gecmisQ->execute([':sid' => $sid, ':sid2' => $sid]);
$gecmisRows = $gecmisQ->fetchAll(PDO::FETCH_ASSOC);

$groupNameCache = [];
function getGroupName($db, $gid, &$cache) {
    if (!$gid) return '';
    if (!isset($cache[$gid])) {
        $s = $db->prepare('SELECT title FROM `groups` WHERE id = ?');
        $s->execute([$gid]);
        $cache[$gid] = (string)($s->fetchColumn() ?: '');
    }
    return $cache[$gid];
}

function studentJoinUrl($row) {
    if ($row['type'] == 1) {
        $key = !empty($row['room_id']) ? $row['room_id'] : ($row['group_time_id'] ?? '');
        return $key ? 'https://evoegitim.com/rooms/call.php?roomid=' . urlencode($key) : null;
    }
    return !empty($row['zoom_link']) ? $row['zoom_link'] : null;
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
  <?php include "../includes_panel/meta.php"; ?>
  <title>Derslerim | Evo Eğitim</title>
  <style>
    .apts-tabs{display:flex;gap:8px;flex-wrap:wrap;margin-bottom:0;}
    .apts-tab{display:inline-flex;align-items:center;gap:8px;padding:9px 20px;border-radius:10px;
      border:1.5px solid #e2e8f0;background:#fff;font-size:14px;font-weight:600;
      color:#64748b;cursor:pointer;transition:all .18s;}
    .apts-tab.is-active{background:#6366f1;border-color:#6366f1;color:#fff;}
    .apts-tab .cnt{background:rgba(0,0,0,.08);border-radius:20px;padding:0 8px;font-size:12px;}
    .apts-tab.is-active .cnt{background:rgba(255,255,255,.22);}

    .lcard{display:flex;align-items:stretch;border-radius:14px;border:1px solid #e9edf3;
      background:#fff;margin-bottom:10px;overflow:hidden;
      transition:box-shadow .2s,transform .2s;}
    .lcard:hover{box-shadow:0 6px 24px rgba(0,0,0,.08);transform:translateY(-1px);}
    .lcard-stripe{width:5px;flex-shrink:0;}
    .lcard.solo  .lcard-stripe{background:#6366f1;}
    .lcard.group .lcard-stripe{background:#10b981;}
    .lcard-icon{width:48px;flex-shrink:0;display:flex;align-items:center;
      justify-content:center;font-size:22px;}
    .lcard.solo  .lcard-icon{color:#6366f1;}
    .lcard.group .lcard-icon{color:#10b981;}
    .lcard-body{flex:1;display:grid;
      grid-template-columns:2fr 1.3fr 1.5fr 1.1fr auto;
      align-items:center;padding:14px 16px;gap:0;min-width:0;}
    @media(max-width:991px){
      .lcard-body{grid-template-columns:1fr 1fr;row-gap:10px;}
      .lcard-action{grid-column:1/-1;justify-content:flex-start;}
    }
    @media(max-width:575px){
      .lcard-body{grid-template-columns:1fr;}
      .lcard-icon{display:none;}
    }
    .lf{padding:0 10px;min-width:0;}
    .lf-lbl{font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.5px;
      color:#94a3b8;margin-bottom:2px;}
    .lf-val{font-size:14px;font-weight:600;color:#1e293b;white-space:nowrap;
      overflow:hidden;text-overflow:ellipsis;}
    .lf-sub{font-size:12px;color:#64748b;margin-top:1px;}

    .tpill{display:inline-flex;align-items:center;gap:4px;font-size:12px;
      font-weight:700;padding:3px 10px;border-radius:20px;white-space:nowrap;}
    .tpill.solo {background:#ede9fe;color:#6366f1;}
    .tpill.group{background:#d1fae5;color:#059669;}

    .spill{display:inline-flex;align-items:center;gap:5px;font-size:12px;
      font-weight:600;padding:4px 10px;border-radius:20px;white-space:nowrap;}
    .spill-plan    {background:#dbeafe;color:#1d4ed8;}
    .spill-active  {background:#d1fae5;color:#065f46;}
    .spill-done    {background:#f0fdf4;color:#166534;}
    .spill-cancel  {background:#fef2f2;color:#b91c1c;}
    .spill-post    {background:#fff7ed;color:#c2410c;}
    .spill-starting{background:#fef9c3;color:#854d0e;}
    @keyframes blink{0%,100%{opacity:1}50%{opacity:.55}}
    .spill-active{animation:blink 2s infinite;}

    .btn-join-solo{display:inline-flex;align-items:center;gap:6px;padding:8px 16px;
      border-radius:10px;border:none;cursor:pointer;font-size:13px;font-weight:700;
      background:#6366f1;color:#fff;text-decoration:none;white-space:nowrap;
      transition:opacity .18s;}
    .btn-join-group{display:inline-flex;align-items:center;gap:6px;padding:8px 16px;
      border-radius:10px;border:none;cursor:pointer;font-size:13px;font-weight:700;
      background:#10b981;color:#fff;text-decoration:none;white-space:nowrap;
      transition:opacity .18s;}
    .btn-join-solo:hover,.btn-join-group:hover{opacity:.85;color:#fff;}
    .btn-cd{display:inline-flex;align-items:center;gap:6px;padding:8px 14px;
      border-radius:10px;border:1.5px solid #e2e8f0;background:#f8fafc;
      color:#64748b;font-size:12px;font-weight:600;cursor:default;white-space:nowrap;}
    .btn-noroom{display:inline-flex;align-items:center;gap:5px;padding:8px 12px;
      border-radius:10px;border:1.5px solid #fecaca;background:#fef2f2;
      color:#b91c1c;font-size:12px;cursor:default;white-space:nowrap;}

    .day-sep{display:flex;align-items:center;gap:10px;font-size:11px;font-weight:700;
      text-transform:uppercase;letter-spacing:.7px;color:#94a3b8;margin:18px 0 8px;}
    .day-sep::after{content:'';flex:1;height:1px;background:#f1f5f9;}

    .empty-st{text-align:center;padding:56px 20px;}
    .empty-st i{font-size:52px;color:#e2e8f0;display:block;margin-bottom:14px;}
    .empty-st p{color:#94a3b8;font-size:15px;margin:0;}

    .gfilter{display:flex;align-items:center;gap:6px;flex-wrap:wrap;margin-bottom:14px;}
    .gf-btn{padding:5px 14px;border-radius:20px;border:1.5px solid #e2e8f0;
      background:#fff;font-size:12px;font-weight:600;color:#64748b;cursor:pointer;transition:all .15s;}
    .gf-btn.fa{background:#6366f1;border-color:#6366f1;color:#fff;}
    .gf-btn.fg{background:#10b981;border-color:#10b981;color:#fff;}
    .lcard-action{display:flex;align-items:center;justify-content:flex-end;
      padding-left:10px;padding-right:4px;}
  </style>
</head>
<body>
  <?php include 'includes/left-menu.php'; ?>
  <div class="dashboard-main-wrapper">
    <?php include 'includes/top-menu.php'; ?>
    <div class="dashboard-body">
      <div class="card mt-24">

        <div class="card-header border-bottom pb-24">
          <h4 class="mb-6">Derslerim</h4>
          <p class="text-gray-600 text-15 mb-20">Tüm solo ve grup dersleriniz burada listelenir.</p>
          <div class="d-flex align-items-center gap-16 mb-20">
            <span class="tpill solo"><i class="ph ph-user"></i> Solo Ders</span>
            <span class="tpill group"><i class="ph ph-users-three"></i> Grup Ders</span>
          </div>
          <div class="apts-tabs">
            <button class="apts-tab is-active" data-tab="guncel">
              <i class="ph ph-calendar-check"></i> Güncel Dersler
              <span class="cnt"><?= count($guncelRows) ?></span>
            </button>
            <button class="apts-tab" data-tab="gecmis">
              <i class="ph ph-clock-counter-clockwise"></i> Geçmiş Dersler
              <span class="cnt"><?= count($gecmisRows) ?></span>
            </button>
          </div>
        </div>

        <div class="card-body py-20 px-24">

          <!-- GÜNCEL -->
          <div id="tab-guncel">
            <?php if (empty($guncelRows)): ?>
              <div class="empty-st">
                <i class="ph ph-calendar-blank"></i>
                <p>Aktif ya da yaklaşan dersiniz bulunmuyor.</p>
              </div>
            <?php else:
              $now = new DateTime();
              $shownDates = [];
              foreach ($guncelRows as $row):
                $isGroup  = ($row['type'] == 1);
                $cls      = $isGroup ? 'group' : 'solo';
                $startDt  = new DateTime($row['start_date']);
                $endDt    = new DateTime($row['end_date']);
                $secsLeft = $startDt->getTimestamp() - $now->getTimestamp();
                $active   = ($now >= $startDt && $now <= $endDt);
                $joinUrl  = studentJoinUrl($row);

                $dk = $startDt->format('Y-m-d');
                if (!in_array($dk, $shownDates)):
                  $shownDates[] = $dk;
                  $dayLbl = function_exists('turkcetarih')
                    ? turkcetarih('j F Y, l', $row['start_date'])
                    : $startDt->format('d.m.Y');
            ?>
                  <div class="day-sep"><?= htmlspecialchars($dayLbl) ?></div>
            <?php endif; ?>

            <div class="lcard <?= $cls ?>">
              <div class="lcard-stripe"></div>
              <div class="lcard-icon"><i class="ph <?= $isGroup ? 'ph-users-three' : 'ph-user' ?>"></i></div>
              <div class="lcard-body">

                <div class="lf">
                  <div class="lf-lbl">Ders</div>
                  <div class="lf-val"><?= htmlspecialchars($row['lit']) ?></div>
                  <div class="lf-sub"><?= function_exists('getLevel') ? getLevel($row['leslevel']) : htmlspecialchars($row['leslevel']) ?></div>
                </div>

                <div class="lf">
                  <div class="lf-lbl">Tür</div>
                  <?php if ($isGroup):
                    $gn = getGroupName($db, $row['group_id'], $groupNameCache); ?>
                    <span class="tpill group"><i class="ph ph-users-three"></i> Grup</span>
                    <?php if ($gn): ?><div class="lf-sub"><?= htmlspecialchars($gn) ?></div><?php endif; ?>
                  <?php else: ?>
                    <span class="tpill solo"><i class="ph ph-user"></i> Solo</span>
                    <?php if ($row['first_lesson'] == 0): ?>
                      <div class="lf-sub" style="color:#6366f1;font-weight:700;">✦ İlk Ders</div>
                    <?php endif; ?>
                  <?php endif; ?>
                </div>

                <div class="lf">
                  <div class="lf-lbl">Öğretmen</div>
                  <div class="lf-val"><?= htmlspecialchars($row['teacher_name']) ?></div>
                  <div class="lf-sub"><?= $startDt->format('d.m.Y') ?></div>
                </div>

                <div class="lf">
                  <div class="lf-lbl">Saat</div>
                  <div class="lf-val"><?= $startDt->format('H:i') ?> – <?= $endDt->format('H:i') ?></div>
                  <?php
                  if ($active)              echo '<span class="spill spill-active"><i class="ph ph-circle-wavy-check"></i> Devam Ediyor</span>';
                  elseif ($secsLeft<=300)   echo '<span class="spill spill-starting"><i class="ph ph-bell-ringing"></i> Başlıyor</span>';
                  elseif ($row['revise']>0) echo '<span class="spill spill-post"><i class="ph ph-clock"></i> Ertelendi</span>';
                  else                      echo '<span class="spill spill-plan"><i class="ph ph-calendar"></i> Planlandı</span>';
                  ?>
                </div>

                <div class="lf lcard-action">
                  <?php if ($row['status'] == 0 && $joinUrl && ($active || $secsLeft <= 300)): ?>
                    <a href="<?= htmlspecialchars($joinUrl) ?>" target="_blank"
                       class="btn-join-<?= $cls ?>">
                      <i class="ph ph-video-camera"></i> Derse Git
                    </a>
                  <?php elseif ($row['status'] == 0 && $joinUrl): ?>
                    <span class="btn-cd"
                          data-open-at="<?= date('Y-m-d H:i:s', $startDt->getTimestamp() - 300) ?>"
                          data-href="<?= htmlspecialchars($joinUrl) ?>"
                          data-cls="<?= $cls ?>">
                      <i class="ph ph-timer"></i>
                      <span class="cd-txt">—</span>
                    </span>
                  <?php elseif ($row['status'] == 0): ?>
                    <span class="btn-noroom"><i class="ph ph-warning"></i> Oda Yok</span>
                  <?php endif; ?>
                </div>

              </div>
            </div>
            <?php endforeach; endif; ?>
          </div>

          <!-- GEÇMİŞ -->
          <div id="tab-gecmis" style="display:none;">
            <div class="gfilter">
              <span style="font-size:13px;color:#64748b;font-weight:600;">Filtre:</span>
              <button class="gf-btn fa" data-gf="all">Tümü</button>
              <button class="gf-btn" data-gf="solo">Solo</button>
              <button class="gf-btn" data-gf="group">Grup</button>
            </div>
            <?php if (empty($gecmisRows)): ?>
              <div class="empty-st">
                <i class="ph ph-books"></i>
                <p>Henüz tamamlanan dersiniz yok.</p>
              </div>
            <?php else:
              foreach ($gecmisRows as $row):
                $isGroup  = ($row['type'] == 1);
                $cls      = $isGroup ? 'group' : 'solo';
                $startDt  = new DateTime($row['start_date']);
                $endDt    = new DateTime($row['end_date']);
                if      ($row['status']==1) $sb='<span class="spill spill-done"><i class="ph ph-check-circle"></i> Tamamlandı</span>';
                elseif  ($row['status']==2) $sb='<span class="spill spill-cancel"><i class="ph ph-x-circle"></i> Tamamlanamadı</span>';
                elseif  ($row['status']==3) $sb='<span class="spill spill-cancel"><i class="ph ph-x-circle"></i> İptal Edildi</span>';
                else                        $sb='<span class="spill spill-done"><i class="ph ph-check-circle"></i> Sona Erdi</span>';
            ?>
            <div class="lcard <?= $cls ?> past-lcard" data-type="<?= $cls ?>" style="opacity:.82;">
              <div class="lcard-stripe"></div>
              <div class="lcard-icon" style="opacity:.5;"><i class="ph <?= $isGroup ? 'ph-users-three' : 'ph-user' ?>"></i></div>
              <div class="lcard-body">
                <div class="lf">
                  <div class="lf-lbl">Ders</div>
                  <div class="lf-val"><?= htmlspecialchars($row['lit']) ?></div>
                  <div class="lf-sub"><?= function_exists('getLevel') ? getLevel($row['leslevel']) : htmlspecialchars($row['leslevel']) ?></div>
                </div>
                <div class="lf">
                  <div class="lf-lbl">Tür</div>
                  <?php if ($isGroup):
                    $gn = getGroupName($db, $row['group_id'], $groupNameCache); ?>
                    <span class="tpill group"><i class="ph ph-users-three"></i> Grup</span>
                    <?php if ($gn): ?><div class="lf-sub"><?= htmlspecialchars($gn) ?></div><?php endif; ?>
                  <?php else: ?>
                    <span class="tpill solo"><i class="ph ph-user"></i> Solo</span>
                  <?php endif; ?>
                </div>
                <div class="lf">
                  <div class="lf-lbl">Öğretmen</div>
                  <div class="lf-val"><?= htmlspecialchars($row['teacher_name']) ?></div>
                  <div class="lf-sub"><?= $startDt->format('d.m.Y') ?></div>
                </div>
                <div class="lf">
                  <div class="lf-lbl">Saat</div>
                  <div class="lf-val"><?= $startDt->format('H:i') ?> – <?= $endDt->format('H:i') ?></div>
                </div>
                <div class="lf lcard-action"><?= $sb ?></div>
              </div>
            </div>
            <?php endforeach; endif; ?>
          </div>

        </div>
      </div>
    </div>
    <?php include '../includes_panel/footer.php'; ?>
  </div>

  <?php include '../includes_panel/scripts.php'; ?><?php include 'includes/student-scripts.php'; ?>
  <script>
  document.querySelectorAll('.apts-tab').forEach(btn => {
    btn.addEventListener('click', function() {
      document.querySelectorAll('.apts-tab').forEach(b => b.classList.remove('is-active'));
      this.classList.add('is-active');
      const t = this.dataset.tab;
      document.getElementById('tab-guncel').style.display = t === 'guncel' ? '' : 'none';
      document.getElementById('tab-gecmis').style.display = t === 'gecmis' ? '' : 'none';
    });
  });

  document.querySelectorAll('.gf-btn').forEach(btn => {
    btn.addEventListener('click', function() {
      document.querySelectorAll('.gf-btn').forEach(b => { b.classList.remove('fa','fg'); });
      const f = this.dataset.gf;
      this.classList.add(f === 'group' ? 'fg' : 'fa');
      document.querySelectorAll('.past-lcard').forEach(c => {
        c.style.display = (f === 'all' || c.dataset.type === f) ? '' : 'none';
      });
    });
  });

  function tickCountdowns() {
    const now = Date.now();
    document.querySelectorAll('.btn-cd').forEach(el => {
      const openAt = new Date(el.dataset.openAt.replace(' ','T')).getTime();
      const diff   = openAt - now;
      if (diff <= 0) {
        const cls = el.dataset.cls;
        const a = document.createElement('a');
        a.href      = el.dataset.href;
        a.target    = '_blank';
        a.className = 'btn-join-' + cls;
        a.innerHTML = '<i class="ph ph-video-camera"></i> Derse Git';
        el.replaceWith(a);
      } else {
        const h = Math.floor(diff / 3600000);
        const m = Math.floor((diff % 3600000) / 60000);
        const s = Math.floor((diff % 60000) / 1000);
        el.querySelector('.cd-txt').textContent = h > 0
          ? h + 's ' + String(m).padStart(2,'0') + 'dk'
          : String(m).padStart(2,'0') + ':' + String(s).padStart(2,'0');
      }
    });
  }
  tickCountdowns();
  setInterval(tickCountdowns, 1000);
  </script>
</body>
</html>
