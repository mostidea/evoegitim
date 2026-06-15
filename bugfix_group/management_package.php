<?php
session_start();
include("../config/connection.php");
checkUnSession();

// Paketleri getir
$packagesData = $db->prepare("SELECT * FROM package ORDER BY id DESC");
$packagesData->execute();

// Silme işlemi
if (isset($_GET["id"])) {
    $stmt = $db->prepare("DELETE FROM package WHERE id = :id");
    $stmt->bindParam(":id", $_GET["id"]);
    $stmt->execute();
    header("location: package.php");
    exit();
}

?>
<!DOCTYPE html>
<html lang="tr">

<head>
  <?php include '../includes/meta.php'; ?>
  <title>Paket Yönetimi | Evo Eğitim</title>
</head>

<body>
  <!--==================== Sidebar ====================-->
  <?php include 'includes/left-menu.php'; ?>
  <!-- ============================ Sidebar End ============================ -->

  <div class="dashboard-main-wrapper">
    <?php include 'includes/top-menu.php'; ?>
    <div class="dashboard-body">
      <div class="card mt-24">
        <div class="card-header border-bottom">
          <h4 class="mb-4">Paketler</h4>
          <div class="d-flex align-items-center justify-content-between">
            <p class="text-gray-600 text-15">Paket yönetim ekranı</p>
            <a href="management/package-create.php" class="btn btn-evo">Yeni Paket Oluştur</a>
          </div>
        </div>
        <div class="card-body">
          <div class="table-responsive">
            <table class="table table-hover js-datatable">
              <thead>
                <tr>
                  <th class="h6 text-gray-300">Başlık</th>
                  <th class="h6 text-gray-300">Açıklama</th>
                  <th class="h6 text-gray-300">Kategori</th>
                  <th class="h6 text-gray-300">Fiyat</th>
                  <th class="h6 text-gray-300">Kredi</th>
                  <th class="h6 text-gray-300">Süre (Gün)</th>
                  <th class="h6 text-gray-300">İndirim</th>
                  <th class="h6 text-gray-300">İşlemler</th>
                </tr>
              </thead>
              <tbody>
                <?php while ($row = $packagesData->fetch(PDO::FETCH_ASSOC)) { ?>
                <tr>
                  <td>
                    <span class="h6 mb-0 fw-medium text-gray-300"><?php echo $row["title"]; ?></span>
                  </td>
                  <td>
                    <span class="h6 mb-0 fw-medium text-gray-300"><?php echo $row["description"]; ?></span>
                  </td>
                  <td>
                    <span class="h6 mb-0 fw-medium text-gray-300"><?php echo $row["type"]==1 ? "Grup Ders Paketi" : "Solo Ders Paketi"; ?></span>
                  </td>
                  <td>
                    <span class="h6 mb-0 fw-medium text-gray-300"><?php echo number_format($row["price"], 0, ',', '.'); ?>₺<?php echo $row["discount"] > 0 ? ' → ' . number_format($row["price"] - $row["discount"], 0, ',', '.') . '₺ (İndirimli)' : ''; ?></span>
                  </td>
                  <td>
                    <span class="h6 mb-0 fw-medium text-gray-300"><?php echo $row["credit"]; ?></span>
                  </td>
                  <td>
                    <span class="h6 mb-0 fw-medium text-gray-300"><?php echo $row["expired_date"]; ?></span>
                  </td>
                  <td>
                    <span class="h6 mb-0 fw-medium text-gray-300"><?php echo $row["discount"] > 0 ? number_format($row["discount"], 0, ',', '.') . '₺' : '-'; ?></span>
                  </td>
                  <td>
                    <a href="management/package-create.php?id=<?php echo $row["id"]; ?>"
                      class="bg-main-50 text-main-600 py-2 px-14 rounded-pill hover-bg-main-600 hover-text-white">Düzenle</a>
                    <a
                      class="btn btn-danger py-2 px-14 rounded-pill rounded hover-bg-main-600 hover-text-white delete-button"
                      data-href="management/package.php?id=<?php echo $row['id']; ?>">Sil</a>
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