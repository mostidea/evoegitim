<?php
include 'config/connection.php';

// Sayfalama için ayarlar
$limit = 9; // Her sayfada kaç grup gösterilecek
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// Filtreleme seçenekleri
$lesson_filter = isset($_GET['lesson']) ? (int)$_GET['lesson'] : 0;
$level_filter = isset($_GET['level']) ? trim($_GET['level']) : '';
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

// WHERE koşulları oluştur
$where_conditions = ["g.publish = 1"];
$params = [];

if ($lesson_filter > 0) {
    $where_conditions[] = "g.lesson_id = :lesson_id";
    $params[':lesson_id'] = $lesson_filter;
}

if (!empty($level_filter)) {
    $where_conditions[] = "(g.level LIKE :level OR g.level LIKE :level_json)";
    $params[':level'] = '%' . $level_filter . '%';
    $params[':level_json'] = '%"' . $level_filter . '"%';
}

if (!empty($search)) {
    $where_conditions[] = "(g.title LIKE :search OR g.subject LIKE :search)";
    $params[':search'] = '%' . $search . '%';
}

$where_clause = implode(' AND ', $where_conditions);

// Toplam grup sayısını al
$countQuery = $db->prepare("
    SELECT COUNT(*) 
    FROM `groups` g 
    WHERE $where_clause
");
$countQuery->execute($params);
$totalGroups = $countQuery->fetchColumn();
$totalPages = ceil($totalGroups / $limit);

// Grup verilerini çek
$groupsQuery = $db->prepare("
    SELECT 
        g.id,
        g.title,
        g.subject,
        g.slug,
        g.image,
        g.weekly_lesson_count,
        g.total_lesson_time,
        g.quota,
        g.credit,
        g.level,
        g.start_date,
        u.fullname as teacher_name,
        u.profile_photo as teacher_photo,
        u.gender as teacher_gender,
        g.teacher_id AS teacher_id,
        l.title as lesson_name
    FROM `groups` g
    LEFT JOIN users u ON g.teacher_id = u.id
    LEFT JOIN lessons l ON g.lesson_id = l.id
    WHERE $where_clause
    ORDER BY g.id DESC
    LIMIT :limit OFFSET :offset
");

foreach ($params as $key => $value) {
    $groupsQuery->bindValue($key, $value);
}
$groupsQuery->bindValue(':limit', $limit, PDO::PARAM_INT);
$groupsQuery->bindValue(':offset', $offset, PDO::PARAM_INT);
$groupsQuery->execute();

// Ders listesi filtre için
$lessonsQuery = $db->prepare("SELECT id, title FROM lessons ORDER BY title");
$lessonsQuery->execute();

// Fonksiyonlar
function getGroupImage($image) {
    if (!empty($image) && file_exists($image)) {
        return $image;
    }
    
    $defaultImages = [
        'assets/img/course/course-2-1.jpg',
        'assets/img/course/course-2-2.jpg',
        'assets/img/course/course-2-3.jpg',
        'assets/img/course/course-4-1.jpg',
        'assets/img/course/course-4-2.jpg',
        'assets/img/course/course-4-3.jpg'
    ];
    return $defaultImages[array_rand($defaultImages)];
}

function getTeacherPhoto($profile_photo, $gender) {
    if (!empty($profile_photo) && file_exists($profile_photo)) {
        return $profile_photo;
    }
    
    if ($gender == 2) {
        return 'assets/img/course/course-2-1.png';
    } else {
        return 'assets/img/course/course-2-3.png';
    }
}

function parseLevel($level) {
    if (empty($level)) return 'Tüm Seviyeler';
    
    if (strpos($level, '[') === 0) {
        $decoded = json_decode($level, true);
        if (is_array($decoded)) {
            return implode(', ', $decoded);
        }
    }
    
    return $level;
}

function createSlug($title) {
    $turkce = array('ç','ğ','ı','ö','ş','ü','Ç','Ğ','I','İ','Ö','Ş','Ü');
    $ingilizce = array('c','g','i','o','s','u','c','g','i','i','o','s','u');
    $title = str_replace($turkce, $ingilizce, $title);
    
    $title = strtolower($title);
    $title = preg_replace('/[^a-z0-9\s-]/', '', $title);
    $title = preg_replace('/[\s-]+/', '-', $title);
    $title = trim($title, '-');
    
    return $title;
}

function formatStartDate($start_date) {
    if (empty($start_date) || strtotime($start_date) <= 0) return 'Esnek';
    
    return turkcetarih('j F Y l', $start_date);
}
?>

<!DOCTYPE html>
<html lang="tr">

<head>
  <?php include 'includes/meta.php'; ?>
<title>Online Grup Dersler | Evo Eğitim Soru-Cevaplı Dersler</title>
<meta name="description" content="Etkileşimli online grup dersleri ile öğrenin. Her ders sonunda soru-cevap, uzman öğretmenler ve uygun fiyatlarla grup eğitimi.">
<meta property="og:title" content="Online Grup Dersler | Evo Eğitim Soru-Cevaplı Dersler">
<meta property="og:description" content="Etkileşimli online grup dersleri ile öğrenin. Her ders sonunda soru-cevap, uzman öğretmenler ve uygun fiyatlarla grup eğitimi.">
<meta property="og:image" content="assets/banners/smiling-caucasian-female-manager-having-video-call-2024-12-02-19-43-00-utc_9_11zon.jpg">

</head>

<body>
  <?php include 'includes/header.php'; ?>
  
  <div class="breadcumb-wrapper" data-bg-src="assets/banners/smiling-caucasian-female-manager-having-video-call-2024-12-02-19-43-00-utc_9_11zon.jpg" style="position: relative;">
        <div style="position: absolute; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0, 0, 0, 0.6); pointer-events: none;"></div>
    <div class="container z-index-common">
      <div class="breadcumb-content">
        <h1 class="breadcumb-title">Grup Dersleri</h1>
        <div class="breadcumb-menu-wrap">
          <ul class="breadcumb-menu">
            <li><a href="index.php">Ana Sayfa</a></li>
            <li>Grup Dersleri</li>
          </ul>
        </div>
      </div>
    </div>
  </div>
  
  <section class="course-layout1 space-top space-extra-bottom" data-bg-src="assets/img/bg/course-bg1.png">
    <div class="container">
      <div class="title-area3 text-center wow fadeInUp" data-wow-delay="0.3s">
        <span class="sec-subtitle style1">Online Özel Ders Grupları</span>
        <h2 class="sec-title">Öğrenim İhtiyacınıza Uygun Kurslara Göz Atın</h2>
      </div>
      
      <!-- Filtre Bölümü -->
      <div class="row mb-4">
        <div class="col-12">
          <div class="course-filter-box">
            <form method="GET" action="" class="row g-3">
              <div class="col-md-3">
                <select name="lesson" class="form-select">
                  <option value="0">Tüm Dersler</option>
                  <?php while ($lesson = $lessonsQuery->fetch(PDO::FETCH_ASSOC)): ?>
                    <option value="<?php echo $lesson['id']; ?>" 
                            <?php echo ($lesson_filter == $lesson['id']) ? 'selected' : ''; ?>>
                      <?php echo htmlspecialchars($lesson['title']); ?>
                    </option>
                  <?php endwhile; ?>
                </select>
              </div>
              <div class="col-md-3">
                <select name="level" class="form-select">
                  <option value="">Tüm Seviyeler</option>
                  <option value="Başlangıç" <?php echo ($level_filter == 'Başlangıç') ? 'selected' : ''; ?>>Başlangıç</option>
                  <option value="Orta" <?php echo ($level_filter == 'Orta') ? 'selected' : ''; ?>>Orta</option>
                  <option value="İleri" <?php echo ($level_filter == 'İleri') ? 'selected' : ''; ?>>İleri</option>
                  <option value="Üst" <?php echo ($level_filter == 'Üst') ? 'selected' : ''; ?>>Üst</option>
                </select>
              </div>
              <div class="col-md-4">
                <input type="text" name="search" class="form-control" 
                       placeholder="Grup ara..." value="<?php echo htmlspecialchars($search); ?>">
              </div>
              <div class="col-md-2">
                <button type="submit" class="btn btn-primary w-100">Filtrele</button>
              </div>
            </form>
          </div>
        </div>
      </div>
      
      <!-- Sonuç Bilgisi -->
      <div class="row mb-3">
        <div class="col-12">
          <p class="course-count">
            Toplam <strong><?php echo $totalGroups; ?></strong> grup dersi bulundu
            <?php if (!empty($search)): ?>
              "<strong><?php echo htmlspecialchars($search); ?></strong>" araması için
            <?php endif; ?>
          </p>
        </div>
      </div>
      <style>
/* Course kartları için eşit yükseklik - DÜZELTİLMİŞ */
.course-layout1 .row {
    display: flex;
    flex-wrap: wrap;
    margin-right: -15px;
    margin-left: -15px;
}

.course-layout1 .row > [class*="col-"] {
    display: flex;
    padding-right: 15px;
    padding-left: 15px;
    margin-bottom: 30px;
}

.course-style2 {
    width: 100%;
    height: 100%;
    display: flex;
    flex-direction: column;
    margin-bottom: 0 !important; /* Çakışmayı önle */
}

.course-style2 .course-img {
    flex-shrink: 0;
    height: 250px;
    overflow: hidden;
    margin: 17px 17px 0 17px; /* SCSS'deki orijinal margin değerleri */
}

.course-style2 .course-img img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.course-style2 .course-content {
    flex: 1;
    display: flex;
    flex-direction: column;
    padding: 25px 30px 0 30px; /* SCSS'deki orijinal padding değerleri */
}

.course-style2 .course-name {
    min-height: 56px;
    overflow: hidden;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    margin-bottom: 10px;
}

.course-style2 .course-meta {
    flex: 1;
    display: flex;
    flex-direction: column;
    gap: 8px;
    padding-bottom: 20px; /* SCSS'deki orijinal padding */
}

.course-style2 .course-meta span {
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
    display: block;
}

.course-style2 .course-footer {
    margin-top: auto;
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 15px 0 20px 0; /* SCSS'deki orijinal padding */
}

.course-style2 .course-teacher {
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
    max-width: 60%;
}

.course-style2 .course-teacher img {
    width: 30px;
    height: 30px;
    object-fit: cover;
    border-radius: 50%;
    vertical-align: middle;
}

.course-style2 .course-review {
    text-align: right;
    max-width: 40%;
}

/* Responsive düzenlemeler */
@media (max-width: 1200px) {
    .course-layout1 .row > .col-xl-4 {
        flex: 0 0 50%;
        max-width: 50%;
    }
}

@media (max-width: 576px) {
    .course-layout1 .row > .col-sm-6 {
        flex: 0 0 100%;
        max-width: 100%;
    }
}
</style>
      <!-- Grup Kartları -->
      <div class="row">
        <?php if ($groupsQuery->rowCount() > 0): ?>
          <?php while ($group = $groupsQuery->fetch(PDO::FETCH_ASSOC)): ?>
            <?php $slug = !empty($group['slug']) ? $group['slug'] : createSlug($group['title']); ?>
            <div class="col-sm-6 col-xl-4">
              <div class="course-style2">
                <div class="course-img">
                  <a href="grup-detay/<?php echo $slug; ?>">
                    <img class="w-100" src="/<?php echo htmlspecialchars(getGroupImage($group['image'])); ?>"
                         alt="<?php echo htmlspecialchars($group['title']); ?>" />
                  </a>
                  <span class="course-price"><?php echo $group["credit"]*$group["total_lesson_time"]*$group["weekly_lesson_count"]; ?> Kredi</span>
                </div>
                <div class="course-content">
                  <h3 class="h5 course-name">
                    <a href="grup-detay/<?php echo $slug; ?>" class="text-inherit">
                      <?php echo htmlspecialchars($group['title']); ?>
                    </a>
                  </h3>
                  <div class="course-meta">
                    <span><i class="fas fa-users"></i><?php echo $group['quota']; ?> Kişi</span>
                    <span><i class="fas fa-clock"></i>Haftalık Ders Saati: <?php echo $group['total_lesson_time']; ?> Saat</span>
                    <span><i class="far fa-calendar"></i>Ders Başlangıç Tarihi: <?php echo formatStartDate($group['start_date']); ?></span>
                  </div>
                  <div class="course-footer">
                    <div class="course-teacher">
                      <a href="ogretmen/<?php echo createSlug($group['teacher_name'])."-".$group['teacher_id']; ?>" class="text-inherit">
                        <img src="/<?php echo htmlspecialchars(getTeacherPhoto($group['teacher_photo'], $group['teacher_gender'])); ?>"
                             alt="<?php echo htmlspecialchars($group['teacher_name']); ?>" />
                        <?php echo htmlspecialchars($group['teacher_name'] ?: 'Evo Eğitim'); ?>
                      </a>
                    </div>
                    <div class="course-review" style="color:#6c00ba;">
                      <small><?php echo htmlspecialchars(parseLevel($group['level'])); ?></small>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          <?php endwhile; ?>
        <?php else: ?>
          <div class="col-12">
            <div class="alert alert-info text-center">
              <h4>Grup bulunamadı</h4>
              <p>
                <?php if (!empty($search) || $lesson_filter > 0 || !empty($level_filter)): ?>
                  Arama kriterlerinize uygun grup dersi bulunamadı. Lütfen farklı filtreler deneyiniz.
                <?php else: ?>
                  Henüz yayınlanmış grup dersi bulunmamaktadır.
                <?php endif; ?>
              </p>
              <a href="online-egitim-gruplari" class="btn btn-primary">Tüm Grupları Görüntüle</a>
            </div>
          </div>
        <?php endif; ?>
      </div>
      
      <!-- Sayfalama -->
      <?php if ($totalPages > 1): ?>
        <div class="row mt-5">
          <div class="col-12">
            <div class="vs-pagination text-center">
              <ul>
                <?php if ($page > 1): ?>
                  <li class="prev">
                    <a href="online-egitim-gruplari/?page=<?php echo ($page - 1); ?><?php 
                      echo ($lesson_filter > 0) ? '&lesson=' . $lesson_filter : '';
                      echo !empty($level_filter) ? '&level=' . urlencode($level_filter) : '';
                      echo !empty($search) ? '&search=' . urlencode($search) : '';
                    ?>">Önceki</a>
                  </li>
                <?php endif; ?>
                
                <?php 
                $startPage = max(1, $page - 2);
                $endPage = min($totalPages, $page + 2);
                
                if ($startPage > 1): ?>
                  <li><a href="online-egitim-gruplari/?page=1<?php 
                    echo ($lesson_filter > 0) ? '&lesson=' . $lesson_filter : '';
                    echo !empty($level_filter) ? '&level=' . urlencode($level_filter) : '';
                    echo !empty($search) ? '&search=' . urlencode($search) : '';
                  ?>">1</a></li>
                  <?php if ($startPage > 2): ?>
                    <li><span>...</span></li>
                  <?php endif; ?>
                <?php endif; ?>
                
                <?php for ($i = $startPage; $i <= $endPage; $i++): ?>
                  <li <?php echo ($i == $page) ? 'class="active"' : ''; ?>>
                    <a href="online-egitim-gruplari/?page=<?php echo $i; ?><?php 
                      echo ($lesson_filter > 0) ? '&lesson=' . $lesson_filter : '';
                      echo !empty($level_filter) ? '&level=' . urlencode($level_filter) : '';
                      echo !empty($search) ? '&search=' . urlencode($search) : '';
                    ?>"><?php echo $i; ?></a>
                  </li>
                <?php endfor; ?>
                
                <?php if ($endPage < $totalPages): ?>
                  <?php if ($endPage < $totalPages - 1): ?>
                    <li><span>...</span></li>
                  <?php endif; ?>
                  <li><a href="online-egitim-gruplari/?page=<?php echo $totalPages; ?><?php 
                    echo ($lesson_filter > 0) ? '&lesson=' . $lesson_filter : '';
                    echo !empty($level_filter) ? '&level=' . urlencode($level_filter) : '';
                    echo !empty($search) ? '&search=' . urlencode($search) : '';
                  ?>"><?php echo $totalPages; ?></a></li>
                <?php endif; ?>
                
                <?php if ($page < $totalPages): ?>
                  <li class="next">
                    <a href="online-egitim-gruplari/?page=<?php echo ($page + 1); ?><?php 
                      echo ($lesson_filter > 0) ? '&lesson=' . $lesson_filter : '';
                      echo !empty($level_filter) ? '&level=' . urlencode($level_filter) : '';
                      echo !empty($search) ? '&search=' . urlencode($search) : '';
                    ?>">Sonraki</a>
                  </li>
                <?php endif; ?>
              </ul>
            </div>
          </div>
        </div>
      <?php endif; ?>
    </div>
  </section>
  
  <?php include 'includes/footer.php'; ?>
  <?php include 'includes/scripts.php'; ?>
</body>

</html>
