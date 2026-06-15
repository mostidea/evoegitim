<?php
session_start();
include("../config/connection.php");
checkUnSession();

// Mevcut tarih ve saat
$current_datetime = date("Y-m-d H:i:s");

// Öğrenci bilgilerini al
$studentData = getStudentInfo($db, $_SESSION["email"]);
$userInfo = $studentData['user_info'] ?? null;
$inviteParentInfo = $studentData['invite_parent_info'] ?? null;
$studentId = $userInfo["id"] ?? 0;

// SQL sorgusu: expired_date bugünkü tarih ve saatten büyük olan en son satın alımı alıyoruz
$loginData = $db->prepare("
    SELECT order_report.*, package.title, package.description, package.details FROM order_report INNER JOIN package ON order_report.product_id=package.id
    WHERE parent_id = :parent_id  AND status=1
    AND order_report.expired_date > :current_datetime 
    ORDER BY order_report.expired_date ASC 
");
$loginData->bindParam(":parent_id", $_SESSION["user_id"]);
$loginData->bindParam(":current_datetime", $current_datetime);
$loginData->execute();

$buyData = $db->prepare("
    SELECT order_report.*, package.title, package.description, package.details FROM order_report INNER JOIN package ON order_report.product_id=package.id
    WHERE parent_id = :parent_id 
    ORDER BY order_report.id DESC 
");
$buyData->bindParam(":parent_id", $_SESSION["user_id"]);
$buyData->execute();

?>

<!DOCTYPE html>
<html lang="tr">

<head>
  <?php include "../includes_panel/meta.php"; ?>
  <title>Eğitim Platformu  | Evo Eğitim</title>
</head>

<body>
  <!--==================== Preloader Start ====================-->
  <?php include 'includes/left-menu.php'; ?>
  <!-- ============================ Sidebar End  ============================ -->

  <div class="dashboard-main-wrapper">
    <?php include 'includes/top-menu.php'; ?>

    <div class="dashboard-body">
      <!-- Breadcrumb Start -->

      <!-- Breadcrumb End -->

      <div class="card overflow-hidden">
        <div class="card-body p-0">


          <div class="setting-profile px-24 mt-24">
          <div class="flex-between">
              <div class="d-flex align-items-end flex-wrap mb-32 gap-24">
                <img src="<?php echo $_SESSION["profile_photo"]=="" ? "assets/images/thumbs/setting-profile-img.jpg" : "/" . $_SESSION["profile_photo"]; ?>" alt=""
                  class="w-120 h-120 rounded-circle border border-white" />
                <div>
                  <h4 class="mb-8"><?php echo $_SESSION["fullname"]; ?></h4>
                  <div class="setting-profile__infos flex-align flex-wrap gap-16">
             
              
                    <div class="flex-align gap-6">
                      <span class="text-gray-600 d-flex text-lg"><i class="ph ph-calendar-dots"></i></span>
                      <span class="text-gray-600 d-flex text-15"><?php echo turkcetarih('j F Y, l', $_SESSION["created_at"]); ?> Tarihinden Beri Üyemizsiniz.</span>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            <ul class="nav common-tab style-two nav-pills mb-0" id="pills-tab" role="tablist">
              <li class="nav-item" role="presentation">
                <button class="nav-link <?php if (!isset($_GET["tab"])) {
                                          echo "active";
                                        } ?>" id="pills-details-tab" data-bs-toggle="pill"
                  data-bs-target="#pills-details" type="button" role="tab" aria-controls="pills-details"
                  aria-selected="true">
                  Profilim
                </button>
              </li>

              <li class="nav-item" role="presentation">
                <button class="nav-link <?php if (isset($_GET["tab"])) {
                                          echo "active";
                                        } ?>" id="pills-password-tab" data-bs-toggle="pill"
                  data-bs-target="#pills-password" type="button" role="tab" aria-controls="pills-password"
                  aria-selected="false">
                  Åifremi Yenile
                </button>
              </li>
              <li class="nav-item" role="presentation">
                <button class="nav-link" id="pills-plan-tab" data-bs-toggle="pill" data-bs-target="#pills-plan"
                  type="button" role="tab" aria-controls="pills-plan" aria-selected="false">
                  Mevcut Paketim
                </button>
              </li>
              <li class="nav-item" role="presentation">
                <button class="nav-link" id="pills-billing-tab" data-bs-toggle="pill" data-bs-target="#pills-billing"
                  type="button" role="tab" aria-controls="pills-billing" aria-selected="false">
                  Geçmiş Satın Alımlarım
                </button>
              </li>

            </ul>
          </div>
        </div>
      </div>

      <div class="tab-content" id="pills-tabContent">
        <!-- My Details Tab start -->
        <div class="tab-pane fade <?php if (!isset($_GET["tab"])) {
                                    echo "show active";
                                  } ?>" id="pills-details" role="tabpanel" aria-labelledby="pills-details-tab"
          tabindex="0">
          <div class="card mt-24">

            <div class="card-header border-bottom">
              <h4 class="mb-4">Profil Bilgilerim</h4>
              <p class="text-gray-600 text-15">Lütfen bilgilerinizin doğruluğunu teyit edin.</p>

            </div>
            <div class="card-body">
              <form action="vbs/profile/profile-update.php" method="POST" enctype="multipart/form-data">
                <div class="row gy-4">
                  <div class="col-sm-6 col-xs-6">
                    <label for="fname" class="form-label mb-8 h6">Ad Soyad</label>
                    <input type="text" name="fullname" class="form-control py-11" id="fname" placeholder="Ad Soyad"
                      value="<?php echo $_SESSION["fullname"]; ?>" />
                  </div>
                  <div class="col-sm-6 col-xs-6">
                    <label for="email" class="form-label mb-8 h6">Email</label>
                    <input type="email" name="email" class="form-control py-11" id="email" placeholder="Email Adresiniz"
                      value="<?php echo $_SESSION["email"]; ?>" />
                  </div>
                  <div class="col-sm-6 col-xs-6">
                    <label for="phone" class="form-label mb-8 h6">Telefon Numarası (Başında 0 olacak şekilde giriniz)</label>
                    <input type="number" name="phone" class="form-control py-11" id="phone"
                      placeholder="Telefon Numarası" value="<?php echo $_SESSION["phone"]; ?>" />
                  </div>
                  <div class="col-sm-6 col-xs-6">
                <label for="imageUpload" class="form-label mb-8 h6">Profil Fotoğrafı</label>
                <div class="flex-align flex-column gap-22 mb-3">
                  <div class="avatar-upload flex-shrink-0">
                    <input type="file" id="imageUpload" name="image" accept=".png, .jpg, .jpeg" />
                    <div class="avatar-preview w-auto h-auto">
                      <div id="profileImagePreview"
                        style="background-image: url('<?php echo $_SESSION["profile_photo"]=="" ? "assets/images/thumbs/setting-profile-img.jpg" : "/" . $_SESSION["profile_photo"]; ?>');"
                        class="rounded-0 w-h-100px"></div>
                    </div>
                  </div>
                  <div
                    class="avatar-upload-box text-center position-relative flex-grow-1 py-24 px-4 rounded-16 border border-main-300 border-dashed bg-main-50 hover-bg-main-100 hover-border-main-400 transition-2 cursor-pointer">
                    <label for="imageUpload"
                      class="position-absolute inset-block-start-0 inset-inline-start-0 w-100 h-100 rounded-16 cursor-pointer z-1"></label>
                    <span class="text-32 icon text-main-600 d-inline-flex"><i class="ph ph-upload"></i></span>
                    <span class="text-13 d-block text-gray-400 text my-8">Yüklemek için tıklayın veya sürükleyip
                      bırakın.<br><b> Fotoğrafınız boydan değil yalnızca yüzünüzün göründüğü biçimde olmalı.</b></span>
                    <span class="text-13 d-block text-main-600">(Zorunlu olmayan alan)</span>
                  </div>
                </div>
              </div>
                  <div class="col-sm-6 col-xs-6">
                    <label for="fname" class="form-label mb-8 h6">Meslek</label>
                    <input type="text" name="job" class="form-control py-11" id="fname" placeholder="Meslek"
                      value="<?php echo $_SESSION["job"]; ?>" />
                  </div>
                  <div class="col-sm-6 col-xs-6">
                    <label for="fname" class="form-label mb-8 h6">Öğrencimle Yakınlık Derecem</label>
                    <input type="text" name="family_rank" class="form-control py-11" id="fname"
                      placeholder="Öğrencimle Yakınlık Derecem" value="<?php echo $_SESSION["family_rank"]; ?>" />
                  </div>
                  <?php if (isset($_GET["success"]) && !isset($_GET["tab"])) { ?>
                  <p class="alert alert-success">Bilgileriniz başarıyla güncellendi.</p>
                  <?php } ?>
                  <?php if (isset($_GET["error"]) && !isset($_GET["tab"])) { ?>
                  <p class="alert alert-danger">Bilgileriniz güncellenemedi, lütfen tekrar deneyiniz.</p>
                  <?php } ?>
                  <div class="col-12">
                    <div class="flex-align justify-content-end gap-8">
                      <button type="submit" class="btn btn-evo
 rounded-pill py-9">Profilimi Güncelle</button>
                    </div>
                  </div>
                </div>
              </form>
            </div>
          </div>
        </div>


        <div class="tab-pane fade <?php if (isset($_GET["tab"])) {
                                    echo "show active";
                                  } ?>" id="pills-password" role="tabpanel" aria-labelledby="pills-password-tab"
          tabindex="0">
          <div class="card mt-24">
            <div class="card-header border-bottom">
              <h4 class="mb-4">Åifremi Yenile</h4>
              <p class="text-gray-600 text-15">Lütfen şifrenizin doğruluğunu teyit edin.</p>
            </div>
            <div class="card-body">
              <div class="row">
                <div class="col-md-6">
                  <form action="vbs/profile/update-password.php" method="POST">
                    <div class="row gy-4">
                      <div class="col-12">
                        <label for="current-password" class="form-label mb-8 h6">Mevcut Åifreniz</label>
                        <div class="position-relative">
                          <input type="text" class="form-control py-11" id="current-password" name="current"
                            placeholder="Mevcut Åifrenizi Giriniz" />
                          <span
                            class="toggle-password position-absolute top-50 inset-inline-end-0 me-16 translate-middle-y ph ph-eye-slash"
                            id="#current-password"></span>
                        </div>
                      </div>
                      <div class="col-12">
                        <label for="new-password" class="form-label mb-8 h6">Yeni Åifreniz</label>
                        <div class="position-relative">
                          <input type="text" class="form-control py-11" id="new-password" name="new"
                            placeholder="Yeni Åifreniz" />
                          <span
                            class="toggle-password position-absolute top-50 inset-inline-end-0 me-16 translate-middle-y ph ph-eye-slash"
                            id="#new-password"></span>
                        </div>
                      </div>

                      <div class="col-12">
                        <label class="form-label mb-8 h6">Åifre Detayları</label>
                        <ul class="list-inside">
                          <li class="text-gray-600 mb-4">Lütfen şifrenizi yenilemeden önce göz ikonuna basarak kontrol
                            ediniz</li>
                          <li class="text-gray-600 mb-4">Åifrenizi kimseyle paylaşmayınız</li>
                        </ul>
                      </div>

                    </div>

                </div>
                <?php if (isset($_GET["success"]) && @$_GET["tab"] == 2) { ?>
                <p class="alert alert-success">Bilgileriniz başarıyla güncellendi.</p>
                <?php } ?>

                <?php if (isset($_GET["error"]) && @$_GET["tab"] == 2 && @$_GET["error"] == 1) { ?>
                <p class="alert alert-danger">Değerli velimiz, girmiş olduğunuz mevcut şifreniz yanlış.</p>
                <?php } ?>

                <div class="col-12">
                  <div class="flex-align justify-content-end gap-8">
                    <button type="submit" class="btn btn-evo
 rounded-pill py-9">Åifremi Yenile</button>
                    </form>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
        <!-- Password Tab End -->
