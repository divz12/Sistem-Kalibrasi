<?php
session_start();
include "../koneksi.php";

if (!isset($_SESSION['id_user']) || $_SESSION['role'] != 'pelanggan') {
  header("Location: ../login.php");
  exit();
}

$idUser  = $_SESSION['id_user'];
$no_hp   = $_POST['no_hp'];
$alamat  = $_POST['alamat'];

//simpan data pelanggan
$cek = mysqli_query($conn, "SELECT id_pelanggan FROM tbl_pelanggan WHERE id_user = '$idUser' LIMIT 1");

if (mysqli_num_rows($cek) > 0) {
  $querySimpan = "UPDATE tbl_pelanggan SET no_hp = '$no_hp', alamat = '$alamat' WHERE id_user = '$idUser'";
} else {
  $querySimpan = "INSERT INTO tbl_pelanggan (id_user, no_hp, alamat) VALUES ('$idUser', '$no_hp', '$alamat')";
}

mysqli_query($conn, $querySimpan);


//simpan foto
if (isset($_FILES['filefoto']) && $_FILES['filefoto']['name'] != "") {

  if (!is_dir("../foto")) {
    mkdir("../foto");
  }

  $namaFile = $_FILES['filefoto']['name'];
  $lokasiSementara = $_FILES['filefoto']['tmp_name'];
  $lokasiTujuan = "../uploads/foto/" . $namaFile;

  $terupload = move_uploaded_file($lokasiSementara, $lokasiTujuan);

  if ($terupload) {

    $queryFoto = "UPDATE tbl_users SET foto = '$namaFile' WHERE id_user = '$idUser'";
    mysqli_query($conn, $queryFoto);

    $_SESSION['foto'] = $namaFile;

  } else {
    echo "File gagal di upload";
    exit();
  }
}

header("Location: profil.php?msg=ok");
exit();
