<?php
session_start();
include "../koneksi.php";

// proteksi login pelanggan
if (!isset($_SESSION['id_user']) || ($_SESSION['role'] ?? '') !== 'pelanggan') {
  header("Location: ../login.php");
  exit();
}

$id_user = (int)($_SESSION['id_user'] ?? 0);
$id_penawaran = (int)($_GET['id'] ?? 0);
$aksi = $_GET['aksi'] ?? "";

if ($id_penawaran <= 0) {
  header("Location: penawaran.php?msg=err");
  exit();
}

if ($aksi != "diterima" && $aksi != "ditolak") {
  header("Location: penawaran.php?msg=err");
  exit();
}

$qPel = mysqli_query($conn, "SELECT id_pelanggan FROM tbl_pelanggan WHERE id_user='$id_user' LIMIT 1");
$dataPel = mysqli_fetch_assoc($qPel);
$id_pelanggan = (int)($dataPel['id_pelanggan'] ?? 0);

if ($id_pelanggan <= 0) {
  header("Location: profil.php");
  exit();
}

// cek penawaran milik pelanggan yang login
$sqlCek = "
  SELECT
    tbl_penawaran.id_penawaran,
    tbl_penawaran.id_pengajuan,
    tbl_penawaran.status_penawaran
  FROM tbl_penawaran
  JOIN tbl_pengajuan_kalibrasi
    ON tbl_pengajuan_kalibrasi.id_pengajuan = tbl_penawaran.id_pengajuan
  WHERE tbl_penawaran.id_penawaran = '$id_penawaran'
    AND tbl_pengajuan_kalibrasi.id_pelanggan = '$id_pelanggan'
  LIMIT 1
";
$cek = mysqli_query($conn, $sqlCek);
$data = mysqli_fetch_assoc($cek);

if (!$data) {
  header("Location: penawaran.php?msg=err");
  exit();
}

$statusSekarang = $data["status_penawaran"] ?? "";
$id_pengajuan = (int)($data["id_pengajuan"] ?? 0);


if ($statusSekarang != "dikirim" && $statusSekarang != "negosiasi") {
  header("Location: penawaran.php?msg=err");
  exit();
}

// update status penawaran
$sqlUpdatePenawaran = "
  UPDATE tbl_penawaran
  SET status_penawaran = '$aksi'
  WHERE id_penawaran = '$id_penawaran'
";
$updatePenawaran = mysqli_query($conn, $sqlUpdatePenawaran);

if (!$updatePenawaran) {
  header("Location: penawaran.php?msg=err");
  exit();
}

// jika diterima, update status pengajuan menjadi diproses
if ($aksi == "diterima" && $id_pengajuan > 0) {
  $sqlUpdatePengajuan = "
    UPDATE tbl_pengajuan_kalibrasi
    SET status_pengajuan = 'diproses'
    WHERE id_pengajuan = '$id_pengajuan'
  ";
  mysqli_query($conn, $sqlUpdatePengajuan);
}

header("Location: penawaran.php?msg=ok");
exit();
