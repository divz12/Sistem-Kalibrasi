<?php
session_start();
include "../koneksi.php";

// cek login & role pelanggan
if (!isset($_SESSION['id_user']) || ($_SESSION['role'] ?? '') !== 'pelanggan') {
  header("Location: ../login.php");
  exit();
}

$id_user = (int) $_SESSION['id_user'];

// nama pelanggan
$nama = $_SESSION['nama'] ?? '';

if ($nama === '') {
  $stmt = $conn->prepare("SELECT nama FROM tbl_users WHERE id_user = ? LIMIT 1");
  $stmt->bind_param("i", $id_user);
  $stmt->execute();
  $res = $stmt->get_result()->fetch_assoc();
  $nama = $res['nama'] ?? 'Pelanggan';
  $stmt->close();

  $_SESSION['nama'] = $nama;
}

// ambil id_pelanggan dari tbl_pelanggan
$id_pelanggan = 0;
$stmt = $conn->prepare("SELECT id_pelanggan FROM tbl_pelanggan WHERE id_user = ? LIMIT 1");
$stmt->bind_param("i", $id_user);
$stmt->execute();
$res = $stmt->get_result()->fetch_assoc();
$id_pelanggan = (int)($res['id_pelanggan'] ?? 0);
$stmt->close();


// status badge function
function badgeStatus($status) {
  $s = strtolower(trim((string)$status));

  if ($s === 'dikirim') return 'bg-label-primary';
  if ($s === 'diproses') return 'bg-label-warning';
  if ($s === 'selesai') return 'bg-label-success';
  if ($s === 'ditolak') return 'bg-label-danger';

  return 'bg-label-secondary';
}

// angka statistik
$totalPengajuan = 0;
$totalPenawaran = 0;

