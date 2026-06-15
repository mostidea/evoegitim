<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo '{"ok":0}';
    exit;
}

$roomId    = intval($_POST['room_id']    ?? 0);
$targetUid = intval($_POST['target_uid'] ?? 0);
$action    = preg_replace('/[^a-zA-Z]/', '', $_POST['action'] ?? '');

if (!$roomId || !$action) { echo '{"ok":0}'; exit; }

$file = sys_get_temp_dir() . "/evo_ctrl_{$roomId}_{$targetUid}.json";
file_put_contents($file, json_encode(['action' => $action, 'ts' => time()]));
echo '{"ok":1}';
