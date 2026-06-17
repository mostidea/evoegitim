<?php
ini_set('session.cookie_path', '/');
ini_set('session.cookie_domain', '');
session_start();
include("../config/connection.php");
checkUnSession();

$current_datetime = date("Y-m-d H:i:s");
$uid = (int)$_SESSION["user_id"];

// Tamamlanan: her ikisi de katıldı
$completedCourses = $db->prepare("SELECT * FROM appointment WHERE student_id=? AND teacher_join IS NOT NULL AND student_join IS NOT NULL AND end_date <= NOW() AND status != 3");
$completedCourses->execute([$uid]);
$completedCount = $completedCourses->rowcount();

// Tamamlanmayan: en az biri katılmadı, ders geçmiş
$notCompletedCourses = $db->prepare("SELECT * FROM appointment WHERE student_id=? AND (teacher_join IS NULL OR student_join IS NULL) AND end_date <= NOW() AND status NOT IN (0,3,4)");
$notCompletedCourses->execute([$uid]);
$notcompletedCount = $notCompletedCourses->rowcount();

// Yaklaşan: tüm tipler
$waitingCourses = $db->prepare("SELECT * FROM appointment WHERE student_id=? AND status=0 AND start_date > NOW()");
$waitingCourses->execute([$uid]);
$waitingCount = $waitingCourses->rowcount();

// Aktif öğretmen: iptal olmayan derslerden tekil öğretmen
$teacherCount = $db->prepare("SELECT * FROM appointment WHERE student_id=? AND status != 3 GROUP BY teacher_id");
$teacherCount->execute([$uid]);
$teachercount = $teacherCount->rowcount();

// Şu an aktif olan ders
$activeLessonQ = $db->prepare("SELECT appointment.*, users.fullname, lessons.title AS lit FROM appointment INNER JOIN users ON appointment.teacher_id=users.id INNER JOIN lessons ON appointment.lesson_id=lessons.id WHERE appointment.student_id=? AND appointment.start_date <= NOW() AND appointment.end_date >= NOW() AND appointment.status=0 LIMIT 1");
$activeLessonQ->execute([$uid]);
$activeLesson = $activeLessonQ->fetch(PDO::FETCH_ASSOC);

$totalCreditData = ['credit' => creditGetBalance($db, $uid, null)];

$totalBoughtCredit = $db->prepare("SELECT SUM(credit) AS credit FROM order_report WHERE user_id=? AND status=1 AND type IS NULL");
$totalBoughtCredit->execute([$uid]);
$totalBoughtCreditData = $totalBoughtCredit->fetch(PDO::FETCH_ASSOC);

$totalUsedCredit = $db->prepare("SELECT SUM(credit) AS credit FROM appointment WHERE student_id=? AND type IS NULL AND status NOT IN (3)");
$totalUsedCredit->execute([$uid]);
$totalUsedCreditData = $totalUsedCredit->fetch(PDO::FETCH_ASSOC);

