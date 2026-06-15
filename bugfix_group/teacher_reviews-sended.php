<?php
session_start();
include("../config/connection.php");
checkUnSession();

$uid = (int)$_SESSION["user_id"];

$reviewsData = $db->prepare("
    SELECT reviews_received.*, users.fullname, lessons.title AS lit, lessons.level AS leslevel, users.level AS ulevel
    FROM reviews_received
    INNER JOIN users    ON reviews_received.student_id = users.id
    INNER JOIN lessons  ON reviews_received.lesson_id  = lessons.id
    WHERE reviews_received.teacher_id = ?
    ORDER BY reviews_received.created_at DESC
");
$reviewsData->execute([$uid]);
$rows = $reviewsData->fetchAll(PDO::FETCH_ASSOC);

$total    = count($rows);
$positive = 0;
foreach ($rows as $r) {
    if (in_array($r['lesson_rate'], ['İyi', 'Çok İyi', 'Evet'])) $positive++;
}
$negative = $total - $positive;

function rateBadge($rate) {
    $map = [
        'Çok İyi'  => ['#059669', '#d1fae5'],
        'İyi'      => ['#0891b2', '#e0f2fe'],
        'Kötü'     => ['#d97706', '#fef3c7'],
        'Çok Kötü' => ['#dc2626', '#fee2e2'],
        'Evet'     => ['#059669', '#d1fae5'],
        'Hayır'    => ['#dc2626', '#fee2e2'],
    ];
    [$c, $bg] = $map[$rate] ?? ['#6366f1', '#eef2ff'];
    return "<span class=\"evo-rate\" style=\"color:{$c};background:{$bg}\">" . htmlspecialchars($rate) . "</span>";
}

function initials($name) {
    $parts = explode(' ', trim($name));
    $i = strtoupper(mb_substr($parts[0], 0, 1));
    if (isset($parts[1])) $i .= strtoupper(mb_substr($parts[1], 0, 1));
    return $i;
}

$avatarColors = ['#2563eb','#7c3aed','#0891b2','#059669','#d97706','#db2777'];
?>
<!DOCTYPE html>
<html lang="tr">
<head>
  <?php include "../includes_panel/meta.php"; ?>
  <title>Gönderilen Değerlendirmeler | Evo Eğitim</title>
  <style>
    :root {
      --ev-purple: #7c3aed; --ev-purple-s: #f5f3ff;
      --ev-blue:   #2563eb; --ev-blue-s:   #eff6ff;
      --ev-green:  #059669; --ev-green-s:  #ecfdf5;
      --ev-red:    #dc2626; --ev-red-s:    #fef2f2;
      --ev-text:   #0f172a; --ev-muted:    #64748b;
      --ev-border: #e2e8f0; --ev-bg:       #f1f5f9;
      --ev-card:   #ffffff; --ev-radius:   16px;
      --ev-shadow: 0 2px 12px rgba(0,0,0,.06);
    }
    .evo-page { background: var(--ev-bg); min-height: 100vh; padding: 1.75rem; display: flex; flex-direction: column; gap: 1.25rem; }

    /* ── Page Header ── */
    .evo-page-header { display: flex; align-items: center; gap: 1rem; }
    .evo-page-icon { width: 48px; height: 48px; border-radius: 14px; background: var(--ev-blue-s); display: flex; align-items: center; justify-content: center; font-size: 1.4rem; color: var(--ev-blue); flex-shrink: 0; }
    .evo-page-title { font-size: 1.25rem; font-weight: 800; color: var(--ev-text); margin: 0; }
    .evo-page-sub   { font-size: .83rem; color: var(--ev-muted); margin: .15rem 0 0; }

    /* ── Stats Row ── */
    .evo-stats { display: grid; grid-template-columns: repeat(3,1fr); gap: 1rem; }
    .evo-stat-card { background: var(--ev-card); border-radius: var(--ev-radius); border: 1px solid var(--ev-border); box-shadow: var(--ev-shadow); padding: 1.25rem 1.5rem; display: flex; align-items: center; gap: 1rem; }
    .evo-stat-icon { width: 44px; height: 44px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 1.3rem; flex-shrink: 0; }
    .evo-stat-num  { font-size: 1.75rem; font-weight: 800; color: var(--ev-text); line-height: 1; }
    .evo-stat-lbl  { font-size: .75rem; color: var(--ev-muted); margin-top: .2rem; }

    /* ── Review Card ── */
    .evo-reviews { display: flex; flex-direction: column; gap: .85rem; }
    .evo-rc { background: var(--ev-card); border: 1px solid var(--ev-border); border-radius: var(--ev-radius); box-shadow: var(--ev-shadow); padding: 1.4rem 1.5rem; display: flex; flex-direction: column; gap: 1rem; transition: box-shadow .18s, transform .18s; }
    .evo-rc:hover { box-shadow: 0 6px 24px rgba(0,0,0,.1); transform: translateY(-2px); }

    .evo-rc-top { display: flex; align-items: center; gap: 1rem; flex-wrap: wrap; }
    .evo-avatar { width: 44px; height: 44px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: .82rem; font-weight: 800; color: #fff; flex-shrink: 0; }
    .evo-rc-info { flex: 1; min-width: 0; }
    .evo-rc-name { font-size: .95rem; font-weight: 700; color: var(--ev-text); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .evo-rc-sub  { font-size: .75rem; color: var(--ev-muted); margin-top: .1rem; }
    .evo-rc-lesson { text-align: right; flex-shrink: 0; }
    .evo-rc-lesson-name  { font-size: .88rem; font-weight: 700; color: var(--ev-blue); }
    .evo-rc-lesson-level { font-size: .72rem; color: var(--ev-muted); margin-top: .1rem; }
    .evo-rc-date { font-size: .72rem; color: var(--ev-muted); margin-top: .15rem; display: flex; align-items: center; gap: .25rem; justify-content: flex-end; }

    .evo-divider { border: none; border-top: 1px solid var(--ev-border); margin: 0; }

    .evo-rc-ratings { display: flex; gap: 2rem; flex-wrap: wrap; }
    .evo-rating-col { display: flex; flex-direction: column; gap: .35rem; }
    .evo-rating-q   { font-size: .7rem; color: var(--ev-muted); font-weight: 500; max-width: 160px; line-height: 1.35; }
    .evo-rate { display: inline-block; padding: .28rem .75rem; border-radius: 20px; font-size: .78rem; font-weight: 700; white-space: nowrap; }

    .evo-rc-note { display: flex; align-items: flex-start; gap: .55rem; background: #f8fafc; border-radius: 10px; padding: .75rem 1rem; }
    .evo-rc-note i { font-size: 1rem; color: var(--ev-blue); flex-shrink: 0; margin-top: .05rem; }
    .evo-rc-note span { font-size: .84rem; color: var(--ev-text); line-height: 1.55; }

    /* ── Empty ── */
    .evo-empty { background: var(--ev-card); border: 1px solid var(--ev-border); border-radius: var(--ev-radius); padding: 4rem 2rem; text-align: center; color: var(--ev-muted); }
    .evo-empty i { font-size: 3rem; display: block; margin-bottom: 1rem; color: #cbd5e1; }
    .evo-empty p { margin: 0; font-size: .9rem; }

    @media (max-width: 768px) {
      .evo-page   { padding: 1rem; }
      .evo-stats  { grid-template-columns: 1fr; }
      .evo-rc-top { flex-direction: column; align-items: flex-start; }
      .evo-rc-lesson { text-align: left; }
      .evo-rc-date   { justify-content: flex-start; }
      .evo-rc-ratings { gap: 1rem; }
    }
  </style>
</head>
<body>
  <?php include 'includes/left-menu.php'; ?>
  <div class="dashboard-main-wrapper">
    <?php include 'includes/top-menu.php'; ?>
    <div class="dashboard-body evo-page">

      <!-- Header -->
      <div class="evo-page-header">
        <div class="evo-page-icon"><i class="ph-fill ph-paper-plane-tilt"></i></div>
        <div>
          <h1 class="evo-page-title">Gönderilen Değerlendirmeler</h1>
          <p class="evo-page-sub">Öğrencileriniz için yaptığınız ders sonu değerlendirmeleri</p>
        </div>
      </div>

      <!-- Stats -->
      <div class="evo-stats">
        <div class="evo-stat-card">
          <div class="evo-stat-icon" style="background:#eff6ff;color:#2563eb;"><i class="ph-fill ph-paper-plane-tilt"></i></div>
          <div>
            <div class="evo-stat-num"><?php echo $total; ?></div>
            <div class="evo-stat-lbl">Toplam Değerlendirme</div>
          </div>
        </div>
        <div class="evo-stat-card">
          <div class="evo-stat-icon" style="background:#ecfdf5;color:#059669;"><i class="ph-fill ph-smiley"></i></div>
          <div>
            <div class="evo-stat-num"><?php echo $positive; ?></div>
            <div class="evo-stat-lbl">Olumlu Öğrenci</div>
          </div>
        </div>
        <div class="evo-stat-card">
          <div class="evo-stat-icon" style="background:#fef2f2;color:#dc2626;"><i class="ph-fill ph-trend-up"></i></div>
          <div>
            <div class="evo-stat-num"><?php echo $negative; ?></div>
            <div class="evo-stat-lbl">Gelişim Gerektiren</div>
          </div>
        </div>
      </div>

      <!-- Review Cards -->
      <?php if (empty($rows)): ?>
        <div class="evo-empty">
          <i class="ph ph-paper-plane-tilt"></i>
          <p>Henüz hiç değerlendirme göndermediniz.</p>
        </div>
      <?php else: ?>
      <div class="evo-reviews">
        <?php foreach ($rows as $i => $row):
          $color = $avatarColors[$i % count($avatarColors)];
          $ini   = initials($row['fullname']);
        ?>
        <div class="evo-rc">
          <div class="evo-rc-top">
            <div class="evo-avatar" style="background:<?php echo $color; ?>"><?php echo $ini; ?></div>
            <div class="evo-rc-info">
              <div class="evo-rc-name"><?php echo htmlspecialchars($row['fullname']); ?></div>
              <div class="evo-rc-sub"><?php echo htmlspecialchars($row['ulevel']); ?></div>
            </div>
            <div class="evo-rc-lesson">
              <div class="evo-rc-lesson-name"><?php echo htmlspecialchars($row['lit']); ?></div>
              <div class="evo-rc-lesson-level"><?php echo getLevel($row['leslevel']); ?></div>
              <div class="evo-rc-date"><i class="ph ph-calendar-blank"></i><?php echo turkcetarih('j F Y l', $row['lesson_date']); ?></div>
            </div>
          </div>

          <hr class="evo-divider">

          <div class="evo-rc-ratings">
            <div class="evo-rating-col">
              <div class="evo-rating-q">Bu derse katılımı nasıldı?</div>
              <?php echo rateBadge($row['lesson_rate']); ?>
            </div>
            <div class="evo-rating-col">
              <div class="evo-rating-q">Bu derse ne kadar hazırdı?</div>
              <?php echo rateBadge($row['ready_rate']); ?>
            </div>
            <div class="evo-rating-col">
              <div class="evo-rating-q">Dersin içeriğini anlama düzeyi?</div>
              <?php echo rateBadge($row['understand_rate']); ?>
            </div>
          </div>

          <?php if (!empty(trim($row['note']))): ?>
          <div class="evo-rc-note">
            <i class="ph ph-chat-circle-text"></i>
            <span><?php echo htmlspecialchars($row['note']); ?></span>
          </div>
          <?php endif; ?>
        </div>
        <?php endforeach; ?>
      </div>
      <?php endif; ?>

    </div>
    <?php include '../includes_panel/footer.php'; ?>
  </div>
  <?php include '../includes_panel/scripts.php'; ?>
  <?php include 'includes/teacher-scripts.php'; ?>
  <?php include 'includes/vbs-scripts.php'; ?>
</body>
</html>
