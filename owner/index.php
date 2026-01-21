<?php
session_start();
include '../koneksi.php';

$base = '../';

// ======================
// cek login
// ======================
if (!isset($_SESSION['id_user'])) {
  header("Location: ../login.php");
  exit();
}

if (($_SESSION['role'] ?? '') !== 'owner') {
  header("Location: ../login.php");
  exit();
}

// ======================
// helper
// ======================
function rupiah($angka)
{
  return "Rp " . number_format((float)$angka, 0, ',', '.');
}

function badgeStatus($text)
{
  $t = strtolower(trim((string)$text));

  if ($t === 'selesai' || $t === 'lunas' || $t === 'terbit' || $t === 'dibayar') {
    return 'bg-success';
  }

  if ($t === 'diproses' || $t === 'proses' || $t === 'berjalan') {
    return 'bg-primary';
  }

  if ($t === 'menunggu penawaran' || $t === 'pending' || $t === 'belum dibayar') {
    return 'bg-warning text-dark';
  }

  if ($t === 'ditolak' || $t === 'batal') {
    return 'bg-danger';
  }

  return 'bg-secondary';
}

$bulanIni = date('m');
$tahunIni = date('Y');

// ======================
// KPI: Pengajuan Hari Ini
// ======================
$pengajuanHariIni = 0;

$sqlPengajuanHariIni = "
  SELECT COUNT(*) AS total_pengajuan_hari_ini
  FROM tbl_pengajuan_kalibrasi
  WHERE DATE(tanggal_pengajuan) = CURDATE()
";
$queryPengajuanHariIni = mysqli_query($conn, $sqlPengajuanHariIni);
if ($queryPengajuanHariIni) {
  $rowPengajuanHariIni = mysqli_fetch_assoc($queryPengajuanHariIni);
  $pengajuanHariIni = (int)($rowPengajuanHariIni['total_pengajuan_hari_ini'] ?? 0);
}

// ======================
// KPI: Proses Berjalan
// ======================
$prosesBerjalan = 0;

$sqlProsesBerjalan = "
  SELECT COUNT(*) AS total_proses_berjalan
  FROM tbl_pengajuan_kalibrasi
  WHERE LOWER(status_pengajuan) <> 'selesai'
";
$queryProsesBerjalan = mysqli_query($conn, $sqlProsesBerjalan);
if ($queryProsesBerjalan) {
  $rowProsesBerjalan = mysqli_fetch_assoc($queryProsesBerjalan);
  $prosesBerjalan = (int)($rowProsesBerjalan['total_proses_berjalan'] ?? 0);
}

// ======================
// KPI: Selesai Bulan Ini
// ======================
$selesaiBulanIni = 0;

$sqlSelesaiBulanIni = "
  SELECT COUNT(*) AS total_selesai_bulan_ini
  FROM tbl_pengajuan_kalibrasi
  WHERE LOWER(status_pengajuan) = 'selesai'
    AND MONTH(tanggal_pengajuan) = '$bulanIni'
    AND YEAR(tanggal_pengajuan) = '$tahunIni'
";
$querySelesaiBulanIni = mysqli_query($conn, $sqlSelesaiBulanIni);
if ($querySelesaiBulanIni) {
  $rowSelesaiBulanIni = mysqli_fetch_assoc($querySelesaiBulanIni);
  $selesaiBulanIni = (int)($rowSelesaiBulanIni['total_selesai_bulan_ini'] ?? 0);
}

// ======================
// KPI: Pendapatan Bulan Ini (invoice lunas)
// ======================
$pendapatanBulanIni = 0;
$invoiceBulanIni = 0;

$sqlPendapatanBulanIni = "
  SELECT COALESCE(SUM(total_tagihan), 0) AS total_pendapatan_bulan_ini
  FROM tbl_invoice
  WHERE MONTH(tanggal_invoice) = '$bulanIni'
    AND YEAR(tanggal_invoice) = '$tahunIni'
    AND LOWER(status_pembayaran) = 'lunas'
