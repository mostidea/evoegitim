/**
 * virtual-bg-room.js
 * Ders odası sayfaları için VirtualBackground entegrasyon katmanı.
 *
 * Bu dosya virtual-bg.js'den SONRA yüklenmeli.
 *
 * Kullanım — mevcut getUserMedia çağrısını şununla değiştirin:
 *
 *   // Eski:
 *   const stream = await navigator.mediaDevices.getUserMedia({ video: true, audio: true });
 *
 *   // Yeni:
 *   const stream = await VirtualBgRoom.getStream();
 *   // Artık stream video = işlenmiş kamera, ses = ham mikrofon
 *
 * Ders sırasında arka planı değiştirmek için "Arka Plan" butonu:
 *   VirtualBgRoom.showSettings();
 *
 * Ders bitince temizle:
 *   VirtualBgRoom.destroy();
 */

const VirtualBgRoom = (() => {

    let _vb    = null;
    let _ready = false;

    // localStorage'dan önceki tercihi oku
    function _loadPref() {
        try { return JSON.parse(localStorage.getItem('vbg_preference') || 'null'); }
        catch { return null; }
    }

    async function _applyPref(vb, pref) {
        if (!pref) return;
        if (pref.mode === 'blur' && pref.blurLevel) {
            vb.setMode('blur');
            vb.setBlurLevel(pref.blurLevel);
        } else if (pref.mode === 'image' && pref.bgUrl && !pref.bgUrl.startsWith('blob:')) {
            await vb.setBackground(pref.bgUrl).catch(() => {});
        }
    }

    /**
     * Kamerayı başlat ve varsa kaydedilmiş arka plan tercihini uygula.
     * Döndürülen stream'in video track'i işlenmiş kaynaktır.
     * Ses için ayrıca getUserMedia({ audio:true }) çağrısı yapın veya
     * dönen ses track'ini kullanın.
     *
     * @returns {Promise<MediaStream>}  video + audio birleşik stream
     */
    async function getStream({ width = 640, height = 480, fps = 20 } = {}) {

        // Tarayıcı desteği yoksa doğrudan raw stream dön
        const supported = typeof VirtualBackground !== 'undefined'
            && await VirtualBackground.isSupported().catch(() => false);

        if (!supported) {
            return navigator.mediaDevices.getUserMedia({ video: true, audio: true });
        }

        _vb = new VirtualBackground({
            width, height, fps,
            onError: (err) => console.warn('[VBG Room]', err.message),
        });

        await _vb.init();
        _ready = true;

        const pref = _loadPref();
        await _applyPref(_vb, pref);

        // İşlenmiş video + ham ses birleştir
        const processedStream = _vb.getProcessedStream();
        const audioTrack      = await _getRawAudioTrack();

        const combined = new MediaStream([
            ...processedStream.getVideoTracks(),
            ...(audioTrack ? [audioTrack] : []),
        ]);

        // "vbg:apply" eventi gelirse (ders sırasında değiştirme) stream'i güncelle
        document.addEventListener('vbg:apply', (e) => {
            const newVideoTrack = e.detail.stream?.getVideoTracks()[0];
            if (!newVideoTrack) return;
            // Mevcut WebRTC sender'larını güncelle
            document.dispatchEvent(new CustomEvent('vbg:room:track-changed', {
                detail: { track: newVideoTrack }
            }));
        });

        return combined;
    }

    async function _getRawAudioTrack() {
        try {
            const audioStream = await navigator.mediaDevices.getUserMedia({ audio: true, video: false });
            return audioStream.getAudioTracks()[0] ?? null;
        } catch {
            return null;
        }
    }

    /**
     * Ders sırasında arka plan ayarları modalını aç.
     * Sayfada virtual-bg-modal.php include edilmiş olmalı.
     */
    function showSettings() {
        if (typeof bootstrap !== 'undefined') {
            const modalEl = document.getElementById('virtualBgModal');
            if (modalEl) {
                new bootstrap.Modal(modalEl).show();
            }
        }
    }

    /**
     * Kamerayı ve VB işlemeyi durdur (ders bitince çağır).
     */
    function destroy() {
        if (_vb) { _vb.stop(); _vb = null; _ready = false; }
    }

    function isReady() { return _ready; }

    return { getStream, showSettings, destroy, isReady };

})();
