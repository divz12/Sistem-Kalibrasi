<?php
session_start();
include "koneksi.php";

if ($_SERVER['REQUEST_METHOD'] != 'POST') {
  header("Location: contact.php?msg=err");
  exit();
}

// ambil data dari form
$nama    = $_POST['name'] ?? '';
$email   = $_POST['email'] ?? '';
$subject = $_POST['subject'] ?? '';
$pesan   = $_POST['message'] ?? '';

// validasi input
if ($nama === '' || $email === '' || $subject === '' || $pesan === '') {
  header("Location: contact.php?msg=err");
  exit();
}

// buat isi pesan
$isiPesan  = "Nama: " . $nama . "\n";
$isiPesan .= "Email: " . $email . "\n";
$isiPesan .= "Subjek: " . $subject . "\n\n";
$isiPesan .= $pesan;

$id_pelanggan = "NULL";

$role    = $_SESSION['role'] ?? '';
$id_user = (int)($_SESSION['id_user'] ?? 0);


if ($role === 'pelanggan' && $id_user > 0) {
  $q = mysqli_query($conn, "SELECT id_pelanggan FROM tbl_pelanggan WHERE id_user = $id_user LIMIT 1");

  if ($q && mysqli_num_rows($q) > 0) {
    $d = mysqli_fetch_assoc($q);
    if (!empty($d['id_pelanggan'])) {
      $id_pelanggan = (int)$d['id_pelanggan'];
    }
  }
}

// simpan ke database
$kontak = $email;
$status_baca_admin = 0;
$balasan_otomatis = "Terima kasih, pesan kamu sudah kami terima. Admin/CS akan segera membalas.";


$kontakEsc   = mysqli_real_escape_string($conn, $kontak);
$pesanEsc    = mysqli_real_escape_string($conn, $isiPesan);
$balasanEsc  = mysqli_real_escape_string($conn, $balasan_otomatis);


$query = "
  INSERT INTO tbl_pesan_cs
    (id_pelanggan, kontak, pesan, waktu_kirim, balasan_otomatis, status_baca_admin)
  VALUES
    ($id_pelanggan, '$kontakEsc', '$pesanEsc', NOW(), '$balasanEsc', $status_baca_admin)
";

$ok = mysqli_query($conn, $query);

if (!$ok) {
  header("Location: contact.php?msg=err");
  exit();
}

header("Location: contact.php?msg=ok");
exit();