";
$queryPendapatanBulanIni = mysqli_query($conn, $sqlPendapatanBulanIni);
if ($queryPendapatanBulanIni) {
  $rowPendapatanBulanIni = mysqli_fetch_assoc($queryPendapatanBulanIni);
  $pendapatanBulanIni = (float)($rowPendapatanBulanIni['total_pendapatan_bulan_ini'] ?? 0);
}

$sqlInvoiceBulanIni = "
  SELECT COUNT(*) AS total_invoice_bulan_ini
  FROM tbl_invoice
  WHERE MONTH(tanggal_invoice) = '$bulanIni'
    AND YEAR(tanggal_invoice) = '$tahunIni'
";
$queryInvoiceBulanIni = mysqli_query($conn, $sqlInvoiceBulanIni);
if ($queryInvoiceBulanIni) {
  $rowInvoiceBulanIni = mysqli_fetch_assoc($queryInvoiceBulanIni);
  $invoiceBulanIni = (int)($rowInvoiceBulanIni['total_invoice_bulan_ini'] ?? 0);
}

// ======================
// Ringkasan: Antrian Minggu Ini
// ======================
$antrianMingguIni = 0;

$sqlAntrianMingguIni = "
  SELECT COUNT(*) AS total_antrian_minggu_ini
  FROM tbl_pengajuan_kalibrasi
  WHERE tanggal_pengajuan >= DATE_SUB(NOW(), INTERVAL 7 DAY)
";
$queryAntrianMingguIni = mysqli_query($conn, $sqlAntrianMingguIni);
if ($queryAntrianMingguIni) {
  $rowAntrianMingguIni = mysqli_fetch_assoc($queryAntrianMingguIni);
  $antrianMingguIni = (int)($rowAntrianMingguIni['total_antrian_minggu_ini'] ?? 0);
}

// ======================
// Ringkasan: Sertifikat Bulan Ini
// ======================
$sertifikatBulanIni = 0;

$sqlSertifikatBulanIni = "
  SELECT COUNT(*) AS total_sertifikat_bulan_ini
  FROM tbl_sertifikat
  WHERE MONTH(dibuat_pada) = '$bulanIni'
    AND YEAR(dibuat_pada) = '$tahunIni'
";
$querySertifikatBulanIni = mysqli_query($conn, $sqlSertifikatBulanIni);
if ($querySertifikatBulanIni) {
  $rowSertifikatBulanIni = mysqli_fetch_assoc($querySertifikatBulanIni);
  $sertifikatBulanIni = (int)($rowSertifikatBulanIni['total_sertifikat_bulan_ini'] ?? 0);
}

// ======================
// Pengajuan Terbaru
// ======================
$listPengajuan = [];

$sqlPengajuanTerbaru = "
  SELECT 
    tbl_pengajuan_kalibrasi.id_pengajuan,
    tbl_pengajuan_kalibrasi.tanggal_pengajuan,
    COALESCE(tbl_pengajuan_kalibrasi.status_pengajuan, '-') AS status_pengajuan,
    tbl_users.nama AS nama_pelanggan
  FROM tbl_pengajuan_kalibrasi
  INNER JOIN tbl_pelanggan 
    ON tbl_pelanggan.id_pelanggan = tbl_pengajuan_kalibrasi.id_pelanggan
  INNER JOIN tbl_users
    ON tbl_users.id_user = tbl_pelanggan.id_user
  ORDER BY tbl_pengajuan_kalibrasi.tanggal_pengajuan DESC
  LIMIT 8
";
$queryPengajuanTerbaru = mysqli_query($conn, $sqlPengajuanTerbaru);
if ($queryPengajuanTerbaru) {
  while ($rowPengajuan = mysqli_fetch_assoc($queryPengajuanTerbaru)) {
    $listPengajuan[] = $rowPengajuan;
  }
}

// ======================
// Invoice Belum Dibayar
// ======================
$unpaidCount = 0;
$unpaidTotal = 0;
$unpaidRows = [];

$sqlInvoiceBelumDibayar = "
  SELECT 
    COUNT(*) AS total_invoice_belum_dibayar,
    COALESCE(SUM(total_tagihan), 0) AS total_tagihan_belum_dibayar
  FROM tbl_invoice
  WHERE LOWER(status_pembayaran) IN ('belum dibayar','pending','unpaid')
