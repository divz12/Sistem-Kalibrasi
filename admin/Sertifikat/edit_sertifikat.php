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

$id = (int)($_GET["id"] ?? 0);
if ($id <= 0) { header("Location: sertifikat.php"); exit(); }

$sql = "SELECT * FROM tbl_sertifikat WHERE id_sertifikat = '$id' LIMIT 1";
$hasil = mysqli_query($conn, $sql);
$data = mysqli_fetch_assoc($hasil);
if (!$data) { header("Location: sertifikat.php"); exit(); }

$base = "../../";
include "../komponen/header.php";
include "../komponen/sidebar.php";
include "../komponen/navbar.php";
?>

<div class="content-wrapper">
  <div class="container-xxl container-p-y">

    <h4 class="fw-bold mb-4">Edit Sertifikat</h4>

    <form action="proses_edit_sertifikat.php" method="post" enctype="multipart/form-data">
      <input type="hidden" name="id_sertifikat" value="<?= $data['id_sertifikat']; ?>">

      <div class="mb-3">
        <label>Nomor Sertifikat</label>
        <input type="text" name="nomor_sertifikat" class="form-control"
               value="<?= $data['nomor_sertifikat']; ?>">
      </div>

      <div class="mb-3">
        <label>Tanggal Terbit</label>
        <input type="date" name="tanggal_terbit" class="form-control"
               value="<?= $data['tanggal_terbit']; ?>">
      </div>

      <div class="mb-3">
        <label>Keterangan</label>
        <textarea name="keterangan_sertifikat" class="form-control"><?= $data['keterangan_sertifikat']; ?></textarea>
      </div>

      <div class="mb-3">
        <label>File Sertifikat (kosongkan jika tidak ganti)</label>
        <input type="file" name="file_sertifikat" class="form-control">
        <?php if ($data['lokasi_file_sertifikat'] != ""): ?>
          <small class="text-muted">File lama: <?= $data['nama_file_sertifikat']; ?></small>
        <?php endif; ?>
      </div>

      <button class="btn btn-primary" type="submit">Simpan Perubahan</button>
      <a href="sertifikat.php" class="btn btn-secondary">Batal</a>
    </form>

  </div>
</div>

<?php include "../komponen/footer.php"; ?>
