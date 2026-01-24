<?php
$host = "sql210.infinityfree.com";
$user = "if0_40963722";
$pass = "sistemKalibrasi";
$db   = "if0_40963722_dbsistem_kalibrasi";

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
  die("Koneksi DB gagal: " . $conn->connect_error);
}

$conn->set_charset("utf8mb4");

?>
