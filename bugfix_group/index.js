let micOn = false;
let camOn = false;
let screenShareExpanded = false;
let _vbTrackActive = false; // custom track (VB canvas) yayında mı?
let screenClient = null;    // ekran paylaşımı için ayrı Agora client
let _pipWindow   = null;    // Document Picture-in-Picture penceresi

$(document).ready(function () {
  $("#btn-enable-mic, #btn-disable-mic").click(async function () {
    if (!micOn) {
      await enableMic();
      micOn = true;
      $("#btn-enable-mic").hide();
      $("#btn-disable-mic").show();
    } else {
      disableMic();
      micOn = false;
      $("#btn-enable-mic").show();
      $("#btn-disable-mic").hide();
    }
  });

  $("#btn-enable-cam, #btn-disable-cam").click(async function () {
    if (!camOn) {
      await enableCamera();
      camOn = true;
      $("#btn-enable-cam").hide();
      $("#btn-disable-cam").show();
    } else {
      disableCamera();
      camOn = false;
      $("#btn-enable-cam").show();
      $("#btn-disable-cam").hide();
    }
  });
});

// fix for delayed render/play issues
function safePlay(track, elementId) {
  setTimeout(() => {
    try {
      track.play(elementId);
    } catch (e) {
      console.warn("Play failed:", e);
    }
  }, 300);
}

window.enableCamera = async function () {
  if (!hasJoined) return alert("Bağlantı tamamlanmadan kamera açılamaz.");
  if (!localTracks.videoTrack) {
    localTracks.videoTrack = await AgoraRTC.createCameraVideoTrack();
    await client.publish([localTracks.videoTrack]);
    addLocalStreamBox();
    safePlay(localTracks.videoTrack, "local-stream");
  } else {
    localTracks.videoTrack.setEnabled(true);
  }
};

window.disableCamera = function () {
  if (localTracks.videoTrack) {
    localTracks.videoTrack.setEnabled(false);
  }
};

window.enableMic = async function () {
  if (!hasJoined) return alert("Bağlantı tamamlanmadan mikrofon açılamaz.");
  if (!localTracks.audioTrack) {
    localTracks.audioTrack = await AgoraRTC.createMicrophoneAudioTrack();
    await client.publish([localTracks.audioTrack]);
  } else {
    localTracks.audioTrack.setEnabled(true);
  }
};

window.disableMic = function () {
  if (localTracks.audioTrack) {
    localTracks.audioTrack.setEnabled(false);
  }
};

let uid = null;
let client = null;
let localTracks = { videoTrack: null, audioTrack: null, screenTrack: null };
let remoteUsers = {};
let hasJoined = false;
const csrfToken  = window.csrfToken;
const isTeacher  = window.isAdmin ?? false;

// teacher.js yüklenemezse (404 vb.) kritik globaller undefined kalır; ekran paylaşımı kırılmasın
if (typeof window.teacherId === 'undefined') window.teacherId = null;
if (typeof window.roomId    === 'undefined') window.roomId    = null;
if (typeof window.isAdmin   === 'undefined') window.isAdmin   = false;
const APP_ID = "33cbda223ae847f9834ab0aec5290286";
let TOKEN = "007eJxTYHjfsjo2MKH9/Huh45WeWX0Lf1y49M7T1eXJxLBnHG9nThdTYDA2Tk5KSTQyMk5MtTAxT7O0MDZJTDJITE02NbI0MLIwe2CnltEQyMigEmzKzMgAgSA+J0NRfn6urrGhsSEDAwCjBiGk";
let CHANNEL = "";

const nameMap = {};

async function loadParticipants() {
  try {
    const res  = await fetch(`/rooms/src/participants.php?lesson_id=${lessonId}`);
    const data = await res.json();
    data.forEach(p => { nameMap[p.id] = p.name; });
  } catch (e) {
    console.warn('Katılımcı isimleri yüklenemedi', e);
  }
}

async function init() {
  await loadParticipants();

  const urlParams = new URLSearchParams(window.location.search);
  let isAdmin = window.isAdmin;
  let roomId  = window.roomId;

  if (!roomId) {
    console.error('[init] window.roomId tanımsız — oda başlatılamıyor.');
    return;
  }

  CHANNEL = `room-${roomId}`;
  client = AgoraRTC.createClient({ mode: "rtc", codec: "vp8" });

  setInterval(checkRoomStatus, 2000);
  checkRoomStatus();
}

document.addEventListener('DOMContentLoaded', () => {
  init().catch(err => console.error('Init hatası:', err));
});

