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

// ambil data alat beserta info pengajuan dan pelanggan
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
    tbl_pengajuan_alat.keterangan,

    tbl_pengajuan_kalibrasi.tanggal_pengajuan,
    tbl_pengajuan_kalibrasi.status_pengajuan,
    tbl_pengajuan_kalibrasi.catatan,

    tbl_pelanggan.no_hp,
    tbl_pelanggan.alamat,

    tbl_users.nama,
    tbl_users.email

  FROM tbl_pengajuan_alat
  LEFT JOIN tbl_pengajuan_kalibrasi
    ON tbl_pengajuan_kalibrasi.id_pengajuan = tbl_pengajuan_alat.id_pengajuan
  LEFT JOIN tbl_pelanggan
    ON tbl_pelanggan.id_pelanggan = tbl_pengajuan_kalibrasi.id_pelanggan
  LEFT JOIN tbl_users
    ON tbl_users.id_user = tbl_pelanggan.id_user
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
        <h4 class="fw-bold mb-1">Detail Alat</h4>
        <p class="text-muted mb-0">Detail alat dan informasi pengajuan.</p>
      </div>
      <a href="alat_pengajuan.php" class="btn btn-outline-primary">
        <i class="bx bx-arrow-back me-1"></i> Kembali
      </a>
    </div>

    <div class="card">
      <div class="card-body">

        <h5 class="mb-3">Informasi Alat</h5>

        <div class="row g-3">
          <div class="col-md-6">
            <div class="text-muted small">ID Alat</div>
            <div class="fw-semibold"><?= $data["id_alat"]; ?></div>
          </div>
          <div class="col-md-6">
            <div class="text-muted small">ID Pengajuan</div>
            <div class="fw-semibold">#<?= $data["id_pengajuan"]; ?></div>
          </div>

          <div class="col-md-6">
            <div class="text-muted small">Nama Alat</div>
            <div><?= $data["nama_alat"]; ?></div>
          </div>
          <div class="col-md-6">
            <div class="text-muted small">Merk / Tipe</div>
            <div><?= $data["merk_tipe"]; ?></div>
          </div>

          <div class="col-md-6">
            <div class="text-muted small">Kapasitas</div>
            <div><?= $data["kapasitas"]; ?></div>
          </div>
          <div class="col-md-6">
            <div class="text-muted small">Jumlah Unit</div>
            <div><?= $data["jumlah_unit"]; ?></div>
          </div>

          <div class="col-md-6">
            <div class="text-muted small">Parameter</div>
            <div><?= $data["parameter"]; ?></div>
          </div>
          <div class="col-md-6">
            <div class="text-muted small">Titik Ukur</div>
            <div><?= $data["titik_ukur"]; ?></div>
          </div>

          <div class="col-12">
            <div class="text-muted small">Keterangan</div>
            <div><?= $data["keterangan"] != "" ? $data["keterangan"] : "-"; ?></div>
          </div>
        </div>

        <hr class="my-4">

        <h5 class="mb-3">Informasi Pengajuan & Pelanggan</h5>

        <div class="row g-3">
          <div class="col-md-6">
            <div class="text-muted small">Tanggal Pengajuan</div>
            <div><?= $data["tanggal_pengajuan"]; ?></div>
          </div>
          <div class="col-md-6">
            <div class="text-muted small">Status Pengajuan</div>
            <div><?= $data["status_pengajuan"]; ?></div>
          </div>

          <div class="col-md-6">
            <div class="text-muted small">Nama Pelanggan</div>
            <div class="fw-semibold"><?= $data["nama"]; ?></div>
          </div>
          <div class="col-md-6">
            <div class="text-muted small">Email</div>
            <div><?= $data["email"]; ?></div>
          </div>

          <div class="col-md-6">
            <div class="text-muted small">No HP / WA</div>
            <div><?= $data["no_hp"]; ?></div>
          </div>
          <div class="col-md-6">
            <div class="text-muted small">Alamat</div>
            <div><?= $data["alamat"]; ?></div>
          </div>

          <div class="col-12">
            <div class="text-muted small">Catatan Pengajuan</div>
            <div><?= $data["catatan"] != "" ? $data["catatan"] : "-"; ?></div>
          </div>
        </div>

        <hr class="my-3">

        <div class="d-flex gap-2">
          <a href="edit_alat.php?id=<?= $data["id_alat"]; ?>" class="btn btn-primary">Edit</a>
          <a href="../Pengajuan/detail_pengajuan.php?id=<?= $data["id_pengajuan"]; ?>" class="btn btn-outline-primary">
            Lihat Pengajuan
          </a>
        </div>

      </div>
    </div>

  </div>
</div>

<?php include "../komponen/footer.php"; ?>
