<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo '{}';
    exit;
}

$roomId = intval($_GET['room_id'] ?? 0);
$myUid  = intval($_GET['uid']     ?? 0);

if (!$roomId || !$myUid) { echo '{}'; exit; }

$commands = [];

// Kişiye özel komut
$file = sys_get_temp_dir() . "/evo_ctrl_{$roomId}_{$myUid}.json";
if (file_exists($file)) {
    $d = json_decode(file_get_contents($file), true);
    if ($d && (time() - ($d['ts'] ?? 0)) <= 10) $commands[] = $d;
    @unlink($file);
}

// Herkese yayın komutu (targetUid=0)
$fileBcast = sys_get_temp_dir() . "/evo_ctrl_{$roomId}_0.json";
if (file_exists($fileBcast)) {
    $d2 = json_decode(file_get_contents($fileBcast), true);
    if ($d2 && (time() - ($d2['ts'] ?? 0)) <= 10) $commands[] = $d2;
}

echo json_encode($commands ? $commands[0] : new stdClass());
