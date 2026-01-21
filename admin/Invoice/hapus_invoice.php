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


$idInvoice = (int)($_GET["id"] ?? 0);
if ($idInvoice <= 0) {
  header("Location: invoice.php?msg=err");
  exit();
}

$cek = mysqli_query($conn, "SELECT lokasi_file_invoice FROM tbl_invoice WHERE id_invoice='$idInvoice' LIMIT 1");
$data = mysqli_fetch_assoc($cek);
$lokasiFile = $data["lokasi_file_invoice"] ?? "";

$hapus = mysqli_query($conn, "DELETE FROM tbl_invoice WHERE id_invoice='$idInvoice'");

if ($hapus) {
  if ($lokasiFile != "") {
    $path = "../../" . $lokasiFile;
    if (file_exists($path)) {
      unlink($path);
    }
  }
  header("Location: invoice.php?msg=ok");
  exit();
} else {
  header("Location: invoice.php?msg=err");
  exit();
}
?>