function createVideoBox(id, labelText) {
  const videoBox = document.createElement("div");
  videoBox.id = id;
  videoBox.className = "video-box";

  const label = document.createElement("div");
  label.className = "remote-label";
  label.innerText = labelText;
  videoBox.appendChild(label);

  const fullscreenControl = document.createElement("div");
  fullscreenControl.className = "fullscreen-control";

  const fullscreenBtn = document.createElement("button");
  fullscreenBtn.innerHTML = '<i class="fas fa-expand"></i>';
  const exitBtn = document.createElement("button");
  exitBtn.innerHTML = '<i class="fas fa-compress"></i>';
  exitBtn.style.display = "none";

  fullscreenBtn.onclick = () => {
    makeFullscreen(videoBox);
    fullscreenBtn.style.display = "none";
    exitBtn.style.display = "inline-block";
  };
  exitBtn.onclick = () => {
    exitFullscreen();
    exitBtn.style.display = "none";
    fullscreenBtn.style.display = "inline-block";
  };

  fullscreenControl.appendChild(fullscreenBtn);
  fullscreenControl.appendChild(exitBtn);
  videoBox.appendChild(fullscreenControl);

  return videoBox;
}

function renderRemote(uid) {
  if (document.getElementById(`player-${uid}`)) return;
  const isScreenUid   = typeof uid === 'string' && uid.endsWith('_screen');
  const displayName   = isScreenUid ? 'Ekran Paylaşımı' : (nameMap[uid] || `Kullanıcı ${uid}`);
  const box = createVideoBox(`player-${uid}`, displayName);

  // Ekran paylaşımı kutusuna kontrol butonları ekleme
  if (isAdmin && !isScreenUid) {
    const controls = document.createElement("div");
    controls.className = "admin-controls";

    const muteBtn = document.createElement("button");
    muteBtn.innerHTML = '<i class="fas fa-microphone"></i>';
    muteBtn.title = 'Mikrofonu Kapat';
    muteBtn.dataset.muted = '0';
    muteBtn.onclick = () => {
      if (muteBtn.dataset.muted === '0') {
        sendControl(uid, 'mute');
        muteBtn.dataset.muted = '1';
        muteBtn.innerHTML = '<i class="fas fa-microphone-slash"></i>';
        muteBtn.title = 'Mikrofonu Aç';
        muteBtn.style.opacity = '0.5';
      } else {
        sendControl(uid, 'unmute');
        muteBtn.dataset.muted = '0';
        muteBtn.innerHTML = '<i class="fas fa-microphone"></i>';
        muteBtn.title = 'Mikrofonu Kapat';
        muteBtn.style.opacity = '1';
      }
    };

    const camBtn = document.createElement("button");
    camBtn.innerHTML = '<i class="fas fa-video"></i>';
    camBtn.title = 'Kamerayı Kapat';
    camBtn.dataset.hidden = '0';
    camBtn.onclick = () => {
      if (camBtn.dataset.hidden === '0') {
        sendControl(uid, 'hideCam');
        camBtn.dataset.hidden = '1';
        camBtn.innerHTML = '<i class="fas fa-video-slash"></i>';
        camBtn.title = 'Kamerayı Aç';
        camBtn.style.opacity = '0.5';
      } else {
        sendControl(uid, 'showCam');
        camBtn.dataset.hidden = '0';
        camBtn.innerHTML = '<i class="fas fa-video"></i>';
        camBtn.title = 'Kamerayı Kapat';
        camBtn.style.opacity = '1';
      }
    };

    controls.appendChild(muteBtn);
    controls.appendChild(camBtn);
    box.appendChild(controls);
  }

  document.getElementById("video-grid").appendChild(box);
  updateGridLayout();
}

function addLocalStreamBox() {
  const existing = document.getElementById("local-stream");
  if (existing) existing.remove();

  const localBox = createVideoBox("local-stream", window.myDisplayName);
  document.getElementById("video-grid").prepend(localBox);
  updateGridLayout();

  if (window._vbInstance) {
    const canvas = window._vbInstance.getPreviewCanvas();
    canvas.style.cssText = 'width:100%;height:100%;object-fit:cover;display:block;border-radius:inherit;position:absolute;inset:0;';
    localBox.style.position = 'relative';
    localBox.appendChild(canvas);
  }
}

async function handleUserPublished(user, mediaType) {
  // Sadece ÖĞRETMEN kendi screenClient'ına subscribe olmaz; öğrenciler normal subscribe eder
  if (isAdmin && typeof user.uid === 'string' && user.uid.endsWith('_screen')) return;
  await client.subscribe(user, mediaType);
  renderRemote(user.uid);
  const container = document.getElementById(`player-${user.uid}`);
  if (mediaType === "video") user.videoTrack.play(container);
  if (mediaType === "audio") user.audioTrack.play();
}

