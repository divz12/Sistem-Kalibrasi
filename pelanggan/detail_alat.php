<?php
session_start();
include "../koneksi.php";

// proteksi login
if (!isset($_SESSION['id_user']) || ($_SESSION['role'] ?? '') !== 'pelanggan') {
  header("Location: ../login.php");
  exit();
}

$id_user = (int)($_SESSION['id_user'] ?? 0);
$id_pengajuan = (int)($_GET['id_pengajuan'] ?? 0);

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
// cek data pengajuan, apakah milik pelanggan yang login
$qCek = mysqli_query($conn, "SELECT * FROM tbl_pengajuan_kalibrasi 
                            WHERE id_pengajuan='$id_pengajuan' AND id_pelanggan='$id_pelanggan' LIMIT 1");
$pengajuan = mysqli_fetch_assoc($qCek);

if (!$pengajuan) {
  // kalau bukan miliknya / tidak ada
  header("Location: riwayat_pengajuan.php?msg=notfound");
  exit();
}

$qAlat = mysqli_query($conn, "SELECT * FROM tbl_pengajuan_alat
                             WHERE id_pengajuan='$id_pengajuan'
                             ORDER BY id_alat DESC");
?>

<?php include 'komponen/header.php'; ?>
<?php include 'komponen/sidebar.php'; ?>
<?php include 'komponen/navbar.php'; ?>

<div class="content-wrapper">
  <div class="container-xxl flex-grow-1 container-p-y">

    <div class="d-flex justify-content-between align-items-center mb-3">
      <div>
        <h4 class="fw-bold mb-1">Detail Alat</h4>
        <p class="text-muted mb-0">Daftar alat untuk pengajuan <b>#<?= $id_pengajuan; ?></b></p>
      </div>
      <a href="detail_pengajuan.php?id=<?= $id_pengajuan; ?>" class="btn btn-outline-primary">
        <i class="bx bx-arrow-back me-1"></i> Kembali
      </a>
    </div>

    <!-- Info pengajuan -->
    <div class="card border-0 shadow-sm mb-3">
      <div class="card-body">
        <div class="row g-3">
          <div class="col-md-4">
            <div class="text-muted small">Tanggal Pengajuan</div>
            <div class="fw-semibold"><?= $pengajuan['tanggal_pengajuan'] ?? '-'; ?></div>
          </div>
          <div class="col-md-4">
            <div class="text-muted small">Status</div>
            <div class="fw-semibold"><?= $pengajuan['status_pengajuan'] ?? '-'; ?></div>
          </div>
          <div class="col-md-4">
            <div class="text-muted small">Catatan</div>
            <div class="fw-semibold"><?= ($pengajuan['catatan'] ?? '-') == '' ? '-' : $pengajuan['catatan']; ?></div>
          </div>
        </div>
      </div>
    </div>

    <!-- List alat -->
    <div class="card border-0 shadow-sm">
      <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Daftar Alat</h5>
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
                  <th>No</th>
                  <th>Nama Alat</th>
                  <th>Merk/Tipe</th>
                  <th>Kapasitas</th>
                  <th>Jumlah</th>
                  <th>Keterangan</th>
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
                    <td><?= ($alat['keterangan'] ?? '-') == '' ? '-' : $alat['keterangan']; ?></td>
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
