/**
 * VirtualBackground v1.0
 * Gerçek zamanlı kamera arka plan işleme motoru
 * MediaPipe Selfie Segmentation kullanır
 *
 * Kullanım:
 *   const vb = new VirtualBackground({ onReady: (instance) => { ... } });
 *   await vb.init();
 *   vb.setMode('blur');
 *   vb.setBlurLevel('strong');
 *   await vb.setBackground('/assets/img/virtual-bg/ebo-kurumsal.jpg');
 *   const stream = vb.getProcessedStream(); // WebRTC peer connection'a ver
 */
class VirtualBackground {
    static BLUR_PX     = { light: 5, medium: 12, strong: 25 };
    static MP_CDN      = 'https://cdn.jsdelivr.net/npm/@mediapipe/selfie_segmentation@0.1.1675465747';
    static MAX_FILE_MB = 10;
    static ALLOWED_TYPES = ['image/jpeg', 'image/png', 'image/webp'];

    constructor({ width = 640, height = 480, fps = 25, onReady = null, onError = null } = {}) {
        this.width  = width;
        this.height = height;
        this.fps    = fps;
        this.onReady = onReady;
        this.onError = onError;

        this.mode      = 'none';    // 'none' | 'blur' | 'image'
        this.blurLevel = 'medium';  // 'light' | 'medium' | 'strong'
        this.bgImage   = null;      // HTMLImageElement

        this._running          = false;
        this._segmentation     = null;
        this._rawStream        = null;
        this._processedStream  = null;
        this._latestMask       = null;
        this._latestFrame      = null;

        this._video = Object.assign(document.createElement('video'), {
            autoplay: true, playsInline: true, muted: true
        });

        // Ana çıkış canvas (processedStream buradan alınır)
        this._canvas = document.createElement('canvas');
        this._canvas.width  = width;
        this._canvas.height = height;
        this._ctx = this._canvas.getContext('2d', { alpha: false });

        // Kişi maskesi için geçici canvas
        this._fgCanvas = document.createElement('canvas');
        this._fgCanvas.width  = width;
        this._fgCanvas.height = height;
        this._fgCtx = this._fgCanvas.getContext('2d');
    }

    // ─── Başlatma ───────────────────────────────────────────────────────────

    async init() {
        try {
            await this._loadSDK();
            await this._openCamera();
            this._initSegmentation();
            this._running = true;
            this._processedStream = this._canvas.captureStream(this.fps);
            this._loop();
            this.onReady?.(this);
        } catch (err) {
            this.onError?.(err);
            throw err;
        }
    }

    async _loadSDK() {
        if (window.SelfieSegmentation) return;
        await new Promise((resolve, reject) => {
            const s = document.createElement('script');
            s.src         = `${VirtualBackground.MP_CDN}/selfie_segmentation.js`;
            s.crossOrigin = 'anonymous';
            s.onload  = resolve;
            s.onerror = () => reject(new Error('MediaPipe SDK yüklenemedi. İnternet bağlantınızı kontrol edin.'));
            document.head.appendChild(s);
        });
    }

    async _openCamera() {
        this._rawStream = await navigator.mediaDevices.getUserMedia({
            video: {
                width:     { ideal: this.width },
                height:    { ideal: this.height },
                frameRate: { ideal: this.fps },
                facingMode: 'user'
            },
            audio: false
        });
        this._video.srcObject = this._rawStream;
        await new Promise(r => { this._video.onloadedmetadata = r; });
        await this._video.play();
    }

    _initSegmentation() {
        this._segmentation = new SelfieSegmentation({
            locateFile: f => `${VirtualBackground.MP_CDN}/${f}`
        });
        this._segmentation.setOptions({ modelSelection: 1 });
        this._segmentation.onResults(results => {
            this._latestMask  = results.segmentationMask;
            this._latestFrame = results.image;
            this._draw();
        });
    }

    _loop() {
        if (!this._running) return;
        this._segmentation.send({ image: this._video })
            .catch(() => {})
            .finally(() => { requestAnimationFrame(() => this._loop()); });
    }

    // ─── Rendering ───────────────────────────────────────────────────────────

