<?php
session_start();
include("../../config/connection.php");
checkUnSession();

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $current_user_id = $_SESSION["user_id"];
    $new_email = htmlspecialchars($_POST["email"]);

    $parentData = $db->prepare("SELECT * FROM users WHERE email = :email AND id != :id");
    $parentData->bindParam(":email", $new_email);
    $parentData->bindParam(":id", $current_user_id);
    $parentData->execute();
    $parentSay = $parentData->rowCount();

    $images = "";

    if (isset($_FILES['image']) && $_FILES['image']['name'][0] !== '') {
        // __DIR__ kullanımı: her PHP yapılandırmasında doğru yolu verir
        $uploadDir = __DIR__ . '/../uploads/';

        // Klasör yoksa oluştur
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $fileTmpPath = $_FILES['image']['tmp_name'];

        $allowedMimes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        $realMime = mime_content_type($fileTmpPath);
        if (!in_array($realMime, $allowedMimes, true)) {
            header("location: ../profile-settings.php?error=invalid_file");
            exit;
        }

        $mimeToExt = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/gif' => 'gif', 'image/webp' => 'webp'];
        $fileExtension = $mimeToExt[$realMime];
        $newFileName = bin2hex(random_bytes(16)) . '.' . $fileExtension;
        $dest_path = $uploadDir . $newFileName;

        if (move_uploaded_file($fileTmpPath, $dest_path)) {
            $images = "student/uploads/" . $newFileName;
        } else {
            echo 'Yüklenen dosya taşınırken hata oluştu';
            exit;
        }
    } else {
        $data = $db->prepare("SELECT profile_photo FROM users WHERE email = :email");
        $data->bindValue(':email', $new_email);
        $data->execute();
        $row = $data->fetch(PDO::FETCH_ASSOC);
        $existingFile = $row['profile_photo'];
        $images = $existingFile;
    }

    $updateData = $db->prepare("UPDATE users
    SET fullname = :fullname,
        phone = :phone,
        description = :description,
        profile_photo = :profile_photo,
        email = :email
    WHERE id = :id");
    $updateData->bindParam(":fullname", htmlspecialchars($_POST["fullname"]));
    $updateData->bindParam(":phone", htmlspecialchars($_POST["phone"]));
    $updateData->bindParam(":description", htmlspecialchars($_POST["description"]));
    $updateData->bindParam(":profile_photo", $images);
    $updateData->bindParam(":email", $new_email);
    $updateData->bindParam(":id", $current_user_id);

    if ($updateData->execute()) {
        $_SESSION["fullname"] = htmlspecialchars($_POST["fullname"]);
        $_SESSION["email"] = $new_email;
        $_SESSION["phone"] = htmlspecialchars($_POST["phone"]);
        $_SESSION["description"] = htmlspecialchars($_POST["description"]);
        $_SESSION["profile_photo"] = $images;

        if (isset($_POST["parent_email"]) && $_POST["parent_email"] != "" && $_SESSION["parent_email"] != $_POST["parent_email"]) {

            $data = $db->prepare("SELECT * FROM invite_parent WHERE user_email = :user_email");
            $data->bindParam(":user_email", $new_email);
            $data->execute();
            $say = $data->rowCount();

            if ($say == 0) {
                $code = "frk" . rand(0, 24) . rand(24, 150);
                $updated_at = date("Y-m-d H:i:s");
                $expired = 0;
                $parentEmailVal = $_POST["parent_email"];

                $query = "INSERT INTO invite_parent SET expired = :expired, updated_at = :updated_at, email = :uid, code = :code, user_email = :user_email";
                $stmt = $db->prepare($query);
                $stmt->bindParam(":uid", $parentEmailVal);
                $stmt->bindParam(":code", $code);
                $stmt->bindParam(":expired", $expired);
                $stmt->bindParam(":updated_at", $updated_at);
                $stmt->bindParam(":user_email", $new_email);
                $stmt->execute();

                $url = "https://evoegitim.com/vbs/register.php?ern=" . base64_encode($parentEmailVal) . "&code=" . $code;
                $to = $parentEmailVal;
                $toName = htmlspecialchars($_POST["fullname"]) . " Velisi";
                $subject = 'Evo Eğitim - Veli Onay Daveti';
                $body = '<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body style="margin: 0; padding: 0; font-family: Arial, sans-serif; background-color: #f5f5f5;">
    <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%" style="background-color: #f5f5f5;">
        <tr>
            <td align="center" style="padding: 20px 0;">
                <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="600" style="background-color: #ffffff; box-shadow: 0 0 20px rgba(0,0,0,0.1);">
                    <tr>
                        <td style="background: #6c00ba; padding: 40px 20px; text-align: center;">
                            <img src="https://evoegitim.com/assets/images/logo/logo-white.png?v=123123" alt="Evo Eğitim" width="150">
                            <h1 style="color: #ffffff; margin: 20px 0 10px; font-size: 32px; font-weight: 300;">Veli Davetiyesi</h1>
                            <p style="color: #ffffff; margin: 0; font-size: 16px;">Çocuğunuzun eğitim yolculuğuna ortak olun</p>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 40px 30px; text-align: center;">
                            <p style="color: #555; line-height: 1.7; margin: 0 0 25px; font-size: 16px;">
                                Öğrenciniz <strong>' . htmlspecialchars($_POST["fullname"]) . '</strong>, Evo Eğitim platformuna kayıt olmuştur.
                                Çocuğunuzun eğitim sürecini yakından takip edebilmeniz için sizi veli sistemimize davet ediyoruz.
                            </p>
                            <a href="' . htmlspecialchars($url) . '" style="display: inline-block; background: #6c00ba; color: #ffffff; text-decoration: none; padding: 16px 45px; border-radius: 50px; font-size: 18px; font-weight: 600;">
                                ÜCRETSİZ KAYIT OL
                            </a>
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
                $altBody = 'Sayın ' . $toName . ', öğrenciniz ' . htmlspecialchars($_POST["fullname"]) . ' sizi Evo Eğitim veli sistemine davet etti. Kayıt olmak için: ' . $url;

                if (sendEmail($to, $toName, $subject, $body, $altBody)) {
                    $queryS = "INSERT INTO parent (fullname, email, created_at, updated_at) VALUES (NULL, :email, current_timestamp(), current_timestamp())";
                    $parentIdStmt = $db->prepare($queryS);
                    $parentIdStmt->bindParam(':email', $to);
                    $parentIdStmt->execute();
                }
            }

            $_SESSION["parent_email"] = $_POST["parent_email"];
        }

        header("location: ../profile-settings.php?success=1");
    } else {
        header("location: ../profile-settings.php?error=1");
    }
}
?>
