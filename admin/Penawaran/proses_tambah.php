<?php
session_start();
include "../../koneksi.php";

$role = $_SESSION["role"] ?? "";

if (!isset($_SESSION["id_user"])) {
  header("Location: ../../login.php");
  exit();
}

if ($role != "admin" && $role != "cs") {
  header("Location: penawaran.php?msg=err");
  exit();
}

$id_admin = (int)($_SESSION["id_user"] ?? 0);

$id_pengajuan = (int)($_POST["id_pengajuan"] ?? 0);
$tanggal_penawaran = $_POST["tanggal_penawaran"] ?? "";
$total_biaya = (int)($_POST["total_biaya"] ?? 0);
$rincian = $_POST["rincian"] ?? "";

// default status penawaran
$status_penawaran = "negosiasi";

if ($id_pengajuan <= 0 || $id_admin <= 0) {
  header("Location: penawaran.php?msg=err");
  exit();
}

$sqlSimpan = "
  INSERT INTO tbl_penawaran
    (id_pengajuan, id_admin, tanggal_penawaran, total_biaya, rincian, status_penawaran, created_at)
  VALUES
    ('$id_pengajuan', '$id_admin', '$tanggal_penawaran', '$total_biaya', '$rincian', 'negosiasi', NOW())
";
$simpan = mysqli_query($conn, $sqlSimpan);

if (!$simpan) {
  header("Location: penawaran.php?msg=err");
  exit();
}

header("Location: penawaran.php?msg=ok");
exit();
?>