<?php
require_once __DIR__ . '/config/connection.php';

$name      = 'Evo Eğitim';
$imagePath = '/assets/img/virtual-bg/Evo-2.webp';

$maxStmt   = $db->query("SELECT COALESCE(MAX(sort_order), 0) + 1 FROM virtual_backgrounds WHERE type = 'system'");
$sortOrder = (int) $maxStmt->fetchColumn();

$insert = $db->prepare(
    "INSERT INTO virtual_backgrounds (name, image_path, type, is_active, sort_order)
     VALUES (:name, :path, 'system', 1, :sort)"
);
$insert->execute([':name' => $name, ':path' => $imagePath, ':sort' => $sortOrder]);

echo 'OK — ID: ' . $db->lastInsertId();
