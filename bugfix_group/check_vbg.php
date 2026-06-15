<?php
$dir = __DIR__ . '/assets/img/virtual-bg/';
echo "Dizin var mı: " . (is_dir($dir) ? 'EVET' : 'HAYIR') . "\n";
echo "Dizin izni: " . decoct(fileperms($dir) & 0777) . "\n\n";
if (is_dir($dir)) {
    $files = scandir($dir);
    echo "Dosyalar:\n";
    foreach ($files as $f) {
        if ($f === '.' || $f === '..') continue;
        $fp = $dir . $f;
        echo "  $f — " . filesize($fp) . " byte — izin: " . decoct(fileperms($fp) & 0777) . "\n";
    }
}
