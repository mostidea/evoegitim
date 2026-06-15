-- ============================================================
-- Sanal Arka Plan (Virtual Background) - Veritabanı Migrasyonu
-- Hedef: evoegiti_tech veritabanı  (connection.php ile aynı)
-- ============================================================

CREATE TABLE IF NOT EXISTS `virtual_backgrounds` (
  `id`           INT(11)                  NOT NULL AUTO_INCREMENT,
  `name`         VARCHAR(255)             NOT NULL,
  `image_path`   VARCHAR(500)             NOT NULL,
  `type`         ENUM('system','user')    NOT NULL DEFAULT 'system',
  `user_id`      INT(11)                  DEFAULT NULL,
  `is_active`    TINYINT(1)               NOT NULL DEFAULT 1,
  `sort_order`   INT(11)                  NOT NULL DEFAULT 0,
  `created_at`   TIMESTAMP                NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_active_sort` (`type`, `is_active`, `sort_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Örnek kurumsal arka planlar (görseller /assets/img/virtual-bg/ altına yüklenmeli)
INSERT INTO `virtual_backgrounds` (`name`, `image_path`, `type`, `is_active`, `sort_order`) VALUES
('EBO Eğitim Kurumsal',    '/assets/img/virtual-bg/ebo-kurumsal.jpg', 'system', 1, 1),
('EBO Eğitim Logo',        '/assets/img/virtual-bg/ebo-logo.jpg',     'system', 1, 2),
('EBO Kampüs Görseli',     '/assets/img/virtual-bg/ebo-kampus.jpg',   'system', 1, 3),
('Profesyonel Ofis',       '/assets/img/virtual-bg/office.jpg',       'system', 1, 4),
('Kütüphane',              '/assets/img/virtual-bg/library.jpg',      'system', 1, 5),
('Doğa Manzarası',         '/assets/img/virtual-bg/nature.jpg',       'system', 1, 6);
