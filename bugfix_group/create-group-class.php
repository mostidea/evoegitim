<?php
header('Content-Type: text/html; charset=UTF-8');
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
include '../config/connection.php';
checkUnSession();

/* ------------------------------------------------------------
|  Yardımcı: slug üret
------------------------------------------------------------ */
function slugify(string $str): string
{
  $tr = ['ş', 'Å', 'ı', 'İ', 'ğ', 'Ä', 'ü', 'Ü', 'ö', 'Ö', 'ç', 'Ç', ' '];
  $en = ['s', 's', 'i', 'i', 'g', 'g', 'u', 'u', 'o', 'o', 'c', 'c', '-'];
  $str = str_replace($tr, $en, mb_strtolower($str));
  $str = preg_replace('/[^a-z0-9\-]+/', '-', $str);
  return trim(preg_replace('/-+/', '-', $str), '-');
}

/* ------------------------------------------------------------
|  ID
------------------------------------------------------------ */
$id = ctype_digit($_POST['id'] ?? '') ? (int)$_POST['id'] : 0;

/* ------------------------------------------------------------
|  POST İÅLE
------------------------------------------------------------ */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
$randId=rand(0,22).time();
  /* -----------  Kapak fotoğrafı (isteğe bağlı) ----------- */
  $imagePath = null;
  if (!empty($_FILES['image']['name'])) {
    $upDir = __DIR__ . '/../uploads/';
    if (!is_dir($upDir)) mkdir($upDir, 0775, true);

    $ext  = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
    $name = uniqid('cover_') . '.' . $ext;

    if (move_uploaded_file($_FILES['image']['tmp_name'], $upDir . $name)) {
      $imagePath = 'uploads/' . $name;
    }
  }
  /* -----------  groups tablosu alanları  ----------- */
  $payload = [
    'lesson_id'           => (int)($_POST['lesson_id']          ?? 0),
    'teacher_id'          => (int)($_POST['teacher_id']         ?? 0),
    'title'               => trim($_POST['title']               ?? ''),
    'subject'               => trim($_POST['subject']               ?? ''),
    'slug'                => slugify($_POST['title']            ?? ''),
    'weekly_lesson_count' => (int)($_POST['weekly_lesson_count'] ?? 0),
    'total_lesson_time'   => (int)($_POST['total_lesson_time']  ?? 0),
    'quota'               => (int)($_POST['quota']              ?? 0),
    'description'         =>         $_POST['description']      ?? '',
    'rule'                =>         $_POST['rule']             ?? '',
    'credit'              => (int)($_POST['credit']             ?? 0),
    'start_date'          =>         $_POST['start_date']       ?? null,
    'level'               => json_encode(
      array_map('trim', $_POST['level'] ?? []),
      JSON_UNESCAPED_UNICODE
    ),
    'publish'             => isset($_POST['publish']) && $_POST['publish'] !== '' ? (int)$_POST['publish'] : 1,
  ];
  if ($imagePath) {                      // âœ“ yeni kapak varsa diziye ekle
    $payload['image'] = $imagePath;
  }

  /* -----------  INSERT - UPDATE ----------- */
  if ($id) {
    $set = [];
    foreach ($payload as $k => $v) {
      $set[] = "$k = :$k";
    }
    $sql = "UPDATE `groups` SET " . implode(', ', $set) . " WHERE id = :id LIMIT 1";
    $payload['id'] = $id;
  } else {
    // Aynı slug ile daha önce grup oluşturulmuş mu kontrol et
    $slugCheck = $db->prepare("SELECT id FROM `groups` WHERE slug = ? LIMIT 1");
    $slugCheck->execute([$payload['slug']]);
    if ($slugCheck->fetch()) {
      header('Location: /management/create-group-class.php?error=duplicate_title');
      exit;
    }
    $cols = implode(',', array_keys($payload));
    $vals = ':' . implode(', :', array_keys($payload));
    $sql  = "INSERT INTO `groups` ($cols) VALUES ($vals)";
  }

  $ok  = $db->prepare($sql)->execute($payload);
  $gid = $id ?: $db->lastInsertId();

  /* --------------------------------------------------------
    |  group_time tablosu â€“ eski saatleri sil, yenileri ekle
    -------------------------------------------------------- */
  /* --------------- group_time güncellendi â†’ appointment senkronizasyonu --------------- */
  if ($ok) {
    $stmtAB = $db->prepare('SELECT * FROM `groups` WHERE id = ?');
    $stmtAB->execute([$gid]);
    $rowGrup = $stmtAB->fetch(PDO::FETCH_ASSOC) ?: null;
    $db->prepare('DELETE FROM group_time WHERE group_id = ?')->execute([$gid]);

    $dates = $_POST['lessonDates'] ?? [];
    $times = $_POST['lessonTimes'] ?? [];

    $ins = $db->prepare(
      'INSERT INTO group_time (group_id, start_date, end_date)
               VALUES (:gid, :start, :end)'
    );

    $newTimes = [];   // topluca tutacağız (start â†’ end)

    foreach ($dates as $k => $d) {
      $t = $times[$k] ?? '';
      if (!$d || !$t) continue;

      $start = date('Y-m-d H:i:s', strtotime("$d $t"));
      $end   = date('Y-m-d H:i:s', strtotime("$start +50 minutes"));

      $ins->execute([
        'gid'   => $gid,
        'start' => $start,
        'end'   => $end
      ]);

      $newTimes[] = [
        'start' => $start,
        'end'   => $end
      ];
    }

    /* -----------------------------------------------
   * â†’ Åimdi gruba kayıtlı öğrencilerin appointment'larını güncelle
   * ----------------------------------------------- */

    // 1. Bu gruba başvuran ve kabul edilen öğrenciler
    $students = $db->prepare(
      'SELECT user_id FROM apply_group WHERE group_id = ? AND status = 1'
    );
    $students->execute([$gid]);
    $studentList = $students->fetchAll(PDO::FETCH_COLUMN);

    // 2. Geçmişi bozmadan (start_date > NOW) tüm randevuları sil
    $del = $db->prepare(
      'DELETE FROM appointment
      WHERE group_id = :gid
        AND student_id = :sid
        AND start_date > NOW()'
    );

    // 3. Yeni appointment kayıt ekleme
    $insApp = $db->prepare("
    INSERT INTO appointment
      (student_id, teacher_id, lesson_id, group_id, group_time_id,
       first_lesson, start_date, end_date, status,
       credit, total_credit, zoom_link, student_join, teacher_join,
       teacher_report, student_report, revise, income, canceller, room_id, type)
    VALUES
      (:student_id, :teacher_id, :lesson_id, :group_id, :group_time_id,
       :first_lesson, :start_date, :end_date, 0,
       :credit, :total_credit, :zoom_link, NULL, NULL,
       0, 0, 0, 0, NULL, :room_id, :type)
  ");

    /* ------------- HER ÖÄRENCİ İÇİN ---------------- */
    foreach ($studentList as $studentId) {

      /* A) Öğrencinin GÜNCELLEME ÖNCESİ ileri tarihli dersi */
      /* öğrencinin güncelleme ÖNCESİ ileri tarihli ders sayısı */
      $stmtPrev = $db->prepare(
        'SELECT COUNT(*) FROM appointment
   WHERE student_id = ? AND group_id = ? AND start_date > NOW()'
      );
      $stmtPrev->execute([$studentId, $gid]);
      $prevCount = (int)$stmtPrev->fetchColumn();

      $stmtPrevCredit = $db->prepare(
        'SELECT COALESCE(SUM(credit),0) FROM appointment
   WHERE student_id = ? AND group_id = ? AND start_date > NOW()'
      );
      $stmtPrevCredit->execute([$studentId, $gid]);
      $prevCreditTotal = (int)$stmtPrevCredit->fetchColumn();


      /* B) Eski ileri tarihli randevularını sil */
      $newCount = count($newTimes);
      $newCreditTotal = $newCount * (int)$rowGrup['credit'];
      $diffAmount = $newCreditTotal - $prevCreditTotal;

      if ($diffAmount > 0) {
        if (creditGetBalance($db, (int)$studentId, 1) < $diffAmount) {
          header('Location: ../management/groups.php?error=insufficient_credit&student_id=' . (int)$studentId);
          exit;
        }
      }

      $del->execute(['gid' => $gid, 'sid' => $studentId]);

      /* C) Yeni randevuları ekle (mevcut kodun aynı) */
      $first = 1;
      $gtRows = $db->prepare('SELECT id,start_date,end_date FROM group_time WHERE group_id = ? ORDER BY start_date');
      $gtRows->execute([$gid]);
      foreach ($gtRows as $gt) {
        $insApp->execute([
          'student_id'    => $studentId,
          'teacher_id'    => $rowGrup['teacher_id'],
          'lesson_id'     => $rowGrup['lesson_id'],
          'group_id'      => $gid,
          'group_time_id' => $gt['id'],
          'first_lesson'  => $first,
          'start_date'    => $gt['start_date'],
          'end_date'      => $gt['end_date'],
          'credit'        => $rowGrup['credit'],
          'total_credit'  => 0,
          'zoom_link'    => "https://evoegitim.com/rooms/call.php?roomid=".$randId,
          'room_id' => $randId,
          'type'  => 1,
        ]);
        $first = 0;
      }

      /* D) Yeni ileri tarihli ders adedi (tüm grup için aynıdır) */
      $newCount = count($newTimes);
      $newCreditTotal = $newCount * (int)$rowGrup['credit'];
      $diffAmount = $newCreditTotal - $prevCreditTotal;

      /* E) Fark & kredi ayarı */
      $diff   = $newCount - $prevCount;                // + arttı, - azaldı
      $amount = abs($diff) * (int)$rowGrup['credit'];  // değişen kredi

      $amount = abs($diffAmount);

      if ($diffAmount > 0 && $amount) {
        creditUse($db, (int)$studentId, (int)$amount, 1);
      } elseif ($diffAmount < 0 && $amount) {
        creditRefund($db, (int)$studentId, (int)$amount, 1);
      }
    }
  }



  header('Location: ../management/groups.php');
  exit;
}

