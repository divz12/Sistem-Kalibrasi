<?php
session_start();
include "../koneksi.php";

if (!isset($_SESSION['id_user']) || $_SESSION['role'] != 'pelanggan') {
  header("Location: ../login.php");
  exit();
}

$idUser = $_SESSION['id_user'];

$sql = "
  SELECT 
    tbl_users.nama,
    tbl_users.email,
    tbl_users.foto,
    tbl_pelanggan.no_hp,
    tbl_pelanggan.alamat
  FROM tbl_users
  LEFT JOIN tbl_pelanggan ON tbl_pelanggan.id_user = tbl_users.id_user
  WHERE tbl_users.id_user = '$idUser'
  LIMIT 1
";
$ambil = mysqli_query($conn, $sql);
$data  = mysqli_fetch_assoc($ambil);

$nama   = $data['nama'] ?? '';
$email  = $data['email'] ?? '';
$foto   = $data['foto'] ?? '';
$no_hp  = $data['no_hp'] ?? '';
$alamat = $data['alamat'] ?? '';


if ($foto != "") {
  $cekFoto = "../foto/" . $foto;
  if (file_exists($cekFoto)) {
    $fotoTampil = $cekFoto;
  }
}

include "komponen/header.php";
include "komponen/sidebar.php";
include "komponen/navbar.php";
?>

<div class="content-wrapper">
  <div class="container-xxl container-p-y">

    <h4 class="fw-bold mb-3">Profil Pelanggan</h4>
    <p>Isi data profile terlebih dahulu, agar pengajuan kalibrasi dapat di proses</p>

    <?php if (isset($_GET['msg']) && $_GET['msg'] == 'ok'): ?>
      <div class="alert alert-success">Data berhasil disimpan</div>
    <?php elseif (isset($_GET['msg']) && $_GET['msg'] == 'err'): ?>
      <div class="alert alert-danger">Gagal menyimpan data</div>
    <?php endif; ?>

    <div class="card">
      <div class="card-body">

        <form action="proses-profil.php" method="post" enctype="multipart/form-data">

          <h5 class="mb-4">Data Akun</h5>

          <div class="row mb-3">
            <div class="col-md-6">
              <label>Nama</label>
              <input type="text" class="form-control" value="<?= $nama ?>" disabled>
            </div>
            <div class="col-md-6">
              <label>Email</label>
              <input type="text" class="form-control" value="<?= $email ?>" disabled>
            </div>
          </div>

          
          <div class="row">
            <div class="col-md-6 mb-3">
              <label>No WhatsApp</label>
              <input type="text" name="no_hp" class="form-control" value="<?= $no_hp ?>" required>
            </div>
            
            <div class="col-md-6 mb-3">
              <label>Alamat</label>
              <input type="text" name="alamat" class="form-control" value="<?= $alamat ?>" required>
            </div>
          </div>
          
          <hr>

          <div class="row mb-4">

            <div class="col-md-12">
              <label>Ubah Foto</label>
              <input type="file" name="filefoto" class="form-control">
              <small class="text-muted">Kosongkan jika tidak ingin mengganti foto.</small>
            </div>
          </div>
          
          <div class="mt-4">
            <button class="btn btn-primary">Simpan</button>
            <a href="index.php" class="btn btn-secondary">Kembali</a>
          </div>
          
        </form>

      </div>
    </div>

  </div>
</div>

<?php include "komponen/footer.php"; ?>
