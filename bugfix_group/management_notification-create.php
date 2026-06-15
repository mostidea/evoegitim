<?php
error_reporting(E_ALL);
ini_set("display_errors", 1);
session_start();
include("../config/connection.php");
checkUnSession();

// Değişkenleri tanımla
$id = isset($_GET['id']) ? intval($_GET['id']) : null;

// Eğer ID varsa mevcut bildiriyi getir
$notificationData = null;
if ($id) {
    $stmt = $db->prepare("SELECT * FROM notification WHERE id = ?");
    $stmt->execute([$id]);
    $notificationData = $stmt->fetch(PDO::FETCH_ASSOC);
}

// Kullanıcıları çek (select için)
$userStmt = $db->query("SELECT id, email FROM users");
$users = $userStmt->fetchAll(PDO::FETCH_ASSOC);

$parentSmt = $db->query("SELECT id, email FROM parent");
$parents = $parentSmt->fetchAll(PDO::FETCH_ASSOC);

$users = array_merge($users, $parents);

// Form gönderildiğinde işle
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title    = $_POST['title'];
    $message  = $_POST['message'];
    $receiver = (int)$_POST['receiver']; // Her zaman sayısal ID olarak sakla

    // INSERT veya UPDATE işlemi
    if ($id) {
        $updateStmt = $db->prepare("UPDATE notification SET title = ?, description = ?, receiver = ? WHERE id = ?");
        $updateStmt->execute([$title, $message, $receiver, $id]);
    } else {
        $insertStmt = $db->prepare("INSERT INTO notification (title, description, receiver, created_at) VALUES (?, ?, ?, NOW())");
        $insertStmt->execute([$title, $message, $receiver]);
    }

    header("Location: notification.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <?php include "../includes_panel/meta.php"; ?>
    <title><?php echo $id ? "Bildiri Güncelle" : "Yeni Bildiri Oluştur"; ?> | Evo Eğitim</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/summernote@0.8.20/dist/summernote-bs4.min.css" rel="stylesheet">
</head>
<body>
    <?php include 'includes/left-menu.php'; ?>
    <div class="dashboard-main-wrapper">
        <?php include 'includes/top-menu.php'; ?>
        <div class="dashboard-body">
            <div class="card">
                <div class="card-header">
                    <h5><?php echo $id ? "Bildiri Güncelle" : "Yeni Bildiri Oluştur"; ?></h5>
                </div>
                <div class="card-body">
                    <form action="" method="POST">
                        <div class="row">
                            <div class="col-lg-12 mb-3">
                                <label class="form-label">Başlık</label>
                                <input type="text" name="title" class="form-control" required
                                    value="<?php echo htmlspecialchars($notificationData['title'] ?? ''); ?>">
                            </div>
                            <div class="col-lg-12 mb-3">
                                <label class="form-label">Mesaj</label>
                                <textarea id="summernote" name="message" class="form-control" required><?php echo htmlspecialchars($notificationData['description'] ?? ''); ?></textarea>
                            </div>
                            <div class="col-lg-12 mb-3">
                                <label class="form-label">Alıcı</label>
                                <select name="receiver" class="form-control">
                                    <option value="0" <?php echo (isset($notificationData['receiver']) && $notificationData['receiver'] == 0) ? 'selected' : ''; ?>>Öğrencilere Genel Bildiri</option>
                                    <option value="1" <?php echo (isset($notificationData['receiver']) && $notificationData['receiver'] == 1) ? 'selected' : ''; ?>>Öğretmenlere Genel Bildiri</option>
                                    <option value="2" <?php echo (isset($notificationData['receiver']) && $notificationData['receiver'] == 2) ? 'selected' : ''; ?>>Velilere Genel Bildiri</option>
                                    <?php foreach ($users as $user): ?>
                                        <option value="<?php echo (int)$user['id']; ?>"
                                            <?php echo (isset($notificationData['receiver']) && $notificationData['receiver'] == $user['id']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($user['email']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary"><?php echo $id ? "Güncelle" : "Oluştur"; ?></button>
                    </form>
                </div>
            </div>
        </div>
        <?php include '../includes_panel/footer.php'; ?>
    </div>

    <?php include '../includes_panel/scripts.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/jquery@3.6.0/dist/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/summernote@0.8.20/dist/summernote-bs4.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/summernote@0.8.20/dist/lang/summernote-tr-TR.min.js"></script>

    <script>
    $(document).ready(function() {
        $('#summernote').summernote({
            height: 300,
            placeholder: 'Mesajınızı buraya yazın...',
            lang: 'tr-TR',
            fontNames: ['Arial', 'Arial Black', 'Comic Sans MS', 'Courier New', 'Helvetica', 'Impact', 'Lucida Grande', 'Tahoma', 'Times New Roman', 'Verdana'],
            fontSizes: ['8', '9', '10', '11', '12', '14', '16', '18', '20', '22', '24', '28', '32', '36', '48', '64'],
            toolbar: [
                ['style', ['style']],
                ['font', ['bold', 'italic', 'underline', 'strikethrough', 'superscript', 'subscript', 'clear']],
                ['fontname', ['fontname']],
                ['fontsize', ['fontsize']],
                ['color', ['forecolor', 'backcolor']],
                ['para', ['ul', 'ol', 'paragraph']],
                ['height', ['height']],
                ['table', ['table']],
                ['insert', ['link', 'picture', 'video']],
                ['view', ['fullscreen', 'codeview', 'help']]
            ],
            styleTags: [
                'p',
                { title: 'Başlık 1', tag: 'h1', className: '', value: 'h1' },
                { title: 'Başlık 2', tag: 'h2', className: '', value: 'h2' },
                { title: 'Başlık 3', tag: 'h3', className: '', value: 'h3' },
                { title: 'Başlık 4', tag: 'h4', className: '', value: 'h4' },
                { title: 'Başlık 5', tag: 'h5', className: '', value: 'h5' },
                { title: 'Başlık 6', tag: 'h6', className: '', value: 'h6' }
            ],
            callbacks: {
                onInit: function() { console.log('Summernote başlatıldı'); },
                onImageUpload: function(files) { console.log('Resim yüklendi:', files); }
            }
        });
    });
    </script>
</body>
</html>
