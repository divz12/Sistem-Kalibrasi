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

$idAlat = (int)($_GET["id"] ?? 0);
if ($idAlat <= 0) {
  header("Location: alat_pengajuan.php");
  exit();
}

$sql = "
  SELECT
    tbl_pengajuan_alat.id_alat,
    tbl_pengajuan_alat.id_pengajuan,
    tbl_pengajuan_alat.nama_alat,
    tbl_pengajuan_alat.merk_tipe,
    tbl_pengajuan_alat.kapasitas,
    tbl_pengajuan_alat.jumlah_unit,
    tbl_pengajuan_alat.parameter,
    tbl_pengajuan_alat.titik_ukur,
    tbl_pengajuan_alat.keterangan
  FROM tbl_pengajuan_alat
  WHERE tbl_pengajuan_alat.id_alat = '$idAlat'
  LIMIT 1
";
$hasil = mysqli_query($conn, $sql);
$data = mysqli_fetch_assoc($hasil);

if (!$data) {
  header("Location: alat_pengajuan.php");
  exit();
}

$base = "../../";
include "../komponen/header.php";
include "../komponen/sidebar.php";
include "../komponen/navbar.php";
?>

<div class="content-wrapper">
  <div class="container-xxl flex-grow-1 container-p-y">

    <div class="d-flex justify-content-between align-items-center mb-3">
      <div>
        <h4 class="fw-bold mb-1">Edit Data Alat</h4>
        <p class="text-muted mb-0">Ubah data alat pada pengajuan.</p>
      </div>
      <a href="alat_pengajuan.php" class="btn btn-outline-primary">
        <i class="bx bx-arrow-back me-1"></i> Kembali
      </a>
    </div>

    <?php if (($_GET["msg"] ?? "") == "err"): ?>
      <div class="alert alert-danger">Gagal menyimpan ❌</div>
    <?php endif; ?>

    <form action="proses_edit_alat.php" method="post" class="card">
      <div class="card-body">

        <input type="hidden" name="id_alat" value="<?= $data["id_alat"]; ?>">
        <input type="hidden" name="id_pengajuan" value="<?= $data["id_pengajuan"]; ?>">

        <div class="row g-3">

          <div class="col-md-6">
            <label class="form-label">Nama Alat</label>
            <input type="text" name="nama_alat" class="form-control" value="<?= $data["nama_alat"]; ?>" required>
          </div>

          <div class="col-md-6">
            <label class="form-label">Merk / Tipe</label>
            <input type="text" name="merk_tipe" class="form-control" value="<?= $data["merk_tipe"]; ?>">
          </div>

          <div class="col-md-6">
            <label class="form-label">Kapasitas</label>
            <input type="text" name="kapasitas" class="form-control" value="<?= $data["kapasitas"]; ?>">
          </div>

          <div class="col-md-6">
            <label class="form-label">Jumlah Unit</label>
            <input type="number" name="jumlah_unit" class="form-control" value="<?= $data["jumlah_unit"]; ?>" min="1">
          </div>

          <div class="col-md-6">
            <label class="form-label">Parameter</label>
            <input type="text" name="parameter" class="form-control" value="<?= $data["parameter"]; ?>" required>
          </div>

          <div class="col-md-6">
            <label class="form-label">Titik Ukur</label>
            <input type="text" name="titik_ukur" class="form-control" value="<?= $data["titik_ukur"]; ?>" required>
          </div>

          <div class="col-12">
            <label class="form-label">Keterangan</label>
            <input type="text" name="keterangan" class="form-control" value="<?= $data["keterangan"]; ?>">
          </div>

        </div>

        <hr class="my-4">

        <div class="d-flex gap-2">
          <button class="btn btn-primary" type="submit">
            <i class="bx bx-save me-1"></i> Simpan
          </button>
          <a href="detail_alat.php?id=<?= $data["id_alat"]; ?>" class="btn btn-outline-primary">Batal</a>
        </div>

      </div>
    </form>

  </div>
</div>

<?php include "../komponen/footer.php"; ?>