";
$queryInvoiceBelumDibayar = mysqli_query($conn, $sqlInvoiceBelumDibayar);
if ($queryInvoiceBelumDibayar) {
  $rowUnpaid = mysqli_fetch_assoc($queryInvoiceBelumDibayar);
  $unpaidCount = (int)($rowUnpaid['total_invoice_belum_dibayar'] ?? 0);
  $unpaidTotal = (float)($rowUnpaid['total_tagihan_belum_dibayar'] ?? 0);
}

$sqlDaftarInvoiceBelumDibayar = "
  SELECT 
    id_invoice,
    nomor_invoice,
    tanggal_invoice,
    tanggal_jatuh_tempo,
    total_tagihan,
    COALESCE(status_pembayaran, '-') AS status_pembayaran
  FROM tbl_invoice
  WHERE LOWER(status_pembayaran) IN ('belum dibayar','pending','unpaid')
  ORDER BY COALESCE(tanggal_jatuh_tempo, tanggal_invoice) ASC
  LIMIT 8
";
$queryDaftarInvoiceBelumDibayar = mysqli_query($conn, $sqlDaftarInvoiceBelumDibayar);
if ($queryDaftarInvoiceBelumDibayar) {
  while ($rowInvoice = mysqli_fetch_assoc($queryDaftarInvoiceBelumDibayar)) {
    $unpaidRows[] = $rowInvoice;
  }
}

// ======================
// Aktivitas Terbaru (tanpa pesan CS)
// Pengajuan + Penawaran + Invoice + Sertifikat
// ======================
$listAktivitas = [];

$sqlAktivitasGabungan = "
  SELECT 
    tbl_pengajuan_kalibrasi.tanggal_pengajuan AS waktu,
    CONCAT('Pengajuan kalibrasi #', tbl_pengajuan_kalibrasi.id_pengajuan) AS aktivitas,
    COALESCE(tbl_pengajuan_kalibrasi.status_pengajuan, '-') AS status,
    tbl_users.nama AS dibuat_oleh
  FROM tbl_pengajuan_kalibrasi
  INNER JOIN tbl_pelanggan 
    ON tbl_pelanggan.id_pelanggan = tbl_pengajuan_kalibrasi.id_pelanggan
  INNER JOIN tbl_users
    ON tbl_users.id_user = tbl_pelanggan.id_user

  UNION ALL

  SELECT
    tbl_penawaran.tanggal_penawaran AS waktu,
    CONCAT('Penawaran #', tbl_penawaran.id_penawaran, ' (Pengajuan #', tbl_penawaran.id_pengajuan, ')') AS aktivitas,
    COALESCE(tbl_penawaran.status_penawaran, '-') AS status,
    COALESCE(tbl_users.nama, 'Admin') AS dibuat_oleh
  FROM tbl_penawaran
  LEFT JOIN tbl_users
    ON tbl_users.id_user = tbl_penawaran.id_admin

  UNION ALL

  SELECT
    COALESCE(tbl_invoice.dibuat_pada, CONCAT(tbl_invoice.tanggal_invoice, ' 00:00:00')) AS waktu,
    CONCAT('Invoice ', tbl_invoice.nomor_invoice) AS aktivitas,
    COALESCE(tbl_invoice.status_pembayaran, '-') AS status,
    'Sistem' AS dibuat_oleh
  FROM tbl_invoice

  UNION ALL

  SELECT
    tbl_sertifikat.dibuat_pada AS waktu,
    CONCAT('Sertifikat ', tbl_sertifikat.nomor_sertifikat) AS aktivitas,
    'Terbit' AS status,
    'Sistem' AS dibuat_oleh
  FROM tbl_sertifikat

  ORDER BY waktu DESC
  LIMIT 10
";

$queryAktivitasGabungan = mysqli_query($conn, $sqlAktivitasGabungan);
if ($queryAktivitasGabungan) {
  while ($rowAkt = mysqli_fetch_assoc($queryAktivitasGabungan)) {
    $listAktivitas[] = $rowAkt;
  }
}