function handleUserUnpublished(user, mediaType) {
  const player = document.getElementById(`player-${user.uid}`);
  if (player && mediaType === "video") {
    player.querySelectorAll('video, canvas').forEach(el => el.remove());
    player.style.background = "#000";
  }
}

window.startRoom = async function () {
  await fetch(`start-room.php?roomid=${roomId}`);
  await startCall();
  await enableCamera();
  await enableMic();
};

let lessonId = window.lessonId;
let endTime  = new Date(window.endTime);

let hasLoggedJoin = false;

async function logJoin() {
  const role = isAdmin ? 'teacher' : 'student';
  await fetch(`/rooms/src/join.php?lesson_id=${lessonId}&role=${role}`);
}

let presenceInterval = null;

async function pingPresence() {
  try {
    await fetch('/rooms/src/signal.php', {
      method: 'POST',
      headers: {'Content-Type':'application/x-www-form-urlencoded'},
      body: `lesson_id=${lessonId}`
    });
  } catch (e) {
    console.warn('Presence ping failed:', e);
  }
}

function startPresencePings() {
  pingPresence();

  function schedule() {
    const now = Date.now();
    const msLeft = endTime.getTime() - now;
    const interval = msLeft <= 10_000 ? 5_000 : 15_000;
    presenceInterval = setTimeout(() => {
      pingPresence();
      if (msLeft > 0) schedule();
    }, interval);
  }

  schedule();
}

window.addEventListener('beforeunload', () => {
  navigator.sendBeacon(
    '/rooms/src/signal.php',
    new URLSearchParams({ lesson_id: lessonId })
  );
  clearTimeout(presenceInterval);
});

async function checkRoomStatus() {
  try {
    const res = await fetch(`/rooms/src/room-status.php?roomid=${roomId}&ts=${Date.now()}`);
    const data = await res.json();

    if (data.status === 'started' && !hasJoined) {
      hasJoined = true;

      if (!hasLoggedJoin) {
        await logJoin();
        hasLoggedJoin = true;
      }

      await startCall();
      startPresencePings();
    }

    if (data.status === 'ended') {
      window.location.reload();
    }
  } catch (err) {
    console.warn("Oda durumu okunamadı:", err);
  }
}

async function startCall() {
  const resp = await fetch(`/rooms/token.php?channel=${encodeURIComponent(CHANNEL)}&uid=${uid}`);
  const { appId, channelName, uid: returnedUid, token } = await resp.json();

  // Event handler'ları join'dan ÖNCE kaydet (Agora kuralı)
  client.on("user-published", handleUserPublished);
  client.on("user-unpublished", handleUserUnpublished);
  client.on("user-joined", user => {
    if (!document.getElementById(`player-${user.uid}`)) renderRemote(user.uid);
  });
  client.on("user-left", user => {
    const player = document.getElementById(`player-${user.uid}`);
    if (player) player.remove();
    updateGridLayout();
    delete remoteUsers[user.uid];
  });

  uid = await client.join(appId, channelName, token, returnedUid);

  // Öğrenci: HTTP polling ile admin komutlarını al
  if (!isAdmin) {
    setInterval(async () => {
      if (!hasJoined) return;
      try {
        const res = await fetch(`/rooms/src/control-check.php?room_id=${encodeURIComponent(window.roomId)}&uid=${encodeURIComponent(uid)}`);
        const data = await res.json();
        if (data.action === 'mute'        && localTracks.audioTrack)  localTracks.audioTrack.setEnabled(false);
        if (data.action === 'unmute'      && localTracks.audioTrack)  localTracks.audioTrack.setEnabled(true);
        if (data.action === 'hideCam'     && localTracks.videoTrack)  localTracks.videoTrack.setEnabled(false);
        if (data.action === 'showCam'     && localTracks.videoTrack)  localTracks.videoTrack.setEnabled(true);
        if (data.action === 'screenShare' && !screenShareExpanded)    { screenShareExpanded = true;  expandScreenShare(); }
        if (data.action === 'stopScreen'  && screenShareExpanded)     { screenShareExpanded = false; collapseScreenShare(); }
      } catch (e) { /* network errors are normal */ }
    }, 1000);
  }

  addLocalStreamBox();
  startVolumeMonitoring();
  setupPiPVisibility();
}

