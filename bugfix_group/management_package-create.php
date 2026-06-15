<?php
error_reporting(E_ALL);
ini_set("display_errors", 1);
session_start();
include("../config/connection.php");
checkUnSession();

// Değişkenleri tanımla
$id = isset($_GET['id']) ? intval($_GET['id']) : null;

// Mevcut paketi getir
$packageData = null;
if ($id) {
    $stmt = $db->prepare("SELECT * FROM package WHERE id = ?");
    $stmt->execute([$id]);
    $packageData = $stmt->fetch(PDO::FETCH_ASSOC);
}

// Form gönderildiğinde işle
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title = $_POST['title'];
    $description = $_POST['description'];
    $price = $_POST['price'];
    $credit = intval($_POST['credit']);
    $expired_date = intval($_POST['expired_date']);
    $discount = $_POST['discount'];
    $details = $_POST['details'];
    $campaign = intval($_POST['campaign']);
    $category = intval($_POST['category']);
    $type = $_POST['type'] != 1 ? NULL : 1;
    $color1 = $_POST['color1'];
    $color2 = $_POST['color2'];
    $color3 = $_POST['color3'];
    $color4 = $_POST['color4'];
    $color5 = $_POST['color5'];
    $color6 = $_POST['color6'];
    $color7 = $_POST['color7'];
    $color8 = $_POST['color8'];
    $color9 = $_POST['color9'];
    $color10 = $_POST['color10'];
    $color11 = $_POST['color11'];

    if ($id) {
        // Güncelleme
        $updateStmt = $db->prepare(
            "UPDATE package SET 
                title = ?, 
                description = ?, 
                price = ?, 
                credit = ?, 
                expired_date = ?, 
                discount = ?, 
                details = ?, 
                campaign = ?, 
                category = ?,
                type = ?, 
                color1 = ?, 
                color2 = ?, 
                color3 = ?, 
                color4 = ?, 
                color5 = ?, 
                color6 = ?, 
                color7 = ?, 
                color8 = ?, 
                color9 = ?, 
                color10 = ?, 
                color11 = ? 
            WHERE id = ?"
        );
        $updateStmt->execute([$title, $description, $price, $credit, $expired_date, $discount, $details, $campaign, $category,$type, $color1, $color2, $color3, $color4, $color5, $color6, $color7, $color8, $color9, $color10, $color11, $id]);
    } else {
        // Yeni ekleme
        $insertStmt = $db->prepare(
            "INSERT INTO package SET 
                title = ?, 
                description = ?, 
                price = ?, 
                credit = ?, 
                expired_date = ?, 
                discount = ?, 
                details = ?, 
                campaign = ?, 
                category = ?, 
                type = ?, 
                color1 = ?, 
                color2 = ?, 
                color3 = ?, 
                color4 = ?, 
                color5 = ?, 
                color6 = ?, 
                color7 = ?, 
                color8 = ?, 
                color9 = ?, 
                color10 = ?, 
                color11 = ?"
        );
        $insertStmt->execute([$title, $description, $price, $credit, $expired_date, $discount, $details, $campaign, $category,$type, $color1, $color2, $color3, $color4, $color5, $color6, $color7, $color8, $color9, $color10, $color11]);
    }


    // Yönlendirme
    header("Location: package.php");
    exit();
}

// Affilate tablosundan kampanya verilerini çek
$campaignData = $db->prepare("SELECT id, affilate FROM affilate WHERE status = 1");
$campaignData->execute();
$campaigns = $campaignData->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="tr">

<head>
    <?php include '../includes/meta.php'; ?>
    <title>Paket Düzenle | Evo Eğitim</title>
    <style>
          h2{
    position: relative;
  }
  .small{
    position: absolute;
    top: 35px;
    font-size: 1.5rem;
  }
    </style>
</head>

