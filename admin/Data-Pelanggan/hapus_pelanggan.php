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

$id_pelanggan = (int)($_GET["id"] ?? 0);
if ($id_pelanggan <= 0) {
  header("Location: pelanggan.php?msg=err");
  exit();
}

$sqlCari = "
  SELECT id_user
  FROM tbl_pelanggan
  WHERE id_pelanggan = '$id_pelanggan'
  LIMIT 1
";
$hasilCari = mysqli_query($conn, $sqlCari);
$dataCari = mysqli_fetch_assoc($hasilCari);
$id_user = (int)($dataCari["id_user"] ?? 0);

if ($id_user <= 0) {
  header("Location: pelanggan.php?msg=err");
  exit();
}

// hapus pesan cs
$sqlHapusPesan = "
  DELETE FROM tbl_pesan_cs
  WHERE id_pelanggan = '$id_pelanggan'
";
mysqli_query($conn, $sqlHapusPesan);

$sqlPengajuan = "
  SELECT id_pengajuan
  FROM tbl_pengajuan_kalibrasi
  WHERE id_pelanggan = '$id_pelanggan'
";
$hasilPengajuan = mysqli_query($conn, $sqlPengajuan);

$idPengajuanList = [];
while ($row = mysqli_fetch_assoc($hasilPengajuan)) {
  $idPengajuanList[] = $row["id_pengajuan"];
}

// hapus alat + penawaran per pengajuan
for ($i = 0; $i < count($idPengajuanList); $i++) {
  $id_pengajuan = (int)$idPengajuanList[$i];

  $sqlHapusPenawaran = "
    DELETE FROM tbl_penawaran
    WHERE id_pengajuan = '$id_pengajuan'
  ";
  mysqli_query($conn, $sqlHapusPenawaran);

  $sqlHapusAlat = "
    DELETE FROM tbl_pengajuan_alat
    WHERE id_pengajuan = '$id_pengajuan'
  ";
  mysqli_query($conn, $sqlHapusAlat);
}

// hapus pengajuan
$sqlHapusPengajuan = "
  DELETE FROM tbl_pengajuan_kalibrasi
  WHERE id_pelanggan = '$id_pelanggan'
";
mysqli_query($conn, $sqlHapusPengajuan);

// hapus pelanggan
$sqlHapusPelanggan = "
  DELETE FROM tbl_pelanggan
  WHERE id_pelanggan = '$id_pelanggan'
";
$hapusPel = mysqli_query($conn, $sqlHapusPelanggan);
if (!$hapusPel) {
  header("Location: pelanggan.php?msg=err");
  exit();
}

// hapus user
$sqlHapusUser = "
  DELETE FROM tbl_users
  WHERE id_user = '$id_user'
";
$hapusUser = mysqli_query($conn, $sqlHapusUser);
if (!$hapusUser) {
  header("Location: pelanggan.php?msg=err");
  exit();
}

header("Location: pelanggan.php?msg=ok");
exit();
