<?php
session_start();
include "../../koneksi.php";

$role = $_SESSION["role"] ?? "";

if (!isset($_SESSION["id_user"])) {
  header("Location: ../../login.php");
  exit();
}

if ($role != "admin" && $role != "cs") {
  header("Location: ../../login.php");
  exit();
}

$id_pengajuan = (int)($_POST["id_pengajuan"] ?? 0);
$status_pengajuan = $_POST["status_pengajuan"] ?? "";

if ($id_pengajuan <= 0 || $status_pengajuan == "") {
  header("Location: detail_pengajuan.php?id=".$id_pengajuan."&msg=err");
  exit();
}

$sql = "
  UPDATE tbl_pengajuan_kalibrasi
  SET
    status_pengajuan = '$status_pengajuan',
    updated_at = NOW()
  WHERE id_pengajuan = '$id_pengajuan'
";
$jalan = mysqli_query($conn, $sql);

if (!$jalan) {
  header("Location: detail_pengajuan.php?id=".$id_pengajuan."&msg=err");
  exit();
}

header("Location: detail_pengajuan.php?id=".$id_pengajuan."&msg=ok");
exit();
?>