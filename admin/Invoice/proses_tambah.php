<?php
session_start();
include "../../koneksi.php";

$role = $_SESSION["role"] ?? "";

if (!isset($_SESSION["id_user"])) {
  header("Location: ../../login.php");
  exit();
}

if ($role != "admin" && $role != "cs" && $role != "owner") {
  header("Location: ../../login.php");
  exit();
}

$idPenawaran = (int)($_POST["id_penawaran"] ?? 0);
$nomorInvoice = $_POST["nomor_invoice"] ?? "";
$tanggalInvoice = $_POST["tanggal_invoice"] ?? "";
$tanggalJatuhTempo = $_POST["tanggal_jatuh_tempo"] ?? "";
$totalTagihan = $_POST["total_tagihan"] ?? "0";
$statusPembayaran = $_POST["status_pembayaran"] ?? "belum dibayar";
$keteranganInvoice = $_POST["keterangan_invoice"] ?? "";

// upload file invoice
$namaFile = $_FILES["file_invoice"]["name"];
$lokasiSementara = $_FILES["file_invoice"]["tmp_name"];

$folderTujuan = "file-invoice/";
$lokasiTujuan = $folderTujuan . $namaFile;

// lokasi file yang disimpan
$lokasiSimpanDatabase = "file-invoice/" . $namaFile;

$terupload = move_uploaded_file($lokasiSementara, $lokasiTujuan);

if ($terupload) {

  if ($idPenawaran <= 0 || $nomorInvoice == "" || $tanggalInvoice == "") {
    header("Location: invoice.php?msg=err");
    exit();
  }

 // cek status penawaran
  $cek = mysqli_query($conn, "SELECT status_penawaran FROM tbl_penawaran WHERE id_penawaran='$idPenawaran' LIMIT 1");
  $dataCek = mysqli_fetch_assoc($cek);
  $statusPenawaran = $dataCek["status_penawaran"] ?? "";

  if ($statusPenawaran != "diterima") {
    header("Location: invoice.php?msg=err");
    exit();
  }

  $querySimpan = "
    INSERT INTO tbl_invoice
      (id_penawaran, nomor_invoice, tanggal_invoice, tanggal_jatuh_tempo, total_tagihan, status_pembayaran, nama_file_invoice, lokasi_file_invoice, keterangan_invoice)
    VALUES
      ('$idPenawaran', '$nomorInvoice', '$tanggalInvoice', '$tanggalJatuhTempo', '$totalTagihan', '$statusPembayaran', '$namaFile', '$lokasiSimpanDatabase', '$keteranganInvoice')
  ";

  $jalan = mysqli_query($conn, $querySimpan);

  if ($jalan) {
    header("Location: invoice.php?msg=ok");
    exit();
  } else {
    header("Location: invoice.php?msg=err");
    exit();
  }

} else {
  header("Location: invoice.php?msg=err");
  exit();
}
?>