<?php
session_start();
include("../config/connection.php");
checkUnSession();
$sid = $_SESSION['user_id'];
?>
<h2>group_time kolonları</h2>
<?php
$cols = $db->query("SHOW COLUMNS FROM `group_time`");
echo "<ul>";
foreach ($cols->fetchAll(PDO::FETCH_ASSOC) as $c) {
    echo "<li>{$c['Field']} ({$c['Type']})</li>";
}
echo "</ul>";
?>

<h2>group_time – onaylı grupların seansları (group_id 56, 57)</h2>
<table border="1" cellpadding="4">
<?php
$gt = $db->query("SELECT * FROM group_time WHERE group_id IN (56,57) ORDER BY group_id, id LIMIT 50");
$rows = $gt->fetchAll(PDO::FETCH_ASSOC);
if ($rows) {
    echo "<tr>" . implode('', array_map(fn($k) => "<th>$k</th>", array_keys($rows[0]))) . "</tr>";
    foreach ($rows as $r) {
        echo "<tr>" . implode('', array_map(fn($v) => "<td>" . htmlspecialchars((string)$v) . "</td>", $r)) . "</tr>";
    }
} else {
    echo "<tr><td>Kayıt yok</td></tr>";
}
?>
</table>

<h2>group_time – mevcut veri örneği (LIMIT 5, herhangi bir grup)</h2>
<table border="1" cellpadding="4">
<?php
$gt2 = $db->query("SELECT * FROM group_time ORDER BY id DESC LIMIT 5");
$rows2 = $gt2->fetchAll(PDO::FETCH_ASSOC);
if ($rows2) {
    echo "<tr>" . implode('', array_map(fn($k) => "<th>$k</th>", array_keys($rows2[0]))) . "</tr>";
    foreach ($rows2 as $r) {
        echo "<tr>" . implode('', array_map(fn($v) => "<td>" . htmlspecialchars((string)$v) . "</td>", $r)) . "</tr>";
    }
} else {
    echo "<tr><td>Kayıt yok</td></tr>";
}
?>
</table>

<h2>appointment – group_time_id dolu olan örnekler (LIMIT 5)</h2>
<table border="1" cellpadding="4">
<?php
$ap = $db->query("SELECT id, student_id, group_id, group_time_id, type, status, start_date, end_date FROM appointment WHERE group_time_id IS NOT NULL AND group_time_id != '' ORDER BY id DESC LIMIT 5");
$rows3 = $ap->fetchAll(PDO::FETCH_ASSOC);
if ($rows3) {
    echo "<tr>" . implode('', array_map(fn($k) => "<th>$k</th>", array_keys($rows3[0]))) . "</tr>";
    foreach ($rows3 as $r) {
        echo "<tr>" . implode('', array_map(fn($v) => "<td>" . htmlspecialchars((string)$v) . "</td>", $r)) . "</tr>";
    }
} else {
    echo "<tr><td>Kayıt yok</td></tr>";
}
?>
</table>
