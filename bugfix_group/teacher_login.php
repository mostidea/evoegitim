<?php
session_start();
include("../../config/connection.php");
checkSession();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (empty($_POST['email']) || empty($_POST['password'])) {
        header("location: ../index.php?error=0");
        exit();
    }

    $email      = trim($_POST['email']);
    $rawPassword = $_POST['password'];

    // Kullanıcıyı email ile çek (SQL injection kapatıldı)
    $stmt = $db->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $d = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$d) {
        header("location: ../index.php?error=1");
        exit();
    }

    // Hesap durumu kontrolleri
    if ((int)($d['status'] ?? -1) === 2) {
        header("location: ../index.php?type=1");
        exit();
    }
    if ((int)($d['status'] ?? -1) === 0) {
        header("location: ../index.php?type=2");
        exit();
    }

    // Şifre doğrulama: önce bcrypt, sonra MD5 fallback + otomatik yükseltme
    $verified = false;
    $stored   = $d['password'] ?? '';

    if (password_verify($rawPassword, $stored)) {
        $verified = true;
    } elseif (strlen($stored) === 32 && hash_equals($stored, md5($rawPassword))) {
        // MD5 eşleşti → bcrypt'e yükselt
        $newHash = password_hash($rawPassword, PASSWORD_BCRYPT);
        $upd = $db->prepare("UPDATE users SET password = ? WHERE id = ?");
        $upd->execute([$newHash, $d['id']]);
        $verified = true;
    }

    if ($verified) {
        $_SESSION["user_id"]      = $d["id"];
        $_SESSION["fullname"]     = $d["fullname"];
        $_SESSION["email"]        = $d["email"];
        $_SESSION["phone"]        = $d["phone"];
        $_SESSION["profession"]   = $d["profession"];
        $_SESSION["level"]        = $d["level"];
        $_SESSION["created_at"]   = $d["created_at"];
        $_SESSION["description"]  = $d["description"];
        $_SESSION["profile_photo"] = $d["profile_photo"];
        $_SESSION["panel"]        = 'teacher';
        header("location: ../dashboard.php");
    } else {
        header("location: ../index.php?error=1");
    }
}
?>
