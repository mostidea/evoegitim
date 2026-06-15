<?php 
 session_start();
 include("../config/connection.php");
 checkSession();
?>
<!DOCTYPE html>
<html lang="tr">
<head>
  <?php include "../includes_panel/meta.php";?>
<title>Giriş Yap | Evo Eğitim</title>
</head>

<body>
  <!--==================== Preloader Start ====================-->
  <div class="preloader">
    <div class="loader"></div>
  </div>
  <!--==================== Preloader End ====================-->

  <!--==================== Sidebar Overlay End ====================-->
  <div class="side-overlay"></div>
  <!--==================== Sidebar Overlay End ====================-->

  <section class="auth d-flex">
    <div class="auth-left bg-main-50 flex-center p-24">
      <?php
// Veritabanı bağlantısından sonra
$stmt = $db->prepare("SELECT admin FROM photos WHERE id = 1");
$stmt->execute();
$photoData = $stmt->fetch(PDO::FETCH_ASSOC);
$coverPhoto = $photoData['admin'] ? '/uploads/covers/' . $photoData['admin'] : '/assets/img/default-vbs.jpg';
?>
      <img src="../<?php echo $coverPhoto; ?>" alt="" />
    </div>
    <div class="auth-right py-40 px-24">
      <div class="auth-right__inner mx-auto w-100">
        <a href="index.php" class="auth-right__logo">
          <img src="assets/images/logo/logo.png" alt="" />
        </a>
        <h2 class="mb-8">Hoş Geldiniz! &#128075;</h2>
        <p class="text-gray-600 text-15 mb-32">Değerli yöneticimiz sağlık ve huzur dolu bir gün dileriz.</p>
        <?php if(isset($_GET["error"])){ ?>
<p class="alert alert-danger">Değerli yöneticimiz, email ve şifreniz ile giriş yapamadık. Lütfen bilgilerinizi kontrol ediniz.</p>
<?php } ?>

<?php if(isset($_GET["action"]) && $_GET["action"]==2){ ?>
<p class="alert alert-success">Değerli yöneticimiz, hesap dondurma işleminiz başarıyla tamamlandı.</p>
<?php } ?>

<?php if(isset($_GET["action"]) && $_GET["action"]==1){ ?>
<p class="alert alert-danger">Değerli yöneticimiz, hesap iptal işleminiz başarıyla tamamlandı.</p>
<?php } ?>

<?php if(isset($_GET["type"]) && $_GET["type"]==2){ ?>
<p class="alert alert-warning">Değerli yöneticimiz, hesabınız sizin tarafınızdan donduruldu. Hesabınızın tekrar aktifleştirilmesi için lütfen müşteri hizmetlerimizle iletişime geçin.</p>
<?php } ?>

<?php if(isset($_GET["type"]) && $_GET["type"]==1){ ?>
<p class="alert alert-warning">Değerli yöneticimiz, hesabınız sizin tarafınızdan iptal edildi. Hesabınızın tekrar aktifleştirilmesi için lütfen müşteri hizmetlerimizle iletişime geçin.</p>
<?php } ?>
<?php if(isset($_GET["recover"])){ ?>
<p class="alert alert-success">Değerli yöneticimiz, şifre yenileme talebiniz alındı. Lütfen telefon numaranıza gelen yeni şifreniz ile giriş yapınız</p>
<?php } ?>
        <form action="management/auth/login.php" method="POST">
          <div class="mb-24">
            <label for="fname" class="form-label mb-8 h6">Kullanıcı Adınız</label>
            <div class="position-relative">
              <input type="text" class="form-control py-11 ps-40" name="email" id="fname" placeholder="Kullanıcı Adınız" />
              <span class="position-absolute top-50 translate-middle-y ms-16 text-gray-600 d-flex"><i
                  class="ph ph-user"></i></span>
            </div>
          </div>
          <div class="mb-24">
            <label for="current-password" class="form-label mb-8 h6">Şifre</label>
            <div class="position-relative">
              <input type="password" name="password" class="form-control py-11 ps-40" id="current-password"
                placeholder="Şifre" value="" />
              <span
                class="toggle-password position-absolute top-50 inset-inline-end-0 me-16 translate-middle-y ph ph-eye-slash"
                id="#current-password"></span>
              <span class="position-absolute top-50 translate-middle-y ms-16 text-gray-600 d-flex"><i
                  class="ph ph-lock"></i></span>
            </div>
          </div>
          <div class="mb-32 flex-between flex-wrap gap-8">
            <div class="form-check mb-0 flex-shrink-0">
              <input class="form-check-input flex-shrink-0 rounded-4" type="checkbox" value="" id="remember" />
              <label class="form-check-label text-15 flex-grow-1" for="remember">Beni Hatırla </label>
            </div>

          </div>
          <button type="submit" class="btn btn-evo rounded-pill w-100">Giriş Yap</button>

     
        </form>
      </div>
    </div>
  </section>

<?php include '../includes_panel/scripts.php'; ?>

</body>

</html>