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

    $stmt = $db->prepare("SELECT * FROM users WHERE email = ?");
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
        $upd = $db->prepare("UPDATE users SET password = ? WHERE id = ?");
        $upd->execute([$newHash, $d['id']]);
        $verified = true;
    }

    if ($verified) {
        $parentEmail = getParentEmail($db, $d["email"]);
        $_SESSION["user_id"]       = $d["id"];
        $_SESSION["fullname"]      = $d["fullname"];
        $_SESSION["email"]         = $d["email"];
        $_SESSION["phone"]         = $d["phone"];
        $_SESSION["level"]         = $d["level"];
        $_SESSION["parent_email"]  = $parentEmail;
        $_SESSION["created_at"]    = $d["created_at"];
        $_SESSION["description"]   = $d["description"];
        $_SESSION["profile_photo"] = $d["profile_photo"];
        $_SESSION["status"]        = $d["status"];
        $_SESSION["panel"]         = 'student';
        header("location: ../dashboard.php");
    } else {
        header("location: ../index.php?error=1");
    }
}
?>
