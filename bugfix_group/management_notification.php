<?php
session_start();
include("../config/connection.php");
checkUnSession();

$ticketsData = $db->prepare("
    SELECT notification.*, users.email AS receiver_user FROM notification LEFT JOIN users ON notification.receiver=users.id
    ORDER BY id DESC

");
$ticketsData->execute();

if(isset($_GET["id"])){
  $stmt = $db->prepare("DELETE FROM notification WHERE id = :id");
  $stmt->bindParam(":id", $_GET["id"]);
  $stmt->execute();
  header("location: notification.php");
}

?>
<!DOCTYPE html>
<html lang="tr">

<head>
  <?php include "../includes_panel/meta.php"; ?>
  <title>Bildirimler | Evo Eğitim</title>
</head>

<body>
  <!--==================== Preloader Start ====================-->
  <?php include 'includes/left-menu.php'; ?>
  <!-- ============================ Sidebar End  ============================ -->


  <div class="dashboard-main-wrapper">
    <?php include 'includes/top-menu.php'; ?>
    <div class="dashboard-body">
      <div class="card mt-24">
        <div class="card-header border-bottom">
          <h4 class="mb-4">Bildirim</h4>
          <div class="d-flex align-items-center justify-content-between">
            <p class="text-gray-600 text-15">Bildirim ekranı</p>
            <a href="management/notification-create.php" class=" btn btn-evo">Yeni Bildirim Oluştur</a>
          </div>
        </div>
        <div class="card-body">
          <div class="table-responsive">
            <table class="table table-hover js-datatable">
              <thead>
                <tr>

                  <th class="h6 text-gray-300">Başlık</th>
                  <th class="h6 text-gray-300">Alıcı</th>
                  <th class="h6 text-gray-300">Gönderim Tarihi</th>
                  <th class="h6 text-gray-300">İşlemler</th>
                </tr>
              </thead>
              <tbody class="sortable-table" data-url="save.php">
                <?php while ($row = $ticketsData->fetch(PDO::FETCH_ASSOC)) { ?>
                <tr>
                  <td>
                  <span class="h6 mb-0 fw-medium text-gray-300"><?php echo $row["title"]; ?></span>
                  </td>
                  <td>
                  <span class="h6 mb-0 fw-medium text-gray-300"><?php if($row["receiver"]==0){ echo "Öğrencilere Genel Bildiri"; } elseif($row["receiver"]==1){ echo "Öğretmenlere Genel Bildiri"; } elseif($row["receiver"]==2){ echo "Velilere Genel Bildiri"; } else { echo $row["receiver_user"]; } ?></span>
                  </td>

                  <td>
                  <span class="h6 mb-0 fw-medium text-gray-300"><?php echo $row["created_at"]; ?></span>
                  </td>

                  <td>
                    <a href="management/notification-create.php?id=<?php echo $row["id"]; ?>"
                      class="bg-main-50 text-main-600 py-2 px-14 rounded-pill hover-bg-main-600 hover-text-white">Düzenle</a>
                      <a href="management/notification.php?id=<?php echo $row['id']; ?>"
   class="btn btn-danger py-2 px-14 rounded-pill rounded hover-bg-main-600 hover-text-white delete-button"
   data-href="management/notification.php?id=<?php echo $row['id']; ?>">Sil</a>
                  </td>
                </tr>
                <?php } ?>

              </tbody>
            </table>
          </div>

        </div>
      </div>
    </div>
    <?php include '../includes_panel/footer.php'; ?>
  </div>

  <?php include '../includes_panel/scripts.php'; ?>



</body>

</html>
<script>
  $(document).ready(function() {
    // Delete butonuna tıklanınca
    $('.delete-button').on('click', function(e) {
      e.preventDefault(); // Linkin normal davranışını engelle

      // Confirm ile kullanıcıdan onay al
      let result = confirm("Bu kaydı silmek istediğinizden emin misiniz?");

      if (result) {
        // Kullanıcı "Evet" dediyse yönlendirme yap
        window.location.href = $(this).data('href');
      } else {
        // Kullanıcı "Hayır" dediyse bir işlem yapılmaz
        alert("Silme işlemi iptal edildi.");
      }
    });
  });
</script>
