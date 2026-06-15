<?php
ini_set('session.cookie_path', '/');
ini_set('session.cookie_domain', '');
session_start();
include("../config/connection.php");
checkUnSession();
$current_datetime = date("Y-m-d H:i:s");

$boughtList = $db->prepare("SELECT product_id FROM order_report WHERE expired_date > :current_datetime AND user_id = :uid AND status=1 AND product_id != 4");
$boughtList->bindParam(":current_datetime", $current_datetime, PDO::PARAM_STR);
$boughtList->bindParam(":uid", $_SESSION["user_id"], PDO::PARAM_INT);
$boughtList->execute();
$boughtProducts = $boughtList->fetchAll(PDO::FETCH_COLUMN, 0);
$productIds = !empty($boughtProducts) ? implode(',', array_map('intval', $boughtProducts)) : '';

if (!empty($productIds)) {
    $loginData = $db->prepare("SELECT * FROM package WHERE type IS NULL ORDER BY FIELD(id, $productIds) ASC");
} else {
    $loginData = $db->prepare("SELECT * FROM package WHERE type IS NULL AND id != 4 ORDER BY id DESC");
}
$loginData->execute();
?>
<!DOCTYPE html>
<html lang="tr">
<head>
  <?php include '../includes_panel/meta.php'; ?>
  <title>Solo Ders Paketleri | Evo Eğitim</title>
  <style>
    .pkg-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 24px; }
    @media (max-width: 991px) { .pkg-grid { grid-template-columns: repeat(2, 1fr); } }
    @media (max-width: 575px) { .pkg-grid { grid-template-columns: 1fr; } }

    .pkg-card {
      border-radius: 20px;
      display: flex;
      flex-direction: column;
      overflow: hidden;
      border: 1px solid rgba(0,0,0,0.07);
      transition: box-shadow .25s, transform .25s;
      height: 100%;
    }
    .pkg-card:hover { box-shadow: 0 12px 40px rgba(0,0,0,0.12); transform: translateY(-3px); }

    .pkg-top { padding: 28px 28px 22px; }
    .pkg-divider { height: 1px; background: rgba(0,0,0,0.08); margin: 0 28px; }
    .pkg-price-area { padding: 20px 28px; }
    .pkg-feats { padding: 18px 28px; flex-grow: 1; }
    .pkg-feats li { display: flex; align-items: flex-start; gap: 9px; margin-bottom: 9px; font-size: 14px; line-height: 1.45; }
    .pkg-feats li i { font-size: 17px; flex-shrink: 0; margin-top: 1px; }
    .pkg-bottom { padding: 20px 28px 28px; }

    .pkg-title { font-size: 1.15rem; font-weight: 700; margin-bottom: 6px; }
    .pkg-desc  { font-size: 13.5px; margin-bottom: 0; opacity: .8; }

    .pkg-old-price { font-size: 13px; text-decoration: line-through; opacity: .55; margin-right: 6px; }
    .pkg-save-badge {
      display: inline-block; font-size: 11px; font-weight: 700;
      background: #ef4444; color: #fff;
      padding: 2px 9px; border-radius: 30px; vertical-align: middle;
    }
    .pkg-amount { font-size: 2.1rem; font-weight: 800; line-height: 1.1; }
    .pkg-credit { font-size: 13px; opacity: .7; margin-left: 3px; }

    /* Aktif paket rozeti */
    .pkg-active-ribbon {
      display: inline-flex; align-items: center; gap: 6px;
      background: rgba(255,255,255,0.22); border: 1px solid rgba(255,255,255,0.35);
      border-radius: 30px; padding: 4px 12px; font-size: 12px; font-weight: 600;
      margin-bottom: 10px;
    }

    /* İndirim kodu input */
    .pkg-coupon-wrap { position: relative; margin-bottom: 12px; }
    .pkg-coupon-wrap i {
      position: absolute; left: 12px; top: 50%; transform: translateY(-50%);
      font-size: 16px; pointer-events: none; opacity: .45;
    }
    .pkg-coupon-input {
      width: 100%; border-radius: 10px; border: 1.5px solid rgba(0,0,0,0.13);
      background: rgba(255,255,255,0.55); backdrop-filter: blur(4px);
      padding: 9px 12px 9px 36px; font-size: 13.5px; outline: none;
      transition: border-color .2s, background .2s;
    }
    .pkg-coupon-input:focus {
      border-color: #6366f1; background: rgba(255,255,255,0.85);
    }
    .pkg-coupon-input::placeholder { opacity: .5; }

    /* Satın al butonu — her zaman kontrast renk */
    .btn-buy {
      display: block; width: 100%; text-align: center;
      padding: 13px 20px; border-radius: 12px; border: none; cursor: pointer;
      font-size: 15px; font-weight: 700; letter-spacing: .2px;
      background: #fff; color: #1e293b;
      box-shadow: 0 2px 8px rgba(0,0,0,0.18);
      transition: opacity .2s, transform .15s;
    }
    .btn-buy:hover { opacity: .92; transform: translateY(-1px); }

    /* Aktif paket butonu */
    .btn-active {
      display: block; width: 100%; text-align: center;
      padding: 13px 20px; border-radius: 12px;
      font-size: 15px; font-weight: 700;
      background: rgba(255,255,255,0.22);
      border: 1.5px solid rgba(255,255,255,0.4);
      color: inherit; cursor: default;
    }
    .pkg-expire-note { font-size: 12px; text-align: center; margin-top: 8px; opacity: .65; }
  </style>
