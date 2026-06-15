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

    $inviteData = $db->prepare("SELECT * FROM invite_teacher WHERE email = :email AND email != :id");
    $inviteData->bindParam(":email", $new_email);
    $inviteData->bindParam(":id", $new_email);
    $inviteData->execute();
    $inviteSay = $inviteData->rowCount();

    if ($parentSay > 0 || $inviteSay > 0) {
        header("location: ../profile-settings.php?error=email_taken");
        exit();
    } else {

        $images = "";

        $data = $db->prepare("SELECT profile_photo FROM users WHERE email = :email");
        $data->bindValue(':email', $new_email);
        $data->execute();
        $row = $data->fetch(PDO::FETCH_ASSOC);
        $existingFile = $row['profile_photo'];

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
                $images = "teacher/uploads/" . $newFileName;
            } else {
                echo 'Yüklenen dosya taşınırken hata oluştu';
                exit;
            }
        } else {
            $images = $existingFile;
        }

        $updateData = $db->prepare("UPDATE users
        SET fullname = :fullname,
            phone = :phone,
            description = :description,
            client_id = :client_id,
            client_secret = :client_secret,
            account_id = :account_id,
            profile_photo = :profile_photo,
            group_count = :group_count,
            email = :email
        WHERE id = :id");
        $updateData->bindParam(":fullname", htmlspecialchars($_POST["fullname"]));
        $updateData->bindParam(":phone", htmlspecialchars($_POST["phone"]));
        $updateData->bindParam(":description", htmlspecialchars($_POST["description"]));
        $updateData->bindParam(":client_id", htmlspecialchars($_POST["client_id"]));
        $updateData->bindParam(":client_secret", htmlspecialchars($_POST["client_secret"]));
        $updateData->bindParam(":account_id", htmlspecialchars($_POST["account_id"]));
        $updateData->bindParam(":profile_photo", $images);
        $updateData->bindParam(":group_count", htmlspecialchars($_POST["group_count"]));
        $updateData->bindParam(":email", $new_email);
        $updateData->bindParam(":id", $current_user_id);

        if ($updateData->execute()) {
            $updateProfileData = $db->prepare("UPDATE invite_teacher SET email = :email WHERE email = :old_email");
            $updateProfileData->bindParam(":email", $new_email);
            $updateProfileData->bindParam(":old_email", $_SESSION["email"]);
            $updateProfileData->execute();

            $_SESSION["fullname"] = htmlspecialchars($_POST["fullname"]);
            $_SESSION["email"] = $new_email;
            $_SESSION["phone"] = htmlspecialchars($_POST["phone"]);
            $_SESSION["description"] = htmlspecialchars($_POST["description"]);
            $_SESSION["profile_photo"] = $images;
            header("location: ../profile-settings.php?success=1");
        } else {
            header("location: ../profile-settings.php?error=1");
        }
    }
}
?>
