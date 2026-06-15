<?php
session_start();
include("../../config/connection.php");
checkUnSession();

// POST verilerini al
$id = isset($_POST['id']) ? $_POST['id'] : null;
$email = $_POST['email'];
$code = $_POST['code'];
$profession = $_POST['profession'];
$level = $_POST['level'];
$accessible_lessons = isset($_POST['lesson_id']) ? implode(',', $_POST['lesson_id']) : '';

// Email ile daha önce bir kayıt olup olmadığını kontrol et
$lessonData = $db->prepare("SELECT COUNT(*) FROM invite_teacher WHERE email = :email");
$lessonData->bindParam(":email", $email);
$lessonData->execute();
$rowCount = $lessonData->fetchColumn();

// ID varsa güncelleme yap, yoksa yeni kayıt ekle
if ($id) {
    $query = $db->prepare("UPDATE invite_teacher SET email = :email, code = :code, profession = :profession, level = :level, accessible_lessons = :accessible_lessons WHERE id = :id");
    $query->bindParam(':id', $id);
    $query->bindParam(':email', $email);
    $query->bindParam(':code', $code);
    $query->bindParam(':profession', $profession);
    $query->bindParam(':level', $level);
    $query->bindParam(':accessible_lessons', $accessible_lessons);
    $query->execute();
} else {
    if ($rowCount == 0) {
        $query = $db->prepare("INSERT INTO invite_teacher (email, code, profession, level, accessible_lessons) VALUES (:email, :code, :profession, :level, :accessible_lessons)");

        $url = "https://evoegitim.com/teacher/register.php?ern=" . base64_encode($email) . "&code=" . $code;
        $to = $email;
        $toName = "Evo Öğretmen";
        $subject = 'Öğretmenlik Daveti | Evo Eğitim';
        $body = '<!DOCTYPE html>
<html lang="tr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body style="margin:0; padding:0; background-color:#f4f6f9; font-family: Arial, sans-serif;">
  <table width="100%" cellpadding="0" cellspacing="0" style="background-color:#f4f6f9; padding: 40px 0;">
    <tr>
      <td align="center">
        <table width="600" cellpadding="0" cellspacing="0" style="background-color:#ffffff; border-radius:12px; overflow:hidden; box-shadow: 0 2px 8px rgba(0,0,0,0.08);">

          <!-- Üst Banner -->
          <tr>
            <td style="background-color:#0d6efd; padding: 32px 40px; text-align:center;">
              <h1 style="color:#ffffff; margin:0; font-size:26px; font-weight:700; letter-spacing:1px;">Evo Eğitim</h1>
              <p style="color:#cfe2ff; margin: 8px 0 0 0; font-size:14px;">Türkiye\'nin en iyi eğitmenleriyle çalışan eğitim platformu</p>
            </td>
          </tr>

          <!-- İçerik -->
          <tr>
            <td style="padding: 40px 40px 20px 40px;">
              <p style="color:#333333; font-size:16px; margin:0 0 16px 0;">Merhaba, Sayın Öğretmenimiz,</p>
              <p style="color:#555555; font-size:15px; line-height:1.7; margin:0 0 20px 0;">
                Sizi <strong>Evo Eğitim</strong> öğretmenlik sistemine davet etmekten büyük mutluluk duyuyoruz.
                Öğrencilerimizle buluşmak ve eğitime katkı sağlamak için ücretsiz kaydınızı tamamlayabilirsiniz.
              </p>

              <!-- Buton -->
              <table cellpadding="0" cellspacing="0" style="margin: 28px 0;">
                <tr>
                  <td align="center" style="background-color:#0d6efd; border-radius:8px;">
                    <a href="' . $url . '" style="display:inline-block; padding: 14px 36px; color:#ffffff; font-size:16px; font-weight:700; text-decoration:none; border-radius:8px;">
                      Hemen Kayıt Ol
                    </a>
                  </td>
                </tr>
              </table>

              <p style="color:#777777; font-size:13px; margin:0 0 24px 0;">
                Bağlantı çalışmıyorsa aşağıdaki adresi tarayıcınıza kopyalayabilirsiniz:<br>
                <a href="' . $url . '" style="color:#0d6efd; word-break:break-all;">' . $url . '</a>
              </p>

              <hr style="border:none; border-top:1px solid #e9ecef; margin: 0 0 24px 0;">

              <h3 style="color:#333333; font-size:16px; margin:0 0 12px 0;">Profil ve Müsaitlik Takvimi Hakkında</h3>
              <p style="color:#555555; font-size:15px; line-height:1.7; margin:0 0 16px 0;">
                Profil bilgilerinizi eksiksiz ve doğru bir şekilde doldurmanız, öğrencilerin sizi daha iyi tanımasını sağlayacaktır.
              </p>
              <p style="color:#555555; font-size:15px; line-height:1.7; margin:0 0 0 0;">
                Müsaitlik takviminizi düzenleyerek kendinize en uygun gün ve saatleri ekleyin. Ne kadar fazla zaman dilimi eklerseniz, o kadar fazla öğrenciye ulaşma şansınız olur.
              </p>
            </td>
          </tr>

          <!-- Alt Footer -->
          <tr>
            <td style="background-color:#f8f9fa; padding: 24px 40px; text-align:center; border-top:1px solid #e9ecef;">
              <p style="color:#888888; font-size:13px; margin:0 0 12px 0;">Bizi takip edin</p>
              <a href="https://www.facebook.com/share/1HSCqtLq84/" target="_blank" style="display:inline-block; background-color:#1877f2; color:#ffffff; font-size:13px; font-weight:600; text-decoration:none; padding: 8px 20px; border-radius:6px;">
                Facebook
              </a>
              <p style="color:#aaaaaa; font-size:12px; margin: 16px 0 0 0;">
                Evo Eğitim Ekibi &bull; <a href="https://evoegitim.com" style="color:#aaaaaa;">evoegitim.com</a>
              </p>
            </td>
          </tr>

        </table>
      </td>
    </tr>
  </table>
</body>
</html>';
        $altBody = 'Merhaba, sizi Evo Eğitim Öğretmenlik sistemine davet ediyoruz. Kayıt olmak için: ' . $url;

        $query->bindParam(':email', $email);
        $query->bindParam(':code', $code);
        $query->bindParam(':profession', $profession);
        $query->bindParam(':level', $level);
        $query->bindParam(':accessible_lessons', $accessible_lessons);
        $query->execute();

        sendEmail($to, $toName, $subject, $body, $altBody);
    } else {
        header("Location: ../new-teachers.php?error=email_exists");
        exit();
    }
}

header("Location: ../new-teachers.php");
exit();
?>
