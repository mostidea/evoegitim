<?php
/**
 * virtual-bg-modal.php
 * Sanal Arka Plan Seçim Modal Bileşeni
 *
 * Bu dosya, ders odası sayfasına şu şekilde dahil edilir:
 *   <?php include 'virtual-bg-modal.php'; ?>
 *
 * Sayfada kullanmak için:
 *   1. Butona tıklayınca modalı aç:
 *      <button data-bs-toggle="modal" data-bs-target="#virtualBgModal">Arka Plan</button>
 *
 *   2. Uygula butonuna basıldığında işlenmiş stream'i alın:
 *      document.addEventListener('vbg:apply', e => {
 *          const processedStream = e.detail.stream;
 *          const audioTrack      = e.detail.audioTrack;
 *          // WebRTC sender'a ver:
 *          // sender.replaceTrack(processedStream.getVideoTracks()[0]);
 *      });
 *
 *   3. Ayarlar değiştirildiğinde (isteğe bağlı):
 *      document.addEventListener('vbg:change', e => { ... });
 */
?>

<!-- ── CSS ──────────────────────────────────────────────────────────────── -->
<style>
  /* ── Modal Genel ── */
  #virtualBgModal .modal-dialog { max-width: 760px; }

  /* ── Önizleme Alanı ── */
  .vbg-preview-wrap {
    position: relative;
    background: #000;
    border-radius: 10px;
    overflow: hidden;
    aspect-ratio: 16/9;
    max-height: 240px;
  }
  #vbgPreviewCanvas {
    width: 100%;
    height: 100%;
    object-fit: cover;
    display: block;
  }
  .vbg-loading-overlay {
    position: absolute;
    inset: 0;
    background: rgba(0,0,0,.7);
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    color: #fff;
    font-size: 14px;
    gap: 10px;
    z-index: 5;
  }

  /* ── Seçenek Grupları ── */
  .vbg-section-title {
    font-size: 12px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .06em;
    color: #6b7280;
    margin-bottom: 10px;
  }

  /* ── Blur Kartları ── */
  .vbg-blur-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 8px;
  }
  .vbg-blur-card {
    border: 2px solid transparent;
    border-radius: 8px;
    overflow: hidden;
    cursor: pointer;
    transition: border-color .15s;
    background: #f3f4f6;
    text-align: center;
    padding: 10px 6px;
    font-size: 12px;
    color: #374151;
    font-weight: 500;
  }
  .vbg-blur-card:hover { border-color: #a5b4fc; }
  .vbg-blur-card.selected { border-color: #6366f1; background: #eef2ff; }
  .vbg-blur-icon {
    font-size: 22px;
    margin-bottom: 4px;
    display: block;
  }

  /* ── Arka Plan Kartları ── */
  .vbg-bg-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(100px, 1fr));
    gap: 8px;
    max-height: 220px;
    overflow-y: auto;
    padding-right: 4px;
  }
  .vbg-bg-card {
    border: 2px solid transparent;
    border-radius: 8px;
    overflow: hidden;
    cursor: pointer;
    transition: border-color .15s, transform .1s;
    aspect-ratio: 16/9;
    position: relative;
  }
  .vbg-bg-card img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    display: block;
  }
  .vbg-bg-card .vbg-bg-name {
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
    background: linear-gradient(transparent, rgba(0,0,0,.7));
    color: #fff;
    font-size: 10px;
    padding: 4px 6px 5px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
  }
  .vbg-bg-card:hover  { border-color: #a5b4fc; transform: scale(1.03); }
  .vbg-bg-card.selected { border-color: #6366f1; }
  .vbg-bg-card.none-card { background: #f9fafb; }
  .vbg-bg-card.none-card img { display: none; }
  .vbg-bg-card.none-card::before {
    content: '✕';
    position: absolute;
    inset: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 24px;
    color: #9ca3af;
  }

  /* ── Kişisel Yükleme ── */
  .vbg-upload-btn {
    border: 2px dashed #d1d5db;
    border-radius: 8px;
    padding: 14px;
    text-align: center;
    cursor: pointer;
    font-size: 12px;
    color: #6b7280;
    transition: border-color .15s, background .15s;
    background: #f9fafb;
    width: 100%;
  }
  .vbg-upload-btn:hover { border-color: #6366f1; background: #eef2ff; color: #6366f1; }

  /* ── Hata Banner ── */
  .vbg-error-banner {
    background: #fef2f2;
    border: 1px solid #fca5a5;
    border-radius: 8px;
    padding: 12px 16px;
    font-size: 13px;
    color: #b91c1c;
    display: none;
  }
  .vbg-error-banner.show { display: flex; align-items: center; gap: 8px; }

  /* ── Düşük performans uyarısı ── */
  .vbg-perf-badge {
    display: none;
    font-size: 11px;
    background: #fef3c7;
    color: #92400e;
    border-radius: 4px;
    padding: 2px 8px;
  }
</style>

<!-- ── Modal HTML ────────────────────────────────────────────────────────── -->
<div class="modal fade" id="virtualBgModal" tabindex="-1"
     aria-labelledby="virtualBgModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">

      <!-- Başlık -->
      <div class="modal-header pb-2">
        <h5 class="modal-title" id="virtualBgModalLabel">
          <i class="far fa-image me-2"></i>Arka Plan Ayarları
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>

      <div class="modal-body pt-2">

        <!-- Hata Banner -->
        <div class="vbg-error-banner mb-3" id="vbgError">
          <i class="far fa-exclamation-circle"></i>
          <span id="vbgErrorText"></span>
        </div>

        <!-- Kamera Önizlemesi -->
        <div class="vbg-preview-wrap mb-3">
          <canvas id="vbgPreviewCanvas" width="640" height="360"></canvas>
          <div class="vbg-loading-overlay" id="vbgLoadingOverlay">
            <div class="spinner-border text-light" style="width:2rem;height:2rem;"></div>
            <span id="vbgLoadingText">Kamera başlatılıyor…</span>
          </div>
        </div>

        <span class="vbg-perf-badge ms-1" id="vbgPerfBadge">
          ⚠ Düşük performans modunda çalışıyor
        </span>

        <hr class="my-3">

        <!-- Blur Seviyesi -->
        <div class="mb-3">
          <div class="vbg-section-title">Arka Plan Bulanıklaştırma</div>
          <div class="vbg-blur-grid">
            <div class="vbg-blur-card selected" data-blur="none">
              <span class="vbg-blur-icon">🚫</span> Kapalı
            </div>
            <div class="vbg-blur-card" data-blur="light">
              <span class="vbg-blur-icon" style="filter:blur(1px)">🙂</span> Hafif
            </div>
            <div class="vbg-blur-card" data-blur="medium">
              <span class="vbg-blur-icon" style="filter:blur(2px)">🙂</span> Orta
            </div>
            <div class="vbg-blur-card" data-blur="strong">
              <span class="vbg-blur-icon" style="filter:blur(4px)">🙂</span> Güçlü
            </div>
          </div>
        </div>

        <!-- Sanal Arka Planlar -->
        <div class="mb-3">
          <div class="vbg-section-title">Sanal Arka Plan</div>
          <div class="vbg-bg-grid" id="vbgBgGrid">
            <!-- Yok seçeneği -->
            <div class="vbg-bg-card none-card selected" data-bg="none">
              <div class="vbg-bg-name">Yok</div>
            </div>
            <!-- Dinamik olarak doldurulacak -->
          </div>
          <div class="text-center py-3 text-gray-400 d-none" id="vbgBgLoading">
            <span class="spinner-border spinner-border-sm me-1"></span> Yükleniyor…
          </div>
        </div>

        <!-- Kişisel Yükleme -->
        <div>
          <div class="vbg-section-title">Kişisel Arka Plan Yükle</div>
          <div class="vbg-upload-btn" id="vbgUserUploadBtn">
            <i class="far fa-cloud-upload-alt me-1"></i>
            Bilgisayarımdan Seç
            <span class="d-block text-11 mt-1">JPG, PNG, WEBP · Maks. 10MB</span>
          </div>
          <input type="file" id="vbgUserFileInput" accept=".jpg,.jpeg,.png,.webp" class="d-none">
        </div>

      </div><!-- modal-body -->

      <div class="modal-footer pt-2">
        <small class="text-gray-400 me-auto" id="vbgActiveLabel">Mod: Kapalı</small>
        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">
          Kapat
        </button>
        <button type="button" class="btn btn-evo btn-sm" id="vbgApplyBtn" disabled>
          <i class="fas fa-check me-1"></i> Uygula
        </button>
      </div>

    </div><!-- modal-content -->
  </div>
</div>

<!-- ── JavaScript ────────────────────────────────────────────────────────── -->
<script>
(function () {

  'use strict';

  const BG_API      = '/inc/post/virtual-bg.php';
  const VBG_STORAGE = 'vbg_preference';

  let vb         = null;   // VirtualBackground instance
  let isReady    = false;
  let pendingMode = null;   // kullanıcı seçimi kamera açılmadan önce gelirse

  // ── Tercih kayıt/yükleme ──────────────────────────────────────────────

  function savePreference(data) {
    try { localStorage.setItem(VBG_STORAGE, JSON.stringify(data)); } catch {}
  }

  function loadPreference() {
    try { return JSON.parse(localStorage.getItem(VBG_STORAGE) || 'null'); } catch { return null; }
  }

  // ── Hata gösterme ─────────────────────────────────────────────────────

  function showError(msg) {
    const el = document.getElementById('vbgError');
    document.getElementById('vbgErrorText').textContent = msg;
    el.classList.add('show');
  }

  function hideError() {
    document.getElementById('vbgError').classList.remove('show');
  }

  // ── Yükleme overlay ───────────────────────────────────────────────────

  function setLoadingText(txt) {
    document.getElementById('vbgLoadingText').textContent = txt;
  }

  function hideLoading() {
    document.getElementById('vbgLoadingOverlay').style.display = 'none';
  }

  // ── Aktif mod etiketi ─────────────────────────────────────────────────

  function updateLabel() {
    if (!vb) return;
    const labels = {
      none  : 'Mod: Kapalı',
      blur  : { light: 'Blur: Hafif', medium: 'Blur: Orta', strong: 'Blur: Güçlü' },
      image : 'Mod: Sanal Arka Plan',
    };
    let text = labels['none'];
    if (vb.mode === 'blur')  text = labels.blur[vb.blurLevel] || 'Blur';
    if (vb.mode === 'image') text = labels.image;
    document.getElementById('vbgActiveLabel').textContent = text;
  }

  // ── Modal açıldığında kamerayı başlat ──────────────────────────────────

  document.getElementById('virtualBgModal').addEventListener('shown.bs.modal', async function () {
    hideError();

    if (vb && isReady) return; // zaten çalışıyor

    // VirtualBackground sınıfı yüklü mü?
    if (typeof VirtualBackground === 'undefined') {
      showError('virtual-bg.js dosyası sayfaya eklenmemiş.');
      return;
    }

    const supported = await VirtualBackground.isSupported().catch(() => false);
    if (!supported) {
      showError('Tarayıcınız kamera erişimine izin vermiyor veya bu özelliği desteklemiyor.');
      return;
    }

    setLoadingText('Kamera başlatılıyor…');

    vb = new VirtualBackground({
      width  : 640,
      height : 360,
      fps    : 20,
      onReady: function (instance) {
        setLoadingText('AI modeli yükleniyor…');

        // Preview canvas'a bağla
        const previewCanvas = document.getElementById('vbgPreviewCanvas');
        const srcCanvas     = instance.getPreviewCanvas();

        // Canlı çerçeve kopyalama
        const previewCtx = previewCanvas.getContext('2d');
        (function copyFrame() {
          previewCtx.drawImage(srcCanvas, 0, 0, previewCanvas.width, previewCanvas.height);
          if (isReady) requestAnimationFrame(copyFrame);
        })();

        isReady = true;
        hideLoading();
        document.getElementById('vbgApplyBtn').disabled = false;

        // Önceki tercihi uygula
        const pref = loadPreference();
        if (pref) applyPreference(pref);

        updateLabel();
      },
      onError: function (err) {
        console.error('[VBG]', err);
        const msg = err.name === 'NotAllowedError'
          ? 'Kamera izni reddedildi. Lütfen tarayıcı izinlerini kontrol edin.'
          : (err.message || 'Kamera başlatılamadı.');
        showError(msg);
        document.getElementById('vbgLoadingOverlay').style.display = 'none';
      }
    });

    vb.init().catch(err => console.error('[VBG init]', err));

    loadSystemBackgrounds();
  });

  // Modal kapanınca kamera serbest bırakılmaz (aktif ders için gerekli)
  // Ders bitince vb.stop() dışarıdan çağrılmalı.

  // ── Sistem arka planlarını yükle ──────────────────────────────────────

  function loadSystemBackgrounds() {
    const grid    = document.getElementById('vbgBgGrid');
    const loading = document.getElementById('vbgBgLoading');

    if (grid.querySelectorAll('[data-bg]:not([data-bg="none"])').length) return;

    loading.classList.remove('d-none');

    fetch(BG_API + '?action=list', { credentials: 'same-origin' })
      .then(r => r.json())
      .then(res => {
        loading.classList.add('d-none');
        if (!res.data?.length) return;
        res.data.forEach(bg => {
          const card = document.createElement('div');
          card.className   = 'vbg-bg-card';
          card.dataset.bg  = bg.image_path;
          card.dataset.id  = bg.id;
          card.innerHTML   = `<img src="${escHtml(bg.image_path)}" alt="${escHtml(bg.name)}" loading="lazy">
                              <div class="vbg-bg-name">${escHtml(bg.name)}</div>`;
          grid.appendChild(card);
        });
      })
      .catch(() => { loading.classList.add('d-none'); });
  }

  // ── Blur seçimi ───────────────────────────────────────────────────────

  document.querySelectorAll('.vbg-blur-card').forEach(card => {
    card.addEventListener('click', function () {
      document.querySelectorAll('.vbg-blur-card').forEach(c => c.classList.remove('selected'));
      this.classList.add('selected');
      const level = this.dataset.blur;

      if (!vb) return;

      if (level === 'none') {
        vb.setMode('none');
      } else {
        // Arka plan seçimini sıfırla
        deselectAllBg();
        document.querySelector('[data-bg="none"]')?.classList.add('selected');
        vb.setMode('blur');
        vb.setBlurLevel(level);
      }
      updateLabel();
    });
  });

  // ── Arka plan kartı seçimi ────────────────────────────────────────────

  document.getElementById('vbgBgGrid').addEventListener('click', function (e) {
    const card = e.target.closest('.vbg-bg-card');
    if (!card) return;

    deselectAllBg();
    card.classList.add('selected');

    const bg = card.dataset.bg;

    if (!vb) return;

    if (bg === 'none') {
      vb.setMode('none');
    } else {
      // Blur seçimini sıfırla
      document.querySelectorAll('.vbg-blur-card').forEach(c => c.classList.remove('selected'));
      document.querySelector('[data-blur="none"]')?.classList.add('selected');

      vb.setBackground(bg).catch(err => {
        showError('Arka plan yüklenemedi: ' + (err.message || bg));
        deselectAllBg();
        document.querySelector('[data-bg="none"]')?.classList.add('selected');
      });
    }
    updateLabel();
  });

  function deselectAllBg() {
    document.querySelectorAll('#vbgBgGrid .vbg-bg-card').forEach(c => c.classList.remove('selected'));
  }

  // ── Kişisel dosya yükleme ─────────────────────────────────────────────

  document.getElementById('vbgUserUploadBtn').addEventListener('click', function () {
    document.getElementById('vbgUserFileInput').click();
  });

  document.getElementById('vbgUserFileInput').addEventListener('change', async function () {
    const file = this.files[0];
    if (!file || !vb) return;

    try {
      const url = await vb.setBackgroundFromFile(file);
      // Kullanıcı yüklenen görseli grid'e ekle
      const grid = document.getElementById('vbgBgGrid');
      const old  = grid.querySelector('[data-bg^="blob:"]');
      if (old) old.remove();

      const card = document.createElement('div');
      card.className  = 'vbg-bg-card selected';
      card.dataset.bg = url;
      card.innerHTML  = `<img src="${url}" alt="Kişisel">
                         <div class="vbg-bg-name">Kişisel</div>`;
      grid.appendChild(card);

      deselectAllBg();
      card.classList.add('selected');

      document.querySelectorAll('.vbg-blur-card').forEach(c => c.classList.remove('selected'));
      document.querySelector('[data-blur="none"]')?.classList.add('selected');
      updateLabel();
    } catch (err) {
      showError(err.message || 'Dosya yüklenemedi.');
    }
    this.value = '';
  });

  // ── Uygula butonu ─────────────────────────────────────────────────────

  document.getElementById('vbgApplyBtn').addEventListener('click', function () {
    if (!vb || !isReady) return;

    const stream     = vb.getProcessedStream();
    const audioTrack = vb.getRawAudioTrack();

    // Tercihi kaydet
    const pref = {
      mode       : vb.mode,
      blurLevel  : vb.blurLevel,
      bgUrl      : vb.mode === 'image' && vb.bgImage ? document.querySelector('#vbgBgGrid .vbg-bg-card.selected')?.dataset.bg : null,
    };
    if (!pref.bgUrl?.startsWith('blob:')) savePreference(pref); // blob URL'ler yenilenince geçersiz

    // Custom event ile dışarıya bildir
    document.dispatchEvent(new CustomEvent('vbg:apply', {
      detail: { stream, audioTrack, mode: vb.mode, blurLevel: vb.blurLevel }
    }));

    // Modalı kapat
    bootstrap.Modal.getInstance(document.getElementById('virtualBgModal'))?.hide();
  });

  // ── Tercih uygulama ───────────────────────────────────────────────────

  function applyPreference(pref) {
    if (pref.mode === 'blur' && pref.blurLevel) {
      document.querySelectorAll('.vbg-blur-card').forEach(c => c.classList.remove('selected'));
      document.querySelector(`[data-blur="${pref.blurLevel}"]`)?.classList.add('selected');
      vb.setMode('blur');
      vb.setBlurLevel(pref.blurLevel);
    } else if (pref.mode === 'image' && pref.bgUrl) {
      vb.setBackground(pref.bgUrl).then(() => {
        const card = document.querySelector(`[data-bg="${CSS.escape(pref.bgUrl)}"]`);
        deselectAllBg();
        card?.classList.add('selected');
      }).catch(() => {});
    }
  }

  // ── Yardımcı: HTML escape ─────────────────────────────────────────────

  function escHtml(str) {
    return String(str).replace(/&/g,'&amp;').replace(/</g,'&lt;')
                      .replace(/>/g,'&gt;').replace(/"/g,'&quot;');
  }

  // ── Global erişim (ders odası entegrasyonu için) ───────────────────────

  window.VirtualBgModal = {
    getVB     : () => vb,
    isReady   : () => isReady,
    stopCamera: () => { if (vb) { vb.stop(); vb = null; isReady = false; } },
  };

})();
</script>
