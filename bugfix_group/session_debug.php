<?php
header('Content-Type: text/plain; charset=utf-8');

echo "COOKIE PHPSESSID: " . ($_COOKIE['PHPSESSID'] ?? 'NOT SET') . "\n";

ini_set('session.cookie_path', '/');
session_start();

echo "SESSION ID: " . session_id() . "\n";
echo "user_id: " . ($_SESSION['user_id'] ?? 'NOT SET') . "\n";
echo "panel: " . ($_SESSION['panel'] ?? 'NOT SET') . "\n";
echo "email: " . ($_SESSION['email'] ?? 'NOT SET') . "\n";

$f = '/var/lib/php/sessions/sess_' . session_id();
echo "\nSession file: " . $f . "\n";
echo "File exists: " . (file_exists($f) ? "YES (" . filesize($f) . " bytes)" : "NO") . "\n";
if (file_exists($f)) {
    echo "Content: " . file_get_contents($f) . "\n";
}
