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

$nama     = $_POST["nama"] ?? "";
$email    = $_POST["email"] ?? "";
$password = $_POST["password"] ?? "";
$no_hp    = $_POST["no_hp"] ?? "";
$alamat   = $_POST["alamat"] ?? "";

// validasi input
if ($nama == "" || $email == "" || $password == "" || $no_hp == "" || $alamat == "") {
  header("Location: tambah_pelanggan.php?msg=err");
  exit();
}

// cek ada tanda petik atau tidak
if (adaTandaPetik($nama) || adaTandaPetik($email) || adaTandaPetik($password) || adaTandaPetik($no_hp) || adaTandaPetik($alamat)) {
  header("Location: tambah_pelanggan.php?msg=err");
  exit();
}

// cek email sudah dipakai atau belum
$sqlCek = "
  SELECT id_user
  FROM tbl_users
  WHERE email = '$email'
  LIMIT 1
";
$cek = mysqli_query($conn, $sqlCek);
if (mysqli_num_rows($cek) > 0) {
  header("Location: tambah_pelanggan.php?msg=err");
  exit();
}

$sqlUser = "
  INSERT INTO tbl_users
  (nama, email, password, role, foto, created_at, updated_at)
  VALUES
  ('$nama', '$email', '$password', 'pelanggan', '', NOW(), NOW())
";
$simpanUser = mysqli_query($conn, $sqlUser);
if (!$simpanUser) {
  header("Location: tambah_pelanggan.php?msg=err");
  exit();
}

$idUserBaru = mysqli_insert_id($conn);
if ($idUserBaru <= 0) {
  header("Location: tambah_pelanggan.php?msg=err");
  exit();
}

$sqlPelanggan = "
  INSERT INTO tbl_pelanggan
  (id_user, alamat, no_hp, created_at, updated_at)
  VALUES
  ('$idUserBaru', '$alamat', '$no_hp', NOW(), NOW())
";
$simpanPelanggan = mysqli_query($conn, $sqlPelanggan);
if (!$simpanPelanggan) {
  header("Location: tambah_pelanggan.php?msg=err");
  exit();
}

header("Location: pelanggan.php?msg=ok");
exit();
