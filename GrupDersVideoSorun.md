# Grup Ders Video Odası — Sorun & Çözüm Kaydı

## Sorunlar ve Çözümler

---

### 1. Onaylanan grup dersleri öğrenci panelinde görünmüyordu

**Dosya:** `student/appointment.php`

**Sorun:** Sorgular sadece `appointment.student_id = :sid` koşulunu kullanıyordu. Grup dersler için appointment kaydı olmasa da görünmesi gerekiyordu.

**Çözüm:** `WHERE` koşuluna `groups_quota` subquery'si eklendi:
```php
WHERE (
    appointment.student_id = :sid
    OR (
        appointment.type = 1
        AND appointment.group_id IN (
            SELECT group_id FROM groups_quota WHERE user_id = :sid2 AND status = 1
        )
    )
)
```
Execute: `[':sid' => $student_id, ':sid2' => $student_id]`

**Lokal:** `bugfix_group/appointment.php`

---

### 2. Öğretmen panelinde "Derse Git" butonu yoktu

**Dosya:** `teacher/appointment.php`

**Sorun:** `room_id` kolonu yeni appointment kayıtlarında boş geliyordu. "Derse Git" butonu sadece `room_id` doluysa gösteriliyordu.

**Çözüm:** `room_id` boşsa `group_time_id` fallback olarak kullanılıyor:
```php
$hasRoom = !empty($buyRow["room_id"]);
$roomKey = $hasRoom ? $buyRow["room_id"] : ($buyRow["group_time_id"] ?? '');
$joinUrl = null;
if ($isGroup && !empty($roomKey))
    $joinUrl = "https://evoegitim.com/rooms/call.php?roomid=" . urlencode($roomKey);
elseif (!$isGroup && $hasZoom)
    $joinUrl = !empty($buyRow["zoom_start_url"]) ? $buyRow["zoom_start_url"] : $buyRow["zoom_link"];
```

**Lokal:** `bugfix_group/appointment.php`

---

### 3. Onay anında appointment kaydı oluşturulmuyordu

**Dosya:** `management/student-group-request.php`

**Sorun:** Öğrenci onaylandığında `appointment` tablosuna kayıt eklenmiyordu. `room_id` de boş bırakılıyordu.

**Çözüm:** Onay işleminde gelecek `group_time` seansları için otomatik appointment oluşturuldu. `room_id = group_time_id` olarak set edildi:
```php
$gtStmt = $db->prepare('SELECT * FROM group_time WHERE group_id = ? AND end_date > NOW() ORDER BY start_date ASC');
$gtStmt->execute([$groupId]);
$groupTimes = $gtStmt->fetchAll(PDO::FETCH_ASSOC);
$firstLesson = 0;
foreach ($groupTimes as $gt) {
    $dupCheck = $db->prepare('SELECT id FROM appointment WHERE student_id = ? AND group_time_id = ?');
    $dupCheck->execute([$rec['user_id'], $gt['id']]);
    if ($dupCheck->fetch()) { $firstLesson = 1; continue; }
    $ins = $db->prepare('INSERT INTO appointment
        (student_id, teacher_id, lesson_id, group_id, group_time_id, room_id,
         first_lesson, start_date, end_date, status, credit, total_credit,
         type, revise, teacher_report, student_report, income)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 0, ?, ?, 1, 0, 0, 0, 0)');
    $ins->execute([
        $rec['user_id'], $group['teacher_id'], $group['lesson_id'],
        $groupId, $gt['id'], $gt['id'],   // room_id = group_time_id
        $firstLesson, $gt['start_date'], $gt['end_date'],
        $group['credit'], $requiredCredits
    ]);
    $firstLesson = 1;
}
```

**Lokal:** `bugfix_group/student-group-request.php`

---

### 4. Mevcut onaylı öğrencilerin appointment'ı yoktu (tek seferlik migrasyon)

**Dosya:** `bugfix_group/fix_create_appointments.php` → sunucu: `/student/fix_create_appointments.php`

**Açıklama:** Daha önce onaylanmış ama appointment kaydı olmayan öğrenciler için toplu oluşturma scripti. Ayrıca `room_id` boş olan grup appointment'larını `group_time_id` ile doldurdu:
```sql
UPDATE appointment
SET room_id = group_time_id
WHERE type = 1
  AND (room_id IS NULL OR room_id = '')
  AND group_time_id IS NOT NULL
  AND group_time_id != ''
```

---

### 5. Öğretmen video odasında öğrencinin mikrofon/kamerasını kapatamıyordu

**Dosya:** `rooms/assets/js/index.js`

