<?php
session_start();
include "../../koneksi.php";

$role = $_SESSION["role"] ?? "";

if (!isset($_SESSION["id_user"])) {
  header("Location: ../../login.php");
  exit();
}

if ($role != "admin" && $role != "cs" && $role != "admin_cs") {
  header("Location: ../../login.php");
  exit();
}

$idSertifikat = (int)($_GET["id"] ?? 0);
if ($idSertifikat <= 0) {
  header("Location: sertifikat.php");
  exit();
}

$sql = "
  SELECT
    tbl_sertifikat.id_sertifikat,
    tbl_sertifikat.id_pengajuan,
    tbl_sertifikat.nomor_sertifikat,
    tbl_sertifikat.tanggal_terbit,
    tbl_sertifikat.nama_file_sertifikat,
    tbl_sertifikat.lokasi_file_sertifikat,
    tbl_sertifikat.keterangan_sertifikat,

    tbl_pengajuan_kalibrasi.tanggal_pengajuan,
    tbl_pengajuan_kalibrasi.status_pengajuan,

    tbl_users.nama,
    tbl_users.email

  FROM tbl_sertifikat
  LEFT JOIN tbl_pengajuan_kalibrasi
    ON tbl_pengajuan_kalibrasi.id_pengajuan = tbl_sertifikat.id_pengajuan
  LEFT JOIN tbl_pelanggan
    ON tbl_pelanggan.id_pelanggan = tbl_pengajuan_kalibrasi.id_pelanggan
  LEFT JOIN tbl_users
    ON tbl_users.id_user = tbl_pelanggan.id_user
  WHERE tbl_sertifikat.id_sertifikat = '$idSertifikat'
  LIMIT 1
";
$hasil = mysqli_query($conn, $sql);
$data = mysqli_fetch_assoc($hasil);

if (!$data) {
  header("Location: sertifikat.php");
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
        <h4 class="fw-bold mb-1">Detail Sertifikat</h4>
        <p class="text-muted mb-0">Sertifikat ID: <?= $data["id_sertifikat"]; ?></p>
      </div>
      <a href="sertifikat.php" class="btn btn-outline-primary">
        <i class="bx bx-arrow-back me-1"></i> Kembali
      </a>
    </div>

    <div class="card">
      <div class="card-body">

        <div class="row g-3">
          <div class="col-md-6">
            <div class="text-muted small">Pelanggan</div>
            <div class="fw-semibold"><?= $data["nama"] ?? "-"; ?></div>
            <div class="text-muted small"><?= $data["email"] ?? "-"; ?></div>
          </div>

          <div class="col-md-6">
            <div class="text-muted small">Pengajuan</div>
            <div>#<?= $data["id_pengajuan"]; ?></div>
            <div class="text-muted small">Status Pengajuan: <?= $data["status_pengajuan"] ?? "-"; ?></div>
          </div>
        </div>

        <hr>

        <div class="row g-3">
          <div class="col-md-4">
            <div class="text-muted small">Nomor Sertifikat</div>
            <div class="fw-semibold"><?= $data["nomor_sertifikat"]; ?></div>
          </div>

          <div class="col-md-4">
            <div class="text-muted small">Tanggal Terbit</div>
            <div><?= $data["tanggal_terbit"]; ?></div>
          </div>

          <div class="col-md-4">
            <div class="text-muted small">File</div>
            <?php if (($data["lokasi_file_sertifikat"] ?? "") != ""): ?>
              <a class="btn btn-sm btn-success" href="../../<?= $data["lokasi_file_sertifikat"]; ?>" target="_blank">
                Unduh Sertifikat
              </a>
            <?php else: ?>
              <div>-</div>
            <?php endif; ?>
          </div>
        </div>

        <div class="mt-3">
          <div class="text-muted small">Keterangan</div>
          <div><?= $data["keterangan_sertifikat"] ?? "-"; ?></div>
        </div>

      </div>
    </div>

  </div>
</div>

<?php include "../komponen/footer.php"; ?>
