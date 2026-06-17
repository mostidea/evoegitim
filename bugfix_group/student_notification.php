<?php
ini_set('session.cookie_path', '/');
ini_set('session.cookie_domain', '');
session_start();
include("../config/connection.php");
checkUnSession();

$uid = (int) $_SESSION["user_id"];

$ticketsData = $db->prepare("
    SELECT notification.*, users.email AS receiver_user
    FROM notification
    LEFT JOIN users ON notification.receiver = users.id
    WHERE notification.receiver = 0
       OR notification.receiver = :uid
    ORDER BY id DESC
");
$ticketsData->bindParam(":uid", $uid, PDO::PARAM_INT);
$ticketsData->execute();

?>
<!DOCTYPE html>
<html lang="tr">
<head>
  <?php include '../includes_panel/meta.php'; ?>
  <title>Bildirimler | Evo Eğitim</title>
  <style>
    :root{
      --np:#7c3aed;--np-s:#f5f3ff;--ng:#10b981;--ng-s:#ecfdf5;
      --nb:#2563eb;--nb-s:#eff6ff;--nm:#f59e0b;--nm-s:#fffbeb;
      --tx:#0f172a;--mu:#64748b;--lt:#94a3b8;--br:#e8edf5;
      --bg:#f4f6fb;--card:#fff;--rad:16px;--shd:0 2px 16px rgba(0,0,0,.06);
    }
    .ntf-page{background:var(--bg);min-height:100vh;padding:1.75rem;}

    .ntf-hdr{
      background:linear-gradient(135deg,#1e1b4b 0%,#4c1d95 50%,#7c3aed 100%);
      border-radius:20px;padding:1.75rem 2rem;margin-bottom:1.5rem;
      box-shadow:0 8px 32px rgba(124,58,237,.22);
      display:flex;align-items:center;gap:1rem;
    }
    .ntf-hdr-icon{
      width:52px;height:52px;border-radius:14px;flex-shrink:0;
      background:rgba(255,255,255,.15);
      display:flex;align-items:center;justify-content:center;
      font-size:1.5rem;color:#fff;
    }
    .ntf-hdr h1{font-size:1.3rem;font-weight:800;color:#fff;margin:0 0 .2rem;}
    .ntf-hdr p {font-size:.82rem;color:rgba(255,255,255,.68);margin:0;}

    .ntf-list{display:flex;flex-direction:column;gap:.6rem;}

    .ntf-item{
      background:var(--card);border:1px solid var(--br);border-radius:var(--rad);
      padding:1.1rem 1.3rem;
      display:flex;align-items:flex-start;gap:1rem;
      transition:box-shadow .18s,transform .18s,border-color .18s;
    }
    .ntf-item:hover{
      box-shadow:0 6px 24px rgba(0,0,0,.08);
      transform:translateY(-2px);
      border-color:var(--np-s);
    }
    .ntf-dot{
      width:38px;height:38px;border-radius:11px;flex-shrink:0;
      display:flex;align-items:center;justify-content:center;
      font-size:1.1rem;margin-top:.1rem;
    }
    .ntf-dot.general{background:var(--nb-s);color:var(--nb);}
    .ntf-dot.personal{background:var(--np-s);color:var(--np);}
    .ntf-dot.teacher {background:var(--nm-s);color:var(--nm);}

    .ntf-body{flex:1;min-width:0;}
    .ntf-title{font-size:.9rem;font-weight:700;color:var(--tx);margin-bottom:.2rem;line-height:1.35;}
    .ntf-meta{display:flex;align-items:center;gap:.75rem;flex-wrap:wrap;margin-top:.35rem;}
    .ntf-tag{
      display:inline-flex;align-items:center;gap:.25rem;
      padding:.18rem .6rem;border-radius:20px;
      font-size:.68rem;font-weight:700;
    }
    .ntf-tag.general {background:var(--nb-s);color:var(--nb);}
    .ntf-tag.personal{background:var(--np-s);color:var(--np);}
    .ntf-tag.teacher {background:var(--nm-s);color:var(--nm);}
    .ntf-date{font-size:.72rem;color:var(--lt);display:flex;align-items:center;gap:.25rem;}

    .ntf-empty{
      text-align:center;padding:5rem 1.5rem;
      background:var(--card);border:1px solid var(--br);
      border-radius:var(--rad);box-shadow:var(--shd);
    }
    .ntf-empty i{font-size:3rem;color:#dde3ee;display:block;margin-bottom:.85rem;}
    .ntf-empty h3{font-size:1rem;font-weight:700;color:var(--tx);margin:0 0 .3rem;}
    .ntf-empty p {font-size:.85rem;color:var(--lt);margin:0;}
  </style>
</head>
<body>
  <?php include 'includes/left-menu.php'; ?>

  <div class="dashboard-main-wrapper">
    <?php include 'includes/top-menu.php'; ?>
    <div class="dashboard-body ntf-page">

      <!-- Header -->
      <div class="ntf-hdr">
        <div class="ntf-hdr-icon"><i class="ph ph-bell-ringing"></i></div>
        <div>
          <h1>Bildirimler</h1>
          <p>Sisteme ait tüm duyuru ve bilgilendirmeler</p>
        </div>
      </div>

      <!-- List -->
      <?php
      $rows = [];
      while ($row = $ticketsData->fetch(PDO::FETCH_ASSOC)) $rows[] = $row;
      ?>

      <?php if (empty($rows)): ?>
        <div class="ntf-empty">
          <i class="ph ph-bell-slash"></i>
          <h3>Henüz bildirim yok</h3>
          <p>Yeni bildirimler geldiğinde burada görünecek.</p>
        </div>
      <?php else: ?>
        <div class="ntf-list">
          <?php foreach ($rows as $row):
            if ($row["receiver"] == 0)     { $tagCls = 'general';  $tagTxt = 'Genel Duyuru';            $icon = 'ph-megaphone'; }
            elseif ($row["receiver"] == 1) { $tagCls = 'teacher';  $tagTxt = 'Öğretmen Bildirimi';      $icon = 'ph-chalkboard-teacher'; }
            else                           { $tagCls = 'personal'; $tagTxt = 'Kişisel Bildirim';         $icon = 'ph-user-circle'; }
          ?>
          <div class="ntf-item">
            <div class="ntf-dot <?= $tagCls ?>"><i class="ph <?= $icon ?>"></i></div>
            <div class="ntf-body">
              <div class="ntf-title"><?= htmlspecialchars($row["title"]) ?></div>
              <div class="ntf-meta">
                <span class="ntf-tag <?= $tagCls ?>">
                  <i class="ph <?= $icon ?>"></i> <?= $tagTxt ?>
                </span>
                <span class="ntf-date">
                  <i class="ph ph-clock"></i>
                  <?= htmlspecialchars($row["created_at"]) ?>
                </span>
              </div>
            </div>
          </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>

    </div>
    <?php include '../includes_panel/footer.php'; ?>
  </div>

  <?php include '../includes_panel/scripts.php'; ?>
  <?php include 'includes/student-scripts.php'; ?>
</body>
</html>
