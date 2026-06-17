<?php
ini_set('session.cookie_path', '/');
ini_set('session.cookie_domain', '');
session_start();
include("../config/connection.php");
checkUnSession();

try {
    $fc = $db->query("SHOW COLUMNS FROM `groups` LIKE 'featured'");
    if (!$fc || !$fc->fetch(PDO::FETCH_ASSOC)) {
        $db->exec("ALTER TABLE `groups` ADD `featured` TINYINT(1) NOT NULL DEFAULT 0 AFTER `publish`");
    }
} catch (Throwable $e) {}

$uid = (int)$_SESSION['user_id'];
$groupData = $db->prepare("
    SELECT g.*, l.title AS lestitle, l.description AS lesdescription,
           u.fullname, u.profile_photo
    FROM `groups` g
    INNER JOIN lessons l ON g.lesson_id = l.id
    INNER JOIN users   u ON g.teacher_id = u.id
    WHERE g.teacher_id = ?
    ORDER BY g.id DESC
");
$groupData->execute([$uid]);
$groups = $groupData->fetchAll(PDO::FETCH_ASSOC);

$quotaCounts = [];
foreach ($groups as $g) {
    $qc = $db->prepare("SELECT COUNT(*) FROM groups_quota WHERE group_id = ?");
    $qc->execute([$g['id']]);
    $quotaCounts[$g['id']] = (int)$qc->fetchColumn();
}
$total = count($groups);
?>
<!DOCTYPE html>
<html lang="tr">
<head>
  <?php include "../includes_panel/meta.php"; ?>
  <title>Grup Dersler | Evo Eğitim</title>
  <style>
    :root {
      --gp: #7c3aed; --gp-s: #f5f3ff;
      --gb: #2563eb; --gb-s: #eff6ff;
      --gg: #10b981; --gg-s: #ecfdf5;
      --ga: #f59e0b; --ga-s: #fffbeb;
      --gt: #0f172a; --gm: #64748b;
      --gbr: #e2e8f0; --gbg: #f1f5f9;
      --gc: #ffffff; --gr: 16px;
      --gs: 0 2px 12px rgba(0,0,0,.06);
    }
    .evo-page { background: var(--gbg); min-height: 100vh; padding: 1.75rem; display: flex; flex-direction: column; gap: 1.25rem; }

    /* Header */
    .evo-ph { display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 1rem; }
    .evo-ph-left { display: flex; align-items: center; gap: .85rem; }
    .evo-ph-icon { width: 48px; height: 48px; border-radius: 14px; background: var(--gp-s); display: flex; align-items: center; justify-content: center; font-size: 1.4rem; color: var(--gp); flex-shrink: 0; }
    .evo-ph-title { font-size: 1.2rem; font-weight: 800; color: var(--gt); margin: 0; }
    .evo-ph-sub   { font-size: .78rem; color: var(--gm); margin: .1rem 0 0; }
    .evo-btn-new { display: inline-flex; align-items: center; gap: .4rem; background: var(--gp); color: #fff; font-size: .84rem; font-weight: 700; padding: .6rem 1.25rem; border-radius: 10px; text-decoration: none; transition: opacity .15s; white-space: nowrap; }
    .evo-btn-new:hover { opacity: .88; color: #fff; }

    /* Search */
    .evo-search-wrap { position: relative; max-width: 460px; }
    .evo-search-wrap i { position: absolute; left: .9rem; top: 50%; transform: translateY(-50%); color: var(--gm); font-size: .95rem; pointer-events: none; }
    .evo-search-input { width: 100%; padding: .65rem 1rem .65rem 2.4rem; border: 1px solid var(--gbr); border-radius: 10px; font-size: .88rem; background: var(--gc); outline: none; color: var(--gt); transition: border-color .15s, box-shadow .15s; }
    .evo-search-input:focus { border-color: var(--gp); box-shadow: 0 0 0 3px rgba(124,58,237,.1); }
    .evo-search-count { font-size: .78rem; color: var(--gm); margin-top: .4rem; min-height: 1.1em; }

    /* Grid */
    .evo-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(290px, 1fr)); gap: 1.1rem; }

    /* Card */
    .evo-gc { background: var(--gc); border: 1px solid var(--gbr); border-radius: var(--gr); box-shadow: var(--gs); overflow: hidden; display: flex; flex-direction: column; transition: box-shadow .2s, transform .2s; }
    .evo-gc:hover { box-shadow: 0 8px 32px rgba(0,0,0,.1); transform: translateY(-3px); }
    .evo-gc-img { position: relative; height: 175px; flex-shrink: 0; }
    .evo-gc-img img { width: 100%; height: 100%; object-fit: cover; }
    .evo-gc-overlay { position: absolute; inset: 0; background: linear-gradient(180deg, rgba(0,0,0,.02) 0%, rgba(0,0,0,.35) 100%); }
    .evo-gc-top-badges { position: absolute; top: 10px; left: 10px; display: flex; gap: 5px; }
    .evo-pill { display: inline-flex; align-items: center; gap: .25rem; padding: .2rem .6rem; border-radius: 20px; font-size: .68rem; font-weight: 700; white-space: nowrap; backdrop-filter: blur(4px); }
    .evo-pill-pub  { background: rgba(5,150,105,.9);  color: #fff; }
    .evo-pill-draf { background: rgba(100,116,139,.85); color: #fff; }
    .evo-pill-feat { background: rgba(245,158,11,.9); color: #fff; }
    .evo-gc-credit { position: absolute; bottom: 10px; right: 10px; background: rgba(124,58,237,.92); color: #fff; font-size: .72rem; font-weight: 700; padding: .2rem .6rem; border-radius: 8px; backdrop-filter: blur(4px); }

    .evo-gc-body { padding: 1.1rem 1.2rem; flex: 1; display: flex; flex-direction: column; gap: .6rem; }
    .evo-gc-title { font-size: .97rem; font-weight: 800; color: var(--gt); line-height: 1.3; margin: 0; }
    .evo-gc-lesson { font-size: .75rem; color: var(--gp); font-weight: 600; }
    .evo-gc-subject { font-size: .75rem; color: var(--gm); }

    .evo-gc-teacher { display: flex; align-items: center; gap: .5rem; }
    .evo-gc-teacher img { width: 26px; height: 26px; border-radius: 50%; object-fit: cover; }
    .evo-gc-teacher span { font-size: .78rem; color: var(--gm); font-weight: 500; }

    .evo-quota-wrap { display: flex; flex-direction: column; gap: .3rem; }
    .evo-quota-row { display: flex; justify-content: space-between; align-items: center; }
    .evo-quota-lbl { font-size: .7rem; color: var(--gm); }
    .evo-quota-val { font-size: .75rem; font-weight: 700; color: var(--gt); }
    .evo-quota-bar { height: 6px; background: #e2e8f0; border-radius: 6px; overflow: hidden; }
    .evo-quota-fill { height: 100%; border-radius: 6px; background: linear-gradient(90deg, var(--gp), #a855f7); transition: width .3s; }

    .evo-gc-meta { display: flex; flex-direction: column; gap: .25rem; }
    .evo-gc-meta-row { display: flex; align-items: center; gap: .4rem; font-size: .74rem; color: var(--gm); }
    .evo-gc-meta-row i { color: var(--gp); width: 14px; text-align: center; }

    /* Actions */
    .evo-gc-actions { display: grid; grid-template-columns: 1fr 1fr; gap: .4rem; padding: .85rem 1.2rem; border-top: 1px solid var(--gbr); }
    .evo-gc-act { display: inline-flex; align-items: center; justify-content: center; gap: .3rem; padding: .45rem .5rem; border-radius: 8px; font-size: .72rem; font-weight: 600; text-decoration: none; border: none; cursor: pointer; transition: opacity .15s, transform .1s; white-space: nowrap; }
    .evo-gc-act:hover { opacity: .85; transform: translateY(-1px); text-decoration: none; }
    .evo-gc-act-edit   { background: #fef3c7; color: #92400e; }
    .evo-gc-act-detail { background: var(--gb-s); color: var(--gb); }
    .evo-gc-act-approve{ background: var(--gg-s); color: var(--gg); }
    .evo-gc-act-members{ background: var(--gp-s); color: var(--gp); }
    .evo-gc-act-publish{ color: #fff; grid-column: 1 / -1; }
    .evo-gc-act-feat   { grid-column: 1 / -1; }

    /* Empty */
    .evo-empty { background: var(--gc); border: 1px solid var(--gbr); border-radius: var(--gr); padding: 4rem 2rem; text-align: center; color: var(--gm); }
    .evo-empty i { font-size: 2.5rem; display: block; margin-bottom: .75rem; color: #cbd5e1; }

    @media (max-width: 640px) {
      .evo-page { padding: 1rem; }
      .evo-grid { grid-template-columns: 1fr; }
    }
  </style>
</head>
<body>
  <?php include 'includes/left-menu.php'; ?>
  <div class="dashboard-main-wrapper">
    <?php include 'includes/top-menu.php'; ?>
    <div class="dashboard-body evo-page">

      <!-- Page Header -->
      <div class="evo-ph">
        <div class="evo-ph-left">
          <div class="evo-ph-icon"><i class="ph-fill ph-users-three"></i></div>
          <div>
            <h1 class="evo-ph-title">Grup Dersleri</h1>
            <p class="evo-ph-sub"><?php echo $total; ?> grup ders listeleniyor</p>
          </div>
        </div>
      </div>

      <!-- Search -->
      <div>
        <div class="evo-search-wrap">
          <i class="ph ph-magnifying-glass"></i>
          <input type="text" id="courseSearchInput" class="evo-search-input" placeholder="Ders adı, öğretmen veya konu ara…">
        </div>
        <div class="evo-search-count" id="searchCount"></div>
      </div>

      <!-- Grid -->
      <?php if (empty($groups)): ?>
        <div class="evo-empty">
          <i class="ph ph-folder-open"></i>
          <p>Henüz grup ders oluşturulmamış.</p>
        </div>
      <?php else: ?>
      <div class="evo-grid" id="coursesContainer">
        <?php foreach ($groups as $g):
          $levels   = json_decode($g['level'] ?? '[]', true) ?: [];
          $levelTxt = $levels ? implode(', ', $levels) : '—';
          $enrolled = $quotaCounts[$g['id']] ?? 0;
          $quota    = max(1, (int)$g['quota']);
          $pct      = min(100, round($enrolled / $quota * 100));
          $totalCredit = (int)$g['credit'] * (int)$g['total_lesson_time'] * (int)$g['weekly_lesson_count'];
          $imgSrc   = !empty($g['image']) ? '/' . $g['image'] : '/assets/img/course/course-2-1.jpg';
          $avatarSrc = !empty($g['profile_photo']) ? '/' . $g['profile_photo'] : '/assets/img/team/team-1-1.jpg';
          $featured  = (int)($g['featured'] ?? 0);
          $publish   = (int)($g['publish'] ?? 0);
        ?>
        <div class="evo-gc course-item">
          <!-- Image -->
          <div class="evo-gc-img">
            <img src="<?php echo htmlspecialchars($imgSrc); ?>" alt="<?php echo htmlspecialchars($g['title']); ?>">
            <div class="evo-gc-overlay"></div>
            <div class="evo-gc-top-badges">
              <?php if ($publish): ?>
                <span class="evo-pill evo-pill-pub"><i class="ph ph-eye"></i> Yayında</span>
              <?php else: ?>
                <span class="evo-pill evo-pill-draf"><i class="ph ph-eye-slash"></i> Taslak</span>
              <?php endif; ?>
              <?php if ($featured): ?>
                <span class="evo-pill evo-pill-feat"><i class="ph ph-star"></i> Öne Çıkan</span>
              <?php endif; ?>
            </div>
            <div class="evo-gc-credit"><i class="ph ph-ticket"></i> <?php echo $totalCredit; ?> Kredi</div>
          </div>

          <!-- Body -->
          <div class="evo-gc-body">
            <h3 class="evo-gc-title"><?php echo htmlspecialchars($g['title']); ?></h3>
            <div class="evo-gc-lesson"><?php echo htmlspecialchars($g['lestitle']); ?> · <?php echo htmlspecialchars($g['lesdescription']); ?></div>
            <?php if (!empty($g['subject'])): ?>
            <div class="evo-gc-subject"><?php echo htmlspecialchars($g['subject']); ?></div>
            <?php endif; ?>

            <div class="evo-gc-teacher">
              <img src="<?php echo htmlspecialchars($avatarSrc); ?>" alt="">
              <span><?php echo htmlspecialchars($g['fullname']); ?></span>
            </div>

            <div class="evo-quota-wrap">
              <div class="evo-quota-row">
                <span class="evo-quota-lbl">Kontenjan</span>
                <span class="evo-quota-val"><?php echo $enrolled; ?> / <?php echo $quota; ?> öğrenci</span>
              </div>
              <div class="evo-quota-bar">
                <div class="evo-quota-fill" style="width:<?php echo $pct; ?>%"></div>
              </div>
            </div>

            <div class="evo-gc-meta">
              <div class="evo-gc-meta-row"><i class="ph ph-calendar-blank"></i><?php echo turkcetarih('j F Y H:i', $g['start_date']); ?></div>
              <div class="evo-gc-meta-row"><i class="ph ph-clock"></i>Haftalık <?php echo $g['weekly_lesson_count']; ?> ders · <?php echo $g['total_lesson_time']; ?> hafta</div>
              <?php if ($levelTxt !== '—'): ?>
              <div class="evo-gc-meta-row"><i class="ph ph-graduation-cap"></i><?php echo htmlspecialchars($levelTxt); ?></div>
              <?php endif; ?>
            </div>
          </div>

          <!-- Actions -->
          <div class="evo-gc-actions">
            <a href="group-class-detail.php?kurs=<?php echo htmlspecialchars($g['slug']); ?>" class="evo-gc-act evo-gc-act-detail">
              <i class="ph ph-info"></i> Detay
            </a>
            <a href="teacher/class-members.php?id=<?php echo $g['id']; ?>" class="evo-gc-act evo-gc-act-members">
              <i class="ph ph-users"></i> Üyeler
            </a>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
      <?php endif; ?>

    </div>
    <?php include '../includes_panel/footer.php'; ?>
  </div>

  <?php include '../includes_panel/scripts.php'; ?>
  <?php include 'includes/teacher-scripts.php'; ?>
  <script>
  document.addEventListener('DOMContentLoaded', function () {
    const inp   = document.getElementById('courseSearchInput');
    const count = document.getElementById('searchCount');
    const cards = document.querySelectorAll('.course-item');

    function doSearch(q) {
      q = q.toLowerCase().trim();
      let n = 0;
      cards.forEach(c => {
        const match = c.textContent.toLowerCase().includes(q);
        c.style.display = (!q || match) ? '' : 'none';
        if (!q || match) n++;
      });
      count.textContent = q ? `${n} grup bulundu` : '';
    }

    inp.addEventListener('input', () => doSearch(inp.value));
    inp.addEventListener('keypress', e => { if (e.key === 'Enter') doSearch(inp.value); });
  });
  </script>
</body>
</html>
