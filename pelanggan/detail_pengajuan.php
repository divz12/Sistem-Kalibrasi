<?php
session_start();
include "../koneksi.php";

// proteksi login
if (!isset($_SESSION['id_user']) || ($_SESSION['role'] ?? '') !== 'pelanggan') {
  header("Location: ../login.php");
  exit();
}

$id_user = (int)($_SESSION['id_user'] ?? 0);
$id_pengajuan = (int)($_GET['id'] ?? 0);

if ($id_pengajuan <= 0) {
  header("Location: riwayat_pengajuan.php");
  exit();
}

$qPel = mysqli_query($conn, "SELECT id_pelanggan FROM tbl_pelanggan WHERE id_user='$id_user' LIMIT 1");
$dataPel = mysqli_fetch_assoc($qPel);
$id_pelanggan = (int)($dataPel['id_pelanggan'] ?? 0);

if ($id_pelanggan <= 0) {
  header("Location: profil.php");
  exit();
}

// memastikan pengajuan ini milik pelanggan yang login
$qPengajuan = mysqli_query($conn, "
  SELECT *
  FROM tbl_pengajuan_kalibrasi
  WHERE id_pengajuan='$id_pengajuan' AND id_pelanggan='$id_pelanggan'
  LIMIT 1
");

$pengajuan = mysqli_fetch_assoc($qPengajuan);

if (!$pengajuan) {
  header("Location: riwayat_pengajuan.php?msg=notfound");
  exit();
}

$qAlat = mysqli_query($conn, "
  SELECT *
  FROM tbl_pengajuan_alat
  WHERE id_pengajuan='$id_pengajuan'
  ORDER BY id_alat DESC
");

// untuk menampilkan status
function badgeStatus($status) {
  $status = strtolower($status ?? '');
  if ($status == 'dikirim') return 'bg-label-primary';
  if ($status == 'diproses') return 'bg-label-warning';
  if ($status == 'selesai') return 'bg-label-success';
  if ($status == 'ditolak') return 'bg-label-danger';
  return 'bg-label-secondary';
}

include 'komponen/header.php';
include 'komponen/sidebar.php';
include 'komponen/navbar.php';
?>

<div class="content-wrapper">
  <div class="container-xxl flex-grow-1 container-p-y">

    <div class="d-flex justify-content-between align-items-center mb-3">
      <div>
        <h4 class="fw-bold mb-1">Detail Pengajuan</h4>
        <p class="text-muted mb-0">Informasi pengajuan dan data alat yang diajukan.</p>
      </div>
      <a href="riwayat_pengajuan.php" class="btn btn-outline-primary">
        <i class="bx bx-arrow-back me-1"></i> Kembali
      </a>
    </div>

    <!-- Card Ringkasan Pengajuan -->
    <div class="card border-0 shadow-sm mb-3">
      <div class="card-body">
        <div class="d-flex flex-wrap justify-content-between align-items-start gap-2">
          <div>
            <h5 class="mb-1">Pengajuan #<?= $pengajuan['id_pengajuan']; ?></h5>
            <div class="text-muted small">
              Diajukan pada: <?= $pengajuan['tanggal_pengajuan'] ?? '-'; ?>
            </div>
          </div>

          <span class="badge <?= badgeStatus($pengajuan['status_pengajuan'] ?? ''); ?> px-3 py-2">
            <?= $pengajuan['status_pengajuan'] ?? '-'; ?>
          </span>
        </div>

        <hr class="my-3">

        <div class="row g-3">
          <div class="col-md-6">
            <div class="text-muted small">Catatan Pengajuan</div>
            <div class="fw-semibold">
              <?= ($pengajuan['catatan'] ?? '') == '' ? '-' : $pengajuan['catatan']; ?>
            </div>
          </div>

          <div class="col-md-6">
            <div class="text-muted small">ID Pelanggan</div>
            <div class="fw-semibold"><?= $pengajuan['id_pelanggan'] ?? '-'; ?></div>
          </div>
        </div>

        <div class="d-flex flex-wrap gap-2 mt-4">
          <a href="detail_alat.php?id_pengajuan=<?= $id_pengajuan; ?>" class="btn btn-primary">
            <i class="bx bx-detail me-1"></i> Lihat Detail Alat
          </a>

          <a href="penawaran.php" class="btn btn-outline-primary">
            <i class="bx bx-receipt me-1"></i> Cek Penawaran
          </a>

          <a href="status_proses.php" class="btn btn-outline-primary">
            <i class="bx bx-loader-circle me-1"></i> Status Proses
          </a>
        </div>
      </div>
    </div>

    <!-- Ringkasan Data Alat -->
    <div class="card border-0 shadow-sm">
      <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Ringkasan Data Alat</h5>
        <a href="detail_alat.php?id_pengajuan=<?= $id_pengajuan; ?>" class="btn btn-sm btn-outline-primary">
          Lihat semua
        </a>
      </div>

      <div class="card-body">
        <?php if (!$qAlat || mysqli_num_rows($qAlat) == 0): ?>
          <div class="alert alert-warning mb-0">
            Belum ada data alat untuk pengajuan ini.
          </div>
        <?php else: ?>
          <div class="table-responsive">
            <table class="table align-middle">
              <thead class="table-light">
                <tr>
                  <th>#</th>
                  <th>Nama Alat</th>
                  <th>Merk/Tipe</th>
                  <th>Kapasitas</th>
                  <th>Jumlah</th>
                </tr>
              </thead>
              <tbody>
                <?php $no = 1; ?>
                <?php while ($alat = mysqli_fetch_assoc($qAlat)): ?>
                  <tr>
                    <td><?= $no++; ?></td>
                    <td class="fw-semibold"><?= $alat['nama_alat'] ?? '-'; ?></td>
                    <td><?= $alat['merk_tipe'] ?? '-'; ?></td>
                    <td><?= $alat['kapasitas'] ?? '-'; ?></td>
                    <td><?= $alat['jumlah_unit'] ?? '1'; ?></td>
                  </tr>
                <?php endwhile; ?>
              </tbody>
            </table>
          </div>

          <div class="text-muted small">
          </div>
        <?php endif; ?>
      </div>
    </div>

  </div>
</div>

<?php include 'komponen/footer.php'; ?>
