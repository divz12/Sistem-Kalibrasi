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

$nama = $_POST["nama"] ?? "";
$email = $_POST["email"] ?? "";
$password = $_POST["password"] ?? "";
$roleUser = $_POST["role"] ?? "";

if ($nama == "" || $email == "" || $password == "" || $roleUser == "") {
  header("Location: tambah_pengguna.php?msg=err");
  exit();
}

$sqlCek = "
  SELECT id_user
  FROM tbl_users
  WHERE email = '$email'
  LIMIT 1
";
$cek = mysqli_query($conn, $sqlCek);
if (mysqli_num_rows($cek) > 0) {
  header("Location: tambah_pengguna.php?msg=err");
  exit();
}

$sql = "
  INSERT INTO tbl_users
  (nama, email, password, role, foto, created_at, updated_at)
  VALUES
  ('$nama', '$email', '$password', '$roleUser', '', NOW(), NOW())
";
$simpan = mysqli_query($conn, $sql);

if (!$simpan) {
  header("Location: tambah_pengguna.php?msg=err");
  exit();
}

header("Location: pengguna.php?msg=ok");
exit();
?>