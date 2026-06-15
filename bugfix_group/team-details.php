<?php
include 'config/connection.php';

// URL'den name parametresini al ve parse et
if (!isset($_GET['name']) || empty($_GET['name'])) {
    header('Location: /uzman-ogretmenler');
    exit;
}

$nameParam = $_GET['name'];
// Son tire ile split et ve ID'yi al
$parts = explode('-', $nameParam);
$teacherId = end($parts);

// Öğretmen bilgilerini getir
$teacherQuery = $db->prepare("
    SELECT 
        id,
        fullname, 
        profession, 
        profile_photo, 
        description,
        accessible_lessons,
        level,
        created_at,
        gender
    FROM users 
    WHERE id = ? AND role = 2
");
$teacherQuery->execute([$teacherId]);

if ($teacherQuery->rowCount() == 0) {
       header('Location: /uzman-ogretmenler');
    exit;
}

$teacher = $teacherQuery->fetch(PDO::FETCH_ASSOC);

// Lessons tablosundan dersleri getir
$lessonsQuery = $db->prepare("SELECT id, title FROM lessons");
$lessonsQuery->execute();
$lessons = [];
while ($lesson = $lessonsQuery->fetch(PDO::FETCH_ASSOC)) {
    $lessons[$lesson['id']] = $lesson['title'];
}

// Ders alanı eşleştirmesi
function getLessonArea($accessible_lessons, $lessons) {
    if (!empty($accessible_lessons)) {
        $lessonIds = explode(',', $accessible_lessons);
        $lessonNames = [];
        foreach ($lessonIds as $id) {
            $id = trim($id);
            if (isset($lessons[$id])) {
                $lessonNames[] = $lessons[$id];
            }
        }
        return !empty($lessonNames) ? implode(', ', $lessonNames) : 'Genel Eğitim';
    }
    return 'Genel Eğitim';
}

// Varsayılan profil fotoğrafı
function getProfilePhoto($profile_photo, $gender) {
    if (!empty($profile_photo)) {
        return '/' . $profile_photo;
    }

    if ($gender == 2) {
        return '/assets/img/team/team-1-2.jpg';
    } else {
        return '/assets/img/team/team-1-1.jpg';
    }
}

// Deneyim hesaplama
function getExperience($created_at) {
    $start = new DateTime($created_at);
    $now = new DateTime();
    $diff = $start->diff($now);
    
    if ($diff->y > 0) {
        return $diff->y . '+ yıl deneyim';
    } elseif ($diff->m > 0) {
        return $diff->m . '+ ay deneyim';
    } else {
        return 'Yeni katıldı';
    }
}
?>

<!DOCTYPE html>
<html lang="tr">

<head>
  <?php include 'includes/meta.php'; ?>
  <title><?php echo htmlspecialchars($teacher['fullname']); ?> | Evo Eğitim Online Eğitim Platformu</title>
</head>

<body>
 <?php include 'includes/header.php'; ?>
  <div class="breadcumb-wrapper" data-bg-src="assets/img/breadcumb/breadcumb-bg.png">
    <div class="container z-index-common">
      <div class="breadcumb-content">
        <h1 class="breadcumb-title"><?php echo htmlspecialchars($teacher['fullname']); ?></h1>
        <div class="breadcumb-menu-wrap">
          <ul class="breadcumb-menu">
            <li><a href="index.php">Ana Sayfa</a></li>
            <li><a href="teachers.php">Öğretmenler</a></li>
            <li><?php echo htmlspecialchars($teacher['fullname']); ?></li>
          </ul>
        </div>
      </div>
    </div>
  </div>
  <!--==============================
      Team Area
    ==============================-->
  <section class="space-top space-extra-bottom">
    <div class="container">
      <div class="row justify-content-center align-items-center gx-80 mb-lg-4 pb-3">
        <div class="col-lg-5 col-xl-auto order-lg-2 mb-4 mb-lg-0 pb-2 pb-lg-0">
          <div class="img-box1 style3">
            <div class="vs-circle">
              <div class="mega-hover">
                <img src="<?php echo getProfilePhoto($teacher['profile_photo'], $teacher['gender']); ?>" 
                     alt="<?php echo htmlspecialchars($teacher['fullname']); ?>" width="350px" height="250px" style="    width: 350px !important;
    height: 250px !important;" />
              </div>
            </div>
          </div>
        </div>
        <div class="col-md-6 col-lg-4 col-xl order-lg-1 mb-4 mb-md-0">
          <div class="team-details">
            <h2 class="team-name h2"><?php echo htmlspecialchars($teacher['fullname']); ?></h2>
            <p class="team-degi"><?php echo htmlspecialchars(getLessonArea($teacher['accessible_lessons'], $lessons)); ?> Öğretmeni</p>
            <span class="team-courses"><?php echo htmlspecialchars($teacher['level'] ?: 'Tüm Seviyeler'); ?></span>
            <p class="team-experi">                        <?php echo turkcetarih('j F Y l', $teacher['created_at']); ?> tarihinden beri üye.
</p>
        
          </div>
        </div>
        <div class="col-md-6 col-lg-3 col-xl order-lg-3">
          <h4 class="border-title2">Detay Bilgileri</h4>
   
          <div class="graduation-media">
            <div class="media-body">
              <h6 class="media-title">Uzmanlık Alanı</h6>
              <p class="media-text"><?php echo htmlspecialchars(getLessonArea($teacher['accessible_lessons'], $lessons)); ?></p>
            </div>
          </div>
          <div class="graduation-media">
            <div class="media-body">
              <h6 class="media-title">Eğitim Seviyesi</h6>
              <p class="media-text"><?php echo htmlspecialchars($teacher['level'] ?: 'Tüm Seviyeler'); ?></p>
            </div>
          </div>
        </div>
      </div>
      <h2 class="border-title2 mb-4"><?php echo htmlspecialchars(getLessonArea($teacher['accessible_lessons'], $lessons)); ?> Öğretmeni Hakkında</h2>
      <p>
        <?php 
        if (!empty($teacher['description'])) {
            echo nl2br(htmlspecialchars($teacher['description']));
        } else {
            echo "Deneyimli ve başarılı bir öğretmen olan " . htmlspecialchars($teacher['fullname']) . ", " . 
                 htmlspecialchars(getLessonArea($teacher['accessible_lessons'], $lessons)) . 
                 " alanında öğrencilerine kaliteli eğitim sunmaktadır. Evo Eğitim platformunda verdiği derslerle öğrencilerinin akademik başarılarını artırmayı hedeflemektedir.";
        }
        ?>
      </p>

    </div>
  </section>

  <?php include 'includes/footer.php'; ?>
  
  
  <?php include 'includes/scripts.php'; ?>
</body>

</html>