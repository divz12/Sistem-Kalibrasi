<?php
session_start();
include "../../koneksi.php";

$role = $_SESSION["role"] ?? "";

if (!isset($_SESSION["id_user"])) {
  header("Location: ../../login.php");
  exit();
}

if ($role != "admin" && $role != "cs") {
  header("Location: invoice.php?msg=err");
  exit();
}

if ($_SERVER["REQUEST_METHOD"] != "POST") {
  header("Location: invoice.php?msg=err");
  exit();
}

$idInvoice = (int)($_POST["id_invoice"] ?? 0);
$nomorInvoice = $_POST["nomor_invoice"] ?? "";
$tanggalInvoice = $_POST["tanggal_invoice"] ?? "";
$tanggalJatuhTempo = $_POST["tanggal_jatuh_tempo"] ?? "";
$totalTagihan = $_POST["total_tagihan"] ?? 0;
$statusPembayaran = $_POST["status_pembayaran"] ?? "belum dibayar";
$keteranganInvoice = $_POST["keterangan_invoice"] ?? "";

if ($idInvoice <= 0) {
  header("Location: invoice.php?msg=err");
  exit();
}

$sqlLama = "
  SELECT nama_file_invoice, lokasi_file_invoice
  FROM tbl_invoice
  WHERE id_invoice = '$idInvoice'
  LIMIT 1
";
$hasilLama = mysqli_query($conn, $sqlLama);
$dataLama = mysqli_fetch_assoc($hasilLama);

$namaFileLama = $dataLama["nama_file_invoice"] ?? "";
$lokasiFileLama = $dataLama["lokasi_file_invoice"] ?? "";

$namaFileBaru = $namaFileLama;
$lokasiFileBaru = $lokasiFileLama;

// jika ada file baru diupload
if (!empty($_FILES["file_invoice"]["name"])) {

  $namaFile = $_FILES["file_invoice"]["name"];
  $lokasiSementara = $_FILES["file_invoice"]["tmp_name"];

  // buat folder kalau belum ada
  $folderTujuan = "../../file_invoice/";
  if (!is_dir($folderTujuan)) {
    mkdir($folderTujuan, 0777, true);
  }

  $lokasiTujuan = $folderTujuan . $namaFile;

  $terupload = move_uploaded_file($lokasiSementara, $lokasiTujuan);

  if ($terupload) {
    // set nama file dan lokasi file baru
    $namaFileBaru = $namaFile;
    $lokasiFileBaru = "file_invoice/" . $namaFile;

    // hapus file lama
    if ($lokasiFileLama != "") {
      $pathHapus = "../../" . $lokasiFileLama;
      if (file_exists($pathHapus)) {
        unlink($pathHapus);
      }
    }

  } else {
    header("Location: invoice.php?msg=err");
    exit();
  }
}

$sqlUpdate = "
  UPDATE tbl_invoice SET
    nomor_invoice = '$nomorInvoice',
    tanggal_invoice = '$tanggalInvoice',
    tanggal_jatuh_tempo = '$tanggalJatuhTempo',
    total_tagihan = '$totalTagihan',
    status_pembayaran = '$statusPembayaran',
    nama_file_invoice = '$namaFileBaru',
    lokasi_file_invoice = '$lokasiFileBaru',
    keterangan_invoice = '$keteranganInvoice'
  WHERE id_invoice = '$idInvoice'
";

$update = mysqli_query($conn, $sqlUpdate);

if (!$update) {
  header("Location: invoice.php?msg=err");
  exit();
}

header("Location: invoice.php?msg=ok");
exit();
?>