function startVolumeMonitoring() {
  if (document.getElementById('speaking-style')) return; // zaten çalışıyor

  const style = document.createElement('style');
  style.id = 'speaking-style';
  style.textContent = `
    .video-box { transition: box-shadow 0.15s ease; }
    .video-box.speaking {
      box-shadow: 0 0 0 3px #22c55e, 0 0 16px rgba(34,197,94,0.4) !important;
    }
  `;
  document.head.appendChild(style);

  const THRESHOLD = 0.06; // gürültüyü filtrele (0.0–1.0)

  setInterval(() => {
    // Yerel mikrofon
    const localLevel = localTracks.audioTrack?.getVolumeLevel() ?? 0;
    document.getElementById('local-stream')?.classList.toggle('speaking', localLevel > THRESHOLD);

    // Uzak kullanıcılar (ekran paylaşımı UID'si hariç)
    client.remoteUsers.forEach(user => {
      if (typeof user.uid === 'string' && user.uid.endsWith('_screen')) return;
      const level = user.audioTrack?.getVolumeLevel() ?? 0;
      document.getElementById(`player-${user.uid}`)?.classList.toggle('speaking', level > THRESHOLD);
    });
  }, 200);
}

window.enableCamera = async function () {
  if (!hasJoined) return alert("Bağlantı tamamlanmadan kamera açılamaz.");
  if (!localTracks.videoTrack) {
    const vbTrack = await getVirtualBgVideoTrack();
    if (vbTrack) {
      localTracks.videoTrack = await AgoraRTC.createCustomVideoTrack({ mediaStreamTrack: vbTrack });
    } else {
      localTracks.videoTrack = await AgoraRTC.createCameraVideoTrack();
    }
    await client.publish([localTracks.videoTrack]);
    addLocalStreamBox();
    if (!window._vbInstance) {
      localTracks.videoTrack.play(document.getElementById("local-stream"));
    }
  } else {
    localTracks.videoTrack.setEnabled(true);
  }
};

window.disableCamera = function () {
  if (localTracks.videoTrack) localTracks.videoTrack.setEnabled(false);
};

window.enableMic = async function () {
  if (!hasJoined) return alert("Bağlantı tamamlanmadan mikrofon açılamaz.");
  if (!localTracks.audioTrack) {
    localTracks.audioTrack = await AgoraRTC.createMicrophoneAudioTrack();
    await client.publish([localTracks.audioTrack]);
  } else {
    localTracks.audioTrack.setEnabled(true);
  }
};

window.disableMic = function () {
  if (localTracks.audioTrack) localTracks.audioTrack.setEnabled(false);
};

window.shareScreen = async function () {
  if (!isAdmin || screenClient) return;
  if (!window.teacherId) { console.error('[SS] window.teacherId tanımsız — teacher.js yüklenememiş olabilir.'); return; }

  try {
    // Screen track oluştur (auto: mümkünse sistem sesini de al)
    const tracks = await AgoraRTC.createScreenVideoTrack(
      { optimizationMode: 'detail', encoderConfig: { width: 1280, height: 720, frameRate: 15 } },
      'auto'
    );
    const screenVideoTrack = Array.isArray(tracks) ? tracks[0] : tracks;
    const screenAudioTrack = Array.isArray(tracks) ? tracks[1] : null;

    // Tarayıcının "Paylaşımı Durdur" butonunu yakala
    screenVideoTrack.on('track-ended', () => window.stopScreenShare());

    // Agora kuralı: ekran paylaşımı AYRI client ile yapılır → kamera track'i yerinden edilmez
    screenClient = AgoraRTC.createClient({ mode: 'rtc', codec: 'vp8' });
    // String user account kullan: token.php buildTokenWithUserAccount kullanıyor,
    // numeric uid ile uyuşmaz. teacherId + '_screen' her iki tarafta da bilinir.
    const screenUid = String(window.teacherId) + '_screen';

    const resp = await fetch(`/rooms/screen-token.php?channel=${encodeURIComponent(CHANNEL)}&uid=${encodeURIComponent(screenUid)}`);
    const { appId, channelName, token: screenToken } = await resp.json();

    await screenClient.join(appId, channelName, screenToken, screenUid);
    console.log('[SS] screenClient join başarılı, uid:', screenUid);

    const toPublish = screenAudioTrack ? [screenVideoTrack, screenAudioTrack] : [screenVideoTrack];
    await screenClient.publish(toPublish);
    console.log('[SS] publish başarılı');

    localTracks.screenTrack = screenVideoTrack;
    sendControl(0, 'screenShare');

    // Öğretmen kendi overlay/strip'ini burada açar (polling bloğu sadece öğrencileri kapsar)
    screenShareExpanded = true;
    expandScreenShare();

  } catch (err) {
    console.error('[SS] Ekran paylaşımı hatası:', err);
    if (screenClient) { screenClient.leave().catch(() => {}); screenClient = null; }
    if (localTracks.screenTrack) { localTracks.screenTrack.stop(); localTracks.screenTrack.close(); localTracks.screenTrack = null; }
  }
};

