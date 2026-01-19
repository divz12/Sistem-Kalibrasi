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

$id_user = (int)($_POST["id_user"] ?? 0);
$nama = $_POST["nama"] ?? "";
$email = $_POST["email"] ?? "";
$password = $_POST["password"] ?? "";
$roleUser = $_POST["role"] ?? "";

if ($id_user <= 0) {
  header("Location: pengguna.php?msg=err");
  exit();
}

if ($nama == "" || $email == "" || $password == "" || $roleUser == "") {
  header("Location: edit_pengguna.php?id=$id_user&msg=err");
  exit();
}

$sqlCek = "
  SELECT id_user
  FROM tbl_users
  WHERE email = '$email'
    AND id_user != '$id_user'
  LIMIT 1
";
$cek = mysqli_query($conn, $sqlCek);
if (mysqli_num_rows($cek) > 0) {
  header("Location: edit_pengguna.php?id=$id_user&msg=err");
  exit();
}

$sql = "
  UPDATE tbl_users
  SET
    nama = '$nama',
    email = '$email',
    password = '$password',
    role = '$roleUser',
    updated_at = NOW()
  WHERE id_user = '$id_user'
";
$update = mysqli_query($conn, $sql);

if (!$update) {
  header("Location: edit_pengguna.php?id=$id_user&msg=err");
  exit();
}

header("Location: pengguna.php?msg=ok");
exit();
?>