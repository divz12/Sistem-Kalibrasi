<?php
session_start();
include "../koneksi.php";

if (!isset($_SESSION['id_user']) || ($_SESSION['role'] ?? '') !== 'pelanggan') {
  header("Location: ../login.php");
  exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  header("Location: hub-cs.php");
  exit();
}

$idPelanggan = (int)($_POST['id_pelanggan'] ?? 0);
$kontak = $_POST['kontak'] ?? '';
$pesan  = $_POST['pesan'] ?? '';

if ($idPelanggan <= 0 || $kontak == '' || $pesan == '') {
  header("Location: hub-cs.php?msg=err");
  exit();
}

// balasan otomatis
$balasanOtomatis = "Terima kasih, pesan kamu sudah kami terima. Tim CS akan meninjau dan membantu secepatnya. 
                    Jika butuh respon cepat, kamu bisa hubungi WhatsApp CS.";

// simpan ke database
$sql = "
  INSERT INTO tbl_pesan_cs
  (id_pelanggan, kontak, pesan, waktu_kirim, balasan_otomatis, status_baca_admin)
  VALUES
  ('$idPelanggan', '$kontak', '$pesan', NOW(), '$balasanOtomatis', 0)
";

$simpan = mysqli_query($conn, $sql);

if ($simpan) {
  header("Location: hub-cs.php?msg=ok");
  exit();
} else {
  header("Location: hub-cs.php?msg=err");
  exit();
}