window.stopScreenShare = async function () {
  if (!localTracks.screenTrack) return;

  localTracks.screenTrack.stop();
  localTracks.screenTrack.close();
  localTracks.screenTrack = null;

  if (screenClient) {
    await screenClient.leave();
    screenClient = null;
  }
  // Kamera track'i hiç unpublish edilmedi → tekrar publish gerekmez
  sendControl(0, 'stopScreen');

  // Öğretmen kendi overlay/strip'ini kapatır (polling bloğu sadece öğrencileri kapsar)
  screenShareExpanded = false;
  collapseScreenShare();
};

window.leaveCall = async function () {
  await fetch(`stop-room.php?roomid=${roomId}`);
  for (let track of Object.values(localTracks)) {
    if (track) {
      track.stop();
      track.close();
    }
  }
  if (window._vbInstance) {
    window._vbInstance.stop();
    window._vbInstance = null;
  }
  if (screenClient) {
    await screenClient.leave();
    screenClient = null;
  }
  await client.leave();
  document.getElementById("video-grid").innerHTML = "";
};

window.muteAll = function () {
  Object.values(client.remoteUsers).forEach(u => sendControl(u.uid, 'mute'));
};

window.hideAllCams = function () {
  Object.values(client.remoteUsers).forEach(u => sendControl(u.uid, 'hideCam'));
};

function sendControl(targetUid, action) {
  const body = new URLSearchParams({ room_id: window.roomId, target_uid: targetUid, action });
  fetch('/rooms/src/control-send.php', { method: 'POST', body }).catch(console.error);
}

function expandScreenShare() {
  if (document.getElementById('ss-overlay')) return;

  const screenUid = String(window.teacherId) + '_screen';
  let screenBox = document.getElementById('player-' + screenUid);

  // Öğretmen kendi screen stream'ini subscribe etmez; lokal track'ten önizleme kutusu oluştur
  if (!screenBox) {
    if (isAdmin && localTracks.screenTrack) {
      screenBox = createVideoBox('player-' + screenUid, 'Ekran Paylaşımı');
      document.body.appendChild(screenBox);
      localTracks.screenTrack.play(screenBox);
    } else {
      setTimeout(expandScreenShare, 500);
      return;
    }
  }

  const grid = document.getElementById('video-grid');

  const overlay = document.createElement('div');
  overlay.id = 'ss-overlay';
  Object.assign(overlay.style, {
    position: 'fixed', inset: '0', zIndex: '9000',
    background: '#000', overflow: 'hidden'
  });
  document.body.appendChild(overlay);

  Object.assign(screenBox.style, { width: '100%', height: '100%', borderRadius: '0' });
  screenBox.dataset.screenExpanded = '1';
  overlay.appendChild(screenBox);

  // Kamera şeridi — her zaman sağda, dikey, kaydırılabilir
  const strip = document.createElement('div');
  strip.id = 'ss-strip';
  Object.assign(strip.style, {
    position: 'fixed', top: '16px', bottom: '16px', right: '16px',
    zIndex: '9001', display: 'flex', flexDirection: 'column',
    gap: '8px', overflowY: 'auto', overflowX: 'hidden'
  });
  document.body.appendChild(strip);

  // "Mini Pencereye Geç" butonu — yetki ve API kontrolüne bakılmaksızın her zaman oluştur
  const pipBtn = document.createElement('button');
  pipBtn.id = 'btn-pip-trigger';
  pipBtn.title = 'Kameraları yüzen mini pencerede göster';
  pipBtn.innerHTML = `<svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink:0"><rect x="2" y="3" width="20" height="14" rx="2"/><rect x="12" y="11" width="10" height="7" rx="1" fill="currentColor" stroke="none"/></svg><span>Mini Pencereye Geç</span>`;
  Object.assign(pipBtn.style, {
    display: 'flex', alignItems: 'center', justifyContent: 'center', gap: '6px',
    width: '100%', padding: '8px 12px', marginBottom: '8px',
    background: 'linear-gradient(135deg,#2563eb,#1d4ed8)',
    color: '#fff', border: 'none', borderRadius: '8px',
    fontSize: '12px', fontWeight: '600', cursor: 'pointer',
    flexShrink: '0', boxShadow: '0 2px 8px rgba(37,99,235,0.5)',
    letterSpacing: '0.02em'
  });
  pipBtn.addEventListener('mouseenter', () => { pipBtn.style.background = 'linear-gradient(135deg,#1d4ed8,#1e40af)'; });
  pipBtn.addEventListener('mouseleave', () => { pipBtn.style.background = 'linear-gradient(135deg,#2563eb,#1d4ed8)'; });
  pipBtn.addEventListener('click', () => enterPiP());
  strip.appendChild(pipBtn);

  grid.querySelectorAll('.video-box').forEach(box => {
    Object.assign(box.style, {
      width: '180px', height: '101px',
      borderRadius: '8px', flexShrink: '0',
      boxShadow: '0 2px 12px rgba(0,0,0,0.7)'
    });
    strip.appendChild(box);
  });
}

