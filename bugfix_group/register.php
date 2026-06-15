<?php
 session_start();
 include("../../config/connection.php");
 checkSession();
$previous_url = $_SERVER['HTTP_REFERER'];
$referer_domain = parse_url($_SERVER['HTTP_REFERER'], PHP_URL_HOST);

if ($referer_domain !== "evoegitim.com") {
        header("Location: " . htmlspecialchars($previous_url) . "&error=0");
        exit();
    }

$email = trim($_POST["email"]);
$emailData = $db->prepare("SELECT * FROM users WHERE email=?");
$emailData->execute([$email]);
$emailSay = $emailData->rowCount();



if($emailSay != 0){
    header("Location: " . $previous_url . "?error=2");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Form verilerini al
    $parent_email = trim(htmlspecialchars($_POST["family_email"]));
    $code = "frk" . random_int(1000, 9999);
    $updated_at = date("Y-m-d H:i:s");
    $expired=0;

        $last_ip = $_SERVER['REMOTE_ADDR'];
        $last_device = $_SERVER['HTTP_USER_AGENT'];
        $fullname = htmlspecialchars($_POST["fullname"]);
        $phone = htmlspecialchars($_POST["phone"]);
        $password = password_hash($_POST["password"], PASSWORD_BCRYPT);
        $level = htmlspecialchars(base64_decode($_POST["class"]));
        $codeSms = "EVO-" . random_int(10000, 99999);

        // Parent tablosuna insert işlemi
        $insert_query = "INSERT INTO users (fullname, phone, level, email, password, last_ip, last_device, code, parent_email) VALUES (:fullname, :phone, :level, :email, :password, :last_ip, :last_device, :code, :parent_email)";
        $insert_stmt = $db->prepare($insert_query);
        $insert_stmt->bindParam(":fullname", $fullname);
        $insert_stmt->bindParam(":phone", $phone);
        $insert_stmt->bindParam(":level", $level);
        $insert_stmt->bindParam(":email", $email);
        $insert_stmt->bindParam(":password", $password);
        $insert_stmt->bindParam(":last_ip", $last_ip);
        $insert_stmt->bindParam(":last_device", $last_device);
        $insert_stmt->bindParam(":code", $codeSms);
        $insert_stmt->bindParam(":parent_email", $parent_email);

        if ($insert_stmt->execute()) {
            $current_datetime = date("Y-m-d H:i:s");
            // Başarılı insert işlemi, session başlat
            $_SESSION["user_id"] = $db->lastInsertId();
            $_SESSION["fullname"] = $fullname;
            $_SESSION["email"] = $email;
            $_SESSION["phone"] = $phone;
            $_SESSION["level"] = $level;
            $_SESSION["created_at"] = $current_datetime;
            $_SESSION["created_at"] = $current_datetime;
            $_SESSION["description"] = "";
            $_SESSION["role"] = 0;
            $_SESSION["status"] = 0;
            $_SESSION["profile_photo"] = "";
        $_SESSION["parent_email"] = $parent_email;
        sendSms("Değerli Öğrencimiz, Evo Eğitim sistemine hoş geldiniz! Evo Eğitim ile sizlere daha kaliteli ve verimli bir ders deneyimi sunmayı amaçlıyoruz. Sisteminize ilk kaydınızla birlikte 2 ders kredisi hesabınıza tanımlanmıştır. Panelinizin sol tarafında bulunan 'Solo Ders Talep Et' veya 'Grup Ders Talep Et' butonlarına tıklayarak size en uygun bireysel ya da grup derslerini kolayca seçebilirsiniz. Evo ailesi olarak başarılarla dolu bir eğitim süreci dileriz!", [$phone]);

           sendSms("Değerli Öğrencimiz, hesabınızı onaylamak için tek kullanımlık şifreniz ".$codeSms."'dir. ", [$phone]);

            if($parent_email != ""){

                $data = $db->prepare("SELECT * FROM invite_parent WHERE user_email = :student_email LIMIT 1");
                $data->execute([':student_email' => $email]);
                $say = $data->rowCount();
                if($say==0){

                    $query = "INSERT INTO invite_parent SET expired = :expired, updated_at = :updated_at, email = :uid, code = :code, user_email = :user_email";

                    $stmt = $db->prepare($query);

                    $stmt->bindParam(":uid", $parent_email);
                    $stmt->bindParam(":code", $code);
                    $stmt->bindParam(":expired", $expired);
                    $stmt->bindParam(":updated_at", $updated_at);
                    $stmt->bindParam(":user_email", $email);
                    $stmt->execute();

                    $url="https://evoegitim.com/vbs/register.php?ern=".base64_encode($parent_email)."&code=".$code;
                    $to = $parent_email;
                    $toName = $fullname." Velisi";
                    $subject = 'Evo Eğitim - Veli Onay Daveti';
                    $body = '<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!--[if mso]>
    <noscript>
        <xml>
            <o:OfficeDocumentSettings>
                <o:PixelsPerInch>96</o:PixelsPerInch>
            </o:OfficeDocumentSettings>
        </xml>
    </noscript>
    <![endif]-->
    <style>
        @media only screen and (max-width: 600px) {
            .content { padding: 20px !important; }
            .header h1 { font-size: 24px !important; }
            .feature { display: block !important; text-align: center !important; }
            .feature-icon { margin: 0 auto 15px !important; }
        }
    </style>
</head>
<body style="margin: 0; padding: 0; font-family: Arial, sans-serif; background-color: #f5f5f5;">
    <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%" style="background-color: #f5f5f5;">
        <tr>
            <td align="center" style="padding: 20px 0;">
                <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="600" style="background-color: #ffffff; box-shadow: 0 0 20px rgba(0,0,0,0.1);">
                    <tr>
                        <td style="background: #6c00ba; padding: 40px 20px; text-align: center;">
                            <div style="margin-bottom: 20px;">
                                <img src="https://evoegitim.com/assets/images/logo/logo-white.png?v=123123" alt="Evo Eğitim" width="150">
                            </div>
                            <h1 style="color: #ffffff; margin: 20px 0 10px; font-size: 32px; font-weight: 300;">Veli Davetiyesi</h1>
                            <p style="color: #ffffff; margin: 0; font-size: 16px;">Çocuğunuzun eğitim yolculuğuna ortak olun</p>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 40px 30px;">
                            <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%" style="margin-bottom: 30px;">
                                <tr>
                                    <td style="background-color: #f8f4ff; padding: 25px; border-radius: 10px; border-left: 5px solid #6c00ba;">
                                        <h3 style="margin: 0 0 10px; color: #2f2552; font-size: 18px;">📚 Öğrenci Davet Bildirimi</h3>
                                        <p style="margin: 0; color: #4a4a4a; font-size: 16px;">
                                            Öğrenciniz <strong style="color: #6c00ba;">' . htmlspecialchars($fullname) . '</strong> sizi Evo Eğitim veli sistemine davet etti.
                                        </p>
                                    </td>
                                </tr>
                            </table>
                            <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%" style="margin-bottom: 30px;">
                                <tr>
                                    <td style="background-color: #fff9e6; padding: 30px; border-radius: 10px; border: 1px solid #ffcc33; text-align: center;">
                                        <h2 style="color: #2f2552; margin: 0 0 15px; font-size: 26px;">Sayın ' . htmlspecialchars($toName) . ',</h2>
                                        <p style="color: #555; line-height: 1.7; margin: 0 0 25px; font-size: 16px;">
                                            Öğrenciniz <strong>' . htmlspecialchars($fullname) . '</strong>, Evo Eğitim platformuna kayıt olmuştur.
                                            Çocuğunuzun eğitim sürecini yakından takip edebilmeniz için sizi veli sistemimize davet ediyoruz.
                                        </p>
                                        <a href="' . htmlspecialchars($url) . '" style="display: inline-block; background: #6c00ba; color: #ffffff; text-decoration: none; padding: 16px 45px; border-radius: 50px; font-size: 18px; font-weight: 600;">
                                            ÜCRETSİZ KAYIT OL
                                        </a>
                                    </td>
                                </tr>
                            </table>
                            <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%" style="margin-bottom: 35px;">
                                <tr>
                                    <td align="center">
                                        <img src="https://evoegitim.com/popgorsel/6569d8998c6061%20(1).jpg" alt="Evo Eğitim" style="width: 100%; max-width: 500px; height: auto; border-radius: 10px;">
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    <tr>
                        <td style="background: #2f2552; padding: 24px 30px; text-align: center;">
                            <p style="color: rgba(255,255,255,0.7); font-size: 12px; margin: 0;">
                                Bu e-posta öğrenciniz tarafından Evo Eğitim sisteminden gönderilmiştir.
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>';
                    $altBody = 'Sayın ' . $toName . ', öğrenciniz ' . htmlspecialchars($fullname) . ' sizi Evo Eğitim veli sistemine davet etti. Kayıt olmak için: ' . $url;

                    sendEmail($to, $toName, $subject, $body, $altBody);

                    $queryS = "INSERT INTO parent (fullname, email, created_at, updated_at) VALUES (NULL, :email, current_timestamp(), current_timestamp())";
                    $parentIdStmt = $db->prepare($queryS);
                    $parentIdStmt->bindParam(':email', $to);
                    $parentIdStmt->execute();
                }
            }

            header("Location: ../dashboard.php");
            exit();
        } else {
            header("Location: " . $previous_url . "?error=4");
            exit();
        }
}
?>
