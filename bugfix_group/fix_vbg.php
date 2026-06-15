<?php
require_once __DIR__ . '/config/connection.php';

// office.jpg, library.jpg, nature.jpg, ebo-kampus.jpg → pasif yap
$deactivate = $db->prepare(
    "UPDATE virtual_backgrounds SET is_active = 0
     WHERE image_path LIKE :p"
);
foreach (['%office.jpg%', '%library.jpg%', '%nature.jpg%', '%ebo-kampus.jpg%'] as $pattern) {
    $deactivate->execute([':p' => $pattern]);
    echo "Pasif: $pattern — etkilenen: " . $deactivate->rowCount() . "\n";
}

// ebo-kampus.jpg kaydını Evo-kampus.webp olarak güncelle
$update = $db->prepare(
    "UPDATE virtual_backgrounds
     SET image_path = '/assets/img/virtual-bg/Evo-kampus.webp',
         name = 'Evo Kampüs',
         is_active = 1
     WHERE image_path LIKE '%ebo-kampus%'"
);
$update->execute();
echo "Kampüs güncellendi: " . $update->rowCount() . " kayıt\n";

// Mevcut tüm arka planları listele
$list = $db->query("SELECT id, name, image_path, is_active FROM virtual_backgrounds WHERE type='system' ORDER BY sort_order");
echo "\n--- Mevcut kayıtlar ---\n";
foreach ($list->fetchAll(PDO::FETCH_ASSOC) as $r) {
    echo ($r['is_active'] ? '✓' : '✗') . " [{$r['id']}] {$r['name']} — {$r['image_path']}\n";
}