</head>
<body>
  <?php include 'includes/left-menu.php'; ?>
  <div class="dashboard-main-wrapper">
    <?php include 'includes/top-menu.php'; ?>
    <div class="dashboard-body">

      <div class="card mt-24">
        <div class="card-header border-bottom">
          <h4 class="mb-4">Solo Ders Paketleri</h4>
          <p class="text-gray-600 text-15 mb-0">İhtiyacınıza uygun paketi seçin — krediniz hemen hesabınıza tanımlanır.</p>
        </div>
        <div class="card-body py-32">
          <div class="pkg-grid">

            <?php while ($row = $loginData->fetch(PDO::FETCH_ASSOC)):
              $packageId   = (int)$row['id'];
              $price       = (float)$row['price'];
              $discount    = (float)$row['discount'];
              $finalPrice  = $price - $discount;
              $discountPct = ($discount > 0 && $price > 0) ? round($discount / $price * 100) : 0;
              $details     = array_filter(array_map('trim', explode(',', $row['details'])));

              // Renk değerleri — DB boşsa güvenli default
              $bg     = !empty($row['color1']) ? $row['color1'] : '#f8f9ff';
              $clrT   = !empty($row['color2']) ? $row['color2'] : '#1e293b';
              $clrD   = !empty($row['color3']) ? $row['color3'] : '#64748b';
              $clrP   = !empty($row['color4']) ? $row['color4'] : '#1e293b';
              $clrFt  = !empty($row['color6']) ? $row['color6'] : '#334155';
              $clrIc  = !empty($row['color7']) ? $row['color7'] : '#6366f1';

              $pd = $db->prepare("SELECT credit FROM active_credit WHERE user_id = ? AND product_id = ?");
              $pd->execute([$_SESSION["user_id"], $packageId]);
              $pDetail = $pd->fetch(PDO::FETCH_ASSOC);

              $cb = $db->prepare("SELECT expired_date FROM order_report WHERE product_id = ? AND expired_date > ? AND user_id = ? AND status = 1 LIMIT 1");
              $cb->execute([$packageId, $current_datetime, $_SESSION["user_id"]]);
              $bought   = $cb->fetch(PDO::FETCH_ASSOC);
              $isActive = $bought && $pDetail && $pDetail['credit'] > 0;
            ?>

            <div class="pkg-card" style="background:<?= htmlspecialchars($bg) ?>;">

              <!-- Başlık -->
              <div class="pkg-top">
                <?php if ($isActive): ?>
                  <div class="pkg-active-ribbon" style="color:<?= htmlspecialchars($clrT) ?>;">
                    <i class="ph ph-check-circle"></i> Aktif Paket
                  </div>
                <?php endif; ?>
                <div class="pkg-title" style="color:<?= htmlspecialchars($clrT) ?>;">
                  <?= htmlspecialchars($row['title']) ?>
                </div>
                <p class="pkg-desc" style="color:<?= htmlspecialchars($clrD) ?>;">
                  <?= htmlspecialchars($row['description']) ?>
                </p>
              </div>

              <div class="pkg-divider"></div>

              <!-- Fiyat -->
              <div class="pkg-price-area">
                <?php if ($discount > 0): ?>
                  <div class="mb-4">
                    <span class="pkg-old-price" style="color:<?= htmlspecialchars($clrP) ?>;">
                      <?= number_format($price, 0, ',', '.') ?>₺
                    </span>
                    <span class="pkg-save-badge">%<?= $discountPct ?> İndirim</span>
                  </div>
                <?php endif; ?>
                <div style="display:flex; align-items:baseline; flex-wrap:wrap; gap:4px;">
                  <span class="pkg-amount" style="color:<?= htmlspecialchars($clrP) ?>;">
                    <?= $price == 0 ? 'Ücretsiz' : number_format($finalPrice, 0, ',', '.') . '₺' ?>
                  </span>
                  <span class="pkg-credit" style="color:<?= htmlspecialchars($clrP) ?>;">
                    / <?= htmlspecialchars($row['credit']) ?> Kredi
                  </span>
                </div>
              </div>

              <div class="pkg-divider"></div>

              <!-- Özellikler -->
              <div class="pkg-feats">
                <ul class="list-unstyled mb-0">
                  <?php foreach ($details as $d): ?>
                    <li style="color:<?= htmlspecialchars($clrFt) ?>;">
                      <i class="ph ph-check-circle" style="color:<?= htmlspecialchars($clrIc) ?>;"></i>
                      <span><?= htmlspecialchars($d) ?></span>
                    </li>
                  <?php endforeach; ?>
                </ul>
              </div>

              <!-- CTA -->
              <div class="pkg-bottom">
                <?php if ($isActive): ?>
                  <div class="btn-active" style="color:<?= htmlspecialchars($clrT) ?>;">
                    <i class="ph ph-check-circle me-2"></i>Aktif Paket
                  </div>
                  <p class="pkg-expire-note" style="color:<?= htmlspecialchars($clrD) ?>;">
                    <i class="ph ph-calendar me-1"></i>
                    <?= turkcetarih('j F Y', $bought['expired_date']) ?> tarihinde sona erer
                  </p>
                <?php else: ?>
                  <form action="student/payment/index.php" method="POST">
                    <div class="pkg-coupon-wrap">
                      <i class="ph ph-tag" style="color:<?= htmlspecialchars($clrIc) ?>;"></i>
                      <input type="text" name="affilate"
                             class="pkg-coupon-input"
                             placeholder="İndirim kodunuz varsa girin">
                    </div>
                    <input type="hidden" name="id" value="<?= $packageId ?>">
                    <button type="submit" class="btn-buy">
                      <i class="ph ph-shopping-cart me-2"></i>Paketi Satın Al
                    </button>
                  </form>
                <?php endif; ?>
              </div>

            </div>
            <?php endwhile; ?>

          </div><!-- /pkg-grid -->
        </div>
      </div>

    </div>
    <?php include '../includes_panel/footer.php'; ?>
  </div>

  <?php include '../includes_panel/scripts.php'; ?>
  <?php include 'includes/student-scripts.php'; ?>
  <?php include 'includes/vbs-scripts.php'; ?>
</body>
</html>
