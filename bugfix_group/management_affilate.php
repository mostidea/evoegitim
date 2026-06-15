<?php
session_start();
include("../config/connection.php");
checkUnSession();

// Kampanyaları getir
$affilateData = $db->prepare("SELECT * FROM affilate ORDER BY id DESC");
$affilateData->execute();

// Silme işlemi
if (isset($_GET['id'])) {
    $stmt = $db->prepare("DELETE FROM affilate WHERE id = :id");
    $stmt->bindParam(':id', $_GET['id']);
    $stmt->execute();
    header("Location: affilate.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="tr">

<head>
  <?php include '../includes/meta.php'; ?>
  <title>Kampanyalar | Evo Eğitim</title>
</head>

<body>
  <?php include 'includes/left-menu.php'; ?>
  <div class="dashboard-main-wrapper">
    <?php include 'includes/top-menu.php'; ?>
    <div class="dashboard-body">
      <div class="card mt-24">
        <div class="card-header border-bottom">
          <h4 class="mb-4">Kampanyalar</h4>
          <div class="d-flex align-items-center justify-content-between">
            <p class="text-gray-600 text-15">Kampanya yönetim ekranı</p>
            <a href="management/affilate-create.php" class="btn btn-evo">Yeni Kampanya Oluştur</a>
          </div>
        </div>
        <div class="card-body">
          <div class="table-responsive">
            <table class="table table-hover js-datatable">
              <thead>
                <tr>
                  <th class="h6 text-gray-300">ID</th>
                  <th class="h6 text-gray-300">Kampanya Adı</th>
                  <th class="h6 text-gray-300">Fiyat</th>
                  <th class="h6 text-gray-300">Durum</th>
                  <th class="h6 text-gray-300">İşlemler</th>
                </tr>
              </thead>
              <tbody>
                <?php while ($row = $affilateData->fetch(PDO::FETCH_ASSOC)) { ?>
                <tr>
                  <td><?php echo $row['id']; ?></td>
                  <td><?php echo htmlspecialchars($row['affilate']); ?></td>
                  <td><?php echo number_format($row['price'], 0, ',', '.'); ?>₺</td>
                  <td><?php echo $row['status'] == 1 ? 'Aktif' : 'Pasif'; ?></td>
                  <td>
                    <a href="management/affilate-create.php?id=<?php echo $row['id']; ?>"
                      class="btn btn-success">Düzenle</a>
                    <a href="management/affilate.php?id=<?php echo $row['id']; ?>"
                      class="btn btn-danger delete-button" data-href="management/affilate.php?id=<?php echo $row['id']; ?>">Sil</a>
                  </td>
                </tr>
                <?php } ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
    <?php include '../includes/footer.php'; ?>
  </div>

  <?php include '../includes/scripts.php'; ?>
  <script>
    $(document).ready(function() {
      // Silme işlemi onayı
      $('.delete-button').on('click', function(e) {
        e.preventDefault();
        let result = confirm("Bu kampanyayı silmek istediğinizden emin misiniz?");
        if (result) {
          window.location.href = $(this).data('href');
        }
      });
    });
  </script>
</body>

</html>
