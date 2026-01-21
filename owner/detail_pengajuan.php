<?php
session_start();
include "../koneksi.php";

// ======================
// Proteksi akses OWNER
// ======================
if (!isset($_SESSION['id_user'])) {
  header("Location: ../login.php");
  exit();
}

if (($_SESSION['role'] ?? '') !== 'owner') {
  header("Location: ../login.php");
  exit();
}

// ambil id pengajuan dari URL
$id_pengajuan = (int)($_GET['id'] ?? 0);

if ($id_pengajuan <= 0) {
  header("Location: index.php");
  exit();
}

// ======================
// helper badge status (untuk warna status)
// ======================
function badgeStatus($status)
{
  $status = strtolower(trim($status ?? ''));

  // pengajuan
  if ($status == 'dikirim') return 'bg-label-primary';
  if ($status == 'diproses') return 'bg-label-warning';
  if ($status == 'selesai') return 'bg-label-success';
  if ($status == 'ditolak') return 'bg-label-danger';

  // penawaran
  if ($status == 'menunggu' || $status == 'menunggu konfirmasi' || $status == 'pending') return 'bg-label-warning';
  if ($status == 'disetujui' || $status == 'diterima') return 'bg-label-success';
  if ($status == 'ditolak') return 'bg-label-danger';

  // invoice
  if ($status == 'belum dibayar' || $status == 'unpaid' || $status == 'pending') return 'bg-label-warning';
  if ($status == 'lunas' || $status == 'dibayar' || $status == 'paid') return 'bg-label-success';

  // sertifikat
  if ($status == 'terbit') return 'bg-label-success';

  return 'bg-label-secondary';
}

// ======================
// 1) Ambil data pengajuan + data pelanggan (JOIN)
// tbl_pengajuan_kalibrasi -> tbl_pelanggan -> tbl_users
// ======================
$pengajuan = null;

$sqlPengajuan = "
  SELECT 
    tbl_pengajuan_kalibrasi.*,
    tbl_users.nama AS nama_pelanggan,
    tbl_users.email AS email_pelanggan
  FROM tbl_pengajuan_kalibrasi
  INNER JOIN tbl_pelanggan 
    ON tbl_pelanggan.id_pelanggan = tbl_pengajuan_kalibrasi.id_pelanggan
  INNER JOIN tbl_users
    ON tbl_users.id_user = tbl_pelanggan.id_user
  WHERE tbl_pengajuan_kalibrasi.id_pengajuan = '$id_pengajuan'
  LIMIT 1
";

$queryPengajuan = mysqli_query($conn, $sqlPengajuan);
if ($queryPengajuan) {
  $pengajuan = mysqli_fetch_assoc($queryPengajuan);
}

if (!$pengajuan) {
  header("Location: index.php?msg=notfound");
  exit();
}

// ======================
// 2) Ambil daftar alat pada pengajuan ini
// tbl_pengajuan_alat
// ======================
$sqlAlat = "
  SELECT *
  FROM tbl_pengajuan_alat
  WHERE id_pengajuan = '$id_pengajuan'
  ORDER BY id_alat DESC
";
$queryAlat = mysqli_query($conn, $sqlAlat);

// ======================
// 3) Ambil penawaran terbaru untuk pengajuan ini
// tbl_penawaran.id_pengajuan
// ======================
$penawaran = null;
$id_penawaran = 0;
$status_penawaran = '-';

$sqlPenawaran = "
  SELECT *
  FROM tbl_penawaran
  WHERE id_pengajuan = '$id_pengajuan'
  ORDER BY id_penawaran DESC
  LIMIT 1
";

$queryPenawaran = mysqli_query($conn, $sqlPenawaran);
if ($queryPenawaran) {
  $penawaran = mysqli_fetch_assoc($queryPenawaran);
}

if ($penawaran) {
  $id_penawaran = (int)($penawaran['id_penawaran'] ?? 0);
  $status_penawaran = $penawaran['status_penawaran'] ?? '-';
}

// ======================
// 4) Ambil invoice terbaru (jika ada penawaran)
// tbl_invoice.id_penawaran
// ======================
$invoice = null;
$id_invoice = 0;
$status_invoice = '-';

