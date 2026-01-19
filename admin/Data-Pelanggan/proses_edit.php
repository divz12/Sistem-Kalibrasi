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

function adaTandaPetik($teks) {
  $panjang = strlen($teks);
  for ($i = 0; $i < $panjang; $i++) {
    $huruf = $teks[$i];
    if ($huruf == "'" || $huruf == '"') {
      return true;
    }
  }
  return false;
}

$id_pelanggan = (int)($_POST["id_pelanggan"] ?? 0);
$id_user      = (int)($_POST["id_user"] ?? 0);

$nama   = $_POST["nama"] ?? "";
$email  = $_POST["email"] ?? "";
$no_hp  = $_POST["no_hp"] ?? "";
$alamat = $_POST["alamat"] ?? "";

if ($id_pelanggan <= 0 || $id_user <= 0) {
  header("Location: pelanggan.php?msg=err");
  exit();
}

// validasi input
if ($nama == "" || $email == "" || $no_hp == "" || $alamat == "") {
  header("Location: edit_pelanggan.php?id=$id_pelanggan&msg=err");
  exit();
}

// cek ada tanda petik atau tidak
if (adaTandaPetik($nama) || adaTandaPetik($email) || adaTandaPetik($no_hp) || adaTandaPetik($alamat)) {
  header("Location: edit_pelanggan.php?id=$id_pelanggan&msg=err");
  exit();
}

// cek email dipakai user lain atau tidak
$sqlCek = "
  SELECT id_user
  FROM tbl_users
  WHERE email = '$email'
    AND id_user != '$id_user'
  LIMIT 1
";
$cek = mysqli_query($conn, $sqlCek);
if (mysqli_num_rows($cek) > 0) {
  header("Location: edit_pelanggan.php?id=$id_pelanggan&msg=err");
  exit();
}

// update tbl_users
$sqlUser = "
  UPDATE tbl_users
  SET
    nama = '$nama',
    email = '$email',
    updated_at = NOW()
  WHERE id_user = '$id_user'
";
$upUser = mysqli_query($conn, $sqlUser);
if (!$upUser) {
  header("Location: edit_pelanggan.php?id=$id_pelanggan&msg=err");
  exit();
}

// update tbl_pelanggan
$sqlPelanggan = "
  UPDATE tbl_pelanggan
  SET
    alamat = '$alamat',
    no_hp = '$no_hp',
    updated_at = NOW()
  WHERE id_pelanggan = '$id_pelanggan'
";
$upPel = mysqli_query($conn, $sqlPelanggan);
if (!$upPel) {
  header("Location: edit_pelanggan.php?id=$id_pelanggan&msg=err");
  exit();
}

header("Location: pelanggan.php?msg=ok");
exit();
