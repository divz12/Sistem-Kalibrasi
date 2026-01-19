<?php
$host = "localhost";
$user = "root";
$pass = "";
$db   = "dbsistem_kalibrasi";

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
  die("Koneksi DB gagal: " . $conn->connect_error);
}

$conn->set_charset("utf8mb4");

?>
