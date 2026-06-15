<?php
session_start();
include("../config/connection.php");
checkUnSession();

// AJAX soft-delete isteği
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'soft_delete') {
    header('Content-Type: application/json');
    $ids = $_POST['ids'] ?? [];
    $ids = array_filter(array_map('intval', (array)$ids));
    if (empty($ids)) {
        echo json_encode(['status' => 0, 'message' => 'Geçersiz istek.']);
        exit;
    }
    $placeholders = implode(',', array_fill(0, count($ids), '?'));
    $stmt = $db->prepare("UPDATE users SET status = 2 WHERE id IN ($placeholders) AND role = 0");
    $stmt->execute($ids);
    echo json_encode(['status' => 1, 'message' => count($ids) . ' öğrenci pasife alındı.']);
    exit;
}

$showPassive = isset($_GET['pasif']) && $_GET['pasif'] == 1;
$statusFilter = $showPassive ? 'u.status = 2' : '(u.status IS NULL OR u.status != 2)';

$page    = max(1, (int)($_GET['page'] ?? 1));
$perPage = 25;
$offset  = ($page - 1) * $perPage;

$totalData = $db->prepare("SELECT COUNT(*) FROM users u WHERE u.role = 0 AND $statusFilter");
$totalData->execute();
$totalRecords = (int)$totalData->fetchColumn();
$totalPages   = max(1, (int)ceil($totalRecords / $perPage));
if ($page > $totalPages) { $page = $totalPages; $offset = ($page - 1) * $perPage; }

