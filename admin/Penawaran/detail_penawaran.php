<?php
session_start();
include "../../koneksi.php";

$role = $_SESSION["role"] ?? "";

if (!isset($_SESSION["id_user"])) {
  header("Location: ../../login.php");
  exit();
}

if ($role != "admin" && $role != "cs" && $role != "owner") {
  header("Location: ../../login.php");
  exit();
}

$idPenawaran = (int)($_GET["id"] ?? 0);
if ($idPenawaran <= 0) {
  header("Location: penawaran.php");
  exit();
}

$sql = "
  SELECT
    tbl_penawaran.id_penawaran,
    tbl_penawaran.id_pengajuan,
    tbl_penawaran.tanggal_penawaran,
    tbl_penawaran.total_biaya,
    tbl_penawaran.rincian,
    tbl_penawaran.status_penawaran,

    tbl_pengajuan_kalibrasi.tanggal_pengajuan,
    tbl_pengajuan_kalibrasi.status_pengajuan,
    tbl_pengajuan_kalibrasi.catatan,

    tbl_pelanggan.no_hp,
    tbl_pelanggan.alamat,

    tbl_users.nama,
    tbl_users.email

  FROM tbl_penawaran
  LEFT JOIN tbl_pengajuan_kalibrasi
    ON tbl_pengajuan_kalibrasi.id_pengajuan = tbl_penawaran.id_pengajuan
  LEFT JOIN tbl_pelanggan
    ON tbl_pelanggan.id_pelanggan = tbl_pengajuan_kalibrasi.id_pelanggan
  LEFT JOIN tbl_users
    ON tbl_users.id_user = tbl_pelanggan.id_user
  WHERE tbl_penawaran.id_penawaran = '$idPenawaran'
  LIMIT 1
";
$hasil = mysqli_query($conn, $sql);
$data = mysqli_fetch_assoc($hasil);

if (!$data) {
  header("Location: penawaran.php");
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
        <h4 class="fw-bold mb-1">Detail Penawaran</h4>
        <p class="text-muted mb-0">Informasi penawaran dan pelanggan.</p>
      </div>
      <a href="penawaran.php" class="btn btn-outline-primary">
        <i class="bx bx-arrow-back me-1"></i> Kembali
      </a>
    </div>

    <div class="card">
      <div class="card-body">

        <h5 class="mb-3">Data Penawaran</h5>
        <div class="row g-3">
          <div class="col-md-4">
            <div class="text-muted small">ID Penawaran</div>
            <div class="fw-semibold"><?= $data["id_penawaran"]; ?></div>
          </div>
          <div class="col-md-4">
            <div class="text-muted small">ID Pengajuan</div>
            <div class="fw-semibold">#<?= $data["id_pengajuan"]; ?></div>
          </div>
          <div class="col-md-4">
            <div class="text-muted small">Tanggal Penawaran</div>
            <div><?= $data["tanggal_penawaran"]; ?></div>
          </div>

          <div class="col-md-4">
            <div class="text-muted small">Total Biaya</div>
            <div>Rp <?= number_format($data["total_biaya"], 0, ',', '.'); ?></div>
          </div>
          <div class="col-md-4">
            <div class="text-muted small">Status Penawaran</div>
            <div><?= $data["status_penawaran"]; ?></div>
          </div>
        </div>

        <div class="mt-3">
          <div class="text-muted small">Rincian</div>
          <div class="border rounded p-3 bg-light"><?= $data["rincian"]; ?></div>
        </div>

        <hr class="my-4">

        <h5 class="mb-3">Data Pelanggan</h5>
        <div class="row g-3">
          <div class="col-md-6">
            <div class="text-muted small">Nama</div>
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
        </div>

        <hr class="my-4">

        <div class="d-flex gap-2">
          <a href="edit_penawaran.php?id=<?= $data["id_penawaran"]; ?>" class="btn btn-primary">Edit</a>
          <a href="../Pengajuan/pengajuan_detail.php?id=<?= $data["id_pengajuan"]; ?>" class="btn btn-outline-primary">
            Lihat Pengajuan
          </a>
        </div>

      </div>
    </div>

  </div>
</div>

<?php include "../komponen/footer.php"; ?>