function collapseScreenShare() {
  exitPiP(); // strip PiP'teyse önce ana belgeye geri al

  const overlay = document.getElementById('ss-overlay');
  const strip   = document.getElementById('ss-strip');
  const grid    = document.getElementById('video-grid');

  const CLEAR = ['width', 'height', 'borderRadius', 'flexShrink', 'boxShadow', 'position'];

  // Kamera kutularını (strip içindekiler) grid'e geri taşı
  try {
    if (strip) {
      strip.querySelectorAll('.video-box').forEach(box => {
        CLEAR.forEach(p => { box.style[p] = ''; });
        delete box.dataset.screenExpanded;
        grid.appendChild(box);
      });
    }
  } catch (e) { console.warn('[collapse] strip temizleme hatası:', e); }

  // Overlay içindeki ekran paylaşımı kutusunu kaldır (track durduruldu, grid'e taşıma)
  try {
    if (overlay) {
      overlay.querySelectorAll('.video-box').forEach(box => box.remove());
    }
  } catch (e) { console.warn('[collapse] overlay temizleme hatası:', e); }

  try { if (overlay) overlay.remove(); } catch (e) {}
  try { if (strip)   strip.remove();   } catch (e) {}

  try { updateGridLayout(); } catch (e) { console.warn('[collapse] updateGridLayout hatası:', e); }
}

// ── Document Picture-in-Picture — kamera şeridini yüzen pencereye taşır ──────

function setupPiPVisibility() {
  // Tarayıcıya geri dönüldüğünde PiP açıksa otomatik kapat (user gesture gerektirmez)
  document.addEventListener('visibilitychange', () => {
    if (!screenShareExpanded) return;
    if (!document.hidden) exitPiP();
  });
}

async function enterPiP() {
  if (_pipWindow) return;
  const strip = document.getElementById('ss-strip');
  if (!strip) return;

  if (!window.documentPictureInPicture) {
    console.warn('[PiP] Document Picture-in-Picture bu tarayıcıda desteklenmiyor.');
    return;
  }

  try {
    const boxCount = strip.querySelectorAll('.video-box').length || 1;
    const pipWin = await window.documentPictureInPicture.requestWindow({
      width: 216,
      height: Math.min(boxCount * 121 + 24, window.screen.availHeight - 80)
    });
    _pipWindow = pipWin;

    // Ana belgedeki CSS stillerini PiP penceresine kopyala
    [...document.styleSheets].forEach(sheet => {
      try {
        const cssText = [...sheet.cssRules].map(r => r.cssText).join('\n');
        const el = pipWin.document.createElement('style');
        el.textContent = cssText;
        pipWin.document.head.appendChild(el);
      } catch (_) {} // cross-origin sheet → atla
    });

    // PiP penceresi içi düzenleme stilleri
    const override = pipWin.document.createElement('style');
    override.textContent = `
      html,body{margin:0;padding:0;background:#111;overflow:hidden;width:100%;height:100%;}
      #ss-strip{position:static!important;top:auto!important;bottom:auto!important;right:auto!important;
        display:flex!important;flex-direction:column;gap:8px;padding:8px;box-sizing:border-box;
        width:100%;height:100%;overflow-y:auto;overflow-x:hidden;}
      .video-box{width:100%!important;height:auto!important;aspect-ratio:16/9;border-radius:8px;}
    `;
    pipWin.document.head.appendChild(override);

    // Strip'i PiP penceresine taşı
    pipWin.document.body.appendChild(strip);

    // Kullanıcı PiP penceresini elle kapattığında strip'i geri al
    pipWin.addEventListener('pagehide', () => {
      if (_pipWindow === pipWin) _pipWindow = null;
      try {
        const s = pipWin.document.getElementById('ss-strip');
        if (s) _moveStripToMain(s);
      } catch (_) {}
    });

  } catch (err) {
    console.warn('[PiP] Pencere açılamadı:', err.message);
    console.error('[PiP Hata Detayı]:', err);
    _pipWindow = null;
  }
}

