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

$idPenawaran = (int)($_GET["id"] ?? 0);
if ($idPenawaran <= 0) {
  header("Location: penawaran.php?msg=err");
  exit();
}

$sql = "
  DELETE FROM tbl_penawaran
  WHERE id_penawaran = '$idPenawaran'
";
$hapus = mysqli_query($conn, $sql);

if (!$hapus) {
  header("Location: penawaran.php?msg=err");
  exit();
}

header("Location: penawaran.php?msg=ok");
exit();
?>