$appointmentData = $db->prepare("
    SELECT appointment.*, users.fullname, lessons.title AS lit, lessons.level AS leslevel, users.profession, users.level FROM appointment INNER JOIN users ON appointment.teacher_id=users.id INNER JOIN lessons ON appointment.lesson_id=lessons.id
    WHERE student_id=? AND start_date > NOW() AND appointment.status != 3
    ORDER BY start_date ASC
");
$appointmentData->execute([$uid]);

$loginData = $db->prepare("
    SELECT order_report.*, package.title, package.description, package.details FROM order_report INNER JOIN package ON order_report.product_id=package.id
    WHERE user_id=? AND status=1
    AND order_report.expired_date > ?
    ORDER BY order_report.expired_date ASC
");
$loginData->execute([$uid, $current_datetime]);

// Grup Ders Kredileri
$totalCreditGroupData = ['credit' => creditGetBalance($db, $uid, 1)];

$totalBoughtCreditGroup = $db->prepare("SELECT SUM(credit) AS credit FROM order_report WHERE user_id=? AND status=1 AND type=1");
$totalBoughtCreditGroup->execute([$uid]);
$totalBoughtCreditGroupData = $totalBoughtCreditGroup->fetch(PDO::FETCH_ASSOC);

$totalUsedCreditGroup = $db->prepare("SELECT SUM(credit) AS credit FROM appointment WHERE student_id=? AND type=1 AND status NOT IN (3)");
$totalUsedCreditGroup->execute([$uid]);
$totalUsedCreditGroupData = $totalUsedCreditGroup->fetch(PDO::FETCH_ASSOC);

// Bildirim
$getNotific = $db->prepare("SELECT * FROM notification
    WHERE (receiver=:email OR receiver='0')
    AND created_at >= (SELECT created_at FROM users WHERE id=:user_id)
    ORDER BY id DESC");
$getNotific->bindParam(":email", $_SESSION["email"]);
$getNotific->bindParam(":user_id", $_SESSION["user_id"]);
$getNotific->execute();
$getNotificData = $getNotific->fetch(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="tr">
<head>
  <?php include "../includes_panel/meta.php"; ?>
  <title>Dashboard | Evo Eğitim</title>
  <style>
    /* ── CSS Custom Properties ──────────────────────────────── */
    :root {
      --evo-blue:        #2563eb;
      --evo-blue-soft:   #eff6ff;
      --evo-blue-grad:   linear-gradient(135deg,#1d4ed8 0%,#2563eb 55%,#3b82f6 100%);
      --evo-green:       #10b981;
      --evo-green-soft:  #ecfdf5;
      --evo-green-grad:  linear-gradient(135deg,#059669 0%,#10b981 60%,#34d399 100%);
      --evo-amber:       #f59e0b;
      --evo-amber-soft:  #fffbeb;
      --evo-red:         #ef4444;
      --evo-red-soft:    #fef2f2;
      --evo-purple:      #7c3aed;
      --evo-purple-soft: #f5f3ff;
      --evo-text:        #0f172a;
      --evo-muted:       #64748b;
      --evo-light:       #94a3b8;
      --evo-bg:          #f1f5f9;
      --evo-card:        #ffffff;
      --evo-border:      #e2e8f0;
      --evo-radius-sm:   10px;
      --evo-radius:      16px;
      --evo-radius-lg:   20px;
      --evo-shadow:      0 4px 20px rgba(0,0,0,.06);
      --evo-shadow-lg:   0 8px 32px rgba(0,0,0,.09);
    }

    /* ── Base ─────────────────────────────────────────────── */
    .evo-db {
      background: var(--evo-bg);
      min-height: 100vh;
      padding: 1.75rem;
      display: flex;
      flex-direction: column;
      gap: 1.25rem;
    }

    /* ── Shared card shell ───────────────────────────────── */
    .evo-card {
      background: var(--evo-card);
      border-radius: var(--evo-radius);
      border: 1px solid var(--evo-border);
      box-shadow: var(--evo-shadow);
      overflow: hidden;
    }
    .evo-card-head {
      padding: 1.1rem 1.5rem;
      border-bottom: 1px solid var(--evo-border);
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: .5rem;
    }
    .evo-card-head h5 {
      font-size: .97rem;
      font-weight: 700;
      color: var(--evo-text);
      margin: 0;
      display: flex;
      align-items: center;
      gap: .45rem;
    }
    .evo-card-head a {
      font-size: .78rem;
      color: var(--evo-blue);
      text-decoration: none;
      font-weight: 600;
      white-space: nowrap;
    }
    .evo-card-head a:hover { text-decoration: underline; }

    /* ── Hero Row ────────────────────────────────────────── */
    .evo-hero-row {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 1.1rem;
    }

    .evo-hero-card {
      border-radius: var(--evo-radius-lg);
      padding: 2rem 2rem 1.75rem;
      color: #fff;
      position: relative;
      overflow: hidden;
    }
    .evo-hero-card::before,
    .evo-hero-card::after {
      content: '';
      position: absolute;
      border-radius: 50%;
      pointer-events: none;
    }
    .evo-hero-card--blue {
      background: var(--evo-blue-grad);
      box-shadow: 0 8px 32px rgba(37,99,235,.28);
    }
    .evo-hero-card--blue::before { top:-50px; right:-50px; width:180px; height:180px; background:rgba(255,255,255,.08); }
    .evo-hero-card--blue::after  { bottom:-70px; left:-30px; width:220px; height:220px; background:rgba(255,255,255,.05); }
    .evo-hero-card--green {
      background: var(--evo-green-grad);
      box-shadow: 0 8px 32px rgba(16,185,129,.22);
    }
    .evo-hero-card--green::before { top:-40px; right:-40px; width:150px; height:150px; background:rgba(255,255,255,.1); }

    .evo-hero-card h2 {
      font-size: 1.4rem;
      font-weight: 800;
      margin: 0 0 .6rem;
      position: relative;
      z-index: 1;
      line-height: 1.3;
    }
    .evo-hero-card p {
      font-size: .88rem;
      opacity: .88;
      margin: 0;
      position: relative;
      z-index: 1;
      line-height: 1.65;
    }
    .evo-hero-card .evo-hero-icon {
      font-size: 1.5rem;
      vertical-align: middle;
      margin-right: .35rem;
    }

    /* ── Live Lesson Banner ──────────────────────────────── */
    .evo-live-banner {
      background: linear-gradient(135deg,#4f46e5 0%,#7c3aed 100%);
      border-radius: var(--evo-radius);
      padding: 1.25rem 1.75rem;
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 1rem;
      flex-wrap: wrap;
      box-shadow: 0 8px 32px rgba(124,58,237,.25);
    }
    .evo-live-pill {
      display: inline-flex;
      align-items: center;
      gap: .35rem;
      background: rgba(255,255,255,.18);
      color: #fff;
      font-size: .7rem;
      font-weight: 700;
      letter-spacing: .08em;
      text-transform: uppercase;
      padding: .22rem .7rem;
      border-radius: 20px;
      margin-bottom: .4rem;
    }
    .evo-live-dot {
      width: 7px; height: 7px;
      border-radius: 50%;
      background: #4ade80;
      animation: evoPulse 1.3s ease-in-out infinite;
    }
    @keyframes evoPulse {
      0%,100% { opacity:1; transform:scale(1); }
      50%      { opacity:.45; transform:scale(1.5); }
    }
    .evo-live-info h3 { color:#fff; font-size:1.05rem; font-weight:700; margin:0; }
    .evo-live-info p  { color:rgba(255,255,255,.8); font-size:.83rem; margin:.15rem 0 0; }
    .evo-live-btn {
      background: #fff;
      color: var(--evo-purple);
      font-weight: 700;
      font-size: .88rem;
      padding: .7rem 1.5rem;
      border-radius: var(--evo-radius-sm);
      text-decoration: none;
      display: inline-flex;
      align-items: center;
      gap: .4rem;
      transition: transform .15s, box-shadow .15s;
      white-space: nowrap;
      min-height: 44px;
    }
    .evo-live-btn:hover {
      transform: translateY(-2px);
      box-shadow: 0 6px 20px rgba(0,0,0,.18);
      color: var(--evo-purple);
    }

    /* ── Stats Row ───────────────────────────────────────── */
    .evo-stats-row {
      display: grid;
      grid-template-columns: repeat(4,1fr);
      gap: 1rem;
    }
    .evo-stat {
      background: var(--evo-card);
      border-radius: var(--evo-radius);
      border: 1px solid var(--evo-border);
      box-shadow: var(--evo-shadow);
      padding: 1.4rem 1.25rem 1.25rem;
      display: flex;
      flex-direction: column;
      gap: .3rem;
      transition: transform .2s, box-shadow .2s;
      cursor: default;
    }
    .evo-stat:hover { transform:translateY(-2px); box-shadow:var(--evo-shadow-lg); }
    .evo-stat-icon {
      width: 46px; height: 46px;
      border-radius: 12px;
      display: flex; align-items: center; justify-content: center;
      font-size: 1.35rem;
      margin-bottom: .5rem;
    }
    .evo-stat-num {
      font-size: 2.1rem;
      font-weight: 800;
      color: var(--evo-text);
      line-height: 1;
    }
    .evo-stat-lbl {
      font-size: .78rem;
      color: var(--evo-muted);
      font-weight: 500;
      margin-top: .1rem;
    }
    .evo-stat-chart { margin-top: .6rem; }

    /* ── Main + Sidebar Layout ───────────────────────────── */
    .evo-content-row {
      display: grid;
      grid-template-columns: 1fr 310px;
      gap: 1.25rem;
      align-items: start;
    }

    /* ── Appointment Table ───────────────────────────────── */
    .evo-tbl-wrap { overflow-x: auto; }
    .evo-tbl {
      width: 100%;
      border-collapse: collapse;
    }
    .evo-tbl thead th {
      background: #f8fafc;
      font-size: .72rem;
      font-weight: 700;
      color: var(--evo-muted);
      text-transform: uppercase;
      letter-spacing: .06em;
      padding: .8rem 1.3rem;
      border-bottom: 1px solid var(--evo-border);
      white-space: nowrap;
    }
    .evo-tbl tbody tr {
      border-bottom: 1px solid var(--evo-border);
      transition: background .12s;
    }
    .evo-tbl tbody tr:last-child { border-bottom: none; }
    .evo-tbl tbody tr:hover { background: #f8fafc; }
    .evo-tbl td { padding: 1rem 1.3rem; vertical-align: middle; }

    .evo-lesson-name { font-weight: 700; color: var(--evo-text); font-size: .9rem; }
    .evo-lesson-lvl  { font-size: .75rem; color: var(--evo-blue); font-weight: 600; margin-top:.1rem; }
    .evo-lesson-date { font-size: .76rem; color: var(--evo-muted); margin-top:.2rem; display:flex; align-items:center; gap:.25rem; }
    .evo-teacher-nm  { font-weight: 600; color: var(--evo-text); font-size: .88rem; }
    .evo-teacher-sub { font-size: .75rem; color: var(--evo-muted); margin-top:.1rem; }

    .evo-badge {
      display: inline-flex; align-items: center; gap: .3rem;
      padding: .3rem .8rem; border-radius: 20px;
      font-size: .74rem; font-weight: 700;
      white-space: nowrap;
    }
    .evo-badge-wait    { background:var(--evo-blue-soft);   color:var(--evo-blue);   }
    .evo-badge-done    { background:var(--evo-green-soft);  color:var(--evo-green);  }
    .evo-badge-missed  { background:var(--evo-red-soft);    color:var(--evo-red);    }

    /* Mobile: table → cards */
    @media (max-width: 600px) {
      .evo-tbl thead { display: none; }
      .evo-tbl, .evo-tbl tbody, .evo-tbl tr, .evo-tbl td { display: block; width: 100%; }
      .evo-tbl tr { padding: 1rem 1.25rem; border-bottom: 1px solid var(--evo-border); }
      .evo-tbl tr:last-child { border-bottom: none; }
      .evo-tbl td { padding: .15rem 0; border: none; }
      .evo-tbl td:last-child { padding-top: .5rem; }
    }

    /* ── Empty state ─────────────────────────────────────── */
    .evo-empty {
      padding: 3rem 1.5rem;
      text-align: center;
      color: var(--evo-light);
    }
    .evo-empty i { font-size: 2.5rem; display: block; margin-bottom: .75rem; }
    .evo-empty p { font-size: .88rem; margin: 0; }

    /* ── Sidebar: Credits ────────────────────────────────── */
    .evo-credit-block { padding: 1.25rem 1.5rem; }
    .evo-credit-tag {
      display: inline-block;
      font-size: .68rem;
      font-weight: 700;
      text-transform: uppercase;
      letter-spacing: .07em;
      color: var(--evo-muted);
      margin-bottom: .4rem;
    }
    .evo-credit-num {
      font-size: 1.8rem;
      font-weight: 800;
      color: var(--evo-text);
      line-height: 1;
    }
    .evo-credit-unit { font-size: .82rem; font-weight: 500; color: var(--evo-muted); }
    .evo-credit-row {
      display: flex;
      gap: 1.5rem;
      margin-top: .85rem;
    }
    .evo-credit-col { display: flex; flex-direction: column; gap: .2rem; }
    .evo-credit-val { font-size: .95rem; font-weight: 700; color: var(--evo-text); }
    .evo-credit-bar { width: 28px; height: 3px; border-radius: 3px; }
    .evo-credit-sub { font-size: .7rem; color: var(--evo-muted); }
    .evo-divider { border: none; border-top: 1px solid var(--evo-border); margin: 0; }

    /* ── Sidebar: Packages ───────────────────────────────── */
    .evo-pkg-list { padding: .75rem 1.25rem 1.25rem; display: flex; flex-direction: column; gap: .6rem; }
    .evo-pkg-item {
      border: 1px solid var(--evo-border);
      border-radius: var(--evo-radius-sm);
      padding: .9rem 1rem;
      transition: border-color .15s, box-shadow .15s;
    }
    .evo-pkg-item:hover {
      border-color: var(--evo-blue);
      box-shadow: 0 2px 12px rgba(37,99,235,.1);
    }
    .evo-pkg-name   { font-size: .88rem; font-weight: 700; color: var(--evo-text); margin-bottom: .2rem; }
    .evo-pkg-credit { font-size: .78rem; color: var(--evo-blue); font-weight: 600; }
    .evo-pkg-exp    { font-size: .71rem; color: var(--evo-muted); margin-top: .2rem; display:flex; align-items:center; gap:.25rem; }

    /* ── Responsive breakpoints ──────────────────────────── */
    @media (max-width: 1100px) {
      .evo-content-row { grid-template-columns: 1fr; }
      .evo-stats-row   { grid-template-columns: repeat(2,1fr); }
    }
    @media (max-width: 768px) {
      .evo-db          { padding: 1rem; gap: 1rem; }
      .evo-hero-row    { grid-template-columns: 1fr; }
      .evo-hero-card   { padding: 1.5rem; }
      .evo-hero-card h2 { font-size: 1.2rem; }
      .evo-live-banner { flex-direction: column; align-items: flex-start; }
      .evo-live-btn    { align-self: stretch; justify-content: center; }
    }
    @media (max-width: 480px) {
      .evo-stats-row { grid-template-columns: repeat(2,1fr); gap: .75rem; }
      .evo-stat-num  { font-size: 1.7rem; }
    }
  </style>
</head>

<body>
  <?php include 'includes/left-menu.php'; ?>

  <div class="dashboard-main-wrapper">
    <?php include 'includes/top-menu.php'; ?>

    <div class="dashboard-body evo-db">

      <!-- ═══════════════════════════════════════
           HERO ROW — Karşılama + Bildirim
      ════════════════════════════════════════════ -->
      <div class="evo-hero-row">

        <div class="evo-hero-card evo-hero-card--blue">
          <h2>Hoş geldin, <?php echo htmlspecialchars($_SESSION["fullname"]); ?>! 👋</h2>
          <p><?php echo randomTeacherText(rand(0, 10)); ?></p>
        </div>

        <div class="evo-hero-card evo-hero-card--green">
          <h2>
            <i class="ph ph-bell-simple-ringing evo-hero-icon"></i>
            <?php echo htmlspecialchars($getNotificData["title"] ?: "Bildirim Paneli"); ?>
          </h2>
          <p><?php
            $nd = $getNotificData["description"] ?: "Şu an yeni bir bildiriminiz bulunmuyor.";
            $nd = preg_replace('/<br\s*\/?>/i', "\n", $nd);
            $nd = strip_tags($nd);
            echo nl2br(htmlspecialchars(trim($nd)));
          ?></p>
        </div>

      </div><!-- /.evo-hero-row -->

      <!-- ═══════════════════════════════════════
           CANLI DERS BANNERI (koşullu)
      ════════════════════════════════════════════ -->
      <?php if ($activeLesson): ?>
      <div class="evo-live-banner">
        <div class="evo-live-info">
          <div class="evo-live-pill">
            <span class="evo-live-dot"></span>
            Canlı Ders
          </div>
          <h3><?php echo htmlspecialchars($activeLesson["lit"]); ?></h3>
          <p>
            Öğretmen: <strong><?php echo htmlspecialchars($activeLesson["fullname"]); ?></strong>
            &nbsp;·&nbsp;
            <?php echo turkcetarih('H:i', $activeLesson["start_date"]); ?> – <?php echo turkcetarih('H:i', $activeLesson["end_date"]); ?>
          </p>
        </div>
        <a href="appointment.php" class="evo-live-btn">
          <i class="ph-fill ph-video-camera"></i> Derse Katıl
        </a>
      </div>
      <?php endif; ?>

      <!-- ═══════════════════════════════════════
           İSTATİSTİK KARTLARI
      ════════════════════════════════════════════ -->
      <div class="evo-stats-row">

        <div class="evo-stat">
          <div class="evo-stat-icon" style="background:var(--evo-blue-soft);">
            <i class="ph-fill ph-book-open" style="color:var(--evo-blue);"></i>
          </div>
          <div class="evo-stat-num"><?php echo $completedCount; ?></div>
          <div class="evo-stat-lbl">Tamamlanan Dersler</div>
          <div class="evo-stat-chart">
            <div id="complete-course" class="remove-tooltip-title rounded-tooltip-value"></div>
          </div>
        </div>

        <div class="evo-stat">
          <div class="evo-stat-icon" style="background:var(--evo-red-soft);">
            <i class="ph-fill ph-certificate" style="color:var(--evo-red);"></i>
          </div>
          <div class="evo-stat-num"><?php echo $notcompletedCount; ?></div>
          <div class="evo-stat-lbl">Tamamlanamayan Dersler</div>
          <div class="evo-stat-chart">
            <div id="earned-certificate" class="remove-tooltip-title rounded-tooltip-value"></div>
          </div>
        </div>

        <div class="evo-stat">
          <div class="evo-stat-icon" style="background:var(--evo-purple-soft);">
            <i class="ph-fill ph-graduation-cap" style="color:var(--evo-purple);"></i>
          </div>
          <div class="evo-stat-num"><?php echo $waitingCount; ?></div>
          <div class="evo-stat-lbl">Yaklaşan Dersler</div>
          <div class="evo-stat-chart">
            <div id="course-progress" class="remove-tooltip-title rounded-tooltip-value"></div>
          </div>
        </div>

        <div class="evo-stat">
          <div class="evo-stat-icon" style="background:var(--evo-amber-soft);">
            <i class="ph-fill ph-users-three" style="color:var(--evo-amber);"></i>
          </div>
          <div class="evo-stat-num"><?php echo $teachercount; ?></div>
          <div class="evo-stat-lbl">Aktif Öğretmen</div>
          <div class="evo-stat-chart">
            <div id="community-support" class="remove-tooltip-title rounded-tooltip-value"></div>
          </div>
        </div>

      </div><!-- /.evo-stats-row -->

      <!-- ═══════════════════════════════════════
           ANA İÇERİK + SIDEBAR
      ════════════════════════════════════════════ -->
      <div class="evo-content-row">

        <!-- Yaklaşan Dersler Tablosu -->
        <div class="evo-card">
          <div class="evo-card-head">
            <h5>
              <i class="ph ph-calendar-check" style="color:var(--evo-blue);font-size:1rem;"></i>
              Ders Detayları
            </h5>
            <a href="appointment.php">Tüm Dersler →</a>
          </div>
          <div class="evo-tbl-wrap">
            <table class="evo-tbl">
              <thead>
                <tr>
                  <th>Ders Bilgileri</th>
                  <th>Öğretmen</th>
                  <th>Durum</th>
                </tr>
              </thead>
              <tbody>
                <?php
                $hasRows = false;
                while ($row = $appointmentData->fetch(PDO::FETCH_ASSOC)):
                  $hasRows = true;
                ?>
                <tr>
                  <td>
                    <div class="evo-lesson-name"><?php echo htmlspecialchars($row["lit"]); ?></div>
                    <div class="evo-lesson-lvl"><?php echo getLevel($row["leslevel"]); ?></div>
                    <div class="evo-lesson-date">
                      <i class="ph ph-clock"></i>
                      <?php echo turkcetarih('j F Y l H:i', $row["start_date"]); ?> – <?php echo turkcetarih('H:i', $row["end_date"]); ?>
                    </div>
                  </td>
                  <td>
                    <div class="evo-teacher-nm"><?php echo htmlspecialchars($row["fullname"]); ?></div>
                    <div class="evo-teacher-sub"><?php echo htmlspecialchars($row["profession"]); ?> · <?php echo htmlspecialchars($row["level"]); ?></div>
                  </td>
                  <td>
                    <?php if ($row["status"] == 0): ?>
                      <span class="evo-badge evo-badge-wait"><i class="ph ph-clock"></i> Bekleniyor</span>
                    <?php elseif ($row["status"] == 2): ?>
                      <span class="evo-badge evo-badge-missed"><i class="ph ph-x-circle"></i> Yapılamadı</span>
                    <?php elseif ($row["status"] == 1): ?>
                      <span class="evo-badge evo-badge-done"><i class="ph ph-check-circle"></i> Tamamlandı</span>
                    <?php endif; ?>
                  </td>
                </tr>
                <?php endwhile; ?>
                <?php if (!$hasRows): ?>
                <tr>
                  <td colspan="3">
                    <div class="evo-empty">
                      <i class="ph ph-calendar-blank"></i>
                      <p>Yaklaşan ders bulunmuyor.<br>Hemen bir ders planlamak için öğretmeninizle iletişime geçin.</p>
                    </div>
                  </td>
                </tr>
                <?php endif; ?>
              </tbody>
            </table>
          </div>
        </div><!-- /.evo-card (table) -->

        <!-- ─── Sidebar ─────────────────────────────── -->
        <div style="display:flex;flex-direction:column;gap:1rem;">

          <!-- Kredi Bilgilerim -->
          <div class="evo-card">
            <div class="evo-card-head">
              <h5>
                <i class="ph ph-coins" style="color:var(--evo-amber);font-size:1rem;"></i>
                Kredi Bilgilerim
              </h5>
            </div>

            <!-- Solo Ders -->
            <div class="evo-credit-block">
              <span class="evo-credit-tag">Solo Ders Kredisi</span>
              <div>
                <span class="evo-credit-num"><?php echo (int)$totalCreditData["credit"]; ?></span>
                <span class="evo-credit-unit"> kredi kaldı</span>
              </div>
              <div class="evo-credit-row">
                <div class="evo-credit-col">
                  <span class="evo-credit-val"><?php echo (int)$totalBoughtCreditData["credit"]; ?></span>
                  <span class="evo-credit-bar" style="background:var(--evo-blue);"></span>
                  <span class="evo-credit-sub">Satın Alınan</span>
                </div>
                <div class="evo-credit-col">
                  <span class="evo-credit-val"><?php echo (int)$totalUsedCreditData["credit"]; ?></span>
                  <span class="evo-credit-bar" style="background:var(--evo-green);"></span>
                  <span class="evo-credit-sub">Kullanılan</span>
                </div>
              </div>
            </div>

            <hr class="evo-divider">

            <!-- Grup Ders -->
            <div class="evo-credit-block">
              <span class="evo-credit-tag">Grup Ders Kredisi</span>
              <div>
                <span class="evo-credit-num"><?php echo (int)$totalCreditGroupData["credit"]; ?></span>
                <span class="evo-credit-unit"> kredi kaldı</span>
              </div>
              <div class="evo-credit-row">
                <div class="evo-credit-col">
                  <span class="evo-credit-val"><?php echo (int)$totalBoughtCreditGroupData["credit"]; ?></span>
                  <span class="evo-credit-bar" style="background:var(--evo-blue);"></span>
                  <span class="evo-credit-sub">Satın Alınan</span>
                </div>
                <div class="evo-credit-col">
                  <span class="evo-credit-val"><?php echo (int)$totalUsedCreditGroupData["credit"]; ?></span>
                  <span class="evo-credit-bar" style="background:var(--evo-green);"></span>
                  <span class="evo-credit-sub">Kullanılan</span>
                </div>
              </div>
            </div>
          </div><!-- /.evo-card (credits) -->

          <!-- Paketlerim -->
          <div class="evo-card">
            <div class="evo-card-head">
              <h5>
                <i class="ph ph-package" style="color:var(--evo-purple);font-size:1rem;"></i>
                Paketlerim
              </h5>
              <a href="#">Aktif Paketlerim</a>
            </div>
            <div class="evo-pkg-list">
              <?php
              $pkgFound = false;
              while ($row = $loginData->fetch(PDO::FETCH_ASSOC)):
                $productDetail = $db->prepare("SELECT SUM(credit) AS credit FROM active_credit WHERE user_id=? AND product_id=? AND credit > 0 AND " . creditTypeWhere($row["type"] ?? null));
                $productDetail->execute([$uid, (int)$row["product_id"]]);
                $pDetail = $productDetail->fetch(PDO::FETCH_ASSOC);
                if ($pDetail["credit"] == 0) continue;
                $pkgFound = true;
              ?>
              <div class="evo-pkg-item">
                <div class="evo-pkg-name"><?php echo htmlspecialchars($row["title"]); ?></div>
                <div class="evo-pkg-credit"><?php echo $pDetail["credit"]; ?> Krediniz Kaldı</div>
                <div class="evo-pkg-exp">
                  <i class="ph ph-calendar-blank"></i>
                  <?php echo turkcetarih('j F Y', $row["expired_date"]); ?> tarihine kadar geçerli
                </div>
              </div>
              <?php endwhile; ?>
              <?php if (!$pkgFound): ?>
              <div class="evo-empty" style="padding:1.5rem;">
                <i class="ph ph-package" style="font-size:1.8rem;color:var(--evo-light);display:block;margin-bottom:.5rem;"></i>
                <p style="font-size:.82rem;color:var(--evo-muted);margin:0;">Aktif paketiniz bulunmuyor.</p>
              </div>
              <?php endif; ?>
            </div>
          </div><!-- /.evo-card (packages) -->

        </div><!-- /.sidebar -->

      </div><!-- /.evo-content-row -->

    </div><!-- /.evo-db -->

    <?php include '../includes_panel/footer.php'; ?>
  </div><!-- /.dashboard-main-wrapper -->

  <?php include '../includes_panel/scripts.php'; ?>
  <?php include 'includes/vbs-scripts.php'; ?>
  <?php include 'includes/student-scripts.php'; ?>
  <script src="assets/js/calendar.js?v=2"></script>
  <script>
    createChart("complete-course",    "#2563eb");
    createChart("earned-certificate", "#ef4444");
    createChart("course-progress",    "#7c3aed");
    createChart("community-support",  "#f59e0b");
  </script>
</body>
</html>

<?php if (isset($_GET["register"]) && $_GET["register"] == "new"): ?>
<script>
  Swal.fire({
    icon: 'success',
    title: 'Hoş Geldiniz!',
    text: 'Derslerinde kullanabileceğin 2 adet ücretsiz ders kredisini hesabına tanımladık!',
    confirmButtonText: 'Devam Etmek İçin Tıkla'
  }).then(() => { window.location.href = "student/dashboard.php"; });
</script>
<?php endif; ?>

<?php if (isset($_GET["credit"]) && $_GET["credit"] == 1): ?>
<script>
  Swal.fire({
    icon: 'success',
    title: 'Krediniz Tanımlandı!',
    text: 'Derslerinde kullanabileceğin ders kredisini hesabına tanımladık!',
    confirmButtonText: 'Devam Etmek İçin Tıkla'
  }).then(() => { window.location.href = "student/dashboard.php"; });
</script>
<?php endif; ?>
