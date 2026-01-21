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

$id_invoice = (int)($_GET["id_invoice"] ?? 0);
if ($id_invoice <= 0) {
  header("Location: penawaran.php?msg=err");
  exit();
}

// cek kolom ada atau tidak
function kolomAda($conn, $namaTabel, $namaKolom)
{
  $sql = "
    SELECT COUNT(*) AS total
    FROM information_schema.columns
    WHERE table_schema = DATABASE()
      AND table_name = '$namaTabel'
      AND column_name = '$namaKolom'
  ";
  $q = mysqli_query($conn, $sql);
  if (!$q) return false;
  $r = mysqli_fetch_assoc($q);
  return ((int)($r['total'] ?? 0) > 0);
}

// cek invoice ada
$qCek = mysqli_query($conn, "
  SELECT id_invoice, status_pembayaran
  FROM tbl_invoice
  WHERE id_invoice = '$id_invoice'
  LIMIT 1
");
$inv = $qCek ? mysqli_fetch_assoc($qCek) : null;

if (!$inv) {
  header("Location: penawaran.php?msg=notfound");
  exit();
}

$statusNow = strtolower((string)($inv["status_pembayaran"] ?? ""));

// kalau sudah dibayar, langsung balik
if ($statusNow == "sudah dibayar" || $statusNow == "dibayar" || $statusNow == "paid") {
  header("Location: penawaran.php?msg=already_paid");
  exit();
}


$setExtra = "";

// kalau kamu punya kolom waktu bayar
if (kolomAda($conn, "tbl_invoice", "dibayar_pada")) {
  $setExtra .= ", dibayar_pada = NOW()";
} elseif (kolomAda($conn, "tbl_invoice", "tanggal_bayar")) {
  $setExtra .= ", tanggal_bayar = NOW()";
}

$sqlUpdate = "
  UPDATE tbl_invoice
  SET status_pembayaran = 'sudah dibayar'
  $setExtra
  WHERE id_invoice = '$id_invoice'
  LIMIT 1
";
$upd = mysqli_query($conn, $sqlUpdate);

if (!$upd) {
  header("Location: penawaran.php?msg=err");
  exit();
}

header("Location: penawaran.php?msg=paid");
exit();
?>