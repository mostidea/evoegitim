<?php
/**
 * inc/post/virtual-bg.php
 * Sanal Arka Plan AJAX Handler
 *
 * İzin verilen action'lar:
 *   list          – Aktif sistem arka planlarını getir (herkes)
 *   upload        – Yeni arka plan yükle (management/admin)
 *   delete        – Arka plan sil (management/admin)
 *   toggle        – Aktif/Pasif değiştir (management/admin)
 *   reorder       – Sıralama güncelle (management/admin)
 */

session_start();
header('Content-Type: application/json; charset=utf-8');

require_once dirname(__DIR__, 2) . '/config/connection.php';

$action = $_POST['action'] ?? $_GET['action'] ?? '';

// ── Yetki kontrolleri ──────────────────────────────────────────────────────

function requireUser(): void {
    if (empty($_SESSION['user_id'])) {
        echo json_encode(['status' => 0, 'message' => 'Oturum açmanız gerekiyor.']);
        exit;
    }
}

function requireAdmin(): void {
    requireUser();
    // session role kontrolü – management/admin panelinin kendi yapısına göre düzenleyin
    $role = $_SESSION['role'] ?? $_SESSION['user_role'] ?? '';
    if (!in_array($role, ['admin', 'management', 'manager', 'superadmin'], true)) {
        echo json_encode(['status' => 0, 'message' => 'Bu işlem için yetkiniz yok.']);
        exit;
    }
}

// ── Dosya yükleme sabitleri ───────────────────────────────────────────────

const MAX_SIZE_MB     = 10;
const ALLOWED_MIME    = ['image/jpeg', 'image/png', 'image/webp'];
const ALLOWED_EXT     = ['jpg', 'jpeg', 'png', 'webp'];
const UPLOAD_DIR      = __DIR__ . '/../../assets/img/virtual-bg/';
const UPLOAD_WEB_PATH = '/assets/img/virtual-bg/';

function ensureUploadDir(): void {
    if (!is_dir(UPLOAD_DIR)) {
        mkdir(UPLOAD_DIR, 0755, true);
    }
}

// ── Action: list ──────────────────────────────────────────────────────────
if ($action === 'list') {
    requireUser();

    $stmt = $db->prepare(
        "SELECT id, name, image_path, sort_order
         FROM virtual_backgrounds
         WHERE type = 'system' AND is_active = 1
         ORDER BY sort_order ASC, id ASC"
    );
    $stmt->execute();
    $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['status' => 1, 'data' => $items]);
    exit;
}

