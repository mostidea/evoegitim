<?php
$servername = "localhost";
$username = "evoegiti_tech";
$password = "H~yZjT^w])t5";
$dbname = "evoegiti_tech";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);
// Check connection
if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}

// Set charset
if (!$conn->set_charset("utf8mb4")) {
    printf("Error loading character set utf8mb4: %s\n", $conn->error);
    exit();
}
?>
