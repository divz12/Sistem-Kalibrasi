<?php
session_start();
include "../koneksi.php";

// proteksi login pelanggan
if (!isset($_SESSION['id_user']) || ($_SESSION['role'] ?? '') !== 'pelanggan') {
  header("Location: ../login.php");
  exit();
}

$id_user = (int)($_SESSION['id_user'] ?? 0);
$id_pengajuan = (int)($_POST['id_pengajuan'] ?? 0);

if ($id_pengajuan <= 0) {
  header("Location: status_proses.php?msg=err");
  exit();
}

// ambil id_pelanggan
$qPel = mysqli_query($conn, "SELECT id_pelanggan FROM tbl_pelanggan WHERE id_user='$id_user' LIMIT 1");
$dataPel = $qPel ? mysqli_fetch_assoc($qPel) : null;
$id_pelanggan = (int)($dataPel['id_pelanggan'] ?? 0);

if ($id_pelanggan <= 0) {
  header("Location: profil.php");
  exit();
}

// cek pengajuan milik pelanggan + cek status sekarang + cek invoice sudah dibayar
$sqlCek = "
  SELECT
    pk.id_pengajuan,
    pk.status_pengajuan,
    i.status_pembayaran
  FROM tbl_pengajuan_kalibrasi pk
  LEFT JOIN tbl_penawaran p ON p.id_pengajuan = pk.id_pengajuan
  LEFT JOIN tbl_invoice i ON i.id_penawaran = p.id_penawaran
  WHERE pk.id_pengajuan = '$id_pengajuan'
    AND pk.id_pelanggan = '$id_pelanggan'
  LIMIT 1
";
$qCek = mysqli_query($conn, $sqlCek);
$row = $qCek ? mysqli_fetch_assoc($qCek) : null;

if (!$row) {
  header("Location: status_proses.php?msg=forbidden");
  exit();
}

$statusPengajuan = strtolower($row['status_pengajuan'] ?? '');
$statusPembayaran = strtolower($row['status_pembayaran'] ?? '');

// hanya boleh “Terima” kalau:
// - status pengajuan masih diproses
// - invoice sudah dibayar
if (!($statusPengajuan == 'diproses' && $statusPembayaran == 'sudah dibayar')) {
  header("Location: status_proses.php?msg=err");
  exit();
}

// update status jadi selesai
$upd = mysqli_query($conn, "
  UPDATE tbl_pengajuan_kalibrasi
  SET status_pengajuan = 'selesai'
  WHERE id_pengajuan = '$id_pengajuan'
    AND id_pelanggan = '$id_pelanggan'
");

if (!$upd) {
  header("Location: status_proses.php?msg=err");
  exit();
}

header("Location: status_proses.php?msg=ok");
exit();
?>