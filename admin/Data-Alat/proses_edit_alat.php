<?php
session_start();
include "../../koneksi.php";

$role = $_SESSION["role"] ?? "";

if (!isset($_SESSION["id_user"])) {
  header("Location: ../../login.php");
  exit();
}

if ($role != "admin" && $role != "cs") {
  header("Location: ../../login.php");
  exit();
}

$id_alat = (int)($_POST["id_alat"] ?? 0);
$id_pengajuan = (int)($_POST["id_pengajuan"] ?? 0);

$nama_alat = $_POST["nama_alat"] ?? "";
$merk_tipe = $_POST["merk_tipe"] ?? "";
$kapasitas = $_POST["kapasitas"] ?? "";
$jumlah_unit = (int)($_POST["jumlah_unit"] ?? 1);
$parameter = $_POST["parameter"] ?? "";
$titik_ukur = $_POST["titik_ukur"] ?? "";
$keterangan = $_POST["keterangan"] ?? "";

if ($id_alat <= 0) {
  header("Location: alat_pengajuan.php?msg=err");
  exit();
}

if ($jumlah_unit <= 0) {
  $jumlah_unit = 1;
}

$sql = "
  UPDATE tbl_pengajuan_alat
  SET
    nama_alat = '$nama_alat',
    merk_tipe = '$merk_tipe',
    kapasitas = '$kapasitas',
    jumlah_unit = '$jumlah_unit',
    parameter = '$parameter',
    titik_ukur = '$titik_ukur',
    keterangan = '$keterangan'
  WHERE id_alat = '$id_alat'
";
$jalan = mysqli_query($conn, $sql);

if (!$jalan) {
  header("Location: edit_alat.php?id=".$id_alat."&msg=err");
  exit();
}

header("Location: detail_alat.php?id=".$id_alat."&msg=ok");
exit();
?>