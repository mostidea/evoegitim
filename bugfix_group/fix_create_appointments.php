<?php
session_start();
include("../config/connection.php");
checkUnSession();

// Onaylı ama appointment'ı olmayan öğrenci+grup çiftleri için appointment oluştur
$approved = $db->query("
    SELECT gq.user_id, gq.group_id, g.teacher_id, g.lesson_id, g.credit,
           (g.credit * g.total_lesson_time * g.weekly_lesson_count) AS total_credit
    FROM groups_quota gq
    JOIN `groups` g ON g.id = gq.group_id
    WHERE gq.status = 1
");

$created = 0;
$skipped = 0;

foreach ($approved->fetchAll(PDO::FETCH_ASSOC) as $row) {
    $gtStmt = $db->prepare('SELECT * FROM group_time WHERE group_id = ? ORDER BY start_date ASC');
    $gtStmt->execute([$row['group_id']]);
    $groupTimes = $gtStmt->fetchAll(PDO::FETCH_ASSOC);

    $firstLesson = 0;
    foreach ($groupTimes as $gt) {
        $dup = $db->prepare('SELECT id FROM appointment WHERE student_id = ? AND group_time_id = ?');
        $dup->execute([$row['user_id'], $gt['id']]);
        if ($dup->fetch()) { $firstLesson = 1; $skipped++; continue; }

        $ins = $db->prepare('INSERT INTO appointment (student_id, teacher_id, lesson_id, group_id, group_time_id, room_id, first_lesson, start_date, end_date, status, credit, total_credit, type, revise, teacher_report, student_report, income) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 0, ?, ?, 1, 0, 0, 0, 0)');
        $ins->execute([
            $row['user_id'],
            $row['teacher_id'],
            $row['lesson_id'],
            $row['group_id'],
            $gt['id'],
            $gt['id'],
            $firstLesson,
            $gt['start_date'],
            $gt['end_date'],
            $row['credit'],
            $row['total_credit'],
        ]);
        $created++;
        $firstLesson = 1;
    }
}

// Mevcut grup appointment'larında room_id eksikse group_time_id ile doldur
$fixRoom = $db->exec("UPDATE appointment SET room_id = group_time_id WHERE type = 1 AND (room_id IS NULL OR room_id = '') AND group_time_id IS NOT NULL AND group_time_id != ''");

echo "<p>Tamamlandı. Oluşturulan: <strong>$created</strong>, Zaten var (atlandı): <strong>$skipped</strong>, room_id güncellenen: <strong>$fixRoom</strong></p>";
?>
