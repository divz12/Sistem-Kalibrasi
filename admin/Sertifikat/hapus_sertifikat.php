<?php
session_start();
include "../../koneksi.php";

$role = $_SESSION["role"] ?? "";

if (!isset($_SESSION["id_user"])) {
  header("Location: ../../login.php");
  exit();
}

if ($role != "admin" && $role != "cs" && $role != "owner") {
  header("Location: ../../login.php");
  exit();
}

$idSertifikat = (int)($_GET["id"] ?? 0);
if ($idSertifikat <= 0) {
  header("Location: sertifikat.php?msg=err");
  exit();
}

$cek = mysqli_query($conn, "SELECT lokasi_file_sertifikat FROM tbl_sertifikat WHERE id_sertifikat='$idSertifikat' LIMIT 1");
$data = mysqli_fetch_assoc($cek);
$lokasiFile = $data["lokasi_file_sertifikat"] ?? "";

$hapus = mysqli_query($conn, "DELETE FROM tbl_sertifikat WHERE id_sertifikat='$idSertifikat'");

if ($hapus) {
  if ($lokasiFile != "") {
    $path = "../../" . $lokasiFile;
    if (file_exists($path)) {
      unlink($path);
    }
  }
  header("Location: sertifikat.php?msg=ok");
  exit();
} else {
  header("Location: sertifikat.php?msg=err");
  exit();
}
?>