<?php
session_start();
include "../../koneksi.php";

$role = $_SESSION["role"] ?? "";

if (!isset($_SESSION["id_user"])) {
  header("Location: ../../login.php");
  exit();
}

if ($role != "admin" && $role != "cs" && $role != "admin_cs") {
  header("Location: penawaran.php?msg=err");
  exit();
}

$id_admin = (int)($_SESSION["id_user"] ?? 0);

$id_pengajuan = (int)($_POST["id_pengajuan"] ?? 0);
$tanggal_penawaran = $_POST["tanggal_penawaran"] ?? "";
$total_biaya = (int)($_POST["total_biaya"] ?? 0);
$rincian = $_POST["rincian"] ?? "";

/*
  IMPORTANT:
  Saat admin buat penawaran, status otomatis "negosiasi"
*/
$status_penawaran = "negosiasi";

if ($id_pengajuan <= 0 || $id_admin <= 0) {
  header("Location: penawaran.php?msg=err");
  exit();
}

/*
  INSERT penawaran + wajib id_admin (karena FK fk_penawaran_admin)
*/
$sqlSimpan = "
  INSERT INTO tbl_penawaran
    (id_pengajuan, id_admin, tanggal_penawaran, total_biaya, rincian, status_penawaran, created_at)
  VALUES
    ('$id_pengajuan', '$id_admin', '$tanggal_penawaran', '$total_biaya', '$rincian', '$status_penawaran', NOW())
";
$simpan = mysqli_query($conn, $sqlSimpan);

if (!$simpan) {
  header("Location: penawaran.php?msg=err");
  exit();
}

/*
  OPTIONAL:
  Kalau kamu mau saat ada penawaran dibuat,
  status pengajuan tetap "dikirim" (biar jelas masih tahap awal).
  Tapi kalau kamu mau bisa juga ubah ke "dikirim" lagi.
  Di sini aku biarkan tidak mengubah pengajuan.
*/

header("Location: penawaran.php?msg=ok");
exit();