    _draw() {
        const ctx   = this._ctx;
        const w     = this._canvas.width;
        const h     = this._canvas.height;
        const frame = this._latestFrame;
        const mask  = this._latestMask;

        ctx.clearRect(0, 0, w, h);

        if (this.mode === 'none' || !mask || !frame) {
            ctx.drawImage(frame || this._video, 0, 0, w, h);
            return;
        }

        // 1. Arka planı çiz
        if (this.mode === 'blur') {
            const blurPx = VirtualBackground.BLUR_PX[this.blurLevel] || 12;
            const pad    = blurPx * 2;
            ctx.filter = `blur(${blurPx}px)`;
            ctx.drawImage(frame, -pad, -pad, w + pad * 2, h + pad * 2);
            ctx.filter = 'none';
        } else if (this.mode === 'image' && this.bgImage) {
            const crop = this._coverCrop(this.bgImage, w, h);
            ctx.drawImage(this.bgImage, crop.sx, crop.sy, crop.sw, crop.sh, 0, 0, w, h);
        } else {
            ctx.drawImage(frame, 0, 0, w, h);
        }

        // 2. Maske ile kişiyi arka plandan ayır ve üste çiz
        const fgCtx = this._fgCtx;
        fgCtx.clearRect(0, 0, w, h);
        fgCtx.drawImage(mask, 0, 0, w, h);
        fgCtx.globalCompositeOperation = 'source-in';
        fgCtx.drawImage(frame, 0, 0, w, h);
        fgCtx.globalCompositeOperation = 'source-over';

        ctx.drawImage(this._fgCanvas, 0, 0);
    }

    _coverCrop(img, targetW, targetH) {
        const ir = img.naturalWidth / img.naturalHeight;
        const tr = targetW / targetH;
        let sw, sh, sx = 0, sy = 0;
        if (ir > tr) {
            sh = img.naturalHeight;
            sw = sh * tr;
            sx = (img.naturalWidth - sw) / 2;
        } else {
            sw = img.naturalWidth;
            sh = sw / tr;
            sy = (img.naturalHeight - sh) / 2;
        }
        return { sx, sy, sw, sh };
    }

    // ─── Public API ──────────────────────────────────────────────────────────

    /**
     * Modu ayarla: 'none' | 'blur' | 'image'
     */
    setMode(mode) {
        this.mode = ['none', 'blur', 'image'].includes(mode) ? mode : 'none';
    }

    /**
     * Bulanıklık seviyesi: 'light' | 'medium' | 'strong'
     */
    setBlurLevel(level) {
        if (VirtualBackground.BLUR_PX[level] !== undefined) this.blurLevel = level;
    }

    /**
     * URL'den arka plan görselini yükle ve uygula
     */
    async setBackground(url) {
        return new Promise((resolve, reject) => {
            const img = new Image();
            img.crossOrigin = 'anonymous';
            img.onload  = () => { this.bgImage = img; this.mode = 'image'; resolve(); };
            img.onerror = () => reject(new Error('Arka plan görseli yüklenemedi: ' + url));
            img.src = url;
        });
    }

    /**
     * Kullanıcının bilgisayarından seçtiği dosyayı blob URL olarak ayarla
     */
    async setBackgroundFromFile(file) {
        if (!VirtualBackground.ALLOWED_TYPES.includes(file.type)) {
            throw new Error('Desteklenmeyen dosya formatı. JPG, PNG veya WEBP kullanın.');
        }
        if (file.size > VirtualBackground.MAX_FILE_MB * 1024 * 1024) {
            throw new Error(`Dosya boyutu ${VirtualBackground.MAX_FILE_MB}MB sınırını aşıyor.`);
        }
        const url = URL.createObjectURL(file);
        await this.setBackground(url);
        return url;
    }

    /**
     * Arka planı kaldır, normal kamera göster
     */
    removeBackground() { this.mode = 'none'; this.bgImage = null; }

    /**
     * İşlenmiş video akışını döndür (WebRTC'ye verilecek)
     * Ses için getRawAudioTrack() kullanın
     */
    getProcessedStream() { return this._processedStream; }

    /**
     * Ham kameranın ses parçasını döndür
     */
    getRawAudioTrack() {
        return this._rawStream?.getAudioTracks()[0] ?? null;
    }

    /**
     * Önizleme için canvas elementini döndür
     */
    getPreviewCanvas() { return this._canvas; }

    /**
     * Kamerayı ve işlemeyi durdur
     */
    stop() {
        this._running = false;
        this._rawStream?.getTracks().forEach(t => t.stop());
        this._segmentation?.close().catch(() => {});
    }

    destroy() {
        this.stop();
    }

    // ─── Statik yardımcı ─────────────────────────────────────────────────────

    /**
     * Tarayıcının sanal arka plan özelliğini destekleyip desteklemediğini kontrol et
     */
    static async isSupported() {
        try {
            const stream = await navigator.mediaDevices.getUserMedia({ video: true });
            stream.getTracks().forEach(t => t.stop());
            return !!(window.HTMLCanvasElement && CanvasRenderingContext2D);
        } catch {
            return false;
        }
    }
}