$reviewsData = $db->prepare("
    SELECT u.*,
        COALESCE(SUM(CASE WHEN ac.type IS NULL THEN ac.credit END), 0) AS credit,
        COALESCE(SUM(CASE WHEN ac.type = 1    THEN ac.credit END), 0) AS group_credit
    FROM users AS u
    LEFT JOIN active_credit AS ac ON ac.user_id = u.id AND ac.credit > 0
    WHERE u.role = 0 AND $statusFilter
    GROUP BY u.id
    ORDER BY u.id DESC
    LIMIT :limit OFFSET :offset
");
$reviewsData->bindValue(':limit',  $perPage, PDO::PARAM_INT);
$reviewsData->bindValue(':offset', $offset,  PDO::PARAM_INT);
$reviewsData->execute();

$baseUrl     = '/management/all-students.php';
$passiveUrl  = $showPassive ? $baseUrl : $baseUrl . '?pasif=1';
$activeUrl   = $showPassive ? $baseUrl : null;
?>
<!DOCTYPE html>
<html lang="tr">
<head>
  <?php include "../includes_panel/meta.php"; ?>
  <title>Tüm Öğrenciler | Evo Eğitim</title>
  <style>
    .student-avatar { width:42px; height:42px; border-radius:50%; object-fit:cover; border:2px solid #e2e8f0; }
    .student-avatar-placeholder { width:42px; height:42px; border-radius:50%; background:#e2e8f0; display:flex; align-items:center; justify-content:center; font-size:18px; color:#94a3b8; flex-shrink:0; }
    .badge-credit { font-size:12px; padding:4px 8px; border-radius:20px; font-weight:600; }
    .table td { vertical-align:middle; }
    .table th { white-space:nowrap; font-size:13px; }
    .dropdown-menu { min-width:190px; }
    #bulkDeleteBar { display:none; }
    .table-responsive { overflow: visible !important; }
    .table-responsive table { overflow: visible; }
  </style>
</head>
<body>
  <?php include 'includes/left-menu.php'; ?>
  <div class="dashboard-main-wrapper">
    <?php include 'includes/top-menu.php'; ?>
    <div class="dashboard-body">
      <div class="card mt-24">

        <div class="card-header border-bottom d-flex align-items-center justify-content-between flex-wrap gap-2">
          <div>
            <h4 class="mb-1"><?php echo $showPassive ? 'Pasif Öğrenciler' : 'Tüm Öğrenciler'; ?></h4>
            <span class="text-gray-500 text-14">Toplam <?php echo $totalRecords; ?> öğrenci</span>
          </div>
          <div class="d-flex gap-2">
            <?php if ($showPassive): ?>
              <a href="<?php echo $baseUrl; ?>" class="btn btn-sm btn-primary">← Aktif Öğrenciler</a>
            <?php else: ?>
              <a href="<?php echo $passiveUrl; ?>" class="btn btn-sm btn-warning text-white">Pasif Öğrencileri Gör</a>
            <?php endif; ?>
          </div>
        </div>

        <!-- Toplu silme çubuğu -->
        <div id="bulkDeleteBar" class="px-3 py-2 bg-danger-100 border-bottom d-flex align-items-center gap-3">
          <span class="text-danger fw-semibold"><span id="selectedCount">0</span> öğrenci seçildi</span>
          <button id="bulkDeleteBtn" class="btn btn-sm btn-danger">
            <i class="ph ph-trash me-1"></i>Seçilenleri Pasife Al
          </button>
          <button id="clearSelection" class="btn btn-sm btn-outline-secondary">İptal</button>
        </div>

        <div class="card-body p-0">
          <div class="table-responsive">
            <table class="table table-hover mb-0">
              <thead class="table-light">
                <tr>
                  <th class="ps-3" style="width:44px">
                    <input type="checkbox" id="selectAll" class="form-check-input">
                  </th>
                  <th style="width:52px"></th>
                  <th>Ad Soyad</th>
                  <th>İletişim</th>
                  <th>Veli</th>
                  <th>Solo Kredi</th>
                  <th>Grup Kredisi</th>
                  <th>Üyelik</th>
                  <th style="width:60px">İşlem</th>
                </tr>
              </thead>
              <tbody>
                <?php while ($row = $reviewsData->fetch(PDO::FETCH_ASSOC)):
                  $photo      = $row['profile_photo'];
                  $parentMail = getParentEmail($db, $row['email']);
                ?>
                <tr data-id="<?php echo $row['id']; ?>">
                  <td class="ps-3">
                    <input type="checkbox" class="form-check-input row-check" value="<?php echo $row['id']; ?>">
                  </td>
                  <td>
                    <img src="<?php echo $photo ? '/' . htmlspecialchars($photo) : 'https://www.shutterstock.com/image-vector/vector-flat-illustration-grayscale-avatar-600nw-2281862025.jpg'; ?>" class="student-avatar">
                  </td>
                  <td>
                    <span class="fw-semibold"><?php echo htmlspecialchars($row['fullname']); ?></span>
                  </td>
                  <td>
                    <div class="text-14"><?php echo htmlspecialchars($row['email']); ?></div>
                    <div class="text-13 text-gray-500"><?php echo htmlspecialchars($row['phone'] ?? '—'); ?></div>
                  </td>
                  <td>
                    <span class="text-14 <?php echo $parentMail ? '' : 'text-gray-400'; ?>">
                      <?php echo $parentMail ? htmlspecialchars($parentMail) : 'Bulunamadı'; ?>
                    </span>
                  </td>
                  <td>
                    <span class="badge badge-credit bg-primary-100 text-primary-600"><?php echo (int)$row['credit']; ?> kr</span>
                  </td>
                  <td>
                    <span class="badge badge-credit bg-success-100 text-success-600"><?php echo (int)$row['group_credit']; ?> kr</span>
                  </td>
                  <td class="text-13 text-gray-500">
                    <?php echo turkcetarih('j M Y', $row['created_at']); ?>
                  </td>
                  <td>
                    <div class="dropdown">
                      <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" data-bs-boundary="viewport">
                        <i class="ph ph-dots-three-vertical"></i>
                      </button>
                      <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                        <li>
                          <a class="dropdown-item" href="/management/profile-settings.php?id=<?php echo $row['id']; ?>&email=<?php echo urlencode($row['email']); ?>&tab=1">
                            <i class="ph ph-pencil me-2 text-primary"></i>Profili Düzenle
                          </a>
                        </li>
                        <li>
                          <a class="dropdown-item" href="/management/profile-settings.php?id=<?php echo $row['id']; ?>&email=<?php echo urlencode($row['email']); ?>&payment=1">
                            <i class="ph ph-receipt me-2 text-info"></i>Satın Alım Geçmişi
                          </a>
                        </li>
                        <li>
                          <a class="dropdown-item" href="/management/lesson-create.php?id=<?php echo $row['id']; ?>">
                            <i class="ph ph-book-open me-2 text-success"></i>Ders Tanımla
                          </a>
                        </li>
                        <li>
                          <a class="dropdown-item" href="/management/add-credit.php?id=<?php echo $row['id']; ?>&type=IS NULL">
                            <i class="ph ph-coins me-2 text-warning"></i>Solo Kredi Düzenle
                          </a>
                        </li>
                        <li>
                          <a class="dropdown-item" href="/management/add-credit.php?id=<?php echo $row['id']; ?>&type=1">
                            <i class="ph ph-users me-2 text-warning"></i>Grup Kredisi Düzenle
                          </a>
                        </li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                          <a class="dropdown-item text-danger" href="/management/student-teachers.php?id=<?php echo $row['id']; ?>">
                            <i class="ph ph-user-minus me-2"></i>Öğretmeni Kaldır
                          </a>
                        </li>
                        <li>
                          <a class="dropdown-item text-danger single-delete" href="#" data-id="<?php echo $row['id']; ?>" data-name="<?php echo htmlspecialchars($row['fullname']); ?>">
                            <i class="ph ph-trash me-2"></i>Pasife Al
                          </a>
                        </li>
                      </ul>
                    </div>
                  </td>
                </tr>
                <?php endwhile; ?>
              </tbody>
            </table>
          </div>

          <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 px-3 py-3 border-top">
            <span class="text-gray-600 text-14">
              <?php echo $totalRecords; ?> kayıttan
              <?php echo $totalRecords ? $offset + 1 : 0; ?>–<?php echo min($offset + $perPage, $totalRecords); ?> arası gösteriliyor
            </span>
            <nav>
              <ul class="pagination pagination-sm mb-0">
                <li class="page-item <?php echo $page <= 1 ? 'disabled' : ''; ?>">
                  <a class="page-link" href="<?php echo $baseUrl; ?>?<?php echo $showPassive ? 'pasif=1&' : ''; ?>page=<?php echo max(1, $page - 1); ?>">&#8249; Önceki</a>
                </li>
                <li class="page-item disabled">
                  <span class="page-link"><?php echo $page; ?> / <?php echo $totalPages; ?></span>
                </li>
                <li class="page-item <?php echo $page >= $totalPages ? 'disabled' : ''; ?>">
                  <a class="page-link" href="<?php echo $baseUrl; ?>?<?php echo $showPassive ? 'pasif=1&' : ''; ?>page=<?php echo min($totalPages, $page + 1); ?>">Sonraki &#8250;</a>
                </li>
              </ul>
            </nav>
          </div>
        </div>
      </div>
    </div>
    <?php include '../includes_panel/footer.php'; ?>
  </div>
  <?php include '../includes_panel/scripts.php'; ?>

  <script>
  $(function () {

    function softDelete(ids, onSuccess) {
      $.ajax({
        url: '/management/all-students.php',
        type: 'POST',
        data: { action: 'soft_delete', ids: ids },
        dataType: 'json',
        success: function (res) {
          if (res.status == 1) {
            onSuccess(res.message);
          } else {
            alert(res.message || 'Hata oluştu.');
          }
        },
        error: function () { alert('Sunucu hatası.'); }
      });
    }

    // Tekil sil
    $(document).on('click', '.single-delete', function (e) {
      e.preventDefault();
      var id   = $(this).data('id');
      var name = $(this).data('name');
      if (!confirm('"' + name + '" adlı öğrenciyi pasife almak istediğinize emin misiniz?')) return;
      softDelete([id], function (msg) {
        $('tr[data-id="' + id + '"]').fadeOut(300, function () { $(this).remove(); });
      });
    });

    // Checkbox seçim yönetimi
    function updateBulkBar() {
      var count = $('.row-check:checked').length;
      $('#selectedCount').text(count);
      if (count > 0) {
        $('#bulkDeleteBar').show();
      } else {
        $('#bulkDeleteBar').hide();
        $('#selectAll').prop('checked', false).prop('indeterminate', false);
      }
    }

    $('#selectAll').on('change', function () {
      $('.row-check').prop('checked', this.checked);
      updateBulkBar();
    });

    $(document).on('change', '.row-check', function () {
      var total    = $('.row-check').length;
      var checked  = $('.row-check:checked').length;
      $('#selectAll').prop('checked', checked === total).prop('indeterminate', checked > 0 && checked < total);
      updateBulkBar();
    });

    // Toplu sil
    $('#bulkDeleteBtn').on('click', function () {
      var ids = $('.row-check:checked').map(function () { return $(this).val(); }).get();
      if (!ids.length) return;
      if (!confirm(ids.length + ' öğrenciyi pasife almak istediğinize emin misiniz?')) return;
      softDelete(ids, function (msg) {
        $.each(ids, function (i, id) {
          $('tr[data-id="' + id + '"]').fadeOut(300, function () { $(this).remove(); });
        });
        $('#bulkDeleteBar').hide();
        $('#selectAll').prop('checked', false);
      });
    });

    // İptal
    $('#clearSelection').on('click', function () {
      $('.row-check, #selectAll').prop('checked', false).prop('indeterminate', false);
      $('#bulkDeleteBar').hide();
    });

  });
  </script>
</body>
</html>
