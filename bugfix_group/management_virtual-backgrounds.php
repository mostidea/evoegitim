<?php
/**
 * management/virtual-backgrounds.php
 * Sanal Arka Plan Yönetim Ekranı (Management Paneli)
 *
 * Bağımlılıklar:
 *   - config/connection.php  (PDO $db)
 *   - virtual_bg_migration.sql çalıştırılmış olmalı
 *   - inc/post/virtual-bg.php  (AJAX handler)
 *   - assets/js/virtual-bg.js  (JS motoru)
 */

session_start();
include "../config/connection.php";
checkUnSession();

// Sadece yöneticiler erişebilir
$allowedRoles = ['admin', 'management', 'manager', 'superadmin'];
$userRole = $_SESSION['role'] ?? $_SESSION['user_role'] ?? '';
if (!in_array($userRole, $allowedRoles, true)) {
    header("location: ../index.php");
    exit;
}

// Tüm sistem arka planlarını çek
$bgList = $db->query(
    "SELECT * FROM virtual_backgrounds WHERE type = 'system' ORDER BY sort_order ASC, id ASC"
)->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="tr">
<head>
  <?php include '../includes/meta.php'; ?>
  <title>Sanal Arka Plan Yönetimi | Evo Eğitim</title>
  <style>
    .vbg-grid {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
      gap: 16px;
    }
    .vbg-card {
      border: 1px solid #e5e7eb;
      border-radius: 10px;
      overflow: hidden;
      background: #fff;
      transition: box-shadow .2s;
    }
    .vbg-card:hover { box-shadow: 0 4px 16px rgba(0,0,0,.1); }
    .vbg-card-img {
      width: 100%;
      height: 120px;
      object-fit: cover;
      display: block;
    }
    .vbg-card-body {
      padding: 10px 12px;
    }
    .vbg-card-name {
      font-size: 13px;
      font-weight: 600;
      color: #374151;
      white-space: nowrap;
      overflow: hidden;
      text-overflow: ellipsis;
    }
    .vbg-card-actions {
      display: flex;
      gap: 6px;
      margin-top: 8px;
    }
    .vbg-card.inactive .vbg-card-img { opacity: .4; }
    .vbg-card.inactive { border-color: #fca5a5; background: #fff7f7; }
    .drag-handle { cursor: grab; color: #9ca3af; padding: 2px 6px; }
    .drag-handle:active { cursor: grabbing; }
    .upload-zone {
      border: 2px dashed #d1d5db;
      border-radius: 12px;
      padding: 32px;
      text-align: center;
      cursor: pointer;
      transition: border-color .2s, background .2s;
    }
    .upload-zone:hover, .upload-zone.dragover {
      border-color: #6366f1;
      background: #f5f3ff;
    }
    .upload-preview {
      width: 100%;
      max-height: 200px;
      object-fit: contain;
      border-radius: 8px;
      margin-top: 12px;
      display: none;
    }
    .badge-active   { background: #d1fae5; color: #065f46; }
    .badge-inactive { background: #fee2e2; color: #991b1b; }
  </style>
</head>
<body>
  <?php include 'includes/left-menu.php'; ?>

  <div class="dashboard-main-wrapper">
    <?php include 'includes/top-menu.php'; ?>
    <div class="dashboard-body">

      <!-- Başlık -->
      <div class="d-flex align-items-center justify-content-between mb-24">
        <div>
          <h4 class="mb-4">Sanal Arka Plan Yönetimi</h4>
          <p class="text-gray-600 text-15 mb-0">
            Öğretmenlerin ders sırasında kullanacağı kurumsal arka planları buradan yönetin.
          </p>
        </div>
        <button class="btn btn-evo" data-bs-toggle="modal" data-bs-target="#addBgModal">
          <i class="far fa-plus me-1"></i> Yeni Arka Plan Ekle
        </button>
      </div>

      <!-- Durum özeti -->
      <div class="row g-3 mb-24">
        <div class="col-md-3 col-sm-6">
          <div class="card text-center py-3">
            <div class="h3 mb-1 text-primary-600" id="stat-total"><?php echo count($bgList); ?></div>
            <div class="text-gray-500 text-14">Toplam Arka Plan</div>
          </div>
        </div>
        <div class="col-md-3 col-sm-6">
          <div class="card text-center py-3">
            <div class="h3 mb-1 text-success" id="stat-active">
              <?php echo count(array_filter($bgList, fn($r) => $r['is_active'])); ?>
            </div>
            <div class="text-gray-500 text-14">Aktif</div>
          </div>
        </div>
        <div class="col-md-3 col-sm-6">
          <div class="card text-center py-3">
            <div class="h3 mb-1 text-danger" id="stat-inactive">
              <?php echo count(array_filter($bgList, fn($r) => !$r['is_active'])); ?>
            </div>
            <div class="text-gray-500 text-14">Pasif</div>
          </div>
        </div>
      </div>

      <!-- Arka Plan Grid -->
      <div class="card">
        <div class="card-header border-bottom">
          <h5 class="mb-0">Arka Planlar</h5>
          <small class="text-gray-500">Sıralamayı değiştirmek için sürükleyip bırakın.</small>
        </div>
        <div class="card-body">
          <?php if (empty($bgList)): ?>
            <div class="text-center py-5 text-gray-400">
              <i class="far fa-image fa-3x mb-3 d-block"></i>
              Henüz arka plan eklenmemiş. Yukarıdaki butona tıklayarak ilk arka planı ekleyin.
            </div>
          <?php else: ?>
          <div class="vbg-grid js-vbg-sortable" id="vbgGrid">
            <?php foreach ($bgList as $bg): ?>
            <div class="vbg-card <?php echo $bg['is_active'] ? '' : 'inactive'; ?>"
                 data-id="<?php echo $bg['id']; ?>">
              <img class="vbg-card-img"
                   src="<?php echo htmlspecialchars($bg['image_path']); ?>"
                   alt="<?php echo htmlspecialchars($bg['name']); ?>"
                   onerror="this.src='/assets/img/placeholder-bg.jpg'">
              <div class="vbg-card-body">
                <div class="d-flex align-items-center justify-content-between">
                  <span class="vbg-card-name" title="<?php echo htmlspecialchars($bg['name']); ?>">
                    <?php echo htmlspecialchars($bg['name']); ?>
                  </span>
                  <span class="drag-handle js-drag-handle" title="Sürükle">&#8597;</span>
                </div>
                <div class="mt-1">
                  <span class="badge text-12 px-2 py-1 rounded-pill
                    <?php echo $bg['is_active'] ? 'badge-active' : 'badge-inactive'; ?>"
                    id="badge-<?php echo $bg['id']; ?>">
                    <?php echo $bg['is_active'] ? 'Aktif' : 'Pasif'; ?>
                  </span>
                </div>
                <div class="vbg-card-actions">
                  <button class="btn btn-sm btn-outline-secondary flex-fill js-toggle-btn"
                          data-id="<?php echo $bg['id']; ?>"
                          title="<?php echo $bg['is_active'] ? 'Pasif yap' : 'Aktif yap'; ?>">
                    <i class="far <?php echo $bg['is_active'] ? 'fa-eye-slash' : 'fa-eye'; ?>"></i>
                  </button>
                  <a href="<?php echo htmlspecialchars($bg['image_path']); ?>"
                     class="btn btn-sm btn-outline-info flex-fill"
                     data-fancybox title="Önizle">
                    <i class="far fa-search"></i>
                  </a>
                  <button class="btn btn-sm btn-outline-danger flex-fill js-delete-btn"
                          data-id="<?php echo $bg['id']; ?>"
                          data-name="<?php echo htmlspecialchars($bg['name']); ?>"
                          title="Sil">
                    <i class="far fa-trash"></i>
                  </button>
                </div>
              </div>
            </div>
            <?php endforeach; ?>
          </div>
          <?php endif; ?>
        </div>
      </div>

    </div><!-- dashboard-body -->
    <?php include '../includes/footer.php'; ?>
  </div><!-- dashboard-main-wrapper -->

  <!-- ── Yeni Arka Plan Ekleme Modal ────────────────────────────────────── -->
  <div class="modal fade" id="addBgModal" tabindex="-1" aria-labelledby="addBgModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="addBgModalLabel">Yeni Arka Plan Ekle</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
            <label class="form-label fw-semibold">Arka Plan Adı <span class="text-danger">*</span></label>
            <input type="text" class="form-control" id="bgName"
                   placeholder="Örn: EBO Eğitim Kurumsal Arka Plan" maxlength="255">
          </div>
          <div class="mb-3">
            <label class="form-label fw-semibold">Görsel Yükle <span class="text-danger">*</span></label>
            <div class="upload-zone" id="uploadZone">
              <i class="far fa-cloud-upload fa-2x text-gray-400 mb-2 d-block"></i>
              <p class="text-gray-500 mb-1">Görseli buraya sürükleyin veya tıklayın</p>
              <small class="text-gray-400">JPG, PNG, WEBP · Maks. 10MB · Önerilen: 1920×1080</small>
              <input type="file" id="bgFileInput" accept=".jpg,.jpeg,.png,.webp" class="d-none">
            </div>
            <img id="uploadPreview" class="upload-preview" alt="Önizleme">
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">İptal</button>
          <button type="button" class="btn btn-evo" id="saveBgBtn">
            <span id="saveBtnText">Kaydet</span>
            <span id="saveBtnSpinner" class="spinner-border spinner-border-sm ms-1 d-none"></span>
          </button>
        </div>
      </div>
    </div>
  </div>

  <?php include '../includes/scripts.php'; ?>
  <?php include 'includes/teacher-scripts.php'; ?>

  <script>
  (function () {

    const HANDLER = '../inc/post/virtual-bg.php';

    // ── Sürükle-bırak sıralama ──────────────────────────────────────────

    const grid = document.getElementById('vbgGrid');
    if (grid && typeof Sortable !== 'undefined') {
      Sortable.create(grid, {
        handle: '.js-drag-handle',
        animation: 150,
        onEnd: function () {
          const ids   = [];
          const ranks = [];
          grid.querySelectorAll('.vbg-card[data-id]').forEach((el, idx) => {
            ids.push(el.dataset.id);
            ranks.push(idx + 1);
          });
          $.post(HANDLER, { action: 'reorder', ids: JSON.stringify(ids), ranks: JSON.stringify(ranks) });
        }
      });
    }

    // ── Aktif / Pasif toggle ────────────────────────────────────────────

    $(document).on('click', '.js-toggle-btn', function () {
      const id  = $(this).data('id');
      const btn = $(this);
      $.post(HANDLER, { action: 'toggle', id: id }, function (res) {
        if (res.status !== 1) return;
        const card   = btn.closest('.vbg-card');
        const badge  = card.find('#badge-' + id);
        const icon   = btn.find('i');
        const active = res.is_active === 1;
        card.toggleClass('inactive', !active);
        badge.text(active ? 'Aktif' : 'Pasif')
             .removeClass('badge-active badge-inactive')
             .addClass(active ? 'badge-active' : 'badge-inactive');
        icon.removeClass('fa-eye fa-eye-slash').addClass(active ? 'fa-eye-slash' : 'fa-eye');
        btn.attr('title', active ? 'Pasif yap' : 'Aktif yap');
        updateStats();
      }, 'json');
    });

    // ── Silme ───────────────────────────────────────────────────────────

    $(document).on('click', '.js-delete-btn', function () {
      const id   = $(this).data('id');
      const name = $(this).data('name');
      Swal.fire({
        title: 'Emin misiniz?',
        html : `<b>${name}</b> adlı arka plan kalıcı olarak silinecek.`,
        icon : 'warning',
        showCancelButton : true,
        confirmButtonText: 'Evet, Sil',
        cancelButtonText : 'Vazgeç',
        confirmButtonColor: '#ef4444',
      }).then(result => {
        if (!result.isConfirmed) return;
        $.post(HANDLER, { action: 'delete', id: id }, function (res) {
          if (res.status === 1) {
            $(`[data-id="${id}"]`).remove();
            updateStats();
            if (!document.querySelectorAll('.vbg-card').length) location.reload();
          } else {
            Swal.fire('Hata', res.message || 'Silme başarısız.', 'error');
          }
        }, 'json');
      });
    });

    // ── Yükleme modalı ──────────────────────────────────────────────────

    const uploadZone  = document.getElementById('uploadZone');
    const fileInput   = document.getElementById('bgFileInput');
    const preview     = document.getElementById('uploadPreview');
    let   selectedFile = null;

    uploadZone.addEventListener('click', () => fileInput.click());

    uploadZone.addEventListener('dragover', e => {
      e.preventDefault();
      uploadZone.classList.add('dragover');
    });
    uploadZone.addEventListener('dragleave', () => uploadZone.classList.remove('dragover'));
    uploadZone.addEventListener('drop', e => {
      e.preventDefault();
      uploadZone.classList.remove('dragover');
      handleFile(e.dataTransfer.files[0]);
    });

    fileInput.addEventListener('change', () => handleFile(fileInput.files[0]));

    function handleFile(file) {
      if (!file) return;
      const allowed = ['image/jpeg', 'image/png', 'image/webp'];
      if (!allowed.includes(file.type)) {
        Swal.fire('Hata', 'Sadece JPG, PNG veya WEBP formatı kabul edilir.', 'error');
        return;
      }
      if (file.size > 10 * 1024 * 1024) {
        Swal.fire('Hata', 'Dosya boyutu 10MB sınırını aşıyor.', 'error');
        return;
      }
      selectedFile = file;
      const reader = new FileReader();
      reader.onload = e => {
        preview.src = e.target.result;
        preview.style.display = 'block';
      };
      reader.readAsDataURL(file);
    }

    $('#saveBgBtn').on('click', function () {
      const name = $('#bgName').val().trim();
      if (!name) {
        Swal.fire('Uyarı', 'Arka plan adı boş olamaz.', 'warning');
        return;
      }
      if (!selectedFile) {
        Swal.fire('Uyarı', 'Lütfen bir görsel seçin.', 'warning');
        return;
      }

      const fd = new FormData();
      fd.append('action', 'upload');
      fd.append('name', name);
      fd.append('image', selectedFile);

      $('#saveBtnText').text('Kaydediliyor...');
      $('#saveBtnSpinner').removeClass('d-none');
      $('#saveBgBtn').prop('disabled', true);

      $.ajax({
        url        : HANDLER,
        type       : 'POST',
        data       : fd,
        processData: false,
        contentType: false,
        dataType   : 'json',
        success: function (res) {
          if (res.status === 1) {
            Swal.fire({
              icon : 'success',
              title: 'Başarılı',
              text : 'Arka plan eklendi.',
              timer: 1500,
              showConfirmButton: false
            }).then(() => location.reload());
          } else {
            Swal.fire('Hata', res.message || 'Yükleme başarısız.', 'error');
          }
        },
        error: function () {
          Swal.fire('Hata', 'Sunucuya ulaşılamadı.', 'error');
        },
        complete: function () {
          $('#saveBtnText').text('Kaydet');
          $('#saveBtnSpinner').addClass('d-none');
          $('#saveBgBtn').prop('disabled', false);
        }
      });
    });

    // Modal kapandığında formu sıfırla
    document.getElementById('addBgModal').addEventListener('hidden.bs.modal', function () {
      document.getElementById('bgName').value = '';
      document.getElementById('bgFileInput').value = '';
      preview.src = '';
      preview.style.display = 'none';
      selectedFile = null;
    });

    // ── İstatistik güncelleme ───────────────────────────────────────────

    function updateStats() {
      const cards    = document.querySelectorAll('.vbg-card');
      const inactive = document.querySelectorAll('.vbg-card.inactive');
      document.getElementById('stat-total').textContent   = cards.length;
      document.getElementById('stat-active').textContent  = cards.length - inactive.length;
      document.getElementById('stat-inactive').textContent = inactive.length;
    }

  })();
  </script>

</body>
</html>
