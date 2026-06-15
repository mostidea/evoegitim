<?php
session_start();
include("../../config/connection.php");
checkSession();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (empty($_POST['email']) || empty($_POST['password'])) {
        header("location: ../index.php?error=0");
        exit();
    }

    $username    = trim($_POST['email']);
    $rawPassword = $_POST['password'];

    // Kullanıcıyı kullanıcı adı ile çek (SQL injection kapatıldı)
    $stmt = $db->prepare("SELECT * FROM static_user WHERE title = ?");
    $stmt->execute([$username]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$row) {
        header("location: ../index.php?error=1");
        exit();
    }

    // Şifre doğrulama: önce bcrypt, sonra MD5 fallback + otomatik yükseltme
    $stored   = $row['password'] ?? '';
    $verified = false;

    if (password_verify($rawPassword, $stored)) {
        $verified = true;
    } elseif (strlen($stored) === 32 && hash_equals($stored, md5($rawPassword))) {
        // MD5 eşleşti → bcrypt'e yükselt
        $newHash = password_hash($rawPassword, PASSWORD_BCRYPT);
        $upd = $db->prepare("UPDATE static_user SET password = ? WHERE id = ?");
        $upd->execute([$newHash, $row['id']]);
        $verified = true;
    }

    if ($verified) {
        $_SESSION["panel"] = 'management';
        if ($row['id'] == 1) {
            $_SESSION["user_id"]  = 1;
            $_SESSION["fullname"] = "Evo Admin";
            header("location: ../dashboard.php");
        } elseif ($row['id'] == 2) {
            $_SESSION["user_id"]  = 2;
            $_SESSION["fullname"] = "Evo Finans";
            header("location: ../finance.php");
        } else {
            $_SESSION["user_id"]  = 3;
            $_SESSION["fullname"] = "Evo Blog";
            header("location: ../blog.php");
        }
    } else {
        header("location: ../index.php?error=1");
    }
}
?>
