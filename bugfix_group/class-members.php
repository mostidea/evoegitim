<?php
error_reporting(E_ALL);
ini_set("display_errors", 1);
session_start();
include("../config/connection.php");
checkUnSession();

$ticketsData = $db->prepare("
      SELECT
        apply_group.*,
        users.fullname,
        users.email,
        users.phone,
        users.id   AS user_id,
        groups.id  AS group_id,
        groups.title,
        groups.credit                -- â† eklendi
    FROM apply_group
    JOIN users  ON apply_group.user_id  = users.id
    JOIN `groups` ON apply_group.group_id = groups.id
    WHERE apply_group.status = 1 AND apply_group.group_id = ".$_GET["id"]."
    ORDER BY apply_group.created_at DESC

");
$ticketsData->execute();
$stmtA = $db->prepare('SELECT * FROM `groups` WHERE id = ?');
$stmtA->execute([$_GET['id']]);
$rows = $stmtA->fetch(PDO::FETCH_ASSOC) ?: null;

/* â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€  GRUPTAN ÇIKAR  â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€ */
if (isset($_GET['unverify'])) {

  $getVeliCount = $db->prepare("SELECT * FROM invite_parent WHERE user_email='" . @$_GET["email"] . "' ");
  $getVeliCount->execute();
  $count = $getVeliCount->fetch(PDO::FETCH_ASSOC);

  if ($count) {

      //VeliData
      $veliPhone = $db->prepare("SELECT * FROM parent WHERE email='" . $count["email"] . "' ");
      $veliPhone->execute();
      $vp = $veliPhone->fetch(PDO::FETCH_ASSOC);
      if ($vp) {

      $phone=$vp["phone"];

      } else {
        $phone="";
      }
  } else {
    $phone="";
  }

  /* 0) Gerekli parametreler */
  $applyId   = (int)$_GET['unverify'];   // apply_group.id
  $groupId   = (int)$_GET['id'];         // groups.id
  $studentId = (int)$_GET['user_id'];    // users.id

  /* 1) apply_group â†’ reddedildi */
  $db->prepare(
      'UPDATE apply_group SET status = 2 WHERE id = ?'
  )->execute([$applyId]);

  /* 2) Gelecekteki derslerin toplam kredisi */
  $q  = $db->prepare(
      'SELECT SUM(credit) AS cr
         FROM appointment
        WHERE student_id = :sid
          AND group_id   = :gid
          AND start_date > NOW()'
  );
  $q->execute(['sid' => $studentId, 'gid' => $groupId]);
  $refund = (int)$q->fetchColumn();      // iade edilecek kredi

  /* 3) Gelecekteki dersleri sil */
  $db->prepare(
      'DELETE FROM appointment
        WHERE student_id = :sid
          AND group_id   = :gid
          AND start_date > NOW()'
  )->execute(['sid' => $studentId, 'gid' => $groupId]);

  /* 4) Kredi iadesi */
  if ($refund > 0) {       creditRefund($db, (int)$studentId, (int)$refund, 1);   }

  /* 5) SMS bildirimleri */
  sendSms(
    "Değerli Öğrencimiz, {$_GET['title']} grubundan çıkarıldınız. " .
    "İlgili krediler hesabınıza iade edildi.",
    [$_GET['phone']]
  );
  if (!empty($phone)) {
      sendSms(
        "Değerli Velimiz, {$_GET['fullname']} adlı öğrenciniz " .
        "{$_GET['title']} grubundan çıkarıldı. Kredisi iade edilmiştir.",
        [$phone]
      );
  }

  /* 6) Liste sayfasına geri dön */
  header('Location: ' . $_SERVER['HTTP_REFERER']);
  exit;
}


?>
<!DOCTYPE html>
<html lang="tr">

<head>
  <?php include "../includes_panel/meta.php"; ?>
  <meta charset="utf-8">
  <title><?php echo $rows["title"]; ?> Grubu Üyeleri | Evo Eğitim</title>
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
          <h4 class="mb-4"><?php echo $rows["title"]; ?> Grubu Üyeleri</h4>
          <div class="d-flex align-items-center justify-content-between">
            <p class="text-gray-600 text-15">Grup katılımcılarını ekte görüntüleyebilirsiniz.</p>
          </div>
        </div>
        <div class="card-body">
          <div class="table-responsive">
            <table class="table table-hover js-datatable">
              <thead>
                <tr>

                  <th class="h6 text-gray-300">Öğrenci Bilgileri</th>
                  <th class="h6 text-gray-300">Talep Detayları</th>
                </tr>
              </thead>
              <tbody class="sortable-table" data-url="save.php">
              <?php while ($row = $ticketsData->fetch(PDO::FETCH_ASSOC)) {

?>
<tr>
<td>
  <input type="hidden" class="username" value="<?= $row['fullname'] ?>">
  <span class="h6 mb-0 fw-medium text-gray-300">
    <?= $row['fullname'] ?>
  </span>
</td>

<!-- Talep Detayları -->
<td>
<a class="btn btn-evo" data-detail="<?= base64_encode($row['detail']) ?>">Görüntüle</a>
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

  <?php include '../includes_panel/scripts.php'; ?><?php include 'includes/teacher-scripts.php'; ?>



</body>

</html>
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>



$(document).on('click', '.swal-confirm', function (e) {
    e.preventDefault();                              // linki durdur
    const url   = this.href;                         // hedef URL
    const isOk  = $(this).hasClass('btn-success');   // buton rengine göre metin
    const text  = isOk ? 'talebi onaylamak' : 'talebi reddetmek';

    Swal.fire({
        title: 'Emin misiniz?',
        text: `Bu ${text} istediğinize emin misiniz?`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Evet',
        cancelButtonText: 'Vazgeç'
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = url;              // yönlendir
        }
    });
});

document.addEventListener('click', async (e) => {
  const btn = e.target.closest('.btn-evo[data-detail]');
  if (!btn) return;                     // Görüntüle butonu değilse çık

  /* â‰¡â‰¡ Kullanıcı adı â‰¡â‰¡ */
  const unameInput = btn.closest('tr').querySelector('.username');
  const uname = unameInput ? unameInput.value : '';

  /* Base64 â†’ UTF-8 JSON */
  const jsonStr = decodeURIComponent(escape(atob(btn.dataset.detail)));
  const data    = JSON.parse(jsonStr);            // [{soru, cevap}, â€¦]

  if (!Array.isArray(data) || !data.length) return;

  /* İçeriği oluştur */
  const html = data.map(item => `
    <div class="row py-2 border-bottom">
      <div class="col-12 col-md-5 fw-semibold mb-1 mb-md-0">
        ${item.soru}
      </div>
      <div class="col-12 col-md-7">
        ${item.cevap}
      </div>
    </div>
  `).join('');

  /* SweetAlert2 göster */
  await Swal.fire({
    title: `${uname} â€“ Başvuru Detayı`,
    html,
    width: '40rem',
    customClass: { popup: 'text-start' }
  });
});

</script>

