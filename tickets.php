<?php
session_start();
include("../config/connection.php");
checkUnSession();

$ticketsData = $db->prepare("
    SELECT * FROM tickets
    WHERE user_id = :user_id
    ORDER BY created_at DESC 

");
$ticketsData->bindParam(":user_id", $_SESSION["user_id"]);
$ticketsData->execute();

?>
<!DOCTYPE html>
<html lang="tr">

<head>
  <?php include "../includes_panel/meta.php"; ?>
  <title>Eğitim Platformu  | Evo Eğitim</title>
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
          <h4 class="mb-4">Destek Taleplerim</h4>
          <div class="d-flex align-items-center justify-content-between ">
            <p class="text-gray-600 text-15">Destek taleplerim ekranı</p>
            <a href="student/ticket-create.php" class=" btn btn-evo">Yeni Bilet Oluştur</a>
          </div>
        </div>
        <div class="card-body">
          <div class="table-responsive">
            <table class="table table-hover js-datatable">
              <thead>
                <tr>

                  <th class="h6 text-gray-300 text-center">Destek Numarası</th>
                  <th class="h6 text-gray-300 text-center">Başlık</th>
                  <th class="h6 text-gray-300 text-center">Talep Durumu</th>
                  <th class="h6 text-gray-300 text-center">Talep Tarihi</th>
                  <th class="h6 text-gray-300 text-center">İşlemler</th>
                </tr>
              </thead>
              <tbody class="sortable-table" data-url="save.php">
                <?php while ($row = $ticketsData->fetch(PDO::FETCH_ASSOC)) { ?>
                <tr>
                  <td style="color:Black !important;""  class="text-center">
                  #EVO<?php echo $row["id"]; ?>
                </td>
              
                <td  class="text-center">
                  <span class=" h6 mb-0 fw-medium text-gray-300"><?php echo $row["title"]; ?></span>
                  </td>
                  <?php if ($row["status"] == 0) { ?>
                  <td class="text-center">
                    <span class="text-primary-600 bg-primary-100 py-2 px-10 rounded-pill">İşlem Bekleniyor</span>
                  </td>
                  <?php  } elseif ($row["status"] == 1) { ?>
                  <td class="text-center">
                    <span class="text-danger-600 bg-danger-100 py-2 px-10 rounded-pill">Evo Eğitim Tarafından Yanıtlandı</span>
                  </td>
                  <?php } else {

                  ?>
                      <td class="text-center">
                    <span class="text-success-600 bg-success-100 py-2 px-10 rounded-pill">Tamamlandı</span>
                  </td>
                  <?php } ?>
                  <td  class="text-center">
                    <span
                      class="h6 mb-0 fw-medium text-gray-300"><?php echo turkcetarih('j F Y , l H:i', $row["created_at"]); ?></span>
                  </td>

                  <td  class="text-center">
                    <a href="student/tickets-detail.php?detail=<?php echo $row["id"]; ?>"
                      class="bg-main-50 text-main-600 py-2 px-14 rounded-pill hover-bg-main-600 hover-text-white">Talebimi
                      Görüntüle</a>
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

  <?php include '../includes_panel/scripts.php'; ?><?php include 'includes/student-scripts.php'; ?>



</body>

</html>