// ── Action: upload ────────────────────────────────────────────────────────
if ($action === 'upload') {
    requireAdmin();

    $name = trim($_POST['name'] ?? '');
    if ($name === '') {
        echo json_encode(['status' => 0, 'message' => 'Arka plan adı boş olamaz.']);
        exit;
    }

    if (empty($_FILES['image']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK) {
        echo json_encode(['status' => 0, 'message' => 'Görsel yüklenmedi veya yükleme hatası oluştu.']);
        exit;
    }

    $file     = $_FILES['image'];
    $mimeType = mime_content_type($file['tmp_name']);
    $ext      = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

    if (!in_array($mimeType, ALLOWED_MIME, true) || !in_array($ext, ALLOWED_EXT, true)) {
        echo json_encode(['status' => 0, 'message' => 'Desteklenmeyen format. JPG, PNG veya WEBP yükleyin.']);
        exit;
    }

    if ($file['size'] > MAX_SIZE_MB * 1024 * 1024) {
        echo json_encode(['status' => 0, 'message' => 'Dosya boyutu ' . MAX_SIZE_MB . 'MB limitini aşıyor.']);
        exit;
    }

    ensureUploadDir();

    $filename  = uniqid('vbg_', true) . '.' . $ext;
    $destPath  = UPLOAD_DIR . $filename;
    $webPath   = UPLOAD_WEB_PATH . $filename;

    if (!move_uploaded_file($file['tmp_name'], $destPath)) {
        echo json_encode(['status' => 0, 'message' => 'Dosya kaydedilemedi. Sunucu izinlerini kontrol edin.']);
        exit;
    }

    // Sort order: mevcut en yüksek + 1
    $maxStmt = $db->query("SELECT COALESCE(MAX(sort_order), 0) + 1 FROM virtual_backgrounds WHERE type = 'system'");
    $sortOrder = (int) $maxStmt->fetchColumn();

    $insert = $db->prepare(
        "INSERT INTO virtual_backgrounds (name, image_path, type, is_active, sort_order)
         VALUES (:name, :path, 'system', 1, :sort)"
    );
    $insert->execute([':name' => $name, ':path' => $webPath, ':sort' => $sortOrder]);
    $newId = $db->lastInsertId();

    echo json_encode([
        'status'     => 1,
        'message'    => 'Arka plan başarıyla yüklendi.',
        'id'         => $newId,
        'image_path' => $webPath,
        'name'       => $name,
    ]);
    exit;
}

// ── Action: delete ────────────────────────────────────────────────────────
if ($action === 'delete') {
    requireAdmin();

    $id = (int) ($_POST['id'] ?? 0);
    if ($id <= 0) {
        echo json_encode(['status' => 0, 'message' => 'Geçersiz ID.']);
        exit;
    }

    $row = $db->prepare("SELECT image_path, type FROM virtual_backgrounds WHERE id = ?");
    $row->execute([$id]);
    $bg = $row->fetch(PDO::FETCH_ASSOC);

    if (!$bg) {
        echo json_encode(['status' => 0, 'message' => 'Kayıt bulunamadı.']);
        exit;
    }

    // Dosyayı sil (sistem arka planları için)
    if ($bg['type'] === 'system') {
        $filePath = rtrim($_SERVER['DOCUMENT_ROOT'] ?? '', '/') . $bg['image_path'];
        if (file_exists($filePath)) {
            @unlink($filePath);
        }
    }

    $del = $db->prepare("DELETE FROM virtual_backgrounds WHERE id = ?");
    $del->execute([$id]);

    echo json_encode(['status' => 1, 'message' => 'Arka plan silindi.']);
    exit;
}

// ── Action: toggle ────────────────────────────────────────────────────────
if ($action === 'toggle') {
    requireAdmin();

    $id = (int) ($_POST['id'] ?? 0);
    if ($id <= 0) {
        echo json_encode(['status' => 0, 'message' => 'Geçersiz ID.']);
        exit;
    }

    $toggle = $db->prepare(
        "UPDATE virtual_backgrounds SET is_active = 1 - is_active WHERE id = ?"
    );
    $toggle->execute([$id]);

    $cur = $db->prepare("SELECT is_active FROM virtual_backgrounds WHERE id = ?");
    $cur->execute([$id]);
    $isActive = (int) $cur->fetchColumn();

    echo json_encode(['status' => 1, 'is_active' => $isActive]);
    exit;
}

// ── Action: reorder ───────────────────────────────────────────────────────
if ($action === 'reorder') {
    requireAdmin();

    $ids   = json_decode($_POST['ids']   ?? '[]', true);
    $ranks = json_decode($_POST['ranks'] ?? '[]', true);

    if (!is_array($ids) || !is_array($ranks) || count($ids) !== count($ranks)) {
        echo json_encode(['status' => 0, 'message' => 'Geçersiz sıralama verisi.']);
        exit;
    }

    $upd = $db->prepare("UPDATE virtual_backgrounds SET sort_order = ? WHERE id = ? AND type = 'system'");
    foreach ($ids as $i => $id) {
        $upd->execute([(int)$ranks[$i], (int)$id]);
    }

    echo json_encode(['status' => 1]);
    exit;
}

echo json_encode(['status' => 0, 'message' => 'Geçersiz işlem.']);