if ($id_penawaran > 0) {

  $sqlInvoice = "
    SELECT *
    FROM tbl_invoice
    WHERE id_penawaran = '$id_penawaran'
    ORDER BY id_invoice DESC
    LIMIT 1
  ";

  $queryInvoice = mysqli_query($conn, $sqlInvoice);
  if ($queryInvoice) {
    $invoice = mysqli_fetch_assoc($queryInvoice);
  }

  if ($invoice) {
    $id_invoice = (int)($invoice['id_invoice'] ?? 0);
    $status_invoice = $invoice['status_pembayaran'] ?? '-';
  }
}

// ======================
// 5) Ambil sertifikat (bisa via id_pengajuan atau id_invoice)
// tbl_sertifikat.id_pengajuan / tbl_sertifikat.id_invoice
// ======================
$sertifikat = null;
$status_sertifikat = '-';

$sqlSertifikatA = "
  SELECT *
  FROM tbl_sertifikat
  WHERE id_pengajuan = '$id_pengajuan'
  ORDER BY id_sertifikat DESC
  LIMIT 1
";
$querySertifikatA = mysqli_query($conn, $sqlSertifikatA);
if ($querySertifikatA) {
  $sertifikat = mysqli_fetch_assoc($querySertifikatA);
}

// kalau belum ada, coba cari berdasarkan invoice
if (!$sertifikat && $id_invoice > 0) {

  $sqlSertifikatB = "
    SELECT *
    FROM tbl_sertifikat
    WHERE id_invoice = '$id_invoice'
    ORDER BY id_sertifikat DESC
    LIMIT 1
  ";

  $querySertifikatB = mysqli_query($conn, $sqlSertifikatB);
  if ($querySertifikatB) {
    $sertifikat = mysqli_fetch_assoc($querySertifikatB);
  }
}

if ($sertifikat) {
  // kalau ada kolom status_sertifikat -> pakai
  // kalau tidak ada -> default "Terbit"
  $status_sertifikat = $sertifikat['status_sertifikat'] ?? 'Terbit';
}

$base = "../";
include "komponen/header.php";
include "komponen/sidebar.php";
include "komponen/navbar.php";
?>

