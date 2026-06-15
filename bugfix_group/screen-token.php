<?php
session_start();
if (!isset($_SESSION['user_id'])) {
  http_response_code(403);
  exit(json_encode(['error' => 'No user']));
}
header('Content-Type: application/json');

require_once __DIR__ . '/../config/agora.php';
include __DIR__ . '/src/RtcTokenBuilder2.php';

$channelName = $_GET['channel'] ?? '';
$screenUid   = $_GET['uid'] ?? '';

if (!$channelName || !$screenUid) {
  http_response_code(400);
  exit(json_encode(['error' => 'Missing params']));
}

$token = RtcTokenBuilder2::buildTokenWithUserAccount(
  AGORA_APP_ID,
  AGORA_APP_CERTIFICATE,
  $channelName,
  $screenUid,
  RtcTokenBuilder2::ROLE_PUBLISHER,
  3600,
  3600
);

echo json_encode([
  'appId'       => AGORA_APP_ID,
  'channelName' => $channelName,
  'uid'         => $screenUid,
  'token'       => $token,
]);