if (isset($_GET['id'])) {
  $stmtA = $db->prepare('SELECT * FROM `groups` WHERE id = ?');
  $stmtA->execute([$_GET['id']]);
  $row = $stmtA->fetch(PDO::FETCH_ASSOC) ?: null;

  $stmtG = $db->prepare('SELECT * FROM group_time WHERE group_id = ?');
  $stmtG->execute([$_GET['id']]);

  $teacher = $db->prepare('SELECT profile_photo FROM users WHERE id = ?');
  $teacher->execute([$row['teacher_id']]);
  $teac = $teacher->fetch(PDO::FETCH_ASSOC) ?: null;
}
?>
<!DOCTYPE html>
<html lang="tr">

<head>
  <?php include "../includes_panel/meta.php"; ?>
  <link rel="stylesheet" href="https://evoegitim.com/new-site/assets/css/fontawesome.min.css?v=5021139">
  <!-- <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css"> -->
  <!-- <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" /> -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
  <title>Grup Ders Oluştur | Evo Eğitim</title>
  <style>
    form .form-check-label {
      width: 100%;
    }

    form .form-check {
      flex: 1;
    }

    .card-body form ul {
      list-style: disc;
      padding-left: 1.5rem;
      margin: 1rem 0;
    }

    select {
      /* height: 100%; */
    }

    .teacher-info {
      display: flex;
      align-items: center;
      gap: 15px;
      margin-bottom: 20px;
    }

    .teacher-avatar {
      width: 80px;
      height: 80px;
      border-radius: 50%;
      object-fit: cover;
    }

    .capacity-input {
      display: flex;
      align-items: center;
      gap: 10px;
    }

    .capacity-input .form-control {
      width: 80px;
      text-align: center;
    }

    /* Select2 custom styles */
    .select2-container--default .select2-selection--single {
      height: 38px;
      border: 1px solid #ced4da;
      border-radius: 0.25rem;
    }

    .select2-container--default .select2-selection--single .select2-selection__rendered {
      line-height: 38px;
      padding-left: 12px;
    }

    .select2-container--default .select2-selection--single .select2-selection__arrow {
      height: 36px;
    }
  </style>
  <style>
    /* Summernote içerik alanında liste işaretlerini geri getir */
.note-editor .note-editable ul {
  list-style-type: disc !important;
  list-style-position: outside;
  padding-left: 1.5rem !important;
  margin-left: 0 !important;
}

.note-editor .note-editable ol {
  list-style-type: decimal !important;
  list-style-position: outside;
  padding-left: 1.5rem !important;
  margin-left: 0 !important;
}

/* Her ihtimale karşı li display'i */
.note-editor .note-editable li {
  display: list-item !important;
}

/* Bootstrap/Tailwind gibi class'lar karışıyorsa koruma */
.note-editor .note-editable ul.list-unstyled,
.note-editor .note-editable ol.list-unstyled {
  list-style: none !important;
  padding-left: 0 !important;
}

