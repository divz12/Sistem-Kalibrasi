<?php
session_start();
include "../koneksi.php";

if (!$_SESSION['id_user'] || $_SESSION['role'] != 'pelanggan') {
  header("Location: ../login.php");
  exit();
}

if (!$_POST['id_pelanggan']) {
  header("Location: pengajuan.php?msg=err");
  exit();
}

$id_pelanggan = (int)$_POST['id_pelanggan'];
$catatan = $_POST['catatan'] ?? "";
$catatan = addslashes($catatan);

$nama_alat   = $_POST['nama_alat']   ?? [];
$merk_tipe   = $_POST['merk_tipe']   ?? [];
$kapasitas   = $_POST['kapasitas']   ?? [];
$jumlah_unit = $_POST['jumlah_unit'] ?? [];
$parameter   = $_POST['parameter']   ?? [];
$titik_ukur  = $_POST['titik_ukur']  ?? [];
$keterangan  = $_POST['keterangan']  ?? [];

// validasi alat pertama
$namaAlatPertama = $nama_alat[0]   ?? "";
$jumlahPertama   = (int)($jumlah_unit[0] ?? 0);
$paramPertama    = $parameter[0]   ?? "";
$titikPertama    = $titik_ukur[0]  ?? "";

if ($namaAlatPertama == "" || $jumlahPertama <= 0 || $paramPertama == "" || $titikPertama == "") {
  header("Location: pengajuan.php?msg=err");
  exit();
}

// simpan pengajuan
$sqlPengajuan = "
  INSERT INTO tbl_pengajuan_kalibrasi 
  (id_pelanggan, tanggal_pengajuan, status_pengajuan, catatan)
  VALUES ($id_pelanggan, NOW(), 'dikirim', '$catatan')
";

$jalanPengajuan = mysqli_query($conn, $sqlPengajuan);

if (!$jalanPengajuan) {
  echo "Gagal simpan pengajuan: " . mysqli_error($conn);
  exit();
}

$id_pengajuan = mysqli_insert_id($conn);
if ($id_pengajuan <= 0) {
  echo "Gagal ambil id pengajuan";
  exit();
}

// simpan detail alat
for ($i = 0; $i < count($nama_alat); $i++) {

  $nama  = $nama_alat[$i]   ?? "";
  $merk  = $merk_tipe[$i]   ?? "";
  $kap   = $kapasitas[$i]   ?? "";
  $jml   = (int)($jumlah_unit[$i] ?? 1);
  $param = $parameter[$i]   ?? "";
  $titik = $titik_ukur[$i]  ?? "";
  $ket   = $keterangan[$i]  ?? "";

  if ($nama == "") continue;
  if ($jml <= 0) $jml = 1;

  $nama  = addslashes($nama);
  $merk  = addslashes($merk);
  $kap   = addslashes($kap);
  $param = addslashes($param);
  $titik = addslashes($titik);
  $ket   = addslashes($ket);

  $sqlDetail = "
    INSERT INTO tbl_pengajuan_alat
    (id_pengajuan, nama_alat, merk_tipe, kapasitas, jumlah_unit, parameter, titik_ukur, keterangan)
    VALUES
    ($id_pengajuan, '$nama', '$merk', '$kap', $jml, '$param', '$titik', '$ket')
  ";

  $jalanDetail = mysqli_query($conn, $sqlDetail);
  if (!$jalanDetail) {
    echo "Gagal simpan detail alat: " . mysqli_error($conn);
    exit();
  }
}

header("Location: pengajuan.php?msg=ok");
exit();