**Sorun (1. deneme — Agora data stream):**
- `sendStreamMessage` yanlış API kullanımı (uid yerine stream ID gerekiyor, string yerine Uint8Array gerekiyor)
- Agora 4.18.2 gelen mesaja `{}` prefix ekliyor → `JSON.parse` hata: "Unexpected non-whitespace character after JSON at position 2"

**Çözüm: HTTP polling yaklaşımına geçildi**

**PHP dosyaları:**

`rooms/src/control-send.php` (öğretmen → PHP):
```php
<?php
$roomId    = intval($_POST['room_id']    ?? 0);
$targetUid = intval($_POST['target_uid'] ?? 0);
$action    = preg_replace('/[^a-zA-Z]/', '', $_POST['action'] ?? '');
if (!$roomId || !$action) { echo '{"ok":0}'; exit; }
$file = sys_get_temp_dir() . "/evo_ctrl_{$roomId}_{$targetUid}.json";
file_put_contents($file, json_encode(['action' => $action, 'ts' => time()]));
echo '{"ok":1}';
```

`rooms/src/control-check.php` (öğrenci → PHP):
```php
<?php
$roomId = intval($_GET['room_id'] ?? 0);
$myUid  = intval($_GET['uid']     ?? 0);
if (!$roomId || !$myUid) { echo '{}'; exit; }
$commands = [];
$file = sys_get_temp_dir() . "/evo_ctrl_{$roomId}_{$myUid}.json";
if (file_exists($file)) {
    $d = json_decode(file_get_contents($file), true);
    if ($d && (time() - ($d['ts'] ?? 0)) <= 10) $commands[] = $d;
    @unlink($file);
}
$fileBcast = sys_get_temp_dir() . "/evo_ctrl_{$roomId}_0.json";
if (file_exists($fileBcast)) {
    $d2 = json_decode(file_get_contents($fileBcast), true);
    if ($d2 && (time() - ($d2['ts'] ?? 0)) <= 10) $commands[] = $d2;
}
echo json_encode($commands ? $commands[0] : new stdClass());
```

**JS (index.js) — öğretmen butonu:**
```javascript
function sendControl(targetUid, action) {
  const body = new URLSearchParams({ room_id: window.roomId, target_uid: targetUid, action });
  console.log('[sendControl] roomId=', window.roomId, 'targetUid=', targetUid, 'action=', action);
  fetch('/rooms/src/control-send.php', { method: 'POST', body })
    .then(r => r.text()).then(t => console.log('[sendControl] response:', t))
    .catch(e => console.error('[sendControl] HATA:', e));
}
```

**JS (index.js) — öğrenci polling (startCall içinde):**
```javascript
if (!isAdmin) {
  console.log('[poll] Öğrenci kontrol döngüsü başladı. roomId=', window.roomId, 'uid=', uid);
  setInterval(async () => {
    if (!hasJoined) return;
    try {
      const res = await fetch(`/rooms/src/control-check.php?room_id=${encodeURIComponent(window.roomId)}&uid=${encodeURIComponent(uid)}`);
      const data = await res.json();
      if (data.action) console.log('[poll] Komut alındı:', data);
      if (data.action === 'mute'    && localTracks.audioTrack)  localTracks.audioTrack.setEnabled(false);
      if (data.action === 'hideCam' && localTracks.videoTrack)  localTracks.videoTrack.setEnabled(false);
    } catch (e) { console.warn('[poll] hata:', e); }
  }, 1000);
}
```

---

## Dosya Eşleştirme Tablosu

| Lokal (`bugfix_group/`) | Sunucu (`/var/www/evoegitim/public/`) |
|-------------------------|---------------------------------------|
| `appointment.php` (student versiyonu) | `student/appointment.php` |
| `appointment.php` (teacher versiyonu) | `teacher/appointment.php` |
| `student-group-request.php` | `management/student-group-request.php` |
| `fix_create_appointments.php` | `student/fix_create_appointments.php` |
| `index.js` | `rooms/assets/js/index.js` |
| `control-send.php` | `rooms/src/control-send.php` |
| `control-check.php` | `rooms/src/control-check.php` |

---

## Mevcut Durum (Devam Eden)

- Öğretmen mute/kamera kapatma hâlâ çalışmıyor
- Sunucudaki `index.js` ESKİ versiyonu içeriyor (Agora stream-message handler var, "Geçersiz mesaj" hatası alınıyor)
- Çözüm: `bugfix_group/index.js` + iki PHP dosyasını sunucuya yükle, `Ctrl+Shift+R` hard refresh yap
- Doğrulama: `https://evoegitim.com/rooms/src/control-check.php?room_id=989&uid=1` → `{}` dönmeli

## Tanılama Notu

Agora `stream-message` hatası sunucuda eski kod olduğunu gösterir. Yeni `index.js`'de `stream-message` handler yok — local dosya temiz.