function exitPiP() {
  if (!_pipWindow) return;
  const pipWin = _pipWindow;
  _pipWindow = null;

  // Strip'i kapat öncesi ana belgeye geri al; pagehide sonradan tetiklenirse strip artık ana belgede
  try {
    const s = pipWin.document.getElementById('ss-strip');
    if (s) _moveStripToMain(s);
  } catch (_) {}

  try { pipWin.close(); } catch (_) {}
}

function _moveStripToMain(strip) {
  // Zaten ana belgede ise dokunma
  if (strip.ownerDocument === document) return;

  // Ekran paylaşımı hâlâ aktifse overlay içine, değilse body'ye
  const target = document.getElementById('ss-overlay') || document.body;

  // Pozisyon stillerini sıfırla (PiP override'ını temizle)
  Object.assign(strip.style, {
    position: 'fixed', top: '16px', bottom: '16px', right: '16px',
    height: '', width: '', padding: '', background: '', backdropFilter: ''
  });
  strip.querySelectorAll('.video-box').forEach(box => {
    Object.assign(box.style, { width: '180px', height: '101px' });
  });

  target.appendChild(strip);
}

function makeFullscreen(el) {
  if (el.requestFullscreen) el.requestFullscreen();
  else if (el.webkitRequestFullscreen) el.webkitRequestFullscreen();
  else if (el.msRequestFullscreen) el.msRequestFullscreen();
}

function exitFullscreen() {
  if (document.exitFullscreen) document.exitFullscreen();
  else if (document.webkitExitFullscreen) document.webkitExitFullscreen();
  else if (document.msExitFullscreen) document.msExitFullscreen();
}

function updateGridLayout() {
  const grid = document.getElementById("video-grid");
  const boxes = grid.querySelectorAll(".video-box");
  const count = boxes.length;
  if (window.innerWidth <= 768) {
    if (count === 2) {
      grid.classList.add("two-person-mobile");
      grid.classList.remove("two-person-mode");
    } else {
      grid.classList.remove("two-person-mobile");
      grid.classList.remove("two-person-mode");
    }
  } else {
    if (count <= 2) {
      grid.classList.add("two-person-mode");
      grid.classList.remove("two-person-mobile");
    } else {
      grid.classList.remove("two-person-mode");
      grid.classList.remove("two-person-mobile");
    }
  }
}

function updateCompleteBtn() {
  if (!isTeacher) return;
  const msLeft = endTime - Date.now();
  const btn = document.getElementById('btn-complete');
  if (msLeft > 0 && msLeft <= 60_000) {
    btn.style.display = 'flex';
  } else {
    btn.style.display = 'none';
  }
}

setInterval(updateCompleteBtn, 1000);

document.getElementById('btn-complete').addEventListener('click', () => {
  Swal.fire({
    title: 'Dersin başarıyla tamamlandığını onaylıyor musunuz?',
    icon: 'question',
    showCancelButton: true,
    confirmButtonText: 'Evet',
    cancelButtonText: 'Hayır'
  }).then(result => {
    if (result.isConfirmed) {
      fetch('/rooms/src/complete.php', {
        method: 'POST',
        headers: {'Content-Type':'application/json'},
        body: JSON.stringify({
          lesson_id: lessonId,
          csrf: csrfToken
        })
      })
      .then(res => res.json())
      .then(data => {
        if (data.ok) {
          Swal.fire('Tamamlandı','Ders başarıyla tamamlandı!','success');
        } else {
          Swal.fire('Hata', data.error || 'Beklenmedik bir hata','error');
        }
      })
      .catch(() => {
        Swal.fire('Hata','Sunucuya ulaşılamadı','error');
      });
    }
  });
});

const endDate = new Date(window.endTime);

function pad2(n) {
  return String(n).padStart(2, '0');
}

function updateCountdown() {
  const now  = Date.now();
  let diff   = endDate.getTime() - now;
  if (diff < 0) diff = 0;

  const hours   = Math.floor(diff / 3600000);
  const minutes = Math.floor(diff / 60000) % 60;
  const seconds = Math.floor(diff / 1000) % 60;

  const formatted =
    (hours > 0 ? pad2(hours) + ':' : '') +
    pad2(minutes) + ':' +
    pad2(seconds);

  const timerEl = document.getElementById('countdown-timer');
  if (timerEl) timerEl.textContent = formatted;

  const container = document.getElementById('countdown');
  if (container) {
    if (diff <= 60000) {
      container.classList.add('warning');
    } else {
      container.classList.remove('warning');
    }
  }
}

document.addEventListener('DOMContentLoaded', () => {
  updateCountdown();
  setInterval(updateCountdown, 1000);
});


// ── Sanal Arka Plan entegrasyonu ─────────────────────────────────────────────

