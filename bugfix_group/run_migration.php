<?php
// Güvenlik token'ı — URL'e ?token=EvoVBG2024 ekleyerek çalıştır
define('SECRET_TOKEN', 'EvoVBG2024');

if (!isset($_GET['token']) || $_GET['token'] !== SECRET_TOKEN) {
    http_response_code(403);
    die('<h2 style="color:red;">Yetkisiz erişim. URL\'e ?token=EvoVBG2024 ekleyin.</h2>');
}

require_once __DIR__ . '/config/connection.php'; // PDO $db

$sql = "
CREATE TABLE IF NOT EXISTS `virtual_backgrounds` (
  `id`           INT(11)               NOT NULL AUTO_INCREMENT,
  `name`         VARCHAR(255)          NOT NULL,
  `image_path`   VARCHAR(500)          NOT NULL,
  `type`         ENUM('system','user') NOT NULL DEFAULT 'system',
  `user_id`      INT(11)                        DEFAULT NULL,
  `is_active`    TINYINT(1)            NOT NULL DEFAULT 1,
  `sort_order`   INT(11)               NOT NULL DEFAULT 0,
  `created_at`   TIMESTAMP             NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_active_sort` (`type`, `is_active`, `sort_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
";

$insertSql = "
INSERT INTO `virtual_backgrounds` (`name`, `image_path`, `type`, `is_active`, `sort_order`)
SELECT v.name, v.image_path, v.type, v.is_active, v.sort_order FROM (
  SELECT 'EBO Eğitim Kurumsal' AS name, '/assets/img/virtual-bg/ebo-kurumsal.jpg' AS image_path, 'system' AS type, 1 AS is_active, 1 AS sort_order
  UNION ALL SELECT 'EBO Eğitim Logo',    '/assets/img/virtual-bg/ebo-logo.jpg',     'system', 1, 2
  UNION ALL SELECT 'EBO Kampüs Görseli', '/assets/img/virtual-bg/ebo-kampus.jpg',   'system', 1, 3
  UNION ALL SELECT 'Profesyonel Ofis',   '/assets/img/virtual-bg/office.jpg',       'system', 1, 4
  UNION ALL SELECT 'Kütüphane',          '/assets/img/virtual-bg/library.jpg',      'system', 1, 5
  UNION ALL SELECT 'Doğa Manzarası',     '/assets/img/virtual-bg/nature.jpg',       'system', 1, 6
) AS v
WHERE NOT EXISTS (SELECT 1 FROM `virtual_backgrounds` LIMIT 1);
";

$errors = [];
$success = [];

try {
    $db->exec($sql);
    $success[] = '✅ virtual_backgrounds tablosu oluşturuldu (veya zaten vardı).';
} catch (PDOException $e) {
    $errors[] = '❌ Tablo oluşturma hatası: ' . htmlspecialchars($e->getMessage());
}

if (empty($errors)) {
    try {
        $db->exec($insertSql);
        $success[] = '✅ Örnek sistem arka planları eklendi (tablo boşsa).';
    } catch (PDOException $e) {
        $errors[] = '❌ Veri ekleme hatası: ' . htmlspecialchars($e->getMessage());
    }
}

// Tablo içeriğini kontrol et
$count = 0;
try {
    $count = $db->query("SELECT COUNT(*) FROM virtual_backgrounds")->fetchColumn();
} catch (PDOException $e) {}

// Başarılıysa dosyayı sil
$selfDeleted = false;
if (empty($errors)) {
    @unlink(__FILE__);
    $selfDeleted = true;
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
<meta charset="UTF-8">
<title>Migration — Virtual Backgrounds</title>
<style>
  body { font-family: sans-serif; max-width: 640px; margin: 60px auto; padding: 20px; }
  .ok  { background:#d4edda; border:1px solid #c3e6cb; color:#155724; padding:12px; border-radius:6px; margin:8px 0; }
  .err { background:#f8d7da; border:1px solid #f5c6cb; color:#721c24; padding:12px; border-radius:6px; margin:8px 0; }
  .info{ background:#d1ecf1; border:1px solid #bee5eb; color:#0c5460; padding:12px; border-radius:6px; margin:8px 0; }
  h2 { color: #333; }
</style>
</head>
<body>
<h2>Virtual Backgrounds — Migration</h2>

<?php foreach ($success as $msg): ?>
  <div class="ok"><?= $msg ?></div>
<?php endforeach; ?>

<?php foreach ($errors as $msg): ?>
  <div class="err"><?= $msg ?></div>
<?php endforeach; ?>

<div class="info">
  Tablodaki toplam kayıt: <strong><?= $count ?></strong>
</div>

<?php if ($selfDeleted): ?>
  <div class="ok">✅ Bu dosya güvenlik nedeniyle sunucudan silindi.</div>
<?php else: ?>
  <div class="err">⚠️ Dosya silinemedi — lütfen <code>run_migration.php</code>'yi sunucudan manuel olarak silin.</div>
<?php endif; ?>

</body>
</html>