<body>
    <?php include 'includes/left-menu.php'; ?>
    <div class="dashboard-main-wrapper">
        <?php include 'includes/top-menu.php'; ?>

        <div class="dashboard-body">
            <div class="card">
                <div class="card-header">
                    <h5><?php echo $id ? "Paket Güncelle" : "Paket Oluştur"; ?></h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-lg-6">
                            <form action="" method="POST">
                                <div class="row">
                                    <div class="col-lg-6 mb-3">
                                        <label class="form-label">Paket Başlığı</label>
                                        <input type="text" name="title" class="form-control" required
                                            value="<?php echo htmlspecialchars($packageData['title'] ?? ''); ?>">
                                    </div>
                                    <div class="col-lg-6 mb-3">
                                        <label class="form-label">Kısa Açıklama</label>
                                        <input type="text" name="description" class="form-control" required
                                            value="<?php echo htmlspecialchars($packageData['description'] ?? ''); ?>">
                                    </div>
                                    <div class="col-lg-6 mb-3">
                                        <label class="form-label">Fiyat</label>
                                        <input type="text" name="price" class="form-control" required
                                            value="<?php echo htmlspecialchars($packageData['price'] ?? ''); ?>">
                                    </div>
                                    <div class="col-lg-6 mb-3">
                                        <label class="form-label">Kredi</label>
                                        <input type="number" name="credit" class="form-control" required
                                            value="<?php echo htmlspecialchars($packageData['credit'] ?? ''); ?>">
                                    </div>
                                    <div class="col-lg-6 mb-3">
                                        <label class="form-label">Süresi (Gün)</label>
                                        <input type="number" name="expired_date" class="form-control" required
                                            value="<?php echo htmlspecialchars($packageData['expired_date'] ?? ''); ?>">
                                    </div>
                                    <div class="col-lg-6 mb-3">
                                        <label class="form-label">İndirimli Fiyat</label>
                                        <input type="text" name="discount" class="form-control"
                                            value="<?php echo htmlspecialchars($packageData['discount'] ?? ''); ?>">
                                    </div>
                                    <div class="col-lg-12 mb-3">
                                        <label class="form-label">Detaylar (Virgül ile ayrılı)</label>
                                        <textarea name="details" class="form-control" rows="4"><?php echo htmlspecialchars($packageData['details'] ?? ''); ?></textarea>
                                    </div>
                                    <div class="col-lg-6 mb-3">
                                        <label class="form-label">Kampanya</label>
                                        <select name="campaign" class="form-control">
                                            <option value="">Seçiniz</option>
                                            <?php foreach ($campaigns as $campaign): ?>
                                                <option value="<?php echo $campaign['id']; ?>" <?php echo (isset($packageData['campaign']) && $packageData['campaign'] == $campaign['id']) ? 'selected' : ''; ?>>
                                                    <?php echo htmlspecialchars($campaign['affilate']); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="col-lg-6 mb-3">
                                        <label class="form-label">Paket Türü</label>
                                        <select name="type" class="form-control">
                                            <option value="">Seçiniz</option>
                                            <option value="" <?php echo (isset($packageData['type']) && $packageData['type'] != 1) ? 'selected' : ''; ?>>Solo Ders Paketi</option>
                                            <option value="1" <?php echo (isset($packageData['type']) && $packageData['type'] == 1) ? 'selected' : ''; ?>>Grup Ders Paketi</option>
                                        </select>
                                    </div>
                                    <div class="col-lg-6 mb-3">
                                        <label class="form-label">Kategori</label>
                                        <select name="category" class="form-control">
                                            <option value="">Seçiniz</option>
                                            <option value="1" <?php echo (isset($packageData['category']) && $packageData['category'] == 1) ? 'selected' : ''; ?>>Genel Dersler</option>
                                            <option value="2" <?php echo (isset($packageData['category']) && $packageData['category'] == 2) ? 'selected' : ''; ?>>Rehberlik</option>
                                        </select>
                                    </div>

                                    <div class="col-lg-6 mb-3">
                                        <label class="form-label">Paket Arka Plan Rengi</label>
                                        <input type="color" name="color1" class="form-control"
                                            value="<?php echo htmlspecialchars($packageData['color1'] ?? '#ffffff'); ?>" required>
                                    </div>
                                    <div class="col-lg-6 mb-3">
                                        <label class="form-label">Paket Başlık Rengi</label>
                                        <input type="color" name="color2" class="form-control"
                                            value="<?php echo htmlspecialchars($packageData['color2'] ?? ''); ?>" required>
                                    </div>
                                    <div class="col-lg-6 mb-3">
                                        <label class="form-label">Paket Açıklama Rengi</label>
                                        <input type="color" name="color3" class="form-control"
                                            value="<?php echo htmlspecialchars($packageData['color3'] ?? ''); ?>" required>
                                    </div>
                                    <div class="col-lg-6 mb-3">
                                        <label class="form-label">Paket Kredi/Tutar Rengi</label>
                                        <input type="color" name="color4" class="form-control"
                                            value="<?php echo htmlspecialchars($packageData['color4'] ?? ''); ?>" required>
                                    </div>
                                    <div class="col-lg-6 mb-3">
                                        <label class="form-label">Paket Ayırtıcı Çizgi Rengi</label>
                                        <input type="color" name="color5" class="form-control"
                                            value="<?php echo htmlspecialchars($packageData['color5'] ?? ''); ?>" required>
                                    </div>
                                    <div class="col-lg-6 mb-3">
                                        <label class="form-label">Paket Detaylar İkon Rengi</label>
                                        <input type="color" name="color6" class="form-control"
                                            value="<?php echo htmlspecialchars($packageData['color6'] ?? ''); ?>" required>
                                    </div>
                                    <div class="col-lg-6 mb-3">
                                        <label class="form-label">Paket Detaylar Yazı Rengi</label>
                                        <input type="color" name="color7" class="form-control"
                                            value="<?php echo htmlspecialchars($packageData['color7'] ?? ''); ?>" required>
                                    </div>
                                    <div class="col-lg-6 mb-3">
                                        <label class="form-label">Paket İndirim Kodu Rengi</label>
                                        <input type="color" name="color8" class="form-control"
                                            value="<?php echo htmlspecialchars($packageData['color8'] ?? ''); ?>" required>
                                    </div>
                                    <div class="col-lg-6 mb-3">
                                        <label class="form-label">Paket İndirim Kodu Çerçeve Rengi</label>
                                        <input type="color" name="color9" class="form-control"
                                            value="<?php echo htmlspecialchars($packageData['color9'] ?? ''); ?>" required>
                                    </div>
                                    <div class="col-lg-6 mb-3">
                                        <label class="form-label">Paket Satın Al Buton Rengi</label>
                                        <input type="color" name="color10" class="form-control"
                                            value="<?php echo htmlspecialchars($packageData['color10'] ?? ''); ?>" required>
                                    </div>
                                    <div class="col-lg-6 mb-3">
                                        <label class="form-label">Paket Satın Al Yazı Rengi</label>
                                        <input type="color" name="color11" class="form-control"
                                            value="<?php echo htmlspecialchars($packageData['color11'] ?? ''); ?>" required>
                                    </div>

                                </div>

                                <button type="submit" class="btn btn-primary"><?php echo $id ? "Güncelle" : "Oluştur"; ?></button>
                            </form>
                        </div>
                        <div class="col-lg-6">
                            <div id="preview-wrapper" class="plan-item rounded-16 border border-gray-100 transition-2 position-relative" style="background-color: _Paket Arka Plan Rengi_ !important">
                                <h3 id="preview-title" class="mb-4" style="color: _Paket Başlık Rengi_ !important">_Paket Başlığı_</h3>
                                <span id="preview-description" class="min-height-45" style="color: _Paket Açıklama Rengi_ !important">_Kısa Açıklama_</span>
                                <h2 id="preview-price-credit" class="h1 fw-medium  mb-32 mt-16 pb-32 border-bottom  d-flex gap-4"
                                    style="color: _Paket Kredi/Tutar Rengi_ !important; border-bottom-color: _Paket Ayırtıcı Çizgi Rengi_ !important">
                                    <span id="preview-price">_Fiyat_</span> ₺ / <span id="preview-credit">_Kredi_</span> &nbsp;Kredi
                                </h2>

                                <ul id="preview-details">
                                    <!-- Detaylar liste elemanları jquery ile oluşturulacak -->
                                    <li class="flex-align gap-8 text-gray-600 mb-lg-4 mb-20" style="color: _Paket Detaylar Yazı Rengi_ !important">
                                        <span class="text-24 d-flex text-main-600">
                                            <i class="ph ph-check-circle preview-detail-icon" style="color: _Paket Detaylar İkon Rengi_ !important"></i>
                                        </span>
                                        _Detaylar_
                                    </li>
                                </ul>

                                <form action="student/payment/index.php" method="POST">
                                    <div class="mb-3">
                                        <label id="preview-discount-label" class="form-label mb-8 h6" style="color: _Paket İndirim Kodu Rengi_ !important">İndirim Kodu:</label>
                                        <input type="text" name="affilate" class="form-control only-border" id="preview-discount-input" style="border-color: _Paket İndirim Kodu Çerçeve Rengi_ !important;" placeholder="İndirim Kodunuz" />
                                    </div>
                                    <input type="hidden" name="id" value="6" />
                                    <button id="preview-button" type="button" class="btn  w-100 rounded-pill py-16 border-main-600 text-17 fw-medium mt-32"
                                        style="color: _Paket Satın Al Yazı Rengi_ !important; background-color: _Paket Satın Al Buton Rengi_ !important">
                                        Paketi Satın Al
                                    </button>
                                </form>
                            </div>


                        </div>
                    </div>


                </div>
            </div>
        </div>
        <?php include '../includes/footer.php'; ?>
    </div>

    <?php include '../includes/scripts.php'; ?>
    <script>
        function formatTR(n) {
            var num = parseInt(n, 10);
            return isNaN(num) ? n : num.toLocaleString('tr-TR');
        }

        function updatePriceCredit() {
            var price = $('input[name="price"]').val() || '_Fiyat_';
            var discount = $('input[name="discount"]').val();
            var credit = $('input[name="credit"]').val() || '_Kredi_';

            if (discount.trim() !== "") {
                var discounted = parseInt(price, 10) - parseInt(discount, 10);
                $('#preview-price-credit').html(
                    '<span class="text-decoration-line-through small">' + formatTR(price) + '₺</span> ' +
                    '<span>' + formatTR(discounted) + '</span>₺ / <span id="preview-credit">' + credit + '</span> &nbsp;Kredi'
                );
            } else {
                $('#preview-price-credit').html(
                    '<span id="preview-price">' + formatTR(price) + '</span>₺ / <span id="preview-credit">' + credit + '</span> &nbsp;Kredi'
                );
            }
        }


        $(document).ready(function() {

            function updateTitle() {
                $('#preview-title').text($('input[name="title"]').val() || '_Paket Başlığı_');
            }

            function updateDescription() {
                $('#preview-description').text($('input[name="description"]').val() || '_Kısa Açıklama_');
            }

            function updatePrice() {
                $('#preview-price').text($('input[name="price"]').val() || '_Fiyat_');
            }

            function updateCredit() {
                $('#preview-credit').text($('input[name="credit"]').val() || '_Kredi_');
            }

            function updateBackgroundColor() {
                $('#preview-wrapper').css('background-color', $('input[name="color1"]').val());
            }

            function updateTitleColor() {
                $('#preview-title').css('color', $('input[name="color2"]').val());
            }

            function updateDescriptionColor() {
                $('#preview-description').css('color', $('input[name="color3"]').val());
            }

            function updatePriceCreditColor() {
                $('#preview-price-credit').css('color', $('input[name="color4"]').val());
            }

            function updateBorderColor() {
                $('#preview-price-credit').each(function() {
                    this.style.setProperty('border-bottom-color', $('input[name="color5"]').val(), 'important');
                });

                // $('#preview-price-credit').css('border-bottom-color', $('input[name="color5"]').val() + " !important");
            }

            function updateDetailIconColor() {
                $('.preview-detail-icon').css('color', $('input[name="color6"]').val());
            }

            function updateDetailTextColor() {
                $('#preview-details li').css('color', $('input[name="color7"]').val());
            }

            function updateDiscountLabelColor() {
                $('#preview-discount-label').css('color', $('input[name="color8"]').val());
            }

            function updateDiscountInputBorder() {
                $('#preview-discount-input').css('border-color', $('input[name="color9"]').val());
            }

            function updateButtonBgColor() {
                $('#preview-button').css('background-color', $('input[name="color10"]').val());
            }

            function updateButtonTextColor() {
                $('#preview-button').css('color', $('input[name="color11"]').val());
            }

            function updateDetailsList() {
                var detailsValue = $('textarea[name="details"]').val();
                var detailsArr = detailsValue.split(',');
                var detailsList = $('#preview-details');
                detailsList.empty(); // Mevcut liste elemanlarını temizle

                // Her detay için bir li oluştur
                $.each(detailsArr, function(index, detail) {
                    detail = $.trim(detail);
                    if (detail !== "") {
                        var li = $('<li class="flex-align gap-8 text-gray-600 mb-lg-4 mb-20" style="color: ' + ($('input[name="color7"]').val() || '_Paket Detaylar Yazı Rengi_') + ' !important"></li>');
                        li.append('<span class="text-24 d-flex text-main-600"><i class="ph ph-check-circle preview-detail-icon" style="color: ' + ($('input[name="color6"]').val() || '_Paket Detaylar İkon Rengi_') + ' !important"></i></span> ');
                        li.append(detail);
                        detailsList.append(li);
                    }
                });
            }
            $('input[name="price"], input[name="discount"], input[name="credit"]').on('input', updatePriceCredit);
            // Input alanlarına event bağlamaları
            $('input[name="title"]').on('input', updateTitle);
            $('input[name="description"]').on('input', updateDescription);
            $('input[name="price"]').on('input', updatePrice);
            $('input[name="credit"]').on('input', updateCredit);

            $('input[name="color1"]').on('input', updateBackgroundColor);
            $('input[name="color2"]').on('input', updateTitleColor);
            $('input[name="color3"]').on('input', updateDescriptionColor);
            $('input[name="color4"]').on('input', updatePriceCreditColor);
            $('input[name="color5"]').on('input', updateBorderColor);
            $('input[name="color6"]').on('input', function() {
                updateDetailIconColor();
                updateDetailsList(); // İkon rengi değişirse detaylar listesi de güncellensin
            });
            $('input[name="color7"]').on('input', function() {
                updateDetailTextColor();
                updateDetailsList();
            });
            $('input[name="color8"]').on('input', updateDiscountLabelColor);
            $('input[name="color9"]').on('input', updateDiscountInputBorder);
            $('input[name="color10"]').on('input', updateButtonBgColor);
            $('input[name="color11"]').on('input', updateButtonTextColor);

            $('textarea[name="details"]').on('input', updateDetailsList);

            // Sayfa yüklendiğinde mevcut değerleri güncelle
            updatePriceCredit();
            updateTitle();
            updateDescription();
            updatePrice();
            updateCredit();
            updateBackgroundColor();
            updateTitleColor();
            updateDescriptionColor();
            updatePriceCreditColor();
            updateBorderColor();
            updateDetailIconColor();
            updateDetailTextColor();
            updateDiscountLabelColor();
            updateDiscountInputBorder();
            updateButtonBgColor();
            updateButtonTextColor();
            updateDetailsList();

        });
    </script>

</body>

</html>