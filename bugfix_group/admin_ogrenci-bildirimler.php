<?php include '../inc/db/session.php'; ?>
<?php include '../inc/db/user-veri.php'; ?>
<?php
  // Kullanıcının giriş yapıp yapmadığını kontrol et
  if (!isset($row['role'])) {
    header('Location: ../'); // ana sayfaya yönlendir
    exit(); // işlemi durdur
  }

  // Kullanıcı 'role' değeri 1'e eşit değilse 
  if ($row['role'] != 4) {
    header('Location: ../'); // ana sayfaya yönlendir
    exit(); // işlemi durdur
  }
?>
<?php include '../inc/ust/admin-header.php'; ?>


            <!-- Content -->

            <div class="container-xxl flex-grow-1 container-p-y">
              <!-- Users List Table -->
              <div class="card">
                <div class="card-header border-bottom">
                  <h5 class="card-title mb-3">Filtrele</h5>
                  <div class="d-flex justify-content-between align-items-center row pb-2 gap-3 gap-md-0">
                    <div class="col-md-4 user_role"></div>
                    <div class="col-md-4 user_plan"></div>
                    <div class="col-md-4 user_status"></div>
                  </div>
                  <button class="btn btn-primary btn-toggle-sidebar waves-effect waves-light" data-bs-toggle="modal" data-bs-target="#duyuruekle"><i class="ti ti-plus me-1"></i><span class="align-middle">Duyuru Ekle</span></button>
                </div>
                <div class="card-datatable table-responsive">
                  <table class="datatables-users table border-top">
                    <thead>
                      <tr>
                        <th></th>
                        <th>Gönderen</th>
                        <th>Tarih Saat</th>
                        <th>Mesaj</th>
                        <th>Durum</th>
                        <th>Düzenle</th>
                      </tr>
                    </thead>
                    <tbody>
<?php
$servername = "localhost";
$username = "evoegiti_tech";
$password = "H~yZjT^w])t5";
$dbname = "evoegiti_tech";

$conn = new mysqli($servername, $username, $password, $dbname);
$conn->set_charset("utf8");

if ($conn->connect_error) {
    die("Bağlantı hatası: " . $conn->connect_error);
}

$sql = "SELECT * FROM ogrenci_bildirim ORDER BY id DESC";
$result = $conn->query($sql);

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $message = htmlspecialchars(mb_substr($row['mesaj'], 0, 75, "utf-8"));
        $status = $row['durum'] == 0 ? "<span style='color:red;'>Aktif Değil</span>" : "Aktif";
        $id = (int)$row['id'];

        echo "<tr>
            <td></td>
            <td>".htmlspecialchars($row['gonderen'])."</td>
            <td>".$row['tarih_saat']."</td>
            <td>".$message."</td>
            <td>".$status."</td>
            <td>
                <button class='btn btn-primary waves-effect waves-light' onclick='deactivateRecord($id)'>Deaktif Et</button>
                <button class='btn btn-danger waves-effect waves-light' onclick='deleteRecord($id)'>Sil</button>
            </td>
          </tr>";
    }
} else {
    echo "<tr><td colspan='6' class='text-center'>Kayıt bulunamadı.</td></tr>";
}
$conn->close();
?>
                    </tbody>
                  </table>
                </div>
                <!-- Offcanvas to add new user -->
              </div>
            </div>
            <!--/ Content -->

<script>
function deactivateRecord(id) {
    if (confirm("Bu kaydı deaktif etmek istediğinize emin misiniz?")) {
        var xhttp = new XMLHttpRequest();
        xhttp.onreadystatechange = function() {
            if (this.readyState == 4 && this.status == 200) {
                toastr.success('Kayıt deaktif edildi.');
                setTimeout(function() { location.reload(); }, 2000);
            }
        };
        xhttp.open("POST", "../inc/post/admin/deactivate_record.php", true);
        xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
        xhttp.send("id=" + id);
    }
}

function deleteRecord(id) {
    if (confirm("Bu kaydı silmek istediğinize emin misiniz?")) {
        var xhttp = new XMLHttpRequest();
        xhttp.onreadystatechange = function() {
            if (this.readyState == 4 && this.status == 200) {
                toastr.success('Kayıt silindi.');
                setTimeout(function() { location.reload(); }, 2000);
            }
        };
        xhttp.open("POST", "../inc/post/admin/delete_record.php", true);
        xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
        xhttp.send("id=" + id);
    }
}
</script>
              <div class="modal fade" id="duyuruekle" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-lg modal-simple modal-add-new-address">
                  <div class="modal-content p-3 p-md-5">
                    <div class="modal-body">
                      <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                      <div class="text-center mb-4">
                        <h3 class="address-title mb-2">Yeni Mesaj Gönder</h3>
                        <p class="text-muted address-subtitle">Mesajınız Tüm Öğrencilerin Paneline Gönderilecektir</p>
                      </div>
                      <form id="addNewAddressForm" class="row g-3" onsubmit="return false">
  <div class="col-12 col-md-6">
    <label class="form-label" for="modalAddressFirstName">Gönderen</label>
    <input
      type="text"
      id="modalAddressFirstName"
      name="gonderen"
      class="form-control"
      placeholder="Murat Hoca" />
  </div>
  <div class="col-12 col-md-6">
    <label class="form-label" for="modalAddressLastName">Mesaj</label>
    <input
      type="text"
      id="modalAddressLastName"
      name="mesaj"
      class="form-control"
      placeholder="Merhaba Sevgili Öğrencimiz ....." />
  </div>
  <div class="col-12 text-center">
    <button type="submit" class="btn btn-primary me-sm-3 me-1">Kaydet</button>
    <button
      type="reset"
      class="btn btn-label-secondary"
      data-bs-dismiss="modal"
      aria-label="Close">
      Kapat
    </button>
  </div>
</form>

                    </div>
                  </div>
                </div>
              </div>
<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script>
$(document).ready(function(){
    $('#addNewAddressForm').on('submit', function(){
        var gonderen = $('#modalAddressFirstName').val();
        var mesaj = $('#modalAddressLastName').val();

        $.ajax({
            url: '../inc/post/admin/ogrenci-duyuru-ekle.php', // change this to your file path
            type: 'post',
            data: {gonderen:gonderen, mesaj:mesaj},
            success: function(response){
                if(response == 1){
                    toastr.success('Başarı ile kaydedildi.');
                    setTimeout(function(){
                        location.reload();
                    }, 1000);
                } else{
                    toastr.error('Bir hata oluştu.');
                }
            }
        });

        return false;
    });
});
</script>

<?php include '../inc/ust/admin-footer.php'; ?>