<div class="content-wrapper">
  <div class="container-xxl flex-grow-1 container-p-y">

    <div class="d-flex justify-content-between align-items-center mb-3">
      <div>
        <h4 class="fw-bold mb-1">Detail Pengajuan</h4>
      </div>
      <a href="index.php" class="btn btn-outline-primary">
        <i class="bx bx-arrow-back me-1"></i> Kembali
      </a>
    </div>

    <!-- RINGKASAN PENGAJUAN -->
    <div class="card border-0 shadow-sm mb-3">
      <div class="card-body">

        <div class="d-flex flex-wrap justify-content-between align-items-start gap-2">
          <div>
            <h5 class="mb-1">Pengajuan #<?= $pengajuan['id_pengajuan']; ?></h5>
            <div class="text-muted small">Tanggal pengajuan: <?= $pengajuan['tanggal_pengajuan'] ?? '-'; ?></div>
            <div class="text-muted small">Pelanggan: <?= $pengajuan['nama_pelanggan'] ?? '-'; ?> (<?= $pengajuan['email_pelanggan'] ?? '-'; ?>)</div>
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

        <!-- STATUS TAMBAHAN -->
        <div class="row g-3 mt-2">
          <div class="col-12 col-md-4">
            <div class="text-muted small">Status Penawaran</div>
            <span class="badge <?= badgeStatus($status_penawaran); ?> px-3 py-2">
              <?= $status_penawaran; ?>
            </span>
          </div>

          <div class="col-12 col-md-4">
            <div class="text-muted small">Status Invoice</div>
            <span class="badge <?= badgeStatus($status_invoice); ?> px-3 py-2">
              <?= $status_invoice; ?>
            </span>
          </div>

          <div class="col-12 col-md-4">
            <div class="text-muted small">Status Sertifikat</div>
            <span class="badge <?= badgeStatus($status_sertifikat); ?> px-3 py-2">
              <?= $status_sertifikat; ?>
            </span>
          </div>
        </div>

      </div>
    </div>

    <!-- DETAIL PENAWARAN / INVOICE / SERTIFIKAT -->
    <div class="row g-3 mb-3">

      <!-- PENAWARAN -->
      <div class="col-12 col-lg-4">
        <div class="card border-0 shadow-sm h-100">
          <div class="card-header bg-white">
            <h6 class="mb-0">Penawaran</h6>
          </div>
          <div class="card-body">
            <?php if (!$penawaran): ?>
              <div class="alert alert-warning mb-0">Belum ada penawaran untuk pengajuan ini.</div>
            <?php else: ?>
              <div class="mb-2"><span class="text-muted small">ID Penawaran:</span> <b>#<?= $penawaran['id_penawaran']; ?></b></div>
              <div class="mb-2"><span class="text-muted small">Tanggal:</span> <?= $penawaran['tanggal_penawaran'] ?? '-'; ?></div>
              <div class="mb-2"><span class="text-muted small">Status:</span>
                <span class="badge <?= badgeStatus($penawaran['status_penawaran'] ?? '-'); ?>"><?= $penawaran['status_penawaran'] ?? '-'; ?></span>
              </div>
              <div class="mb-2"><span class="text-muted small">Catatan:</span> <?= ($penawaran['catatan_penawaran'] ?? '') == '' ? '-' : $penawaran['catatan_penawaran']; ?></div>
            <?php endif; ?>
          </div>
        </div>
      </div>

      <!-- INVOICE -->
      <div class="col-12 col-lg-4">
        <div class="card border-0 shadow-sm h-100">
          <div class="card-header bg-white">
            <h6 class="mb-0">Invoice</h6>
          </div>
          <div class="card-body">
            <?php if (!$invoice): ?>
              <div class="alert alert-warning mb-0">Belum ada invoice untuk pengajuan ini.</div>
            <?php else: ?>
              <div class="mb-2"><span class="text-muted small">No Invoice:</span> <b><?= $invoice['nomor_invoice'] ?? '-'; ?></b></div>
              <div class="mb-2"><span class="text-muted small">Tanggal Invoice:</span> <?= $invoice['tanggal_invoice'] ?? '-'; ?></div>
              <div class="mb-2"><span class="text-muted small">Jatuh Tempo:</span> <?= $invoice['tanggal_jatuh_tempo'] ?? '-'; ?></div>
              <div class="mb-2"><span class="text-muted small">Total Tagihan:</span> <b><?= $invoice['total_tagihan'] ?? '0'; ?></b></div>
              <div class="mb-2"><span class="text-muted small">Status Pembayaran:</span>
                <span class="badge <?= badgeStatus($invoice['status_pembayaran'] ?? '-'); ?>"><?= $invoice['status_pembayaran'] ?? '-'; ?></span>
              </div>
            <?php endif; ?>
          </div>
        </div>
      </div>

      <!-- SERTIFIKAT -->
      <div class="col-12 col-lg-4">
        <div class="card border-0 shadow-sm h-100">
          <div class="card-header bg-white">
            <h6 class="mb-0">Sertifikat</h6>
          </div>
          <div class="card-body">
            <?php if (!$sertifikat): ?>
              <div class="alert alert-warning mb-0">Belum ada sertifikat untuk pengajuan ini.</div>
            <?php else: ?>
              <div class="mb-2"><span class="text-muted small">No Sertifikat:</span> <b><?= $sertifikat['nomor_sertifikat'] ?? '-'; ?></b></div>
              <div class="mb-2"><span class="text-muted small">Tanggal Terbit:</span> <?= $sertifikat['dibuat_pada'] ?? '-'; ?></div>
              <div class="mb-2"><span class="text-muted small">Status:</span>
                <span class="badge <?= badgeStatus($status_sertifikat); ?>"><?= $status_sertifikat; ?></span>
              </div>
            <?php endif; ?>
          </div>
        </div>
      </div>

    </div>

    <!-- DATA ALAT -->
    <div class="card border-0 shadow-sm">
      <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Data Alat pada Pengajuan Ini</h5>
      </div>

      <div class="card-body">
        <?php if (!$queryAlat || mysqli_num_rows($queryAlat) == 0): ?>
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
                <?php while ($alat = mysqli_fetch_assoc($queryAlat)): ?>
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
        <?php endif; ?>
      </div>
    </div>

  </div>
</div>

<?php include "komponen/footer.php"; ?>