// ======================
// komponen layout
// ======================
include 'komponen/header.php';
include 'komponen/sidebar.php';
include 'komponen/navbar.php';
?>

<div class="content-wrapper">
  <div class="container-fluid p-4">

    <!-- HEADER -->
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
      <div>
        <h4 class="mb-1">Dashboard Owner</h4>
        <div class="text-secondary">
          Selamat datang, <?= $_SESSION['nama'] ?? 'Owner'; ?>!
        </div>
      </div>

      <div class="d-flex align-items-center gap-2">
        <span class="badge bg-primary d-none d-md-inline">
          <i class="bi bi-calendar3 me-1"></i><?= date('F Y'); ?>
        </span>
      </div>
    </div>

    <!-- KPI -->
    <div class="row g-3 mb-4">
      <div class="col-12 col-md-6 col-xl-3">
        <div class="card card-kpi">
          <div class="card-body d-flex justify-content-between align-items-center">
            <div>
              <div class="text-secondary small">Pengajuan Hari Ini</div>
              <div class="fs-4 fw-bold"><?= $pengajuanHariIni; ?></div>
            </div>
            <div class="kpi-icon"><i class="bi bi-inbox"></i></div>
          </div>
        </div>
      </div>

      <div class="col-12 col-md-6 col-xl-3">
        <div class="card card-kpi">
          <div class="card-body d-flex justify-content-between align-items-center">
            <div>
              <div class="text-secondary small">Proses Berjalan</div>
              <div class="fs-4 fw-bold"><?= $prosesBerjalan; ?></div>
            </div>
            <div class="kpi-icon"><i class="bi bi-arrow-repeat"></i></div>
          </div>
        </div>
      </div>

      <div class="col-12 col-md-6 col-xl-3">
        <div class="card card-kpi">
          <div class="card-body d-flex justify-content-between align-items-center">
            <div>
              <div class="text-secondary small">Selesai Bulan Ini</div>
              <div class="fs-4 fw-bold"><?= $selesaiBulanIni; ?></div>
            </div>
            <div class="kpi-icon"><i class="bi bi-check2-circle"></i></div>
          </div>
        </div>
      </div>

      <div class="col-12 col-md-6 col-xl-3">
        <div class="card card-kpi">
          <div class="card-body d-flex justify-content-between align-items-center">
            <div>
              <div class="text-secondary small">Pendapatan Bulan Ini</div>
              <div class="fs-5 fw-bold"><?= rupiah($pendapatanBulanIni); ?></div>
              <div class="small text-secondary"><?= $invoiceBulanIni; ?> invoice tercatat</div>
            </div>
            <div class="kpi-icon"><i class="bi bi-cash-coin"></i></div>
          </div>
        </div>
      </div>
    </div>

    <!-- 2 kolom -->
    <div class="row g-3 mb-4">

      <!-- Invoice Belum Dibayar -->
      <div class="col-12 col-xl-6">
        <div class="card soft-card">
          <div class="card-header bg-white rounded-top d-flex align-items-center justify-content-between">
            <div class="fw-semibold">
              <i class="bi bi-receipt-cutoff me-2 text-danger"></i>Invoice Belum Dibayar
            </div>
            <span class="badge bg-danger"><?= $unpaidCount; ?> belum dibayar</span>
          </div>

          <div class="card-body">
            <div class="d-flex align-items-center justify-content-between mb-3">
              <div class="small text-secondary">Total tagihan belum dibayar</div>
              <div class="fw-bold"><?= rupiah($unpaidTotal); ?></div>
            </div>

            <div class="table-responsive">
              <table class="table table-sm align-middle mb-0">
                <thead class="table-light">
                  <tr>
                    <th>No. Invoice</th>
                    <th>Jatuh Tempo</th>
                    <th class="text-end">Total</th>
                  </tr>
                </thead>
                <tbody>
                  <?php if (count($unpaidRows) === 0): ?>
                    <tr>
                      <td colspan="3" class="text-center text-secondary py-3">
                        Tidak ada invoice yang belum dibayar.
                      </td>
                    </tr>
                  <?php else: ?>
                    <?php foreach ($unpaidRows as $inv): ?>
                      <tr>
                        <td class="fw-semibold">
                          <?= $inv['nomor_invoice']; ?>
                          <div class="small text-secondary">
                            Status: <?= $inv['status_pembayaran']; ?>
                          </div>
                        </td>
                        <td class="text-secondary">
                          <?= $inv['tanggal_jatuh_tempo'] ?? '-'; ?>
                        </td>
                        <td class="text-end fw-semibold">
                          <?= rupiah($inv['total_tagihan']); ?>
                        </td>
                      </tr>
                    <?php endforeach; ?>
                  <?php endif; ?>
                </tbody>
              </table>
            </div>

            <div class="mt-3 text-end">
              <a href="invoice.php" class="btn btn-sm btn-outline-danger">Lihat Semua Invoice</a>
            </div>
          </div>
        </div>
      </div>

      <!-- Pengajuan Terbaru -->
      <div class="col-12 col-xl-6" id="pengajuan">
        <div class="card soft-card">
          <div class="card-header bg-white rounded-top d-flex align-items-center justify-content-between">
            <div class="fw-semibold">
              <i class="bi bi-inboxes me-2 text-primary"></i>Pengajuan Terbaru
            </div>
            <a href="#" class="small text-decoration-none">Lihat semua</a>
          </div>

          <div class="card-body">
            <div class="table-responsive">
              <table class="table table-sm align-middle mb-0">
                <thead class="table-light">
                  <tr>
                    <th>Kode</th>
                    <th>Pelanggan</th>
                    <th>Status</th>
                    <th class="text-end">Aksi</th>
                  </tr>
                </thead>
                <tbody>
                  <?php if (count($listPengajuan) === 0): ?>
                    <tr>
                      <td colspan="4" class="text-center text-secondary py-3">Belum ada data pengajuan.</td>
                    </tr>
                  <?php else: ?>
                    <?php foreach ($listPengajuan as $p): ?>
                      <?php $st = $p['status_pengajuan'] ?? '-'; ?>
                      <tr>
                        <td class="fw-semibold">#<?= (int)$p['id_pengajuan']; ?></td>
                        <td><?= $p['nama_pelanggan']; ?></td>
                        <td>
                          <span class="badge <?= badgeStatus($st); ?>">
                            <?= $st; ?>
                          </span>
                        </td>
                        <td class="text-end">
                          <a class="btn btn-sm btn-outline-primary" href="detail_pengajuan.php?id=<?= $p['id_pengajuan']; ?>">Detail</a>
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

    </div>

    <!-- Aktivitas Terbaru -->
    <div class="card soft-card" id="aktivitas">
      <div class="card-header bg-white rounded-top d-flex align-items-center justify-content-between">
        <div class="fw-semibold">
          <i class="bi bi-clock-history me-2 text-primary"></i>Aktivitas Terbaru
        </div>
      </div>

      <div class="card-body">
        <div class="table-responsive">
          <table class="table align-middle mb-0">
            <thead class="table-light">
              <tr>
                <th>Tanggal</th>
                <th>Aktivitas</th>
                <th>Status</th>
                <th>Dibuat Oleh</th>
              </tr>
            </thead>
            <tbody>
              <?php if (count($listAktivitas) === 0): ?>
                <tr>
                  <td colspan="4" class="text-center text-secondary py-4">Belum ada aktivitas.</td>
                </tr>
              <?php else: ?>
                <?php foreach ($listAktivitas as $a): ?>
                  <?php $stA = $a['status'] ?? '-'; ?>
                  <tr>
                    <td class="text-secondary"><?= $a['waktu']; ?></td>
                    <td><?= $a['aktivitas']; ?></td>
                    <td>
                      <span class="badge <?= badgeStatus($stA); ?>">
                        <?= $stA; ?>
                      </span>
                    </td>
                    <td><?= $a['dibuat_oleh']; ?></td>
                  </tr>
                <?php endforeach; ?>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <?php include 'komponen/footer.php'; ?>

  </div>
</div>
