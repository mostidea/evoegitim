<?php
ob_start();
session_start();
error_reporting(0);
include("../config/connection.php");

ob_clean();
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 0, 'message' => 'Geçersiz istek.']);
    exit;
}

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 0, 'message' => 'Oturum açmanız gerekiyor.']);
    exit;
}

$groupId = (int)($_POST['id'] ?? 0);
$userId  = (int)($_POST['uid'] ?? 0);

if ($groupId <= 0 || $userId <= 0 || $userId !== (int)$_SESSION['user_id']) {
    echo json_encode(['status' => 0, 'message' => 'Geçersiz istek parametreleri.']);
    exit;
}

// Grup bilgilerini çek
$stmt = $db->prepare('SELECT id, title, credit, total_lesson_time, weekly_lesson_count, quota FROM `groups` WHERE id = ?');
$stmt->execute([$groupId]);
$group = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$group) {
    echo json_encode(['status' => 0, 'message' => 'Grup dersi bulunamadı.']);
    exit;
}

// Zaten başvurmuş mu?
$stmt = $db->prepare('SELECT id FROM groups_quota WHERE group_id = ? AND user_id = ?');
$stmt->execute([$groupId, $userId]);
if ($stmt->rowCount() > 0) {
    echo json_encode(['status' => 0, 'message' => 'Bu grup dersine zaten başvurmuşsunuz.']);
    exit;
}

// Kontenjan dolu mu? (sadece onaylananlar sayılır)
$stmt = $db->prepare('SELECT COUNT(*) FROM groups_quota WHERE group_id = ? AND status = 1');
$stmt->execute([$groupId]);
if ((int)$stmt->fetchColumn() >= (int)$group['quota']) {
    echo json_encode(['status' => 0, 'message' => 'Bu grup dersinin kontenjanı dolmuştur.']);
    exit;
}

// Gereken kredi kontrolü (kredi düşülmez — onayda düşülür)
$requiredCredits = (int)$group['credit'] * (int)$group['total_lesson_time'] * (int)$group['weekly_lesson_count'];
if ($requiredCredits > 0) {
    $stmt = $db->prepare('SELECT COALESCE(SUM(credit), 0) FROM active_credit WHERE user_id = ? AND type = ? AND credit > 0');
    $stmt->execute([$userId, '1']);
    $totalAvailable = (int)$stmt->fetchColumn();
    if ($totalAvailable < $requiredCredits) {
        echo json_encode(['status' => 0, 'message' => "Yetersiz grup ders kredisi. Gereken: {$requiredCredits}, Mevcut: {$totalAvailable}"]);
        exit;
    }
}

// Başvuruyu kaydet (status=0: onay bekliyor)
$stmt = $db->prepare('INSERT INTO groups_quota (group_id, user_id, status) VALUES (?, ?, 0)');
$stmt->execute([$groupId, $userId]);

// Öğrenci adını çek
$stmt = $db->prepare('SELECT fullname FROM users WHERE id = ?');
$stmt->execute([$userId]);
$student = $stmt->fetch(PDO::FETCH_ASSOC);
$studentName = $student['fullname'] ?? 'Öğrenci';

// Yöneticilere (role=4) mail + SMS gönder
$stmt = $db->prepare('SELECT email, phone FROM users WHERE role = 4');
$stmt->execute();
$admins = $stmt->fetchAll(PDO::FETCH_ASSOC);

$smsText   = "{$studentName}, {$group['title']} Grup Dersine Katilim Basvurusunda Bulundu.";
$mailKonu  = "Yeni Grup Ders Başvurusu";
$mailIcerik = "
<p>Merhaba,</p>
<p><strong>{$studentName}</strong> adlı öğrenci, <strong>{$group['title']}</strong> grup dersine katılım başvurusunda bulundu.</p>
<p>Başvuruyu incelemek için: <a href='https://evoegitim.com/management/group-request.php?id={$groupId}'>Buraya tıklayın</a></p>
";

$phones = [];
foreach ($admins as $admin) {
    if (!empty($admin['email'])) {
        sendEmail($admin['email'], 'Evo Eğitim Yönetim', $mailKonu, $mailIcerik);
    }
    if (!empty($admin['phone'])) {
        $phones[] = $admin['phone'];
    }
}
if (!empty($phones)) {
    sendSms($smsText, $phones);
}

ob_clean();
echo json_encode(['status' => 1, 'message' => 'Başvurunuz alındı! Yönetici onayından sonra kayıt tamamlanacaktır.']);
