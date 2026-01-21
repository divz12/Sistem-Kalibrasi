<?php
session_start();
include "../koneksi.php";

// proteksi login pelanggan
if (!isset($_SESSION['id_user']) || ($_SESSION['role'] ?? '') !== 'pelanggan') {
  header("Location: ../login.php");
  exit();
}

$id_user = (int)($_SESSION['id_user'] ?? 0);
$id_pengajuan = (int)($_GET['id_pengajuan'] ?? 0);

// kalau id tidak valid -> balik
if ($id_pengajuan <= 0) {
  header("Location: sertifikat.php?msg=invalid");
  exit();
}

// ambil id_pelanggan dari user login
$sqlPel = "SELECT id_pelanggan FROM tbl_pelanggan WHERE id_user='$id_user' LIMIT 1";
$qPel = mysqli_query($conn, $sqlPel);
$dataPel = mysqli_fetch_assoc($qPel);
$id_pelanggan = (int)($dataPel['id_pelanggan'] ?? 0);

if ($id_pelanggan <= 0) {
  header("Location: profil.php?msg=pelanggan_notfound");
  exit();
}

// pastikan pengajuan milik pelanggan yg login
$sqlCekPengajuan = "
  SELECT id_pengajuan
  FROM tbl_pengajuan_kalibrasi
  WHERE id_pengajuan='$id_pengajuan'
    AND id_pelanggan='$id_pelanggan'
  LIMIT 1
";
$qCekPengajuan = mysqli_query($conn, $sqlCekPengajuan);
$cek = mysqli_fetch_assoc($qCekPengajuan);

if (!$cek) {
  header("Location: sertifikat.php?msg=notyours");
  exit();
}

// ambil data sertifikat dari pengajuan
$sqlSertifikat = "
  SELECT
    id_sertifikat,
    nomor_sertifikat,
    tanggal_terbit,
    nama_file_sertifikat,
    lokasi_file_sertifikat
  FROM tbl_sertifikat
  WHERE id_pengajuan='$id_pengajuan'
  ORDER BY id_sertifikat DESC
  LIMIT 1
";
$qSertifikat = mysqli_query($conn, $sqlSertifikat);
$sertifikat = mysqli_fetch_assoc($qSertifikat);

if (!$sertifikat) {
  header("Location: sertifikat.php?msg=nosertifikat");
  exit();
}

$namaFile = $sertifikat['nama_file_sertifikat'] ?? "sertifikat.pdf";
$lokasiDB = $sertifikat['lokasi_file_sertifikat'] ?? "";

// kalau lokasi kosong -> balik
if ($lokasiDB == "") {
  header("Location: sertifikat.php?msg=nofilepath");
  exit();
}

$lokasiDB = str_replace("\\", "/", $lokasiDB);

while (strpos($lokasiDB, "../") === 0) {
  $lokasiDB = substr($lokasiDB, 3);
}
while (strpos($lokasiDB, "./") === 0) {
  $lokasiDB = substr($lokasiDB, 2);
}

$lokasiDB = ltrim($lokasiDB, "/");

$folderAdminSertifikat = rtrim("../admin/Sertifikat", "/") . "/";

// gabungkan path
$pathFile = $folderAdminSertifikat . $lokasiDB;

if (!file_exists($pathFile)) {
  header("Location: sertifikat.php?msg=filenotfound");
  exit();
}

$ext = strtolower(pathinfo($pathFile, PATHINFO_EXTENSION));

if ($ext == "pdf") {
  header("Content-Type: application/pdf");
} elseif ($ext == "jpg" || $ext == "jpeg") {
  header("Content-Type: image/jpeg");
} elseif ($ext == "png") {
  header("Content-Type: image/png");
} else {
  header("Content-Type: application/octet-stream");
}

header("Content-Disposition: attachment; filename=\"$namaFile\"");
header("Content-Length: " . filesize($pathFile));
readfile($pathFile);
exit();
?>