<style>
.package-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 20px;
    align-items: stretch;
}

.plan-item {
    display: flex;
    flex-direction: column;
    height: 100%;
}

.plan-item ul {
    flex: 1;
}
</style>
        <!-- Plan Tab Start -->
        <div class="tab-pane fade" id="pills-plan" role="tabpanel" aria-labelledby="pills-plan-tab" tabindex="0">
          <div class="card mt-24">
            <div class="card-header border-bottom">
              <h4 class="mb-4">Mevcut Paketim</h4>
              <p class="text-gray-600 text-15">Aktif paket detaylarınız.</p>
            </div>
            <div class="card-body">
              <div class="row gy-4">
                <?php
                if (!$loginData->rowCount() > 0) { ?>
                <div class="col-12">
                  <label class="form-label mb-8 h6 mt-32">Aktif Paketiniz Bulunamadı</label>
                  <ul class="list-inside">
                      <li class="text-gray-600 mb-4">
                      1. Panelinizin Paketler kısmına tıklayarak paket satın alabilirsiniz.
                    </li>
                    <li class="text-gray-600 mb-4">
                      2. Satın aldığınız paket hesabınıza tanımlanacaktır.
                    </li>
                    <li class="text-gray-600 mb-4">
                      3. Yalnızca Veli hesabından değil, öğrenci hesabından da paket satın alabilirsiniz.
                    </li>
                  </ul>
                  <button type="button" class="btn btn-evo
 text-sm btn-sm px-24 rounded-pill py-12 d-flex align-items-center gap-2 mt-24">
                    <i class="ph ph-plus me-4"></i>
                    <a href="vbs/package.php" href="vbs/package.php" class="text-white">
                    Solo Ders Paketi Satın Al
                </a>
                  
                  </button>
                                    </button>
                      <button type="button" class="btn btn-evo
 text-sm btn-sm px-24 rounded-pill py-12 d-flex align-items-center gap-2 mt-24">
                    <i class="ph ph-plus me-4"></i>
                    <a href="vbs/group-package.php" class="text-white">
                    Grup Ders Paketi Satın Al
                </a>
                 

                  </button>
                </div>
                
                <?php
                } else {
                  while ($row = $loginData->fetch(PDO::FETCH_ASSOC)) {

                                        $productDetail = $db->prepare("SELECT SUM(credit) AS credit FROM active_credit WHERE user_id = :uid AND product_id = :pid AND credit > 0 AND type <=> :ctype");
$productDetail->bindParam(":uid", $studentId);
$productDetail->bindParam(":pid", $row["product_id"]);
$ctype = creditNormalizeType($row["type"] ?? null);
$productDetail->bindParam(":ctype", $ctype);
$productDetail->execute();
$pDetail = $productDetail->fetch(PDO::FETCH_ASSOC);
$pDetailCredit = $pDetail["credit"] ?? 0;

if($pDetailCredit == 0){
    $style = "display:none";
} else {
    $style = "";
}
                   
                  ?>
                <div class="col-md-4 col-sm-6" style="<?php echo $style; ?>">
                  <div class="plan-item rounded-16 border border-gray-100 transition-2 position-relative active">
                    <span
                      class="plan-badge bg-evo py-4 px-16 text-white position-absolute inset-inline-end-0 inset-block-start-0 mt-8 text-15">
                      <?php echo turkcetarih('j F Y , l H:i', $row["expired_date"]); ?> Tarihinde Sona Erecek
                    </span>
                    <span class="text-2xl d-flex mb-16 text-main-600 mt-5"></span><br>
                    <h3 class="mb-4"><?php echo $row["title"]; ?></h3>
                    <span class="text-gray-600"><?php echo $row["description"]; ?></span>
                    <h2 class="h1 fw-medium text-main mb-16 mt-16 d-flex gap-4">
                      <?php echo $row["price"]; ?>â‚º / <?php echo $row["credit"]; ?> Kredi
                    </h2>
                    <p class="pb-32 border-bottom border-gray-100">
                      <span class="<?php echo $pDetailCredit > 0 ? 'text-success' : 'text-danger'; ?> fw-semibold">
                        <?php echo (int)$pDetailCredit; ?> Kredi Kalan
                      </span>
                    </p>

                    <ul>
                      <?php
                          // details verisini virgül ile ayırarak ul li içinde yazdırıyoruz
                          $details = explode(",", $row["details"]);
                          foreach ($details as $detail) { ?>
                      <li class="flex-align gap-8 text-gray-600 mb-lg-4 mb-20">
                        <span class="text-24 d-flex text-main-600"><i class="ph ph-check-circle"></i></span>
                        <?php echo trim($detail); ?>
                        <!-- trim() ile boşlukları kaldırıyoruz -->
                      </li>
                      <?php } ?>
                    </ul>

                    <a href="vbs/package.php" class="btn btn-evo w-100 rounded-pill py-16  text-17 fw-medium mt-32">Tüm
                      Paketleri Gör</a>
                  </div>
                </div>
                <?php
                  }
                }
                ?>




              </div>
            </div>
          </div>
        </div>
        <!-- Plan Tab End -->

        <!-- Billing Tab Start -->
        <div class="tab-pane fade" id="pills-billing" role="tabpanel" aria-labelledby="pills-billing-tab" tabindex="0">

          <div class="card mt-24">
            <div class="card-header border-bottom">
              <div class="flex-between flex-wrap gap-16">
                <div>
                  <h4 class="mb-4">Geçmiş Satın Alımlarım</h4>
                  <p class="text-gray-600 text-15">Velisi olduğunuz öğrenciye ait satın alım geçmişi.</p>
                </div>

              </div>
            </div>



            <div class="card-body table-responsive">
    <table id="vbsSatinAlimTable" class="table table-lg table-striped w-100">
        <thead>
            <tr>
                <th class="h6 text-gray-600 text-center">Paket Adı</th>
                <th class="h6 text-gray-600 text-center">Paket Türü</th>
                <th class="h6 text-gray-600 text-center">Tutar</th>
                <th class="h6 text-gray-600 text-center">Satın Alınan Kredi</th>
                <th class="h6 text-gray-600 text-center">Kalan Kredi</th>
                <th class="h6 text-gray-600 text-center">Affilate</th>
                <th class="h6 text-gray-600 text-center">Durum</th>
                <th class="h6 text-gray-600 text-center">Satın Alım Tarihi</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($buyRow = $buyData->fetch(PDO::FETCH_ASSOC)) { 
                // Kredi detaylarını çek
                $productDetail = $db->prepare("SELECT SUM(credit) AS credit FROM active_credit WHERE user_id = :uid AND product_id = :product_id AND credit > 0 AND type <=> :ctype");
                $productDetail->bindParam(":uid", $studentId);
                $productDetail->bindParam(":product_id", $buyRow["product_id"]);
                $ctype = creditNormalizeType($buyRow["type"] ?? null);
                $productDetail->bindParam(":ctype", $ctype);
                $productDetail->execute();
                $pDetail = $productDetail->fetch(PDO::FETCH_ASSOC);
                $kalanKredi = $pDetail["credit"] ?? 0;
            ?>
            <tr>
                <!-- Paket Adı -->
                <td class="text-center">
                    <h6 class="mb-0"><?php echo $buyRow["title"]; ?></h6>
                    <small class="text-muted">
                        Bitiş - <?php echo turkcetarih('j F Y, l H:i', $buyRow["expired_date"]); ?>
                    </small>
                </td>
                
                <!-- Paket Türü -->
                <td class="text-center">
                    <?php echo $buyRow["type"] != 1 ? "Solo Ders Paketi" : "Grup Ders Paketi"; ?>
                </td>
                
                <!-- Tutar -->
                <td class="text-center"><?php echo $buyRow["price"]; ?>â‚º</td>
                
                <!-- Satın Alınan Kredi -->
                <td class="text-center"><?php echo $buyRow["credit"]; ?> Kredi</td>
                
                <!-- Kalan Kredi -->
                <td class="text-center">
                    <?php 
                    if($buyRow["status"] == 0) {
                        echo '<span class="text-danger">Ödeme Alınamadı</span>';
                    } else {
                        if($kalanKredi == 0) {
                            echo '<span class="text-danger">0 Kredi</span>';
                        } else {
                            echo '<span class="text-success">' . $kalanKredi . ' Kredi</span>';
                        }
                    }
                    ?>
                </td>
                
                <!-- Affilate -->
                <td class="text-center">
                    <?php echo empty($buyRow["affilate"]) ? "-" : $buyRow["affilate"]; ?>
                </td>
                
                <!-- Durum -->
                <td class="text-center">
                    <?php if ($buyRow["status"] == 1) { ?>
                        <span class="badge bg-success">Ödeme Yapıldı</span>
                    <?php } else { ?>
                        <span class="badge bg-danger">Ödeme Hatası</span>
                    <?php } ?>
                </td>
                
                <!-- Satın Alım Tarihi -->
                <td class="text-center">
                    <?php echo turkcetarih('j F Y, l H:i', $buyRow["created_at"]); ?>
                </td>
            </tr>
            <?php } ?>
        </tbody>
    </table>
