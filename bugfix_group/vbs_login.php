<?php
session_start();
include("../../config/connection.php");
checkSession();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (empty($_POST['email']) || empty($_POST['password'])) {
        header("location: ../index.php?error=0");
        exit();
    }

    $email       = trim($_POST['email']);
    $rawPassword = $_POST['password'];

    $stmt = $db->prepare("SELECT * FROM parent WHERE email = ?");
    $stmt->execute([$email]);
    $d = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$d) {
        header("location: ../index.php?error=1");
        exit();
    }

    $stored   = $d['password'] ?? '';
    $verified = false;

    if (password_verify($rawPassword, $stored)) {
        $verified = true;
    } elseif (strlen($stored) === 32 && hash_equals($stored, md5($rawPassword))) {
        $newHash = password_hash($rawPassword, PASSWORD_BCRYPT);
        $upd = $db->prepare("UPDATE parent SET password = ? WHERE id = ?");
        $upd->execute([$newHash, $d['id']]);
        $verified = true;
    }

    if ($verified) {
        $_SESSION["user_id"]      = $d["id"];
        $_SESSION["fullname"]     = $d["fullname"];
        $_SESSION["email"]        = $d["email"];
        $_SESSION["phone"]        = $d["phone"];
        $_SESSION["job"]          = $d["job"];
        $_SESSION["family_rank"]  = $d["family_rank"];
        $_SESSION["created_at"]   = $d["created_at"];
        $_SESSION["status"]       = $d["status"];
        $_SESSION["panel"]        = 'vbs';
        header("location: ../dashboard.php");
    } else {
        header("location: ../index.php?error=1");
    }
}
?>
