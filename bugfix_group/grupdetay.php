<?php
session_start();
include 'config/connection.php';

// URL'den slug parametresini al
if (!isset($_GET['slug']) || empty($_GET['slug'])) {
    header('Location: online-egitim-gruplari');
    exit;
}

$slug = $_GET['slug'];

// Grup bilgilerini getir
$groupQuery = $db->prepare("
    SELECT 
        g.*,
        u.id as teacher_id,
        u.fullname as teacher_name,
        u.profile_photo as teacher_photo,
        u.gender as teacher_gender,
        u.description as teacher_description,
        l.title as lesson_name
    FROM `groups` g
    LEFT JOIN users u ON g.teacher_id = u.id
    LEFT JOIN lessons l ON g.lesson_id = l.id
    WHERE g.slug = ? AND g.publish = 1
");
$groupQuery->execute([$slug]);

if ($groupQuery->rowCount() == 0) {
    header('Location: online-egitim-gruplari');
    exit;
}

$group = $groupQuery->fetch(PDO::FETCH_ASSOC);

// Öğretmen profil fotoğrafı
function getTeacherPhoto($profile_photo, $gender) {
    if (!empty($profile_photo) && file_exists($profile_photo)) {
        return $profile_photo;
    }
    
    if ($gender == 2) {
        return 'assets/img/team/team-1-2.jpg';
    } else {
        return 'assets/img/team/team-1-1.jpg';
    }
}

// Grup görsel
function getGroupImage($image) {
    if (!empty($image) && file_exists($image)) {
        return $image;
    }
    return 'assets/img/course/course-details-2-1.jpg';
}

// Level parsing
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

// Başlangıç tarihi formatla
function formatStartDate($start_date) {
    if (empty($start_date) || strtotime($start_date) <= 0) return 'Esnek Başlangıç';
    return turkcetarih('j F Y', $start_date);
}

// Slug oluşturma
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
?>

<!DOCTYPE html>
<html lang="tr">

<head>
  <?php include 'includes/meta.php'; ?>
  <title><?php echo htmlspecialchars($group['title']); ?> | Evo Eğitim Online Eğitim Platformu</title>
  <style>
    .editor-content ul { list-style-type: disc !important; padding-left: 2rem !important; margin-bottom: 1rem !important; }
    .editor-content ol { list-style-type: decimal !important; padding-left: 2rem !important; margin-bottom: 1rem !important; }
    .editor-content table { width: 100% !important; margin-bottom: 1rem; border-collapse: collapse; }
    .editor-content table th, .editor-content table td { padding: 8px 12px; border: 1px solid #dee2e6; text-align: left; }
  </style>
</head>

<body>
 <?php include 'includes/header.php'; ?>

  <!--==============================
      Course Area
    ==============================-->
  <section class="course-details space-top space-extra-bottom">
    <div class="container">
      <div class="mega-hover course-img">
        <img src="/<?php echo htmlspecialchars(getGroupImage($group['image'])); ?>" alt="<?php echo htmlspecialchars($group['title']); ?>" style="width:350px; height:250px;" />
      </div>
      <div class="row flex-row-reverse">
        <div class="col-lg-4">
          <div class="course-meta-box">
            <table>
              <tbody>
                <tr>
                  <th><i class="far fa-users"></i>Grup Kapasitesi:</th>
                  <td><?php echo $group['quota']; ?> Kişi</td>
                </tr>
                <tr>
                  <th><i class="far fa-clock"></i>Haftalık Ders:</th>
                  <td><?php echo $group['weekly_lesson_count']; ?> Ders</td>
                </tr>
                <tr>
                  <th><i class="far fa-hourglass"></i>Haftalık Ders Süresi:</th>
                  <td><?php echo $group['total_lesson_time']; ?> Saat</td>
                </tr>
                <tr>
                  <th><i class="far fa-calendar"></i>Başlangıç:</th>
                  <td><?php echo formatStartDate($group['start_date']); ?></td>
                </tr>
                <tr>
                  <th><i class="far fa-star"></i>Kredi:</th>
                  <td><?php echo $group["credit"]*$group["total_lesson_time"]*$group["weekly_lesson_count"]; ?> Kredi</td>
                </tr>
                <tr>
                  <th><i class="far fa-graduation-cap"></i>Seviye:</th>
                  <td><?php echo parseLevel($group['level']); ?></td>
                </tr>
              </tbody>
            </table>
                    <?php
// Mevcut öğrenci sayısını kontrol et
$studentCountQuery = $db->prepare("
    SELECT COUNT(*) as student_count 
    FROM groups_quota
    WHERE group_id = ?
");
$studentCountQuery->execute([$group['id']]);
$currentStudentCount = $studentCountQuery->fetch(PDO::FETCH_ASSOC)['student_count'];

// Kayıt durumunu kontrol et
$isQuotaFull = $currentStudentCount >= $group['quota'];
$isDatePassed = false;

if (!empty($group['start_date'])) {
    $startDate = new DateTime($group['start_date']);
    $today = new DateTime();
    $isDatePassed = $startDate < $today;
}

$isRegistrationOpen = !$isQuotaFull && !$isDatePassed;

if (!isset($_SESSION['user_id'])) {
    $hrefUrl='student/register.php';

} else {
      $hrefUrl='../student/group-class-detail.php?kurs='.$group["slug"];

}
?>
           <a href="<?php echo $hrefUrl; ?>" 
   class="vs-btn <?php echo !$isRegistrationOpen ? 'disabled' : ''; ?>" 
   <?php echo !$isRegistrationOpen ? 'onclick="return false;" style="opacity: 0.5; cursor: not-allowed;"' : ''; ?>>
    <?php echo $isRegistrationOpen ? 'Bugün Katıl' : 'Kayıt Kapalı'; ?>
</a>
          </div>
        </div>
        <div class="col-lg-8">
          <div class="course-category">
            <a href="online-egitim-gruplari"><?php echo htmlspecialchars($group['lesson_name'] ?: 'Grup Dersi'); ?></a>
          </div>
          <h2 class="course-title"><?php echo htmlspecialchars($group['title']); ?></h2>
          <div class="course-review">
            <i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i>
            (5.0)
          </div>
          
          <?php if (!empty($group['subject'])): ?>
            <h5 class="border-title2">Konu Başlığı</h5>
            <p><?php echo nl2br(htmlspecialchars($group['subject'])); ?></p>
          <?php endif; ?>
          
          <h5 class="border-title2">Genel Bakış</h5>
          <div class="editor-content">
          <?php if (!empty($group['description'])): ?>
            <?php echo $group['description']; ?>
          <?php else: ?>
            <p>
              Bu grup dersi, <?php echo htmlspecialchars($group['lesson_name'] ?: 'belirlenen ders'); ?> alanında 
              <?php echo $group['quota']; ?> kişilik küçük gruplarla verimli öğrenme deneyimi sunar. 
              Haftalık <?php echo $group['weekly_lesson_count']; ?> ders ile düzenli çalışma programı ile 
              öğrencilerimizin akademik başarılarını artırmayı hedefliyoruz.
            </p>
          <?php endif; ?>
          </div>

          <h5>Grup Dersinin Avantajları</h5>
          <div class="list-style1 vs-list">
            <ul>
              <li>Küçük grup eğitimi ile kişiselleştirilmiş öğrenme</li>
              <li>Akran öğrenmesi ve grup dinamiği</li>
              <li>Interaktif ders ortamı</li>
              <li>Düzenli takip ve değerlendirme</li>
              <li>Uygun fiyatlı kaliteli eğitim</li>
            </ul>
          </div>
          
          <?php if (!empty($group['rule'])): ?>
            <h5 class="border-title2">Grup Kuralları</h5>
            <div class="editor-content"><?php echo $group['rule']; ?></div>
          <?php endif; ?>
          
          <h5 class="border-title2">Ne Zaman Başlamak İstersiniz?</h5>
          <p>
            <?php if (!empty($group['start_date'])): ?>
              Bu grup dersi <?php echo turkcetarih('j F Y l', $group['start_date']); ?> tarihinde başlayacaktır. 
              Hemen kayıt olun ve yerinizi ayırtın.
            <?php else: ?>
              Esnek başlangıç tarihi ile istediğiniz zaman gruba katılabilirsiniz. 
              Minimum katılımcı sayısına ulaştığımızda derslere başlıyoruz.
            <?php endif; ?>
          </p>

<!-- HTML kısmında kullanım -->
<a href="<?php echo $hrefUrl; ?>" 
   class="vs-btn <?php echo !$isRegistrationOpen ? 'disabled' : ''; ?>" 
   <?php echo !$isRegistrationOpen ? 'onclick="return false;" style="opacity: 0.5; cursor: not-allowed;"' : ''; ?>>
    <?php echo $isRegistrationOpen ? 'Bugün Katıl' : 'Kayıt Kapalı'; ?>
</a>

<span class="available-badge <?php echo !$isRegistrationOpen ? 'closed' : ''; ?>">
    <?php 
    if ($isQuotaFull) {
        echo 'Kontenjan Dolu';
    } elseif ($isDatePassed) {
        echo 'Kayıt Süresi Doldu';
    } else {
        echo 'Kayıt Açık';
    }
    ?>
</span>

<!-- CSS eklemeleri -->
<style>
.available-badge {
    color: #28a745;
    display: inline-block;
    vertical-align: middle;
    font-size: 16px;
    margin-left: 20px;
    padding: 5px 15px 5px 35px;
    position: relative;
    background-color: rgba(40, 167, 69, 0.1);
    border-radius: 20px;
    font-weight: 500;
}

.available-badge::before {
    content: '';
    position: absolute;
    left: 15px;
    top: 50%;
    transform: translateY(-50%);
    width: 10px;
    height: 10px;
    background-color: #28a745;
    border-radius: 50%;
    animation: pulse 2s infinite;
}

.available-badge.closed {
    color: #dc3545;
    background-color: rgba(220, 53, 69, 0.1);
}

.available-badge.closed::before {
    background-color: #dc3545;
    animation: none;
}

@keyframes pulse {
    0% {
        box-shadow: 0 0 0 0 rgba(40, 167, 69, 0.7);
    }
    70% {
        box-shadow: 0 0 0 10px rgba(40, 167, 69, 0);
    }
    100% {
        box-shadow: 0 0 0 0 rgba(40, 167, 69, 0);
    }
}

.vs-btn.disabled {
    background-color: #6c757d !important;
    border-color: #6c757d !important;
    cursor: not-allowed !important;
}
</style>
          
     
          <br><br><br><br>
          <h5 class="border-title2">Kimlerle Öğreneceksiniz?</h5>
          <!--==============================
      Team Area - ONLY SHOW THE GROUP'S TEACHER
  ==============================-->
          <div class="row">
            <!-- Ana öğretmen -->
            <div class="col-sm-6 col-lg-4">
              <div class="team-style1">
                <div class="team-img">
                  <img class="w-100" src="/<?php echo htmlspecialchars(getTeacherPhoto($group['teacher_photo'], $group['teacher_gender'])); ?>"
                       alt="<?php echo htmlspecialchars($group['teacher_name']); ?>" />
                </div>
                <div class="team-content">
                  <div class="team-review">
                    <i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i>
                  </div>
                  <h4 class="team-name">
                    <a href="/ogretmen/<?php echo createSlug($group['teacher_name']) . '-' . $group['teacher_id']; ?>/">
                      <?php echo htmlspecialchars($group['teacher_name']); ?>
                    </a>
                  </h4>
                  <p class="team-degi"><?php echo htmlspecialchars($group['lesson_name'] ?: 'Ders'); ?> Öğretmeni</p>
                  <p class="team-text">
                    <?php 
                    if (!empty($group['teacher_description'])) {
                        echo htmlspecialchars(strip_tags($group['teacher_description']));
                    } else {
                        echo 'Deneyimli ve başarılı bir öğretmen. Grup derslerinde öğrenci odaklı eğitim yaklaşımı benimser.';
                    }
                    ?>
                  </p>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>
  
  <?php include 'includes/footer.php'; ?>
  
  <?php include 'includes/scripts.php'; ?>
</body>

</html>
