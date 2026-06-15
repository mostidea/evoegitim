<?php
 ini_set('session.cookie_path', '/');
 ini_set('session.cookie_domain', '');
 session_start();
 include("../config/connection.php");
 checkSession();
?>
<!DOCTYPE html>
<html lang="tr">
<head>
  <?php include "../includes_panel/meta.php";?>
<title>Eğitim Platformu  | Evo Eğitim</title>
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
$stmt = $db->prepare("SELECT student FROM photos WHERE id = 1");
$stmt->execute();
$photoData = $stmt->fetch(PDO::FETCH_ASSOC);
$coverPhoto = $photoData['student'] ? '/uploads/covers/' . $photoData['student'] : '/assets/img/default-vbs.jpg';
?>
      <img src="../<?php echo $coverPhoto; ?>" alt="" />
    </div>
    <div class="auth-right py-40 px-24">
      <div class="auth-right__inner mx-auto w-100">
        <a href="https://evoegitim.com/student/register.php" class="auth-right__logo">
          <img src="assets/images/logo/logo.png" alt="" />
        </a>
        <h2 class="mb-8">Hoş Geldiniz! &#128075;</h2>
        <p class="text-gray-600 text-15 mb-32">Değerli öğrencimiz sağlık ve huzur dolu bir gün dileriz.</p>
        <?php if(isset($_GET["error"])){ ?>
<p class="alert alert-danger">Değerli öğrencimiz, email ve şifreniz ile giriş yapamadık. Lütfen bilgilerinizi kontrol ediniz.</p>
<?php } ?>

<?php if(isset($_GET["recover"])){ ?>
<p class="alert alert-success">Değerli öğrencimiz, şifre yenileme talebiniz alındı. Lütfen telefon numaranıza gelen yeni şifreniz ile giriş yapınız</p>
<?php } ?>
        <form action="student/auth/login.php" method="POST">
          <div class="mb-24">
            <label for="fname" class="form-label mb-8 h6">Email Adresiniz</label>
            <div class="position-relative">
              <input type="email" class="form-control py-11 ps-40" name="email" id="fname" placeholder="Email Adresiniz" />
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
            <a href="student/forgot-password.php"
              class="text-main-600 hover-text-decoration-underline text-15 fw-medium">Şifremi Unuttum</a>
          </div>
          <button type="submit" class="btn btn-evo rounded-pill w-100">Giriş Yap</button>

          <a href="student/register.php" class="btn btn-success w-100 mt-24">Ücretsiz Kayıt Ol</a>

        </form>
      </div>
    </div>
  </section>

<?php include '../includes_panel/scripts.php'; ?><?php include 'includes/student-scripts.php'; ?>

</body>

</html>