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

$idPengajuan = (int)($_GET["id"] ?? 0);
if ($idPengajuan <= 0) {
  header("Location: pengajuan.php?msg=err");
  exit();
}

// hapus penawaran
$sqlHapusPenawaran = "
  DELETE FROM tbl_penawaran
  WHERE id_pengajuan = '$idPengajuan'
";
mysqli_query($conn, $sqlHapusPenawaran);

// hapus alat
$sqlHapusAlat = "
  DELETE FROM tbl_pengajuan_alat
  WHERE id_pengajuan = '$idPengajuan'
";
mysqli_query($conn, $sqlHapusAlat);

// hapus pengajuan
$sqlHapusPengajuan = "
  DELETE FROM tbl_pengajuan_kalibrasi
  WHERE id_pengajuan = '$idPengajuan'
";
$hapus = mysqli_query($conn, $sqlHapusPengajuan);

if (!$hapus) {
  header("Location: pengajuan.php?msg=err");
  exit();
}

header("Location: pengajuan.php?msg=ok");
exit();
