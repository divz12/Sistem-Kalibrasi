<?php
session_start();
include "../../koneksi.php";

$role = $_SESSION["role"] ?? "";
if (!isset($_SESSION["id_user"])) { header("Location: ../../login.php"); exit(); }
if ($role != "admin" && $role != "cs" && $role != "admin_cs") { header("Location: ../../login.php"); exit(); }

$id = (int)($_POST["id_sertifikat"] ?? 0);
$nomor = $_POST["nomor_sertifikat"] ?? "";
$tanggal = $_POST["tanggal_terbit"] ?? "";
$ket = $_POST["keterangan_sertifikat"] ?? "";

if ($id <= 0) {
  header("Location: sertifikat.php?msg=err");
  exit();
}

/* ambil data lama */
$q = mysqli_query($conn, "SELECT * FROM tbl_sertifikat WHERE id_sertifikat='$id' LIMIT 1");
$lama = mysqli_fetch_assoc($q);
$namaFileLama = $lama["nama_file_sertifikat"] ?? "";
$lokasiFileLama = $lama["lokasi_file_sertifikat"] ?? "";

/* upload baru kalau ada */
$namaFileBaru = $_FILES["file_sertifikat"]["name"];
$tmp = $_FILES["file_sertifikat"]["tmp_name"];

if ($namaFileBaru != "") {
  $folder = "file-sertifikat/";
  $tujuan = $folder . $namaFileBaru;
  $lokasiDb = "file-sertifikat/" . $namaFileBaru;

  move_uploaded_file($tmp, $tujuan);

  $sql = "
    UPDATE tbl_sertifikat SET
      nomor_sertifikat = '$nomor',
      tanggal_terbit = '$tanggal',
      nama_file_sertifikat = '$namaFileBaru',
      lokasi_file_sertifikat = '$lokasiDb',
      keterangan_sertifikat = '$ket'
    WHERE id_sertifikat = '$id'
  ";
} else {
  $sql = "
    UPDATE tbl_sertifikat SET
      nomor_sertifikat = '$nomor',
      tanggal_terbit = '$tanggal',
      keterangan_sertifikat = '$ket'
    WHERE id_sertifikat = '$id'
  ";
}

$jalan = mysqli_query($conn, $sql);

if ($jalan) {
  header("Location: sertifikat.php?msg=ok");
  exit();
} else {
  header("Location: sertifikat.php?msg=err");
  exit();
}
?>
