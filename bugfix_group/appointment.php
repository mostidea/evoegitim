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
    :root{
      --ap:  #7c3aed; --ap-s:#f5f3ff; --ap-d:#6d28d9;
      --ag:  #10b981; --ag-s:#ecfdf5;
      --ab:  #2563eb; --ab-s:#eff6ff;
      --ar:  #ef4444; --ar-s:#fef2f2;
      --am:  #f59e0b; --am-s:#fffbeb;
      --tx:  #0f172a; --mu: #64748b; --lt: #94a3b8;
      --br:  #e8edf5; --bg: #f4f6fb; --card:#fff;
      --rad: 14px;    --shd:0 2px 16px rgba(0,0,0,.06);
    }

    /* Page shell */
    .apts-page{background:var(--bg);min-height:100vh;padding:1.75rem;}

    /* Page header */
    .apts-hdr{
      display:flex;align-items:flex-start;justify-content:space-between;
      flex-wrap:wrap;gap:1rem;
      background:linear-gradient(135deg,#4c1d95 0%,#7c3aed 55%,#6366f1 100%);
      border-radius:20px;padding:1.75rem 2rem;margin-bottom:1.5rem;
      box-shadow:0 8px 32px rgba(124,58,237,.22);
    }
    .apts-hdr-left h1{font-size:1.35rem;font-weight:800;color:#fff;margin:0 0 .3rem;}
    .apts-hdr-left p {font-size:.82rem;color:rgba(255,255,255,.72);margin:0;}
    .apts-hdr-pills{display:flex;gap:.5rem;align-items:center;flex-wrap:wrap;}
    .apts-hdr-pill{
      display:inline-flex;align-items:center;gap:.3rem;
      padding:.35rem .8rem;border-radius:20px;
      font-size:.72rem;font-weight:700;
      background:rgba(255,255,255,.15);color:#fff;border:1px solid rgba(255,255,255,.2);
    }

    /* Tabs */
    .apts-tabs{
      display:flex;gap:.5rem;flex-wrap:wrap;
      background:#fff;border-radius:12px;border:1px solid var(--br);
      padding:.35rem;margin-bottom:1.25rem;
      box-shadow:var(--shd);
    }
    .apts-tab{
      display:inline-flex;align-items:center;gap:.5rem;
      padding:.55rem 1.1rem;border-radius:9px;
      border:none;background:transparent;
      font-size:.82rem;font-weight:700;color:var(--mu);
      cursor:pointer;transition:all .18s;white-space:nowrap;
    }
    .apts-tab:hover{background:var(--ap-s);color:var(--ap);}
    .apts-tab.is-active{background:var(--ap);color:#fff;box-shadow:0 4px 12px rgba(124,58,237,.3);}
    .apts-tab .cnt{
      background:rgba(0,0,0,.1);border-radius:20px;
      padding:1px 7px;font-size:.7rem;line-height:1.6;
    }
    .apts-tab.is-active .cnt{background:rgba(255,255,255,.22);}

    /* Day separator */
    .day-sep{
      display:flex;align-items:center;gap:.75rem;
      font-size:.68rem;font-weight:800;
      text-transform:uppercase;letter-spacing:.08em;
      color:var(--ap);margin:1.25rem 0 .6rem;
    }
    .day-sep::before{content:'';width:4px;height:14px;background:var(--ap);border-radius:4px;}
    .day-sep::after {content:'';flex:1;height:1px;background:var(--br);}

    /* Lesson card */
    .lcard{
      display:flex;align-items:stretch;
      border-radius:var(--rad);border:1px solid var(--br);
      background:var(--card);margin-bottom:.6rem;overflow:hidden;
      transition:box-shadow .2s,transform .2s,border-color .2s;
    }
    .lcard:hover{box-shadow:0 6px 28px rgba(0,0,0,.09);transform:translateY(-2px);border-color:var(--ap-s);}
    .lcard-stripe{width:4px;flex-shrink:0;}
    .lcard.solo  .lcard-stripe{background:linear-gradient(180deg,#7c3aed,#6366f1);}
    .lcard.group .lcard-stripe{background:linear-gradient(180deg,#059669,#10b981);}
    .lcard-icon{
      width:52px;flex-shrink:0;display:flex;align-items:center;
      justify-content:center;font-size:1.3rem;
    }
    .lcard.solo  .lcard-icon{color:var(--ap);}
    .lcard.group .lcard-icon{color:var(--ag);}
    .lcard-body{
      flex:1;display:grid;
      grid-template-columns:2.2fr 1.2fr 1.5fr 1.1fr auto;
      align-items:center;padding:1rem 1.1rem;gap:0;min-width:0;
    }
    @media(max-width:991px){
      .lcard-body{grid-template-columns:1fr 1fr;row-gap:.75rem;}
      .lcard-action{grid-column:1/-1;justify-content:flex-start;}
    }
    @media(max-width:767px){
      .lcard-icon{display:none;}
    }
    @media(max-width:575px){
      .lcard-body{grid-template-columns:1fr;}
    }
    .lf{padding:0 .8rem;min-width:0;}
    .lf-lbl{
      font-size:.6rem;font-weight:800;text-transform:uppercase;letter-spacing:.07em;
      color:var(--lt);margin-bottom:.2rem;
    }
    .lf-val{font-size:.88rem;font-weight:700;color:var(--tx);overflow:hidden;text-overflow:ellipsis;white-space:nowrap;}
    .lf-sub{font-size:.75rem;color:var(--mu);margin-top:.15rem;}

    /* Type pill */
    .tpill{display:inline-flex;align-items:center;gap:4px;font-size:.72rem;font-weight:700;padding:.25rem .65rem;border-radius:20px;white-space:nowrap;}
    .tpill.solo {background:var(--ap-s);color:var(--ap);}
    .tpill.group{background:var(--ag-s);color:#059669;}

    /* Status pill */
    .spill{display:inline-flex;align-items:center;gap:5px;font-size:.72rem;font-weight:700;padding:.28rem .7rem;border-radius:20px;white-space:nowrap;}
    .spill-plan    {background:#dbeafe;color:#1e40af;}
    .spill-done    {background:var(--ag-s);color:#065f46;}
    .spill-cancel  {background:var(--ar-s);color:#991b1b;}
    .spill-post    {background:#fff7ed;color:#9a3412;}
    .spill-starting{background:#fef9c3;color:#78350f;}
    .spill-active  {background:#d1fae5;color:#065f46;box-shadow:0 0 0 2px rgba(16,185,129,.25);}
    @keyframes blink{0%,100%{opacity:1}50%{opacity:.6}}
    .spill-active{animation:blink 1.8s ease-in-out infinite;}

    /* Buttons */
    .btn-join-solo,
    .btn-join-group{
      display:inline-flex;align-items:center;gap:.4rem;
      padding:.5rem 1.1rem;border-radius:10px;border:none;
      font-size:.8rem;font-weight:700;text-decoration:none;white-space:nowrap;
      cursor:pointer;transition:transform .15s,box-shadow .15s,opacity .15s;
      min-height:38px;
    }
    .btn-join-solo {background:linear-gradient(135deg,var(--ap-d),var(--ap));color:#fff;box-shadow:0 3px 12px rgba(124,58,237,.3);}
    .btn-join-group{background:linear-gradient(135deg,#059669,var(--ag));color:#fff;box-shadow:0 3px 12px rgba(16,185,129,.28);}
    .btn-join-solo:hover {transform:translateY(-1px);box-shadow:0 6px 18px rgba(124,58,237,.35);color:#fff;opacity:1;}
    .btn-join-group:hover{transform:translateY(-1px);box-shadow:0 6px 18px rgba(16,185,129,.32);color:#fff;opacity:1;}
    .btn-cd{
      display:inline-flex;align-items:center;gap:.4rem;
      padding:.5rem .85rem;border-radius:10px;
      border:1.5px solid var(--br);background:#f8fafc;
      color:var(--mu);font-size:.76rem;font-weight:700;
      cursor:default;white-space:nowrap;min-height:38px;
      font-variant-numeric:tabular-nums;
    }
    .btn-noroom{
      display:inline-flex;align-items:center;gap:.35rem;
      padding:.5rem .85rem;border-radius:10px;
      border:1.5px solid #fecaca;background:var(--ar-s);
      color:#991b1b;font-size:.76rem;font-weight:600;cursor:default;
      white-space:nowrap;min-height:38px;
    }

    /* Filter bar */
    .gfilter{display:flex;align-items:center;gap:.5rem;flex-wrap:wrap;margin-bottom:1rem;}
    .gf-lbl{font-size:.78rem;color:var(--mu);font-weight:700;}
    .gf-btn{
      padding:.3rem .9rem;border-radius:20px;
      border:1.5px solid var(--br);background:#fff;
      font-size:.75rem;font-weight:700;color:var(--mu);
      cursor:pointer;transition:all .15s;
    }
    .gf-btn:hover{border-color:var(--ap);color:var(--ap);}
    .gf-btn.fa{background:var(--ap);border-color:var(--ap);color:#fff;}
    .gf-btn.fg{background:var(--ag);border-color:var(--ag);color:#fff;}

    .lcard-action{display:flex;align-items:center;justify-content:flex-end;padding:0 .5rem 0 .75rem;}

    /* Past card */
    .past-lcard{opacity:.75;transition:opacity .2s;}
    .past-lcard:hover{opacity:1;}

    /* Empty state */
    .empty-st{text-align:center;padding:4rem 1.5rem;}
    .empty-st i{font-size:3rem;color:#dde3ee;display:block;margin-bottom:.85rem;}
    .empty-st p{color:var(--lt);font-size:.92rem;margin:0;line-height:1.6;}
  </style>
</head>
<body>
  <?php include 'includes/left-menu.php'; ?>
  <div class="dashboard-main-wrapper">
    <?php include 'includes/top-menu.php'; ?>
    <div class="dashboard-body apts-page">

      <!-- Page header -->
      <div class="apts-hdr">
        <div class="apts-hdr-left">
          <h1><i class="ph ph-book-open" style="vertical-align:middle;margin-right:.4rem;"></i>Derslerim</h1>
          <p>Tüm solo ve grup dersleriniz burada listelenir.</p>
        </div>
        <div class="apts-hdr-pills">
          <span class="apts-hdr-pill"><i class="ph ph-user"></i> Solo Ders</span>
          <span class="apts-hdr-pill"><i class="ph ph-users-three"></i> Grup Ders</span>
        </div>
      </div>

      <!-- Tabs -->
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

      <div style="background:var(--card);border-radius:16px;border:1px solid var(--br);box-shadow:var(--shd);padding:1.25rem 1.5rem;">

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
    </div><!-- /.apts-page -->
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