</div>

          </div>
          <!-- Billing history End -->
        </div>
        <!-- Billing Tab End -->

        <!-- Notification Tab Start -->

        <!-- Notification Tab End -->
      </div>
    </div>
    <?php include '../includes_panel/footer.php'; ?>
  </div>

  <?php include '../includes_panel/scripts.php'; ?>
<?php include 'includes/vbs-scripts.php'; ?>
<?php include 'includes/multiple-student-scripts.php'; ?>

  <script>
  // ============================= Avatar Upload js =============================
  function uploadImageFunction(imageId, previewId) {
    $(imageId).on("change", function() {
      var input = this; // 'this' is the DOM element here
      if (input.files && input.files[0]) {
        var reader = new FileReader();
        reader.onload = function(e) {
          $(previewId).css("background-image", "url(" + e.target.result + ")");
          $(previewId).hide();
          $(previewId).fadeIn(650);
        };
        reader.readAsDataURL(input.files[0]);
      }
    });
  }
  uploadImageFunction("#coverImageUpload", "#coverImagePreview");
  uploadImageFunction("#imageUpload", "#profileImagePreview");

  // ============================= Initialize Quill editor js Start =============================
  function editorFunction(editorId) {
    const quill = new Quill(editorId, {
      theme: "snow",
    });
  }
  editorFunction("#editor");
  editorFunction("#editorTwo");
  // ============================= Initialize Quill editor js End =============================

  // Table Header Checkbox checked all js Start
  $("#selectAll").on("change", function() {
    $(".form-check .form-check-input").prop("checked", $(this).prop("checked"));
  });

  $('#vbsSatinAlimTable').DataTable({
    autoWidth: false,
    scrollX: false,
    order: [],
    language: { url: '//cdn.datatables.net/plug-ins/1.13.4/i18n/tr.json' }
  });
  </script>
</body>

</html>
