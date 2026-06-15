<?php
include("../config/connection.php");
?>
<h2>Öğretmenlerin accessible_lessons değerleri</h2>
<table border="1">
<tr><th>id</th><th>fullname</th><th>accessible_lessons (ham)</th></tr>
<?php
$stmt = $db->query("SELECT id, fullname, accessible_lessons FROM users WHERE role = 2 LIMIT 10");
while ($r = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo "<tr><td>{$r['id']}</td><td>" . htmlspecialchars($r['fullname']) . "</td><td><code>" . htmlspecialchars((string)$r['accessible_lessons']) . "</code></td></tr>\n";
}
?>
</table>

<h2>lessons tablosu</h2>
<table border="1">
<tr><th>id</th><th>title</th><th>description</th></tr>
<?php
$stmt = $db->query("SELECT id, title, description FROM lessons LIMIT 20");
while ($r = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo "<tr><td>{$r['id']}</td><td>" . htmlspecialchars($r['title']) . "</td><td>" . htmlspecialchars($r['description']) . "</td></tr>\n";
}
?>
</table>
