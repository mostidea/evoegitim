<?php
session_start();
include("../config/connection.php");
checkUnSession();

// Mevcut tarih ve saat
$current_datetime = date("Y-m-d H:i:s");

// Aylık kazanç verileri
$_tid = $_SESSION["user_id"];
$_allLessons = $db->prepare("
    SELECT a.*, lessons.title AS lit, u.fullname AS student_name, tu.group_income_cost,
           YEAR(a.start_date) AS yr, MONTH(a.start_date) AS mon
    FROM appointment a
    INNER JOIN lessons ON a.lesson_id = lessons.id
    INNER JOIN users u ON a.student_id = u.id
    INNER JOIN users tu ON a.teacher_id = tu.id
    WHERE a.teacher_id = :tid AND a.end_date <= NOW() AND a.status != 3
    ORDER BY a.start_date DESC
");
$_allLessons->execute([':tid' => $_tid]);
$_lessonsByMonth = [];
$_monthTotals = [];
while ($_l = $_allLessons->fetch(PDO::FETCH_ASSOC)) {
    $_key = $_l['yr'].'-'.$_l['mon'];
    $_lessonsByMonth[$_key][] = $_l;
    $_tK = !empty($_l['teacher_join']); $_sK = !empty($_l['student_join']);
    $_inc = $_l['type']==1 ? (float)$_l['group_income_cost'] : (float)$_l['income'];
    if ($_l['type']==1) {
        $_gInc = (float)$_l['group_income_cost'];
        if ((float)$_l['income'] != 0) $_gInc = (float)$_l['income'];
        if ($_tK && $_sK)       $_hak = $_gInc;
        elseif (!$_tK && $_sK)  $_hak = -($_gInc / 2);
        else                    $_hak = 0;
    } else {
        if ($_tK && $_sK)       $_hak = $_inc;
        elseif ($_tK && !$_sK)  $_hak = $_inc / 2;
        elseif (!$_tK && $_sK)  $_hak = -($_inc / 2);
        else                    $_hak = 0;
    }
    if(!isset($_monthTotals[$_key])) $_monthTotals[$_key] = ['solo'=>0,'group'=>0,'total'=>0];
    $_monthTotals[$_key]['solo']  += $_l['type']!=1 ? 1 : 0;
    $_monthTotals[$_key]['group'] += $_l['type']==1 ? 1 : 0;
    $_monthTotals[$_key]['total'] += $_hak;
}
$_payStmt = $db->prepare("SELECT month, year, status, paid_at, receipt_path, total_amount FROM teacher_monthly_payments WHERE teacher_id = :tid");
$_payStmt->execute([':tid' => $_tid]);
$_payStatus = [];
while($_p = $_payStmt->fetch(PDO::FETCH_ASSOC)) {
    $_payStatus[$_p['year'].'-'.$_p['month']] = $_p;
}
$_monthRows = [];
foreach(array_keys($_lessonsByMonth) as $_key) {
    [$_yr, $_mon] = explode('-', $_key);
    $_pay = $_payStatus[$_key] ?? null;
    $_monthRows[] = ['yr'=>$_yr,'mon'=>$_mon,'key'=>$_key,
        'solo_count'=>$_monthTotals[$_key]['solo'],
        'group_count'=>$_monthTotals[$_key]['group'],
        'total_amount'=>($_pay && (int)$_pay['status'] === 1) ? (float)$_pay['total_amount'] : $_monthTotals[$_key]['total'],
        'pay_status'=>$_pay['status'] ?? null,
        'paid_at'=>$_pay['paid_at'] ?? null,
        'receipt_path'=>$_pay['receipt_path'] ?? null,
    ];
}
$_turkishMonths = ['','Ocak','Şubat','Mart','Nisan','Mayıs','Haziran','Temmuz','Ağustos','Eylül','Ekim','Kasım','Aralık'];


$userDatetail = $db->prepare("SELECT client_id, client_secret, account_id,group_count FROM users WHERE id = :id");
$userDatetail->bindParam(":id", $_SESSION["user_id"]);
$userDatetail->execute();
$ude = $userDatetail->fetch(PDO::FETCH_ASSOC);


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
          <!-- Gradient banner -->
          <div style="background: linear-gradient(135deg, #6d00ba 0%, #3a00c4 100%); height: 110px;"></div>
          <div class="setting-profile px-24 pb-0" style="margin-top: -55px; position: relative;">
            <div class="d-flex align-items-end flex-wrap gap-20 mb-24">
              <img src="<?php echo $_SESSION['profile_photo']=='' ? 'assets/images/thumbs/setting-profile-img.jpg' : '/' . $_SESSION['profile_photo']; ?>"
                   alt="" style="width:100px;height:100px;border-radius:50%;border:4px solid #fff;box-shadow:0 4px 15px rgba(0,0,0,.15);object-fit:cover;flex-shrink:0;" />
              <div style="padding-bottom:6px;">
                <h4 class="mb-8 text-gray-900"><?php echo htmlspecialchars($_SESSION["fullname"]); ?></h4>
                <div class="d-flex flex-wrap gap-8">
                  <?php if(!empty($_SESSION["level"])): ?>
                  <span style="background:rgba(109,0,186,.1);color:#6d00ba;padding:4px 14px;border-radius:20px;font-size:13px;font-weight:600;">
                    <i class="ph ph-chalkboard-teacher me-1"></i><?php echo htmlspecialchars($_SESSION["level"]); ?>
                  </span>
                  <?php endif; ?>
                  <?php if(!empty($_SESSION["profession"])): ?>
                  <span style="background:#f3f4f6;color:#4b5563;padding:4px 14px;border-radius:20px;font-size:13px;">
                    <i class="ph ph-briefcase me-1"></i><?php echo htmlspecialchars($_SESSION["profession"]); ?>
                  </span>
                  <?php endif; ?>
                  <span style="background:#f0fdf4;color:#16a34a;padding:4px 14px;border-radius:20px;font-size:13px;">
                    <i class="ph ph-calendar-check me-1"></i><?php echo turkcetarih('j F Y', $_SESSION["created_at"]); ?> Üyelik
                  </span>
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
                  Şifremi Yenile
                </button>
              </li>
           
              <li class="nav-item" role="presentation">
                <button class="nav-link" id="pills-billing-tab" data-bs-toggle="pill" data-bs-target="#pills-billing"
                  type="button" role="tab" aria-controls="pills-billing" aria-selected="false">
                Aylık Kazanç Durumunuz
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
              <form action="teacher/profile/profile-update.php" method="POST" enctype="multipart/form-data">
                <div class="row gy-4">
                  <div class="col-sm-6 col-xs-6">
                    <label for="fname" class="form-label mb-8 h6">Ad Soyad</label>
                    <input type="text" name="fullname" class="form-control py-11" id="fname" placeholder="Ad Soyad"
                      value="<?php echo $_SESSION["fullname"]; ?>" />
                  </div>
                  <div class="col-sm-6 col-xs-6">
                    <label for="email" class="form-label mb-8 h6">Email</label>
                    <input type="email" name="email" class="form-control py-11" id="email" placeholder="Email Adresiniz"
                      value="<?php echo $_SESSION["email"]; ?>"  readonly />
                  </div>
                  <div class="col-sm-6 col-xs-6">
                    <label for="phone" class="form-label mb-8 h6">Telefon Numarası (Başında 0 olacak şekilde giriniz)</label>
                    <input type="number" name="phone" class="form-control py-11" id="phone"
                      placeholder="Telefon Numarası" value="<?php echo $_SESSION["phone"]; ?>" />
                  </div>
              
                  <div class="col-sm-6 col-xs-6">
                <label for="imageUpload" class="form-label mb-8 h6">Profil Fotoğrafı</label>
                <div class="flex-align  gap-22 mb-3">
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
                    <label for="fname" class="form-label mb-8 h6">Biyografim (Öğrenciler tarafından görüntülenecek)</label>
                    <textarea name="description" class="form-control py-11" /><?php echo $_SESSION["description"]; ?></textarea>
                  </div>
                  <div class="col-sm-6 col-xs-6">
                    <label for="fname" class="form-label mb-8 h6">Grup Ders Kabul Edilebilir Öğrenci Adedi</label>
                    <input type="number" name="group_count" class="form-control py-11" id="fname" placeholder="Grubum için 6 öğrenci kabul edebilirim..."
                      value="<?php echo $ude["group_count"]; ?>" />
                  </div>
                  <div class="col-sm-6 col-xs-6">
                    <label for="fname" class="form-label mb-8 h6">Zoom Client Id</label>
                    <input type="text" name="client_id" class="form-control py-11" id="fname" placeholder="Zoom Client Id"
                      value="<?php echo $ude["client_id"]; ?>" />
                  </div>

                  <div class="col-sm-6 col-xs-6">
                    <label for="fname" class="form-label mb-8 h6">Zoom Account Id</label>
                    <input type="text" name="account_id" class="form-control py-11" id="fname" placeholder="Zoom Account Id"
                      value="<?php echo $ude["account_id"]; ?>" />
                  </div>

                  <div class="col-sm-6 col-xs-6">
                    <label for="fname" class="form-label mb-8 h6">Zoom Client Secret</label>
                    <input type="text" name="client_secret" class="form-control py-11" id="fname" placeholder="Zoom Client Secret"
                      value="<?php echo $ude["client_secret"]; ?>" />
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
              <h4 class="mb-4">Şifremi Yenile</h4>
              <p class="text-gray-600 text-15">Lütfen şifrenizin doğruluğunu teyit edin.</p>
            </div>
            <div class="card-body">
              <div class="row">
                <div class="col-md-6">
                  <form action="teacher/profile/update-password.php" method="POST">
                    <div class="row gy-4">
                      <div class="col-12">
                        <label for="current-password" class="form-label mb-8 h6">Mevcut Şifreniz</label>
                        <div class="position-relative">
                          <input type="text" class="form-control py-11" id="current-password" name="current"
                            placeholder="Mevcut Şifrenizi Giriniz" />
                          <span
                            class="toggle-password position-absolute top-50 inset-inline-end-0 me-16 translate-middle-y ph ph-eye-slash"
                            id="#current-password"></span>
                        </div>
                      </div>
                      <div class="col-12">
                        <label for="new-password" class="form-label mb-8 h6">Yeni Şifreniz</label>
                        <div class="position-relative">
                          <input type="text" class="form-control py-11" id="new-password" name="new"
                            placeholder="Yeni Şifreniz" />
                          <span
                            class="toggle-password position-absolute top-50 inset-inline-end-0 me-16 translate-middle-y ph ph-eye-slash"
                            id="#new-password"></span>
                        </div>
                      </div>

                      <div class="col-12">
                        <label class="form-label mb-8 h6">Şifre Detayları</label>
                        <ul class="list-inside">
                          <li class="text-gray-600 mb-4">Lütfen şifrenizi yenilemeden önce göz ikonuna basarak kontrol
                            ediniz</li>
                          <li class="text-gray-600 mb-4">Şifrenizi kimseyle paylaşmayınız</li>
                        </ul>
                      </div>

                    </div>

                </div>
                <?php if (isset($_GET["success"]) && @$_GET["tab"] == 2) { ?>
                <p class="alert alert-success">Bilgileriniz başarıyla güncellendi.</p>
                <?php } ?>

                <?php if (isset($_GET["error"]) && @$_GET["tab"] == 2 && @$_GET["error"] == 1) { ?>
                <p class="alert alert-danger">Değerli öğretmenimiz, girmiş olduğunuz mevcut şifreniz yanlış.</p>
                <?php } ?>

                <div class="col-12">
                  <div class="flex-align justify-content-end gap-8">
                    <button type="submit" class="btn btn-evo
 rounded-pill py-9">Şifremi Yenile</button>
                    </form>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      
        <div class="tab-pane fade" id="pills-billing" role="tabpanel" aria-labelledby="pills-billing-tab" tabindex="0">

          <div class="card mt-24">
            <div class="card-header border-bottom">
              <h4 class="mb-0">Aylık Kazanç Durumunuz</h4>
            </div>



            <div class="card-body p-0">
              <div class="table-responsive">
                <table class="table table-hover">
                  <thead class="table-light">
                    <tr>
                      <th>Dönem</th>
                      <th class="text-center">Solo Ders</th>
                      <th class="text-center">Grup Ders</th>
                      <th class="text-center">Toplam Hakediş</th>
                      <th class="text-center">Ödeme Durumu</th>
                      <th class="text-center">Makbuz</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php foreach($_monthRows as $_mr):
                      $_k = $_mr['key'];
                      $_ls = $_lessonsByMonth[$_k] ?? [];
                    ?>
                    <tr class="ps-month-row" style="cursor:pointer;" data-key="<?php echo $_k; ?>">
                      <td class="fw-bold align-middle">
                        <i class="ph ph-caret-right me-1 ps-toggle-icon"></i>
                        <?php echo $_turkishMonths[$_mr['mon']] . ' ' . $_mr['yr']; ?>
                      </td>
                      <td class="text-center align-middle"><?php echo $_mr['solo_count']; ?> ders</td>
                      <td class="text-center align-middle"><?php echo $_mr['group_count']; ?> ders</td>
                      <td class="text-center align-middle">
                        <strong class="<?php echo $_mr['total_amount'] < 0 ? 'text-danger' : 'text-success'; ?>">
                          <?php echo number_format($_mr['total_amount'],0,',','.'); ?> ₺
                        </strong>
                      </td>
                      <td class="text-center align-middle">
                        <?php if($_mr['pay_status']==1): ?>
                          <span class="badge bg-success p-2">✅ Ödendi</span><br>
                          <small class="text-muted"><?php echo turkcetarih('j M Y', $_mr['paid_at']); ?></small>
                        <?php else: ?>
                          <span class="badge bg-warning text-dark p-2">⏳ Bekliyor</span>
                        <?php endif; ?>
                      </td>
                      <td class="text-center align-middle">
                        <?php if($_mr['pay_status']==1 && !empty($_mr['receipt_path'])): ?>
                          <a href="<?php echo $_mr['receipt_path']; ?>" target="_blank" class="btn btn-sm btn-success">Makbuz</a>
                        <?php else: ?>
                          <span class="text-muted">—</span>
                        <?php endif; ?>
                      </td>
                    </tr>
                    <tr class="d-none" id="ps-detail-<?php echo $_k; ?>">
                      <td colspan="6" class="p-0">
                        <table class="table table-sm mb-0">
                          <thead class="table-secondary">
                            <tr>
                              <th class="text-center">Ders</th>
                              <th class="text-center">Tür</th>
                              <th class="text-center">Öğrenci</th>
                              <th class="text-center">Tarih</th>
                              <th class="text-center">Katılım</th>
                              <th class="text-center">Hakediş</th>
                            </tr>
                          </thead>
                          <tbody>
                            <?php foreach($_ls as $_l):
                              $_tK = !empty($_l['teacher_join']); $_sK = !empty($_l['student_join']);
                              $_fi = $_l['type']==1 ? ((float)$_l['income'] != 0 ? (float)$_l['income'] : (float)$_l['group_income_cost']) : (float)$_l['income'];
                              if ($_tK && $_sK)      { $_kat='<span class="text-success">İkisi Katıldı</span>';    $_h=$_fi; }
                              elseif ($_tK && !$_sK) { $_kat='<span class="text-warning">Öğrenci Gelmedi</span>';  $_h=$_l['type']==1?0:$_fi/2; }
                              elseif (!$_tK && $_sK) { $_kat='<span class="text-danger">Öğretmen Gelmedi</span>';  $_h=-($_fi/2); }
                              else                   { $_kat='<span class="text-muted">İkisi Gelmedi</span>';      $_h=0; }
                              $_hClass = $_h < 0 ? 'text-danger' : ($_h > 0 ? 'text-success' : 'text-muted');
                            ?>
                            <tr>
                              <td class="text-center align-middle"><?php echo htmlspecialchars($_l['lit']); ?></td>
                              <td class="text-center align-middle"><?php echo $_l['type']==1?'Grup':'Solo'; ?></td>
                              <td class="text-center align-middle"><?php echo htmlspecialchars($_l['student_name']); ?></td>
                              <td class="text-center align-middle"><?php echo turkcetarih('j M Y H:i', $_l['start_date']); ?></td>
                              <td class="text-center align-middle"><?php echo $_kat; ?></td>
                              <td class="text-center align-middle <?php echo $_hClass; ?>"><?php echo number_format($_h,0,',','.'); ?> ₺</td>
                            </tr>
                            <?php endforeach; ?>
                          </tbody>
                        </table>
                      </td>
                    </tr>
                    <?php endforeach; ?>
                  </tbody>
                </table>
              </div>
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

  <?php include '../includes_panel/scripts.php'; ?><?php include 'includes/teacher-scripts.php'; ?>


  <script>
  $(document).on('click', '.ps-month-row', function() {
    var key = $(this).data('key');
    $('#ps-detail-' + key).toggleClass('d-none');
    $(this).find('.ps-toggle-icon').toggleClass('ph-caret-right ph-caret-down');
  });
  </script>

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

  $('#hakedisTable').DataTable({
    autoWidth: false,
    scrollX: false,
    order: [],
    language: { url: '//cdn.datatables.net/plug-ins/1.13.4/i18n/tr.json' }
  });
  </script>
</body>

</html>
