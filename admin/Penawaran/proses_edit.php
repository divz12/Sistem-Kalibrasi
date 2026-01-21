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

$id_penawaran = (int)($_POST["id_penawaran"] ?? 0);
$tanggal_penawaran = $_POST["tanggal_penawaran"] ?? "";
$total_biaya = (int)($_POST["total_biaya"] ?? 0);
$rincian = $_POST["rincian"] ?? "";
$status_penawaran = $_POST["status_penawaran"] ?? "dikirim";

if ($id_penawaran <= 0) {
  header("Location: penawaran.php?msg=err");
  exit();
}

$sql = "
  UPDATE tbl_penawaran
  SET
    tanggal_penawaran = '$tanggal_penawaran',
    total_biaya = '$total_biaya',
    rincian = '$rincian',
    status_penawaran = 'negosiasi'
  WHERE id_penawaran = '$id_penawaran'
";
$edit = mysqli_query($conn, $sql);

if (!$edit) {
  header("Location: penawaran.php?msg=err");
  exit();
}

header("Location: penawaran.php?msg=ok");
exit();
?>