async function getVirtualBgVideoTrack() {
  const pref = (() => {
    try { return JSON.parse(localStorage.getItem('vbg_preference') || 'null'); }
    catch { return null; }
  })();
  if (!pref || pref.mode === 'none') return null;
  if (typeof VirtualBackground === 'undefined') return null;

  const supported = await VirtualBackground.isSupported().catch(() => false);
  if (!supported) return null;

  window._vbInstance = new VirtualBackground({ width: 640, height: 360, fps: 20 });
  await window._vbInstance.init();

  if (pref.mode === 'blur') {
    window._vbInstance.setMode('blur');
    window._vbInstance.setBlurLevel(pref.blurLevel || 'medium');
  } else if (pref.mode === 'image' && pref.bgUrl && !pref.bgUrl.startsWith('blob:')) {
    await window._vbInstance.setBackground(pref.bgUrl).catch(() => {
      window._vbInstance.setMode('none');
    });
  }

  const stream = window._vbInstance.getProcessedStream();
  return stream?.getVideoTracks()[0] ?? null;
}

document.addEventListener('vbg:apply', async (e) => {
  const { stream, mode } = e.detail;
  const localBox = document.getElementById('local-stream');
  if (!client || !hasJoined) return;

  try {
    const newVideoTrack = stream?.getVideoTracks()[0];

    if (mode === 'none' || !newVideoTrack) {
      // ── Durum 1: VB kapalı → saf kameraya dön ──────────────────────────────
      if (localTracks.videoTrack) {
        await client.unpublish([localTracks.videoTrack]);
        localTracks.videoTrack.stop();
        localTracks.videoTrack.close();
      }
      _vbTrackActive = false;
      localTracks.videoTrack = await AgoraRTC.createCameraVideoTrack();
      await client.publish([localTracks.videoTrack]);
      if (localBox) {
        localBox.querySelectorAll('canvas, video').forEach(el => el.remove());
        localBox.style.position = '';
        localTracks.videoTrack.play(localBox);
      }
      window._vbInstance = null;

    } else if (_vbTrackActive && localTracks.videoTrack) {
      // ── Durum 2: VB zaten aktifken arka plan değişti ────────────────────────
      // VB instance aynı → canvas MediaStreamTrack aynı nesne.
      // replaceTrack: Agora bağlantısını kesmeden kaynağı günceller, uzak taraf siyah görmez.
      // stopOldTrack=false: canvas track durdurulmaz (zaten aynı nesne, VB hâlâ kullanıyor).
      await localTracks.videoTrack.replaceTrack(newVideoTrack, false);

      const modalVb = window.VirtualBgModal?.getVB();
      window._vbInstance = modalVb || window._vbInstance;
      if (localBox && window._vbInstance) {
        const canvas = window._vbInstance.getPreviewCanvas();
        localBox.querySelectorAll('canvas, video').forEach(el => el.remove());
        canvas.style.cssText = 'width:100%;height:100%;object-fit:cover;display:block;border-radius:inherit;position:absolute;inset:0;';
        localBox.style.position = 'relative';
        localBox.appendChild(canvas);
      }

    } else {
      // ── Durum 3: Kamera → VB ilk geçiş ─────────────────────────────────────
      // ICustomVideoTrack oluştur (ICustomVideoTrack.setEnabled camera'yı durdurmaz).
      // Eski ICameraVideoTrack gecikmeli kapatılır: canvas track'i değil, güvenli.
      const oldTrack = localTracks.videoTrack;
      const newAgoraTrack = await AgoraRTC.createCustomVideoTrack({ mediaStreamTrack: newVideoTrack });
      if (oldTrack) await client.unpublish([oldTrack]);
      localTracks.videoTrack = newAgoraTrack;
      _vbTrackActive = true;
      await client.publish([localTracks.videoTrack]);
      if (oldTrack) setTimeout(() => { try { oldTrack.stop(); oldTrack.close(); } catch (_) {} }, 1500);

      const modalVb = window.VirtualBgModal?.getVB();
      window._vbInstance = modalVb || window._vbInstance;
      if (localBox && window._vbInstance) {
        const canvas = window._vbInstance.getPreviewCanvas();
        localBox.querySelectorAll('canvas, video').forEach(el => el.remove());
        canvas.style.cssText = 'width:100%;height:100%;object-fit:cover;display:block;border-radius:inherit;position:absolute;inset:0;';
        localBox.style.position = 'relative';
        localBox.appendChild(canvas);
      }
    }
  } catch (err) {
    console.warn('[VBG] Track değiştirme hatası:', err);
  }
});

window.showVirtualBgSettings = function () {
  const modal = document.getElementById('virtualBgModal');
  if (modal) new bootstrap.Modal(modal).show();
};
