<?php
session_start();
include("../config/connection.php");
checkUnSession();
if (isset($_GET['kurs'])) {
  $stmtA = $db->prepare('SELECT groups.*, lessons.title AS lestitle, lessons.description AS lesdescription, users.fullname, users.profile_photo, users.profession, users.description AS teacherdescription FROM groups    INNER JOIN lessons ON groups.lesson_id=lessons.id
            INNER JOIN  users ON groups.teacher_id=users.id WHERE slug = ?');
  $stmtA->execute([$_GET['kurs']]);
  $row = $stmtA->fetch(PDO::FETCH_ASSOC) ?: null;

} else {
  header("location: group-classes.php");
  exit;
}

function slugify($text) {
  $tr = ['ş'=>'s','Ş'=>'s','ç'=>'c','Ç'=>'c','ğ'=>'g','Ğ'=>'g','ü'=>'u','Ü'=>'u','ö'=>'o','Ö'=>'o','ı'=>'i','İ'=>'i'];
  $text = strtr($text, $tr);
  $text = strtolower($text);
  $text = preg_replace('/[^a-z0-9\s-]/', '', $text);
  $text = preg_replace('/[\s]+/', '-', trim($text));
  return $text;
}
?>
<!DOCTYPE html>
<html lang="tr">

<head>
  <?php include '../includes_panel/meta.php'; ?>
  <link rel="stylesheet" href="https://evoegitim.com/new-site/assets/css/fontawesome.min.css?v=5021139">
  <!-- SweetAlert2 CSS -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
  <title><?php echo $row["title"]; ?> | Evo Eğitim</title>
  <style>
    .course-details .course-img img{
      object-fit: contain;
    }
    /* .form-check-group {
      display: flex;
      flex-wrap: wrap;
      gap: 1rem;
      margin-bottom: 10px;
    }
    
    .form-check-group .form-check {
      flex: 1 0 auto;
      min-width: 200px;
      max-width: 300px;
      margin: 0;
    }
    
    @media (max-width: 576px) {
      .form-check-group .form-check {
        min-width: 100%;
      }
    } */
  </style>
</head>

<body>
  <!--==================== Preloader Start ====================-->
  <?php include 'includes/left-menu.php'; ?>
  <!-- ============================ Sidebar End  ============================ -->


  <div class="dashboard-main-wrapper">
    <?php include 'includes/top-menu.php'; ?>
    <div class="dashboard-body">

      <div class="card mt-24">
        <div class="card-body">
          <section class="course-details space-top space-extra-bottom">
            <div class="container">
             
              <div class="row flex-row-reverse">
                <div class="col-lg-4">
                  <div class="course-meta-box">
                    <table>
                      <tbody>
                      <tr>
                          <th><i class="far fa-hourglass"></i>Başlangıç:</th>
                          <td><?php echo turkcetarih('j F Y H:i', $row["start_date"]); ?></td>

                        </tr>
                        <tr>
                          <th><i class="far fa-clock"></i>Haftalık Ders:</th>
                          <td><?php echo $row["total_lesson_time"]; ?> Hafta, <?php echo $row["weekly_lesson_count"]; ?> Gün</td>
                        </tr>
                        <tr>
                          <th><i class="far fa-ticket"></i>Kredi Miktarı:</th>
                          <td> <?php echo $row["credit"]*$row["total_lesson_time"]*$row["weekly_lesson_count"]; ?></td>
                        </tr>
                        <tr>
                          <th><i class="far fa-user-alt"></i>Kontenjan Sayısı:</th>
                          <td> <?php
                            $teacher = $db->prepare('SELECT id FROM groups_quota WHERE group_id = ?');
                            $teacher->execute([$row['id']]);
                            $teac = $teacher->rowCount() ?: 0;
                          echo $teac; ?> /<?php echo $row["quota"]; ?> Öğrenci</td>
                        </tr>
                        <tr>
                          <th><i class="far fa-suitcase"></i>Kurs Tipi:</th>
                          <td>%100 online</td>
                        </tr>
                      </tbody>
                    </table>
                    <div class="mt-3 pt-1">
                      <button class="vs-btn" style="width:100%;background:#6366f1;border-color:#6366f1;"
                              data-bs-toggle="modal" data-bs-target="#virtualBgModal">
                        <i class="far fa-image me-2"></i>Arka Plan Seç
                      </button>
                    </div>


                  </div>
                </div>
                <div class="col-lg-8">
                <div class="mega-hover course-img"><img src="/<?php echo ltrim($row["image"], '/'); ?>" alt="Ders Fotoğrafı" /></div>
                  <div class="course-category">
                    <a href="course.php"><?php echo $row["lestitle"]; ?> - <?php echo $row["lesdescription"]; ?></a>
                  </div>
                  <h2 class="course-title"><?php echo $row["title"]; ?></h2>
                  <div class="course-review">
                    <i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i>(
                    5.0 )
                  </div>
                  <h5 class="border-title2">Konu</h5>
                  <p>
                  <?php echo $row["subject"]; ?>
                  </p>
                  <h5 class="border-title2">Genel Bakış</h5>

                <?php echo $row["description"]; ?>

                  <div class="mt-4 pt-lg-2">
                    <h5 class="border-title2">Kurs Kuralları</h5>
                    <?php echo $row["rule"]; ?>
                  </div>

                  <h5 class="border-title2">Eğitmen Hakkında</h5>
                  <div class="row gx-40">
                    <div class="col-sm-6 col-lg-4">
                      <div class="team-style1">
                        <div class="team-img">
                          <img class="w-100" src="/<?php echo ltrim($row["profile_photo"], '/'); ?>" alt="<?php echo htmlspecialchars($row['fullname']); ?>" />
                        </div>
                        <div class="team-content">
                          <h4 class="team-name"><a href="https://evoegitim.com/ogretmen/<?php echo slugify($row['fullname']); ?>-<?php echo $row['teacher_id']; ?>/"><?php echo htmlspecialchars($row["fullname"]); ?></a></h4>
                          <p class="team-degi"><?php echo htmlspecialchars($row["profession"]); ?></p>
                          <p class="team-text"><?php echo nl2br(htmlspecialchars(strip_tags($row["teacherdescription"]))); ?></p>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
          </section>
        </div>
      </div>


    </div>
    <?php include '../includes_panel/footer.php'; ?>
  </div>

  <script src="assets/js/virtual-bg.js"></script>
  <?php include '../includes_panel/scripts.php'; ?>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.js"></script>
  
  <script>
    $(document).ready(function() {
      // Kursa Katıl butonuna tıklandığında modal'ı açma
      $("#enrollButton").on("click", function(e) {
        e.preventDefault();
        $("#courseRegistrationModal").modal("show");
      });

      // Form gönderme butonu
      $("#submitForm").on("click", function() {
        // Form validasyonu
        if (!document.getElementById('courseRegistrationForm').checkValidity()) {
          // Validasyon hatası varsa formu manuel olarak gönder (tarayıcı validasyon mesajlarını göstermek için)
          document.getElementById('courseRegistrationForm').reportValidity();
          return;
        }

        // Form verilerini toplama
        var formData = new FormData(document.getElementById('courseRegistrationForm'));
        
        // Yükleniyor göster
        Swal.fire({
          title: 'İşleniyor...',
          text: 'Talebiniz işleniyor, lütfen bekleyiniz.',
          allowOutsideClick: false,
          didOpen: () => {
            Swal.showLoading();
          }
        });
        
        // AJAX POST isteği
        $.ajax({
          url: document.getElementById('courseRegistrationForm').getAttribute('action'),
          type: 'POST',
          data: formData,
          processData: false,
          contentType: false,
          dataType:"json",
          success: function(response) {
            try {
              // Modal'ı kapat
              $("#courseRegistrationModal").modal("hide");
              
              // Duruma göre SweetAlert2 mesajı gösterme
              if (response.status == 1) {
                Swal.fire({
                  icon: 'success',
                  title: 'Başarılı!',
                  text: response.message || 'Form başarıyla gönderildi.',
                  confirmButtonText: 'Tamam'
                }).then((result) => {
                  // Formu temizle
                  document.getElementById('courseRegistrationForm').reset();
                });
              } else {
                Swal.fire({
                  icon: 'error',
                  title: 'Hata!',
                  text: response.message || 'İşlem sırasında bir hata oluştu.',
                  confirmButtonText: 'Tamam'
                });
              }
            } catch (e) {
              console.error(e);
              Swal.fire({
                icon: 'error',
                title: 'İşlem Hatası',
                text: 'Sunucu yanıtı işlenirken bir hata oluştu.',
                confirmButtonText: 'Tamam'
              });
            }
          },
          error: function(xhr, status, error) {
            console.error(xhr, status, error);
            Swal.fire({
              icon: 'error',
              title: 'Bağlantı Hatası',
              text: 'Sunucuyla iletişim kurulurken bir hata oluştu.',
              confirmButtonText: 'Tamam'
            });
          }
        });
      });
    });
  </script>

</body>

</html>