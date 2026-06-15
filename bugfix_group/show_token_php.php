<?php
// Sadece bir kez çalıştır, sonra sil
$path = __DIR__ . '/rooms/token.php';
if (file_exists($path)) {
    echo '<pre>' . htmlspecialchars(file_get_contents($path)) . '</pre>';
} else {
    echo 'token.php bulunamadı: ' . $path;
}