</style>
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
          <h4 class="mb-4">Grup Ders Oluştur</h4>
        </div>
        <div class="card-body">
          <form id="groupClassForm" action="/management/create-group-class.php" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="id" value="<?php echo @$_GET["id"]; ?>">

            <div class="row mb-3">
              <div class="col-md-6 mb-3">
                <label class="form-label">Dersin Branşı</label>
                <select class="form-select" id="branch" name="lesson_id" data-select2-selector='default' required>
                  <option value="">Branş Seçiniz</option>
                  <?php

                  $stmt = $db->prepare("SELECT id,title,description FROM lessons");
                  $stmt->execute();


                  foreach ($stmt as $l) {
                    // Düzenleme modundaysak mevcut kayda ait lesson_id'yi seçili yap
                    $selected = (isset($row['lesson_id']) && $row['lesson_id'] == $l['id']) ? ' selected' : '';
                    echo '<option value="' . $l['id'] . '"' . $selected . '>'
                      . htmlspecialchars($l['title'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8')
                      . ' / ' . htmlspecialchars($l['description'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '</option>';
                  }


                  ?>
                </select>
              </div>

              <div class="col-md-6 mb-3">
                <label class="form-label">Öğretmen</label>
                <select class="form-select" id="teacherId" name="teacher_id" data-select2-selector='default' required>
                  <option value="">Öğretmen Seçiniz</option>
                  <?php

                  $stmtTeacher = $db->prepare("SELECT profile_photo,id,fullname,accessible_lessons FROM users WHERE role=2");
                  $stmtTeacher->execute();

                  foreach ($stmtTeacher as $lt) {
                    $selectedT = (isset($row['teacher_id']) && $row['teacher_id'] == $lt['id']) ? ' selected' : '';
                    $lessons = htmlspecialchars($lt['accessible_lessons'] ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
                    echo '<option value="' . $lt['id'] . '"' . $selectedT
                      . ' data-image="../' . $lt['profile_photo'] . '"'
                      . ' data-lessons="' . $lessons . '">'
                      . htmlspecialchars($lt['fullname'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8')
                      . '</option>';
                  }


                  ?>
                </select>
              </div>

              <div class="col-md-12 mb-3" id="teacherInfoContainer" <?php if (!@$_GET["id"]) { ?> style="display: none;" <?php } ?>>
                <div class="card border shadow-sm">
                  <div class="card-header bg-light d-flex justify-content-between align-items-center py-2">
                    <h5 class="mb-0">Öğretmen Bilgileri</h5>
                    <span class="badge bg-primary px-3 py-2">Seçildi</span>
                  </div>
                  <div class="card-body">
                    <div class="row">
                      <div class="col-md-4 mb-3 mb-md-0">
                        <div class="d-flex">
                          <div class="teacher-avatar-container me-3">
                            <img src="<?php echo @$teac["profile_photo"]; ?>" alt="Öğretmen Fotoğrafı" class="teacher-avatar" id="teacherAvatar" style="width: 100px; height: 100px; border-radius: 50%; object-fit: cover; border: 3px solid #3498db; box-shadow: 0 3px 10px rgba(0,0,0,0.2);">
                          </div>
                          <div class="d-flex flex-column justify-content-center">
                            <h5 class="mb-1 text-primary" id="teacherName">
                              <?= isset($row['teacher_id']) ? htmlspecialchars($db->query("SELECT fullname FROM users WHERE id = {$row['teacher_id']}")->fetchColumn(), ENT_QUOTES) : '' ?>
                            </h5>
                            <p class="mb-2 text-muted"><i class="fas fa-graduation-cap me-1"></i> <span id="teacherBranch">
                                <?php
                                if (isset($row['lesson_id'])) {
                                  $lessonStmt = $db->prepare("SELECT title, description FROM lessons WHERE id = ?");
                                  $lessonStmt->execute([$row['lesson_id']]);
                                  $lesson = $lessonStmt->fetch(PDO::FETCH_ASSOC);
                                  if ($lesson) {
                                    echo htmlspecialchars($lesson['title'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8')
                                      . ' / ' . htmlspecialchars($lesson['description'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
                                  }
                                }
                                ?>
                              </span></p>
                          </div>
                        </div>

                        <div class="form-group mt-5 ">
                          <label for="teacherCoverPhoto" class="form-label fw-bold">
                            <i class="fas fa-image me-1 text-primary"></i> Kapak Fotoğrafı Ekle
                          </label>
                          <div class="input-group">
                            <input type="file" class="form-control" id="teacherCoverPhoto" name="image" accept="image/*">
                            <button class="btn btn-outline-primary" type="button" id="viewCoverPhotoBtn">
                              <i class="fas fa-eye me-1"></i> Önizle
                            </button>
                          </div>
                          <div class="form-text mt-1"><i class="fas fa-info-circle me-1"></i> Kurs İçin için özel bir kapak fotoğrafı seçmelisiniz.</div>
                        </div>
                        <div id="coverPreviewContainer" class="mt-3" style="display: none;">
                          <p class="mb-1 fw-bold text-dark">Önizleme:</p>
                          <div class="position-relative">
                            <img id="coverPreview" src="<?php echo "../" . @$row["image"]; ?>" alt="Kapak Fotoğrafı Önizleme" class="img-fluid rounded" style="width: 300px; max-height: 300px; object-fit: cover;">
                            <button type="button" class="btn btn-sm btn-danger position-absolute top-0 end-0 m-2" id="removeCoverPhotoBtn">
                              <i class="fas fa-times"></i>
                            </button>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
              <div class="col-lg-6 mb-3">
                <label for="subject" class="form-label">Grup Başlığı</label>
                <input type="text" class="form-control" id="subject" name="title" value="<?php echo @$row["title"]; ?>" required>
              </div>
              <div class="col-lg-6 mb-3">
                <label for="subject" class="form-label">Dersin Konusu</label>
                <input type="text" class="form-control" id="subject" name="subject" value="<?php echo @$row["subject"]; ?>" required>
              </div>

              <div class="col-lg-6 mb-3">
                <label for="duration" class="form-label">Dersin Süresi (Hafta olarak)</label>
                <input type="number" class="form-control" id="duration" name="total_lesson_time" value="<?php echo @$row["total_lesson_time"]; ?>" min="1" required>
              </div>

              <div class="col-lg-6 mb-3">
                <label for="startDate" class="form-label">Dersin Başlangıç Tarihi ve Saati</label>
                <input type="text" class="form-control" id="startDate" name="start_date" value="<?php echo @$row["start_date"]; ?>" placeholder="2025-11-10 14:00" required>
              </div>

              <div class="col-lg-6 mb-3">
                <label for="weeklyLessons" class="form-label">Haftada Kaç Ders Yapılacak</label>
                <input type="number" class="form-control" id="weeklyLessons" value="<?php echo @$row["weekly_lesson_count"]; ?>" name="weekly_lesson_count" min="1" max="7" required>
              </div>

              <div class="col-lg-6 mb-3">
                <label for="capacity" class="form-label">Grup Kontenjanı</label>
                <div class="capacity-input">
                  <div class="input-group">

                    <input type="text" class="form-control" id="totalCapacity" name="quota" value="<?php echo @$row["quota"]; ?>" required>
                    <span class="input-group-text">Toplam</span>
                  </div>
                  <div class="form-text mt-1" id="capacityHelpText">Dolu kontenjan, toplam kontenjanı geçemez.</div>
                </div>
              </div>

              <div class="col-lg-6 mb-3">
                <label for="creditValue" class="form-label">Ders Kredi Değeri</label>
                <input type="number" class="form-control" id="creditValue" value="<?php echo @$row["credit"]; ?>" name="credit" min="1" required>
              </div>

              <div class="col-lg-6 mb-3">
                <label for="publish" class="form-label">Site Dışında Yayın Durumu</label>
                <?php $publishValue = isset($row['publish']) ? (int)$row['publish'] : 1; ?>
                <select class="form-select" id="publish" name="publish">
                  <option value="1" <?php echo $publishValue === 1 ? 'selected' : ''; ?>>Sitede yayınlansın</option>
                  <option value="0" <?php echo $publishValue === 0 ? 'selected' : ''; ?>>Sitede yayınlanmasın</option>
                </select>
              </div>
            </div>
            <?php
            $levels = json_decode($row['level'] ?? '[]', true) ?: [];   // daima []
            /* $levels  â†’  veritabanından gelen seviye dizisi, örn. ["Başlangıç","İleri"] */
            function isChecked(string $val, ?array $arr): string
            {
              return ($arr && in_array($val, $arr, true)) ? ' checked' : '';
            }

            ?>
            <div class="mb-3">
              <label class="form-label">Grup Seviyesi (Birden fazla seçebilirsiniz)</label>
              <div class="d-flex flex-wrap gap-3">

                <div class="form-check mb-0">
                  <input class="form-check-input" type="checkbox" name="level[]" value="Başlangıç"
                    id="levelBeginner" <?= isChecked('Başlangıç', @$levels) ?>>
                  <label class="form-check-label mb-0" for="levelBeginner">Başlangıç</label>
                </div>

                <div class="form-check mb-0">
                  <input class="form-check-input" type="checkbox" name="level[]" value="Orta"
                    id="levelIntermediate" <?= isChecked('Orta', @$levels) ?>>
                  <label class="form-check-label mb-0" for="levelIntermediate">Orta</label>
                </div>

                <div class="form-check mb-0">
                  <input class="form-check-input" type="checkbox" name="level[]" value="İleri"
                    id="levelAdvanced" <?= isChecked('İleri', @$levels) ?>>
                  <label class="form-check-label mb-0" for="levelAdvanced">İleri</label>
                </div>

                <div class="form-check mb-0">
                  <input class="form-check-input" type="checkbox" name="level[]" value="Üst Seviye"
                    id="levelExpert" <?= isChecked('Üst Seviye', @$levels) ?>>
                  <label class="form-check-label mb-0" for="levelExpert">Üst Seviye</label>
                </div>

              </div>
            </div>

            <div class="mb-3">
              <label class="form-label">Ders Günleri ve Saatleri</label>
              <div class="alert alert-info">
                <p class="mb-1">Not: Sistem, seçilen başlangıç tarihi ve haftalık ders sayısına göre ders tarihlerini otomatik hesaplar.</p>
                <p class="mb-0">İhtiyaç halinde oluşturulan tarihleri düzenleyebilirsiniz.</p>
              </div>
              <div id="scheduleContainer">
                <?php if (@$_GET["id"]) {
                  $firstRow = true;
                  foreach ($stmtG as $g) {
                    // 1) Parçala
                    [$fullDate, $fullTime] = explode(' ', $g['start_date']);  // ["2025-05-01", "17:00:00"]

                    // 2) İstenen formata çevir
                    $date = date('Y-m-d', strtotime($fullDate));  // Format for the date picker
                    $time = date('H:i:S', strtotime($fullTime));  // Format for the time picker
                 
                ?>
                    <div class="row mb-3 schedule-row">
                      <div class="col-md-6">
                        <div class="d-flex">
                          <input type="hidden" value="<?= $g['id'] ?>" name="group_time_id[]">
                          <input type="text"
                            class="form-control lesson-date-picker"
                            name="lessonDates[]"
                            required
                            value="<?= date('d-m-Y', strtotime($date)); ?>">

                        </div>
                      </div>
                      <div class="col-md-6">
                        <div class="d-flex">
                          <select class="form-select lesson-time-select" name="lessonTimes[]" data-select2-selector='default' required>
                            <option value="">Saat Seçiniz</option>
                            <option value="07:00:00" <?= $fullTime == '07:00:00' ? 'selected' : '' ?>>07:00</option>
                            <option value="07:30:00" <?= $fullTime == '07:30:00' ? 'selected' : '' ?>>07:30</option>
                            <option value="08:00:00" <?= $fullTime == '08:00:00' ? 'selected' : '' ?>>08:00</option>
                            <option value="08:30:00" <?= $fullTime == '08:30:00' ? 'selected' : '' ?>>08:30</option>
                            <option value="09:00:00" <?= $fullTime == '09:00:00' ? 'selected' : '' ?>>09:00</option>
                            <option value="09:30:00" <?= $fullTime == '09:30:00' ? 'selected' : '' ?>>09:30</option>
                            <option value="10:00:00" <?= $fullTime == '10:00:00' ? 'selected' : '' ?>>10:00</option>
                            <option value="10:30:00" <?= $fullTime == '10:30:00' ? 'selected' : '' ?>>10:30</option>
                            <option value="11:00:00" <?= $fullTime == '11:00:00' ? 'selected' : '' ?>>11:00</option>
                            <option value="11:30:00" <?= $fullTime == '11:30:00' ? 'selected' : '' ?>>11:30</option>
                            <option value="12:00:00" <?= $fullTime == '12:00:00' ? 'selected' : '' ?>>12:00</option>
                            <option value="12:30:00" <?= $fullTime == '12:30:00' ? 'selected' : '' ?>>12:30</option>
                            <option value="13:00:00" <?= $fullTime == '13:00:00' ? 'selected' : '' ?>>13:00</option>
                            <option value="13:30:00" <?= $fullTime == '13:30:00' ? 'selected' : '' ?>>13:30</option>
                            <option value="14:00:00" <?= $fullTime == '14:00:00' ? 'selected' : '' ?>>14:00</option>
                            <option value="14:30:00" <?= $fullTime == '14:30:00' ? 'selected' : '' ?>>14:30</option>
                            <option value="15:00:00" <?= $fullTime == '15:00:00' ? 'selected' : '' ?>>15:00</option>
                            <option value="15:30:00" <?= $fullTime == '15:30:00' ? 'selected' : '' ?>>15:30</option>
                            <option value="16:00:00" <?= $fullTime == '16:00:00' ? 'selected' : '' ?>>16:00</option>
                            <option value="16:30:00" <?= $fullTime == '16:30:00' ? 'selected' : '' ?>>16:30</option>
                            <option value="17:00:00" <?= $fullTime == '17:00:00' ? 'selected' : '' ?>>17:00</option>
                            <option value="17:30:00" <?= $fullTime == '17:30:00' ? 'selected' : '' ?>>17:30</option>
                            <option value="18:00:00" <?= $fullTime == '18:00:00' ? 'selected' : '' ?>>18:00</option>
                            <option value="18:30:00" <?= $fullTime == '18:30:00' ? 'selected' : '' ?>>18:30</option>
                            <option value="19:00:00" <?= $fullTime == '19:00:00' ? 'selected' : '' ?>>19:00</option>
                            <option value="19:30:00" <?= $fullTime == '19:30:00' ? 'selected' : '' ?>>19:30</option>
                            <option value="20:00:00" <?= $fullTime == '20:00:00' ? 'selected' : '' ?>>20:00</option>
                            <option value="20:30:00" <?= $fullTime == '20:30:00' ? 'selected' : '' ?>>20:30</option>
                            <option value="21:00:00" <?= $fullTime == '21:00:00' ? 'selected' : '' ?>>21:00</option>
                            <option value="21:30:00" <?= $fullTime == '21:30:00' ? 'selected' : '' ?>>21:30</option>
                            <option value="22:00:00" <?= $fullTime == '22:00:00' ? 'selected' : '' ?>>22:00</option>
                            <option value="22:30:00" <?= $fullTime == '22:30:00' ? 'selected' : '' ?>>22:30</option>
                            <option value="23:00:00" <?= $fullTime == '23:00:00' ? 'selected' : '' ?>>23:00</option>
                            <option value="23:30:00" <?= $fullTime == '23:30:00' ? 'selected' : '' ?>>23:30</option>
                          </select>
                          <?php if (!$firstRow): ?>
                            <button type="button" class="btn btn-danger btn-sm remove-schedule ms-2">
                              <i class="fas fa-times"></i>
                            </button>
                          <?php endif; ?>
                        </div>
                      </div>
                    </div>
                  <?php
                    $firstRow = false;
                  }
                } else {
                  ?>
                  <div class="row mb-3 schedule-row">
                    <div class="col-md-6">
                      <input type="text" class="form-control lesson-date-picker" name="lessonDates[]" placeholder="Tarih Seçiniz" required>
                    </div>
                    <div class="col-md-6">
                      <div class="d-flex">
                        <select class="form-select lesson-time-select" data-select2-selector='default' name="lessonTimes[]" required>
                          <option value="">Saat Seçiniz</option>
                          <option value="07:00">07:00</option>
                          <option value="07:30">07:30</option>
                          <option value="08:00">08:00</option>
                          <option value="08:30">08:30</option>
                          <option value="09:00">09:00</option>
                          <option value="09:30">09:30</option>
                          <option value="10:00">10:00</option>
                          <option value="10:30">10:30</option>
                          <option value="11:00">11:00</option>
                          <option value="11:30">11:30</option>
                          <option value="12:00">12:00</option>
                          <option value="12:30">12:30</option>
                          <option value="13:00">13:00</option>
                          <option value="13:30">13:30</option>
                          <option value="14:00">14:00</option>
                          <option value="14:30">14:30</option>
                          <option value="15:00">15:00</option>
                          <option value="15:30">15:30</option>
                          <option value="16:00">16:00</option>
                          <option value="16:30">16:30</option>
                          <option value="17:00">17:00</option>
                          <option value="17:30">17:30</option>
                          <option value="18:00">18:00</option>
                          <option value="18:30">18:30</option>
                          <option value="19:00">19:00</option>
                          <option value="19:30">19:30</option>
                          <option value="20:00">20:00</option>
                          <option value="20:30">20:30</option>
                          <option value="21:00">21:00</option>
                          <option value="21:30">21:30</option>
                          <option value="22:00">22:00</option>
                          <option value="22:30">22:30</option>
                          <option value="23:00">23:00</option>
                          <option value="23:30">23:30</option>
                        </select>
                      </div>
                    </div>
                  </div>
                <?php } ?>
              </div>
              <div class="d-flex justify-content-between">
                <button type="button" class="btn btn-primary btn-sm" id="addScheduleBtn">
                  <i class="fas fa-plus"></i> Yeni Zaman Ekle
                </button>
                <button type="button" class="btn btn-success btn-sm" id="autoGenerateScheduleBtn">
                  <i class="fas fa-calendar"></i> Ders Programını Otomatik Oluştur
                </button>
              </div>
            </div>

<div class="mb-3">
  <label for="groupDetails" class="form-label">Grup Detayları</label>
  <textarea
    id="groupDetails"
    name="description"
    class="js-summernote-desc"
    data-url="management/file.php"
    rows="5"
    placeholder="Grup hakkında detaylı bilgi, içerik, kazanımlar, hedefler ve diğer önemli bilgileri buraya giriniz."><?php echo @$row["description"]; ?></textarea>
</div>

<div class="mb-3">
  <label for="groupRules" class="form-label">Grup Kuralları</label>
  <textarea
    id="groupRules"
    name="rule"
    class="js-summernote-rules"
    data-url="management/file.php"
    rows="5"
    placeholder="Grup kuralları hakkında detaylı bilgileri buraya giriniz."><?php echo @$row["rule"]; ?></textarea>
</div>


            <div class="d-flex justify-content-end mt-4">
              <button type="submit" class="btn btn-primary">Grup Dersi Oluştur</button>
            </div>
          </form>
        </div>
      </div>
    </div>
    <?php include '../includes_panel/footer.php'; ?>
  </div>

  <?php include '../includes_panel/scripts.php'; ?>
  <!-- <script type="text/javascript" src="https://cdn.jsdelivr.net/jquery/latest/jquery.min.js"></script> -->
  <!-- <script type="text/javascript" src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script> -->
  <!-- <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script> -->
  <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
  <script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/tr.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/summernote@0.8.20/dist/lang/summernote-tr-TR.min.js"></script>

  <script>

    $(function () {
  function initSummernote($el) {
    $el.summernote({
      height: 350,
      placeholder: $el.attr('placeholder') || '',
      lang: 'tr-TR',
      toolbar: [
        ['style', ['style']],
        ['font', ['bold','italic','underline','strikethrough','clear']],
        ['fontname', ['fontname']],
        ['fontsize', ['fontsize']],
        ['color', ['forecolor','backcolor']],
        ['para', ['ul','ol','paragraph']],
        ['height', ['height']],
        ['table', ['table']],
        ['insert', ['link','picture','video','hr']],
        ['view', ['fullscreen','codeview','help']]
      ],
      callbacks: {
        onImageUpload: function (files) {
          for (let i = 0; i < files.length; i++) {
            const fd = new FormData();
            fd.append('file', files[i]);

            $.ajax({
              url: $el.data('url'),      // örn: management/file.php
              type: 'POST',
              data: fd,
              processData: false,
              contentType: false,
              success: function (res) {
                // res string geldiyse JSONâ€™a çevir
                try { if (typeof res === 'string') res = JSON.parse(res); } catch(e){}
                if (res && res.ok && res.url) {
                  $el.summernote('insertImage', res.url);
                } else {
                  alert('Yükleme başarısız: ' + (res && res.error ? res.error : 'Bilinmeyen hata'));
                }
              },
              error: function (xhr) {
                alert('Yükleme sırasında hata oluştu: ' + (xhr.responseJSON?.error || xhr.statusText));
              }
            });
          }
        }
      }
    });
  }

  // İki ayrı editorü başlat
  initSummernote($('.js-summernote-desc'));
  initSummernote($('.js-summernote-rules'));

});

    $(document).ready(function() {

      // Initialize select2 on all select elements
      function initSelect2() {
        $("[data-select2-selector='default']").select2({ theme: "bootstrap-5", width: "100%" });
      }
      
      // Initialize select2 on page load
      initSelect2();
      <?php if (!@$_GET["id"]) { ?>
      $('#recordInfo').val('1').trigger('change');
      <?php } ?>

      <?php if (@$_GET["id"]) {  ?>

        $('#coverPreview').attr('src', "../<?php echo @$row["image"]; ?>");
        $('#coverPreviewContainer').show();
      <?php } ?>

      // Initialize daterangepicker for start date with time picker
      $('#startDate').daterangepicker({
        singleDatePicker: true,
        timePicker: true,
        timePicker24Hour: true,
        timePickerIncrement: 30,
        startDate: moment().startOf('hour'),
        minDate: moment(),

        locale: {
          format: 'YYYY-MM-DD HH:mm',
          applyLabel: 'Seç',
          cancelLabel: 'İptal',
          daysOfWeek: ['Pz', 'Pt', 'Sa', 'Çr', 'Pr', 'Cu', 'Ct'],
          firstDay: 1,
          monthNames: ['Ocak', 'Åubat', 'Mart', 'Nisan', 'Mayıs', 'Haziran', 'Temmuz', 'Ağustos', 'Eylül', 'Ekim', 'Kasım', 'Aralık']
        },
        isCustomDate: function(date) {
          // Filter available hours
          const hour = date.hour();
          const minute = date.minute();

          // Only allow specific times (7:00-23:30 with 30 min increments)
          const validHours = [7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20, 21, 22, 23];
          const validMinutes = [0, 30];

          if (validHours.includes(hour) && validMinutes.includes(minute)) {
            return 'available';
          }
          return 'unavailable';
        }
      });

      // Ensure only specific times are allowed
      $('#startDate').on('showCalendar.daterangepicker', function(ev, picker) {
        $('.calendar-time select.hourselect option').each(function() {
          const hour = parseInt($(this).val());
          if (!(hour >= 7 && hour <= 23)) {
            $(this).remove();
          }
        });

        $('.calendar-time select.minuteselect option').each(function() {
          const minute = parseInt($(this).val());
          if (minute !== 0 && minute !== 30) {
            $(this).remove();
          }
        });
      });

      // Tarih seçicileri için
      function initializeDatePickers() {
        $(".lesson-date-picker").daterangepicker({
          singleDatePicker: true,
          showDropdowns: true,
          minDate: moment(),
          locale: {
            format: 'DD-MM-YYYY',
            firstDay: 1,
            applyLabel: 'Seç',
            cancelLabel: 'İptal',
            daysOfWeek: ['Pz', 'Pt', 'Sa', 'Çr', 'Pr', 'Cu', 'Ct'],
            monthNames: ['Ocak', 'Åubat', 'Mart', 'Nisan', 'Mayıs', 'Haziran', 'Temmuz', 'Ağustos', 'Eylül', 'Ekim', 'Kasım', 'Aralık']
          }
        });
        
        // Re-initialize select2 when new elements are added
        initSelect2();
      }



      // İlk yükleme için tarih ve saat seçicilerini başlat
      initializeDatePickers();

      // Tüm öğretmen option'larını sakla (filtreleme için)
      var allTeacherOptions = $('#teacherId option').not(':first').clone();

      // Branş seçilince öğretmen listesini filtrele
      $('#branch').on('change', function() {
        var lessonId = $(this).val();
        var $select  = $('#teacherId');

        // Mevcut seçimi ve listesi sıfırla
        $select.val('').trigger('change.select2');
        $select.find('option').not(':first').remove();
        $('#teacherInfoContainer').hide();

        if (!lessonId) {
          // Branş seçilmediyse tümünü göster
          $select.append(allTeacherOptions.clone());
        } else {
          // Sadece bu branşa atanmış öğretmenleri ekle
          allTeacherOptions.each(function() {
            var raw = $(this).attr('data-lessons') || '';
            var ids = raw.split(',').map(function(v){ return v.trim(); });
            if (ids.indexOf(String(lessonId)) !== -1) {
              $select.append($(this).clone());
            }
          });
        }

        // Select2'yi yenile
        $select.trigger('change.select2');
      });

      // Öğretmen seçildiğinde bilgilerini göster
      $('#teacherId').on('change', function() {
        const teacherId = $(this).val();
        if (teacherId) {
          // Seçilen option elementini al
          const selectedOption = $(this).find('option:selected');
          // Option'dan veri çek
          const teacherImage = selectedOption.data('image');
          const teacherName = selectedOption.text();
          const teacherBranch = selectedOption.data('branch');

          // Verileri göster
          $('#teacherInfoContainer').show();
          $('#teacherName').text(teacherName);
          $('#teacherBranch').text(teacherBranch || $('#branch').val());
          $('#teacherAvatar').attr('src', teacherImage);

          // Kapak fotoğrafı seçme alanını sıfırla
          $('#teacherCoverPhoto').val('');
          $('#coverPreviewContainer').hide();
        } else {
          $('#teacherInfoContainer').hide();
        }
      });

      // Kapak fotoğrafı önizleme
      $('#teacherCoverPhoto').on('change', function() {
        const file = this.files[0];
        if (file) {
          const reader = new FileReader();
          reader.onload = function(e) {
            $('#coverPreview').attr('src', e.target.result);
            $('#coverPreviewContainer').show();
          }
          reader.readAsDataURL(file);
        } else {
          $('#coverPreviewContainer').hide();
        }
      });

      // Önizle butonu tıklandığında
      $('#viewCoverPhotoBtn').on('click', function() {
        if ($('#teacherCoverPhoto').val()) {
          // Dosya seçilmişse, önizleme göster
          $('#coverPreviewContainer').toggle();
        } else {
          // Dosya seçilmemişse, kullanıcıya bildir
          Swal.fire({
            icon: 'info',
            title: 'Bilgi',
            text: 'Önce bir kapak fotoğrafı seçmelisiniz.',
            confirmButtonText: 'Tamam'
          });
        }
      });

      // Kapak fotoğrafını kaldırma butonu
      $(document).on('click', '#removeCoverPhotoBtn', function() {
        $('#teacherCoverPhoto').val('');
        $('#coverPreviewContainer').hide();
      });

      // Add more schedule rows
      $('#addScheduleBtn').on('click', function() {
        const newRow = `
          <div class="row mb-3 schedule-row">
            <div class="col-md-6">
              <div class="d-flex">
                <input type="text" class="form-control lesson-date-picker" name="lessonDates[]" placeholder="Tarih Seçiniz" required>
                
              </div>
            </div>
            <div class="col-md-6">
              <div class="d-flex">
              <select class="form-select lesson-time-select" data-select2-selector='default' name="lessonTimes[]" required>
                <option value="">Saat Seçiniz</option>
                <option value="07:00">07:00</option>
                <option value="07:30">07:30</option>
                <option value="08:00">08:00</option>
                <option value="08:30">08:30</option>
                <option value="09:00">09:00</option>
                <option value="09:30">09:30</option>
                <option value="10:00">10:00</option>
                <option value="10:30">10:30</option>
                <option value="11:00">11:00</option>
                <option value="11:30">11:30</option>
                <option value="12:00">12:00</option>
                <option value="12:30">12:30</option>
                <option value="13:00">13:00</option>
                <option value="13:30">13:30</option>
                <option value="14:00">14:00</option>
                <option value="14:30">14:30</option>
                <option value="15:00">15:00</option>
                <option value="15:30">15:30</option>
                <option value="16:00">16:00</option>
                <option value="16:30">16:30</option>
                <option value="17:00">17:00</option>
                <option value="17:30">17:30</option>
                <option value="18:00">18:00</option>
                <option value="18:30">18:30</option>
                <option value="19:00">19:00</option>
                <option value="19:30">19:30</option>
                <option value="20:00">20:00</option>
                <option value="20:30">20:30</option>
                <option value="21:00">21:00</option>
                <option value="21:30">21:30</option>
                <option value="22:00">22:00</option>
                <option value="22:30">22:30</option>
                <option value="23:00">23:00</option>
                <option value="23:30">23:30</option>
              </select>
              <button type="button" class="btn btn-danger btn-sm remove-schedule ms-2">
                  <i class="fas fa-times"></i>
                </button>
                </div>
            </div>
          </div>
        `;
        $('#scheduleContainer').append(newRow);
        // Yeni eklenen tarih ve saat seçicileri için flatpickr'ı aktifleştir
        initializeDatePickers();
      });

      // Remove schedule row
      $(document).on('click', '.remove-schedule', function() {
        $(this).closest('.schedule-row').remove();
      });

      // Kontenjan kontrolü
      $('#totalCapacity, #filledCapacity').on('input', function() {
        const total = parseInt($('#totalCapacity').val()) || 0;
        const filled = parseInt($('#filledCapacity').val()) || 0;

        // Minimum değeri kontrol et
        const totalMin = parseInt($('#totalCapacity').attr('min'));

        let message = "Dolu kontenjan, toplam kontenjanı geçemez.";
        let isError = false;

        // Toplam kontenjan kontrolü
        if (total < totalMin) {
          $('#totalCapacity').val(totalMin);
        }

        // Dolu kontenjan kontrolü
        if (filled < 0) {
          $('#filledCapacity').val(0);
        } else if (filled > total) {
          $('#filledCapacity').val(total);
          message = "Dolu kontenjan, toplam kontenjanı aşamaz.";
          isError = true;
        }

        // Kullanıcıya geri bildirim
        $('#capacityHelpText').text(message);
        if (isError) {
          $('#capacityHelpText').addClass('text-danger').removeClass('text-muted');
        } else {
          $('#capacityHelpText').removeClass('text-danger').addClass('text-muted');
        }
      });

      // Otomatik olarak ders programı oluştur butonu işlevi
      $('#autoGenerateScheduleBtn').on('click', function() {
        const duration = parseInt($('#duration').val()) || 0;
        const weeklyLessons = parseInt($('#weeklyLessons').val()) || 0;
        const startDate = $('#startDate').val();

        if (!duration || !weeklyLessons || !startDate) {
          Swal.fire({
            icon: 'error',
            title: 'Hata',
            text: 'Otomatik program oluşturmak için ders süresi, haftalık ders sayısı ve başlangıç tarihi girilmelidir!'
          });
          return;
        }

        // Toplam ders sayısını hesapla
        const totalLessons = duration * weeklyLessons;

        // Mevcut ders satırlarını temizle
        $('#scheduleContainer').empty();

        // Başlangıç tarihini parse et
        const baseDate = moment(startDate, 'YYYY-MM-DD HH:mm');
        const baseDay = baseDate.locale('tr').format('dddd'); // Başlangıç gününü al
        const rawMins = baseDate.hours() * 60 + baseDate.minutes();
        const roundedMins = Math.ceil(rawMins / 30) * 30;
        const baseTime = String(Math.floor(roundedMins / 60) % 24).padStart(2, '0') + ':' + String(roundedMins % 60).padStart(2, '0');

        // İlk hafta için başlangıç gününü ve saatini kullan
        let currentDate = moment(baseDate);

        // Gün değerlerini Türkçe'den İngilizce'ye çevirmek için map
        const dayMap = {
          'Pazartesi': 1,
          'Salı': 2,
          'Çarşamba': 3,
          'Perşembe': 4,
          'Cuma': 5,
          'Cumartesi': 6,
          'Pazar': 0
        };

        // İngilizce'den Türkçe'ye gün çevirme
        const dayMapReverse = {
          'Monday': 'Pazartesi',
          'Tuesday': 'Salı',
          'Wednesday': 'Çarşamba',
          'Thursday': 'Perşembe',
          'Friday': 'Cuma',
          'Saturday': 'Cumartesi',
          'Sunday': 'Pazar'
        };

        // Toplam ders sayısı kadar döngü
        for (let i = 0; i < totalLessons; i++) {
          // Eğer ilk ders değilse ve yeni bir haftaya geçiliyorsa
          if (i > 0 && i % weeklyLessons === 0) {
            currentDate = moment(baseDate).add(i / weeklyLessons, 'weeks');
          } else if (i > 0) {
            // Aynı hafta içinde ise, günü bir sonraki derse göre ayarla
            // Burada haftada birden fazla ders varsa, günleri eşit aralıklarla dağıt
            const daysToAdd = Math.floor(7 / weeklyLessons);
            currentDate = moment(baseDate).add(Math.floor(i / weeklyLessons), 'weeks').add(i % weeklyLessons * daysToAdd, 'days');
          }

          const currentDayOfWeek = dayMapReverse[currentDate.locale('en').format('dddd')];
          const formattedDate = currentDate.format('DD-MM-YYYY');

          const newRow = `
            <div class="row mb-3 schedule-row">

              <div class="col-md-6">
                <div class="d-flex">
                  <input type="text" class="form-control lesson-date-picker" name="lessonDates[]" placeholder="Tarih Seçiniz" required>
                  <button type="button" class="btn btn-danger btn-sm remove-schedule ms-2">&times;</button>
                </div>
              </div>
              <div class="col-md-6">
                <div class="d-flex"><select class="form-select lesson-time-select" data-select2-selector='default' name="lessonTimes[]" required>
                  <option value="">Saat Seçiniz</option>
                  <option value="07:00">07:00</option>
                  <option value="07:30">07:30</option>
                  <option value="08:00">08:00</option>
                  <option value="08:30">08:30</option>
                  <option value="09:00">09:00</option>
                  <option value="09:30">09:30</option>
                  <option value="10:00">10:00</option>
                  <option value="10:30">10:30</option>
                  <option value="11:00">11:00</option>
                  <option value="11:30">11:30</option>
                  <option value="12:00">12:00</option>
                  <option value="12:30">12:30</option>
                  <option value="13:00">13:00</option>
                  <option value="13:30">13:30</option>
                  <option value="14:00">14:00</option>
                  <option value="14:30">14:30</option>
                  <option value="15:00">15:00</option>
                  <option value="15:30">15:30</option>
                  <option value="16:00">16:00</option>
                  <option value="16:30">16:30</option>
                  <option value="17:00">17:00</option>
                  <option value="17:30">17:30</option>
                  <option value="18:00">18:00</option>
                  <option value="18:30">18:30</option>
                  <option value="19:00">19:00</option>
                  <option value="19:30">19:30</option>
                  <option value="20:00">20:00</option>
                  <option value="20:30">20:30</option>
                  <option value="21:00">21:00</option>
                  <option value="21:30">21:30</option>
                  <option value="22:00">22:00</option>
                  <option value="22:30">22:30</option>
                  <option value="23:00">23:00</option>
                  <option value="23:30">23:30</option>
                </select>
                <button type="button" class="btn btn-danger btn-sm remove-schedule ms-2">&times;</button>
                </div>
              </div>
            </div>
          `;

          $('#scheduleContainer').append(newRow);
          // Yeni eklenen tarih picker'ını doğru tarihle başlat
          $('#scheduleContainer .schedule-row:last .lesson-date-picker').daterangepicker({
            singleDatePicker: true,
            showDropdowns: true,
            startDate: formattedDate,
            locale: {
              format: 'DD-MM-YYYY',
              firstDay: 1,
              applyLabel: 'Seç',
              cancelLabel: 'İptal',
              daysOfWeek: ['Pz', 'Pt', 'Sa', 'Çr', 'Pr', 'Cu', 'Ct'],
              monthNames: ['Ocak', 'Åubat', 'Mart', 'Nisan', 'Mayıs', 'Haziran', 'Temmuz', 'Ağustos', 'Eylül', 'Ekim', 'Kasım', 'Aralık']
            }
          });
          // Son eklenen satırın saat dropdown'ına başlangıç saatini seç
          $('#scheduleContainer .schedule-row:last .lesson-time-select').val(baseTime).trigger('change');
        }

        // Yeni eklenen tarih ve saat seçicileri için flatpickr'ı aktifleştir
        initializeDatePickers();

        Swal.fire({
          icon: 'success',
          title: 'Program Oluşturuldu',
          text: `${totalLessons} derslik program başarıyla oluşturuldu. İhtiyaç halinde tarihleri düzenleyebilirsiniz.`,
          confirmButtonText: 'Tamam'
        });
      });

      // Weekly lessons change handler - dynamically add/remove schedule fields
      $('#weeklyLessons').on('change', function() {
        const weeklyLessons = parseInt($(this).val());
        const currentRows = $('#scheduleContainer .schedule-row').length;

        // Eğer otomatik program oluşturma kullanılmayacaksa manuel olarak ayarla
        if ($('#scheduleContainer .schedule-row').length === 0) {
          if (weeklyLessons > 0) {
            // Add initial rows based on weekly lessons
            for (let i = 0; i < weeklyLessons; i++) {
              $('#addScheduleBtn').trigger('click');
            }
          }
        }
      });

      // Duplicate title hatası kontrolü
      <?php if (isset($_GET['error']) && $_GET['error'] === 'duplicate_title'): ?>
      Swal.fire({
        icon: 'error',
        title: 'Hata',
        text: 'Bu başlıkta daha önce bir grup dersi oluşturulmuş. Lütfen farklı bir başlık girin.'
      });
      <?php endif; ?>

      // Form Submission
      $('#groupClassForm').on('submit', function(e) {

	$('#groupDetails').val($('.js-summernote-desc').summernote('code'));
        $('#groupRules').val($('.js-summernote-rules').summernote('code'));

        // Form doğrulama kontrolleri
        if ($('input[name="level[]"]:checked').length === 0) {
          e.preventDefault(); // Formu durdur
          Swal.fire({
            icon: 'error',
            title: 'Hata',
            text: 'Lütfen en az bir grup seviyesi seçiniz'
          });
          return false;
        }

        // Seçilen seviyeleri ve ders programını hidden input'lara ekle
        const selectedLevels = [];
        $('input[name="level[]"]:checked').each(function() {
          selectedLevels.push($(this).val());
        });

        // Ders programı verilerini topla
        const scheduleData = [];
        const days = $('select[name="lessonDays[]"]');
        const times = $('input[name="lessonTimes[]"]');
        const dates = $('input[name="lessonDates[]"]');

        for (let i = 0; i < days.length; i++) {
          scheduleData.push({
            day: $(days[i]).val(),
            time: $(times[i]).val(),
            date: $(dates[i]).val()
          });
        }

        // Hidden input'ları ekle
        if (!$('#formattedLevels').length) {
          $(this).append('<input type="hidden" id="formattedLevels" name="formattedLevels" value="' + selectedLevels.join(', ') + '">');
          $(this).append('<input type="hidden" id="scheduleData" name="scheduleData" value=\'' + JSON.stringify(scheduleData) + '\'>');
        } else {
          $('#formattedLevels').val(selectedLevels.join(', '));
          $('#scheduleData').val(JSON.stringify(scheduleData));
        }

        // Form normal şekilde submit edilir
        return true;
      });
    });
  </script>

</body>

</html>
