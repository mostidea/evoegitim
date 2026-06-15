<?php include("functions.php"); ?>
<!DOCTYPE html>
<html lang="tr">
<head>
  <meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, minimum-scale=1"/>
  <title>Evo Grup Ders</title>

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet" />
    <link href="assets/css/index.css" rel="stylesheet" />
  <script src="https://download.agora.io/sdk/release/AgoraRTC_N-4.18.2.js"></script>
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <link rel="icon" href="data:,">
</head>
<body>
  <div class="video-grid" id="video-grid">
    <div class="video-box" id="local-stream">
      <div class="media-area" style="width: 100%; height: 100%; background: #111;"></div>
    </div>
  </div>

  <div class="bottom-controls">
    <button onclick="enableMic()" id="btn-enable-mic" style="display:none;"><i class="fa-solid fa-microphone"></i></button>
    <button onclick="disableMic()" id="btn-disable-mic"><i class="fa-solid fa-microphone-slash"></i></button>
    <button onclick="enableCamera()" id="btn-enable-cam" style="display:none;"><i class="fa-solid fa-video"></i></button>
    <button onclick="disableCamera()" id="btn-disable-cam"><i class="fa-solid fa-video-slash"></i></button>
    <?php if(@$admin==1){ ?>
    <button onclick="shareScreen()" id="btn-share-screen"><i class="fa-solid fa-desktop"></i></button>
    <?php } ?>
    <button onclick="leaveCall()" id="btn-leave"><i class="fa-solid fa-phone-slash"></i></button>
    <button onclick="showVirtualBgSettings()" id="btn-virtual-bg" title="Arka Plan Seç"><i class="fa-solid fa-image"></i></button>
        <button id="btn-complete" class="btn-complete" style="display:none; background-color:#28a745; color:#fff;">
      <i class="fa-solid fa-check"></i>
    </button>
  </div>
  <!-- Ders Sonuna Kalan Süre -->
<!-- Ders Sonuna Kalan Süre -->
<div id="countdown" class="countdown">
  <i class="fa fa-clock"></i>
  <span id="countdown-timer">--:--</span>
</div>

</body>
</html>
  <!-- PHP’den gelen verileri JS’e aktaralım -->
  <script>
    window.isAdmin   = <?php echo $admin ?>;
    window.roomId    = <?php echo $roomId ?>;
    window.lessonId  = <?php echo $lessonId ?>;
    window.teacherId = <?php echo $teacherId ?>;
    window.csrfToken = <?= json_encode($_SESSION['csrf_token']) ?>;
    window.myDisplayName = <?= json_encode($fullname) ?>;
    window.endTime  = <?= json_encode(
    (new DateTime($appt['end_date']))->format('Y-m-d\\TH:i:s'),
    JSON_UNESCAPED_SLASHES
  ) ?>;
  </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <script src="assets/js/virtual-bg.js"></script>
  <?php include('../includes/virtual-bg-modal.php'); ?>
  <script src="assets/js/index.js" defer></script>
  <?php 
  if ($userId == $teacherId) {
    echo '<script src="assets/js/teacher.js"></script>';
} elseif ($userId == $studentId) {
    echo '<script src="assets/js/student.js"></script>';
}

?>

