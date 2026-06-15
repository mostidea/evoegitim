<?php
ini_set('session.cookie_path', '/');
ini_set('session.cookie_domain', '');
session_start();
include("../config/connection.php");
checkUnSession();

$uid = (int) $_SESSION["user_id"];

$ticketsData = $db->prepare("
    SELECT notification.*, users.email AS receiver_user
    FROM notification
    LEFT JOIN users ON notification.receiver = users.id
    WHERE notification.receiver = 0
       OR notification.receiver = :uid
    ORDER BY id DESC
");
$ticketsData->bindParam(":uid", $uid, PDO::PARAM_INT);
$ticketsData->execute();

?>
<!DOCTYPE html>
<html lang="tr">

<head>
  <?php include '../includes/meta.php'; ?>
  <title>Eğitim Platformu | Evo Eğitim</title>
</head>

<body>
  <?php include 'includes/left-menu.php'; ?>

  <div class="dashboard-main-wrapper">
    <?php include 'includes/top-menu.php'; ?>
    <div class="dashboard-body">
      <div class="card mt-24">
        <div class="card-header border-bottom">
          <h4 class="mb-4">Bildirim</h4>
          <div class="d-flex align-items-center justify-content-between">
            <p class="text-gray-600 text-15">Bildirim ekranı</p>
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
                </tr>
              </thead>
              <tbody>
                <?php
                $count = 0;
                while ($row = $ticketsData->fetch(PDO::FETCH_ASSOC)) {
                    $count++;
                    ?>
                <tr>
                  <td>
                    <span class="h6 mb-0 fw-medium text-gray-300"><?php echo htmlspecialchars($row["title"]); ?></span>
                  </td>
                  <td>
                    <span class="h6 mb-0 fw-medium text-gray-300">
                      <?php
                      if ($row["receiver"] == 0) {
                          echo "Öğrencilere Genel Bildiri";
                      } elseif ($row["receiver"] == 1) {
                          echo "Öğretmenlere Genel Bildiri";
                      } else {
                          echo htmlspecialchars($row["receiver_user"]);
                      }
                      ?>
                    </span>
                  </td>
                  <td>
                    <span class="h6 mb-0 fw-medium text-gray-300"><?php echo htmlspecialchars($row["created_at"]); ?></span>
                  </td>
                </tr>
                <?php } ?>
                <?php if ($count === 0) { ?>
                <tr>
                  <td colspan="3" class="text-center text-gray-400 py-24">Henüz bildirim bulunmamaktadır.</td>
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
  <?php include 'includes/student-scripts.php'; ?>

</body>
</html>
