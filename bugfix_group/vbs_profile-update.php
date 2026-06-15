<?php
session_start();
include("../../config/connection.php");
checkUnSession();

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $current_user_id = $_SESSION["user_id"];
    $new_email = htmlspecialchars($_POST["email"]);

    $parentData = $db->prepare("SELECT * FROM parent WHERE email = :email AND id != :id");
    $parentData->bindParam(":email", $new_email);
    $parentData->bindParam(":id", $current_user_id);
    $parentData->execute();
    $parentSay = $parentData->rowCount();

    $inviteData = $db->prepare("SELECT * FROM invite_parent WHERE email = :email AND email != :id");
    $inviteData->bindParam(":email", $new_email);
    $inviteData->bindParam(":id", $new_email);
    $inviteData->execute();
    $inviteSay = $inviteData->rowCount();

    if ($parentSay > 0 || $inviteSay > 0) {
        header("location: ../profile-settings.php?error=email_taken");
        exit();
    } else {

        $images = "";

        if (isset($_FILES['image']) && $_FILES['image']['name'][0] !== '') {
            // __DIR__ kullanımı: her PHP yapılandırmasında doğru yolu verir
            $uploadDir = __DIR__ . '/../uploads/';

            // Klasör yoksa oluştur
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }

            $fileTmpPath = $_FILES['image']['tmp_name'];
            $fileNameCmps = explode(".", $_FILES['image']['name']);
            $fileExtension = strtolower(end($fileNameCmps));
            $newFileName = md5(time() . $_FILES['image']['name']) . '.' . $fileExtension;
            $dest_path = $uploadDir . $newFileName;

            if (move_uploaded_file($fileTmpPath, $dest_path)) {
                $images = "vbs/uploads/" . $newFileName;
            } else {
                echo 'Yüklenen dosya taşınırken hata oluştu';
                exit;
            }
        } else {
            $data = $db->prepare("SELECT profile_photo FROM parent WHERE email = :email");
            $data->bindValue(':email', $new_email);
            $data->execute();
            $row = $data->fetch(PDO::FETCH_ASSOC);
            $images = $row['profile_photo'] ?? '';
        }

        $updateData = $db->prepare("UPDATE parent
        SET fullname = :fullname,
            phone = :phone,
            job = :job,
            family_rank = :family_rank,
            profile_photo = :profile_photo,
            email = :email
        WHERE id = :id");
        $updateData->bindParam(":fullname", htmlspecialchars($_POST["fullname"]));
        $updateData->bindParam(":phone", htmlspecialchars($_POST["phone"]));
        $updateData->bindParam(":job", htmlspecialchars($_POST["job"]));
        $updateData->bindParam(":family_rank", htmlspecialchars($_POST["family_rank"]));
        $updateData->bindParam(":profile_photo", $images);
        $updateData->bindParam(":email", $new_email);
        $updateData->bindParam(":id", $current_user_id);

        if ($updateData->execute()) {
            $updateProfileData = $db->prepare("UPDATE invite_parent SET email = :email WHERE email = :old_email");
            $updateProfileData->bindParam(":email", $new_email);
            $updateProfileData->bindParam(":old_email", $_SESSION["email"]);
            $updateProfileData->execute();

            $_SESSION["fullname"] = htmlspecialchars($_POST["fullname"]);
            $_SESSION["email"] = $new_email;
            $_SESSION["phone"] = htmlspecialchars($_POST["phone"]);
            $_SESSION["job"] = htmlspecialchars($_POST["job"]);
            $_SESSION["family_rank"] = htmlspecialchars($_POST["family_rank"]);
            $_SESSION["profile_photo"] = $images;

            header("location: ../profile-settings.php?success=1");
        } else {
            header("location: ../profile-settings.php?error=1");
        }
    }
}
?>