if ($id_pelanggan > 0) {
  // total pengajuan
  $stmt = $conn->prepare("SELECT COUNT(*) AS total
                          FROM tbl_pengajuan_kalibrasi
                          WHERE id_pelanggan = ?");
  $stmt->bind_param("i", $id_pelanggan);
  $stmt->execute();
  $totalPengajuan = (int)($stmt->get_result()->fetch_assoc()['total'] ?? 0);
  $stmt->close();

  // total penawaran
  $stmt = $conn->prepare("SELECT COUNT(*) AS total
                          FROM tbl_penawaran p
                          JOIN tbl_pengajuan_kalibrasi k ON k.id_pengajuan = p.id_pengajuan
                          WHERE k.id_pelanggan = ?");
  $stmt->bind_param("i", $id_pelanggan);
  $stmt->execute();
  $totalPenawaran = (int)($stmt->get_result()->fetch_assoc()['total'] ?? 0);
  $stmt->close();
}

// status pengajuan terakhir
$lastPengajuan = null;

if ($id_pelanggan > 0) {
  $stmt = $conn->prepare("SELECT id_pengajuan, tanggal_pengajuan, status_pengajuan, catatan
                          FROM tbl_pengajuan_kalibrasi
                          WHERE id_pelanggan = ?
                          ORDER BY tanggal_pengajuan DESC
                          LIMIT 1");
  $stmt->bind_param("i", $id_pelanggan);
  $stmt->execute();
  $lastPengajuan = $stmt->get_result()->fetch_assoc();
  $stmt->close();
}

// riwayat pengajuan terbaru
$riwayat = [];

if ($id_pelanggan > 0) {
  $stmt = $conn->prepare("SELECT id_pengajuan, tanggal_pengajuan, status_pengajuan, catatan
                          FROM tbl_pengajuan_kalibrasi
                          WHERE id_pelanggan = ?
                          ORDER BY tanggal_pengajuan DESC
                          LIMIT 5");
  $stmt->bind_param("i", $id_pelanggan);
  $stmt->execute();
  $result = $stmt->get_result();
  while ($row = $result->fetch_assoc()) {
    $riwayat[] = $row;
  }
  $stmt->close();
}

include "komponen/header.php";
include "komponen/sidebar.php";
include "komponen/navbar.php";
?>

<div class="content-wrapper">
  <div class="container-xxl flex-grow-1 container-p-y">

    <!-- Header -->
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
      <div>
        <h4 class="fw-bold mb-1">Dashboard</h4>
        <div class="text-muted">Halo, <b><?= $nama; ?></b> 👋</div>
      </div>

      <div class="d-flex gap-2">
        <a href="pengajuan.php" class="btn btn-primary">
          <i class="bx bx-plus me-1"></i> Ajukan Kalibrasi
        </a>
      </div>
    </div>

    <!-- Stat Cards -->
    <div class="row g-4 mb-4">
      <div class="col-sm-6 col-lg-3">
        <div class="card">
          <div class="card-body">
            <div class="d-flex align-items-center justify-content-between">
              <div>
                <span class="text-muted">Pengajuan</span>
                <h3 class="mb-0"><?= $totalPengajuan; ?></h3>
              </div>
              <div class="avatar">
                <span class="avatar-initial rounded bg-label-primary"><i class="bx bx-edit"></i></span>
              </div>
            </div>
            <small class="text-muted">Total pengajuan kalibrasi</small>
          </div>
        </div>
      </div>

      <div class="col-sm-6 col-lg-3">
        <div class="card">
          <div class="card-body">
            <div class="d-flex align-items-center justify-content-between">
              <div>
                <span class="text-muted">Penawaran</span>
                <h3 class="mb-0"><?= $totalPenawaran; ?></h3>
              </div>
              <div class="avatar">
                <span class="avatar-initial rounded bg-label-info"><i class="bx bx-receipt"></i></span>
              </div>
            </div>
            <small class="text-muted">Quotation yang tersedia</small>
          </div>
        </div>
      </div>

      <div class="col-sm-6 col-lg-3">
        <div class="card">
          <div class="card-body">
            <span class="text-muted">Sertifikat</span>
            <h3 class="mb-0">-</h3>
            <small class="text-muted">Akan muncul setelah proses selesai</small>
          </div>
        </div>
      </div>

      <div class="col-sm-6 col-lg-3">
        <div class="card">
          <div class="card-body">
            <span class="text-muted">Invoice</span>
            <h3 class="mb-0">-</h3>
            <small class="text-muted">Akan tersedia setelah penawaran disetujui</small>
          </div>
        </div>
      </div>
    </div>

    <!-- Status terbaru + panduan -->
    <div class="row g-4 mb-4">
      <div class="col-lg-7">
        <div class="card h-100">
          <div class="card-header d-flex align-items-center justify-content-between">
            <h5 class="mb-0">Pengajuan Terbaru</h5>
            <a href="riwayat_pengajuan.php" class="btn btn-sm btn-outline-primary">Lihat semua</a>
          </div>

          <div class="card-body">
            <?php if (!$lastPengajuan): ?>
              <div class="alert alert-info mb-0">
                Kamu belum punya pengajuan. Klik <b>Ajukan Kalibrasi</b> untuk memulai.
              </div>
            <?php else: ?>
              <div class="d-flex justify-content-between align-items-start gap-3">
                <div>
                  <div class="text-muted small">Tanggal</div>
                  <div class="fw-semibold mb-2"><?= $lastPengajuan['tanggal_pengajuan']; ?></div>

                  <div class="text-muted small">Keterangan</div>
                  <div><?= $lastPengajuan['keterangan'] ?? '-'; ?></div>
                </div>

                <span class="badge <?= badgeStatus($lastPengajuan['status_pengajuan']); ?>">
                  <?= $lastPengajuan['status_pengajuan']; ?>
                </span>
              </div>

              <hr class="my-3">

              <div class="d-flex gap-2 flex-wrap">
                <a href="detail_pengajuan.php?id=<?= (int)$lastPengajuan['id_pengajuan']; ?>" class="btn btn-primary">
                  Detail Pengajuan
                </a>
                <a href="penawaran.php" class="btn btn-outline-primary">Cek Penawaran</a>
                <a href="status_proses.php" class="btn btn-outline-primary">Lihat Status</a>
              </div>
            <?php endif; ?>
          </div>
        </div>
      </div>

      <div class="col-lg-5">
        <div class="card h-100">
          <div class="card-header">
            <h5 class="mb-0">Panduan Singkat</h5>
          </div>
          <div class="card-body">
            <ul class="list-unstyled mb-0">
              <li class="d-flex gap-2 mb-3">
                <i class="bx bx-edit text-primary fs-4"></i>
                <div>
                  <b>Ajukan Kalibrasi</b>
                  <div class="text-muted small">Isi data pengajuan & data alat.</div>
                </div>
              </li>
              <li class="d-flex gap-2 mb-3">
                <i class="bx bx-receipt text-info fs-4"></i>
                <div>
                  <b>Cek Penawaran</b>
                  <div class="text-muted small">Lihat quotation yang dikirim oleh admin/CS.</div>
                </div>
              </li>
              <li class="d-flex gap-2 mb-3">
                <i class="bx bx-loader-circle text-warning fs-4"></i>
                <div>
                  <b>Pantau Status</b>
                  <div class="text-muted small">Pantau progress dari pengajuan kamu.</div>
                </div>
              </li>
              <li class="d-flex gap-2">
                <i class="bx bx-support text-success fs-4"></i>
                <div>
                  <b>Hubungi CS</b>
                  <div class="text-muted small">Kalau bingung, kirim pesan lewat menu CS.</div>
                </div>
              </li>
            </ul>
          </div>
        </div>
      </div>
    </div>

    <!-- Riwayat terbaru -->
    <div class="card">
      <div class="card-header d-flex align-items-center justify-content-between">
        <h5 class="mb-0">Riwayat Pengajuan Terbaru</h5>
        <a href="riwayat_pengajuan.php" class="btn btn-sm btn-outline-primary">Lihat semua</a>
      </div>

      <div class="table-responsive">
        <table class="table">
          <thead>
            <tr>
              <th>No</th>
              <th>Tanggal</th>
              <th>Status</th>
              <th>Keterangan</th>
              <th>Aksi</th>
            </tr>
          </thead>
          <tbody>
            <?php if (empty($riwayat)): ?>
              <tr>
                <td colspan="5" class="text-center text-muted py-4">Belum ada data pengajuan.</td>
              </tr>
            <?php else: ?>
              <?php foreach ($riwayat as $i => $row): ?>
                <tr>
                  <td><?= $i + 1; ?></td>
                  <td><?= $row['tanggal_pengajuan']; ?></td>
                  <td>
                    <span class="badge <?= badgeStatus($row['status_pengajuan']); ?>">
                      <?= $row['status_pengajuan']; ?>
                    </span>
                  </td>
                  <td><?= $row['keterangan'] ?? '-'; ?></td>
                  <td>
                    <a class="btn btn-sm btn-primary"
                       href="detail_pengajuan.php?id=<?= (int)$row['id_pengajuan']; ?>">
                      Detail
                    </a>
                  </td>
                </tr>
              <?php endforeach; ?>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>

  </div>
</div>

<?php include "komponen/footer.php"; ?>
