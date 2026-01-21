<?php
session_start();
include "../koneksi.php";

// proteksi login pelanggan
if (!isset($_SESSION['id_user']) || ($_SESSION['role'] ?? '') !== 'pelanggan') {
  header("Location: ../login.php");
  exit();
}

function kolomAda($conn, $namaTabel, $namaKolom)
{
  $sql = "
    SELECT COUNT(*) AS total
    FROM information_schema.columns
    WHERE table_schema = DATABASE()
      AND table_name = '$namaTabel'
      AND column_name = '$namaKolom'
  ";
  $q = mysqli_query($conn, $sql);
  if (!$q) return false;
  $r = mysqli_fetch_assoc($q);
  return ((int)($r['total'] ?? 0) > 0);
}

$id_user = (int)($_SESSION['id_user'] ?? 0);
$id_pengajuan = (int)($_GET['id_pengajuan'] ?? 0);

if ($id_pengajuan <= 0) {
  header("Location: invoice.php?msg=invalid");
  exit();
}

$qPel = mysqli_query($conn, "SELECT id_pelanggan FROM tbl_pelanggan WHERE id_user='$id_user' LIMIT 1");
$dataPel = mysqli_fetch_assoc($qPel);
$id_pelanggan = (int)($dataPel['id_pelanggan'] ?? 0);

if ($id_pelanggan <= 0) {
  header("Location: profil.php?msg=pelanggan_notfound");
  exit();
}

// cek pengajuan milik pelanggan
$qCek = mysqli_query($conn, "
  SELECT id_pengajuan
  FROM tbl_pengajuan_kalibrasi
  WHERE id_pengajuan='$id_pengajuan' AND id_pelanggan='$id_pelanggan'
  LIMIT 1
");
$cek = mysqli_fetch_assoc($qCek);

if (!$cek) {
  header("Location: invoice.php?msg=notyours");
  exit();
}

// tentukan relasi invoice
$invoicePunyaIdPengajuan = kolomAda($conn, "tbl_invoice", "id_pengajuan");
$invoicePunyaIdPenawaran = kolomAda($conn, "tbl_invoice", "id_penawaran");

// tentukan kolom file invoice
$kolomNamaFile = "";
$kolomLokasiFile = "";

if (kolomAda($conn, "tbl_invoice", "nama_file_invoice")) $kolomNamaFile = "nama_file_invoice";
else if (kolomAda($conn, "tbl_invoice", "nama_file")) $kolomNamaFile = "nama_file";

if (kolomAda($conn, "tbl_invoice", "lokasi_file_invoice")) $kolomLokasiFile = "lokasi_file_invoice";
else if (kolomAda($conn, "tbl_invoice", "lokasi_file")) $kolomLokasiFile = "lokasi_file";

if ($kolomLokasiFile == "") {
  header("Location: invoice.php?msg=nokolomfile");
  exit();
}

// ambil data invoice
$inv = null;

if ($invoicePunyaIdPengajuan) {

  $sqlInv = "
    SELECT nomor_invoice, $kolomNamaFile AS nama_file_invoice, $kolomLokasiFile AS lokasi_file_invoice
    FROM tbl_invoice
    WHERE id_pengajuan='$id_pengajuan'
    ORDER BY tanggal_invoice DESC
    LIMIT 1
  ";
  $qInv = mysqli_query($conn, $sqlInv);
  $inv = mysqli_fetch_assoc($qInv);

} elseif ($invoicePunyaIdPenawaran) {

  // ambil penawaran terbaru untuk pengajuan
  $qPen = mysqli_query($conn, "
    SELECT id_penawaran
    FROM tbl_penawaran
    WHERE id_pengajuan='$id_pengajuan'
    ORDER BY tanggal_penawaran DESC
    LIMIT 1
  ");
  $pen = mysqli_fetch_assoc($qPen);
  $id_penawaran = (int)($pen['id_penawaran'] ?? 0);

  if ($id_penawaran > 0) {
    $sqlInv = "
      SELECT nomor_invoice, $kolomNamaFile AS nama_file_invoice, $kolomLokasiFile AS lokasi_file_invoice
      FROM tbl_invoice
      WHERE id_penawaran='$id_penawaran'
      ORDER BY tanggal_invoice DESC
      LIMIT 1
    ";
    $qInv = mysqli_query($conn, $sqlInv);
    $inv = mysqli_fetch_assoc($qInv);
  }
}

if (!$inv) {
  header("Location: invoice.php?msg=noinvoice");
  exit();
}

$namaFile = $inv['nama_file_invoice'] ?? "";
$lokasiDB = $inv['lokasi_file_invoice'] ?? "";

if ($lokasiDB == "" || $lokasiDB == null) {
  header("Location: invoice.php?msg=nofilepath");
  exit();
}


$lokasiDB = str_replace("\\", "/", $lokasiDB);
$lokasiDB = ltrim($lokasiDB, "/");


$folderInvoiceAdmin = "../admin/Invoice/";
$pathFile = rtrim($folderInvoiceAdmin, "/") . "/" . $lokasiDB;

if (!file_exists($pathFile)) {
  header("Location: invoice.php?msg=filenotfound");
  exit();
}

if ($namaFile == "") {
  $nom = $inv['nomor_invoice'] ?? "invoice";
  $ext = pathinfo($pathFile, PATHINFO_EXTENSION);
  $namaFile = $nom . "." . $ext;
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
