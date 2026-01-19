<?php
session_start();
include "../../koneksi.php";

$role = $_SESSION["role"] ?? "";

if (!isset($_SESSION["id_user"])) {
  header("Location: ../../login.php");
  exit();
}

if ($role != "admin" && $role != "owner") {
  header("Location: ../../login.php");
  exit();
}

$id_user = (int)($_GET["id"] ?? 0);
if ($id_user <= 0) {
  header("Location: pengguna.php?msg=err");
  exit();
}

// jangan hapus akun sendiri
if ($id_user == (int)$_SESSION["id_user"]) {
  header("Location: pengguna.php?msg=err");
  exit();
}

// cek apakah user ini punya pelanggan
$sqlCekPel = "
  SELECT id_pelanggan
  FROM tbl_pelanggan
  WHERE id_user = '$id_user'
  LIMIT 1
";
$cekPel = mysqli_query($conn, $sqlCekPel);
$dataPel = mysqli_fetch_assoc($cekPel);

if ($dataPel) {
  $id_pelanggan = (int)$dataPel["id_pelanggan"];

  // hapus pesan cs
  $sqlHapusPesan = "
    DELETE FROM tbl_pesan_cs
    WHERE id_pelanggan = '$id_pelanggan'
  ";
  mysqli_query($conn, $sqlHapusPesan);

  // ambil semua pengajuan
  $sqlPengajuan = "
    SELECT id_pengajuan
    FROM tbl_pengajuan_kalibrasi
    WHERE id_pelanggan = '$id_pelanggan'
  ";
  $hasilPengajuan = mysqli_query($conn, $sqlPengajuan);

  $list = [];
  while ($row = mysqli_fetch_assoc($hasilPengajuan)) {
    $list[] = $row["id_pengajuan"];
  }

  for ($i = 0; $i < count($list); $i++) {
    $id_pengajuan = (int)$list[$i];

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

  $sqlHapusPengajuan = "
    DELETE FROM tbl_pengajuan_kalibrasi
    WHERE id_pelanggan = '$id_pelanggan'
  ";
  mysqli_query($conn, $sqlHapusPengajuan);

  $sqlHapusPelanggan = "
    DELETE FROM tbl_pelanggan
    WHERE id_user = '$id_user'
  ";
  mysqli_query($conn, $sqlHapusPelanggan);
}

// hapus user
$sqlHapusUser = "
  DELETE FROM tbl_users
  WHERE id_user = '$id_user'
";
$hapus = mysqli_query($conn, $sqlHapusUser);

if (!$hapus) {
  header("Location: pengguna.php?msg=err");
  exit();
}

header("Location: pengguna.php?msg=ok");
exit();
?>