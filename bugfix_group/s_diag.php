<?php
header('Content-Type: text/plain; charset=utf-8');
ini_set('display_errors', 1);
error_reporting(E_ALL);

echo "=== SESSION DIAGNOSTIC ===\n";
echo "PHPSESSID cookie: " . ($_COOKIE['PHPSESSID'] ?? 'NOT SET') . "\n";

session_start();
echo "Session ID: " . session_id() . "\n";
echo "user_id: " . ($_SESSION['user_id'] ?? 'NOT SET') . "\n";
echo "panel: " . ($_SESSION['panel'] ?? 'NOT SET') . "\n";
echo "csrf_token: " . (empty($_SESSION['csrf_token']) ? 'NOT SET' : 'SET (' . strlen($_SESSION['csrf_token']) . ' chars)') . "\n";
echo "REQUEST_URI: " . $_SERVER['REQUEST_URI'] . "\n\n";

echo "=== PANEL CHECK SIMULATION ===\n";
$uri = $_SERVER['REQUEST_URI'] ?? '';
$panel = $_SESSION['panel'] ?? '';
$rules = [
    '/teacher/'    => 'teacher',
    '/student/'    => 'student',
    '/vbs/'        => 'vbs',
    '/management/' => 'management',
];
$matched = false;
foreach ($rules as $path => $expected) {
    if (strpos($uri, $path) !== false) {
        $matched = true;
        echo "Matched rule: $path => $expected\n";
        echo "Panel in session: '$panel'\n";
        if (!empty($panel) && $panel !== $expected) {
            echo "RESULT: WOULD DESTROY SESSION (panel mismatch)\n";
        } elseif (empty($panel)) {
            echo "RESULT: Panel empty, would set to '$expected'\n";
        } else {
            echo "RESULT: OK - panel matches\n";
        }
        break;
    }
}
if (!$matched) {
    echo "No matching rule for URI: $uri\n";
}

echo "\n=== SESSION FILE ===\n";
$f = '/var/lib/php/sessions/sess_' . session_id();
echo "Path: $f\n";
echo "Exists: " . (file_exists($f) ? "YES (" . filesize($f) . " bytes)" : "NO") . "\n";
if (file_exists($f)) {
    echo "Content: " . file_get_contents($f) . "\n";
}
