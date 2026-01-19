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

$idAlat = (int)($_GET["id"] ?? 0);
if ($idAlat <= 0) {
  header("Location: alat_pengajuan.php?msg=err");
  exit();
}

$sql = "
  DELETE FROM tbl_pengajuan_alat
  WHERE id_alat = '$idAlat'
";
$hapus = mysqli_query($conn, $sql);

if (!$hapus) {
  header("Location: alat_pengajuan.php?msg=err");
  exit();
}

header("Location: alat_pengajuan.php?msg=ok");
exit();
?>