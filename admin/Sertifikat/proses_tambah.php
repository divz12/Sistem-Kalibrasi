<?php
session_start();
include "../../koneksi.php";

$role = $_SESSION["role"] ?? "";

if (!isset($_SESSION["id_user"])) {
  header("Location: ../../login.php");
  exit();
}

if ($role != "admin" && $role != "cs" && $role != "admin_cs") {
  header("Location: ../../login.php");
  exit();
}

/* ambil data form */
$idPengajuan = (int)($_POST["id_pengajuan"] ?? 0);
$nomorSertifikat = $_POST["nomor_sertifikat"] ?? "";
$tanggalTerbit = $_POST["tanggal_terbit"] ?? "";
$keteranganSertifikat = $_POST["keterangan_sertifikat"] ?? "";

/* upload file */
$namaFile = $_FILES["file_sertifikat"]["name"];
$lokasiSementara = $_FILES["file_sertifikat"]["tmp_name"];

$folderTujuan = "file-sertifikat/";
$lokasiTujuan = $folderTujuan . $namaFile;

/* path yang disimpan ke database */
$lokasiSimpanDatabase = "file-sertifikat/" . $namaFile;

$terupload = move_uploaded_file($lokasiSementara, $lokasiTujuan);

if ($terupload) {

  if ($idPengajuan <= 0 || $nomorSertifikat == "" || $tanggalTerbit == "") {
    header("Location: sertifikat.php?msg=err");
    exit();
  }

  /* cek pengajuan harus selesai */
  $cek = mysqli_query($conn, "SELECT status_pengajuan FROM tbl_pengajuan_kalibrasi WHERE id_pengajuan='$idPengajuan' LIMIT 1");
  $dataCek = mysqli_fetch_assoc($cek);
  $statusPengajuan = $dataCek["status_pengajuan"] ?? "";

  if ($statusPengajuan != "selesai") {
    header("Location: sertifikat.php?msg=err");
    exit();
  }

  $querySimpan = "
    INSERT INTO tbl_sertifikat
      (id_pengajuan, nomor_sertifikat, tanggal_terbit, nama_file_sertifikat, lokasi_file_sertifikat, keterangan_sertifikat)
    VALUES
      ('$idPengajuan', '$nomorSertifikat', '$tanggalTerbit', '$namaFile', '$lokasiSimpanDatabase', '$keteranganSertifikat')
  ";

  $jalan = mysqli_query($conn, $querySimpan);

  if ($jalan) {
    header("Location: sertifikat.php?msg=ok");
    exit();
  } else {
    header("Location: sertifikat.php?msg=err");
    exit();
  }

} else {
  header("Location: sertifikat.php?msg=err");
  exit();
}
?>