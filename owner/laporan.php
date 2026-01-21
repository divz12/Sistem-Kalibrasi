<?php
session_start();
include "../koneksi.php";

// proteksi owner
if (!isset($_SESSION['id_user']) || ($_SESSION['role'] ?? '') !== 'owner') {
  header("Location: ../login.php");
  exit();
}

function rupiah($angka) {
  return "Rp " . number_format((float)$angka, 0, ',', '.');
}

function badgeStatus($text) {
  $t = strtolower(trim((string)$text));

  if ($t === 'selesai' || $t === 'lunas' || $t === 'terbit' || $t === 'dibayar') return 'bg-success';
  if ($t === 'diproses' || $t === 'proses' || $t === 'berjalan') return 'bg-primary';
  if ($t === 'menunggu penawaran' || $t === 'pending' || $t === 'belum dibayar' || $t === 'unpaid') return 'bg-warning text-dark';
  if ($t === 'ditolak' || $t === 'batal') return 'bg-danger';

  return 'bg-secondary';
}

// filter periode
$bulan = isset($_GET['bulan']) ? (int)$_GET['bulan'] : (int)date('m');
$tahun = isset($_GET['tahun']) ? (int)$_GET['tahun'] : (int)date('Y');

if ($bulan < 1 || $bulan > 12) $bulan = (int)date('m');
if ($tahun < 2000) $tahun = (int)date('Y');

// ringkasan pengajuan periode
$totalPengajuan = 0;
$sqlTotalPengajuan = "
  SELECT COUNT(*) AS total_pengajuan
  FROM tbl_pengajuan_kalibrasi
  WHERE MONTH(tanggal_pengajuan) = '$bulan'
    AND YEAR(tanggal_pengajuan) = '$tahun'
";
$qTotalPengajuan = mysqli_query($conn, $sqlTotalPengajuan);
if ($qTotalPengajuan) {
  $r = mysqli_fetch_assoc($qTotalPengajuan);
  $totalPengajuan = (int)($r['total_pengajuan'] ?? 0);
}

$pengajuanSelesai = 0;
$sqlPengajuanSelesai = "
  SELECT COUNT(*) AS total_selesai
  FROM tbl_pengajuan_kalibrasi
  WHERE LOWER(status_pengajuan) = 'selesai'
    AND MONTH(tanggal_pengajuan) = '$bulan'
    AND YEAR(tanggal_pengajuan) = '$tahun'
";
$qPengajuanSelesai = mysqli_query($conn, $sqlPengajuanSelesai);
if ($qPengajuanSelesai) {
  $r = mysqli_fetch_assoc($qPengajuanSelesai);
  $pengajuanSelesai = (int)($r['total_selesai'] ?? 0);
}

// ringkasan invoice periode
$invoiceCount = 0;
$sqlInvoiceCount = "
  SELECT COUNT(*) AS total_invoice
  FROM tbl_invoice
  WHERE MONTH(tanggal_invoice) = '$bulan'
    AND YEAR(tanggal_invoice) = '$tahun'
";
$qInvoiceCount = mysqli_query($conn, $sqlInvoiceCount);
if ($qInvoiceCount) {
  $r = mysqli_fetch_assoc($qInvoiceCount);
  $invoiceCount = (int)($r['total_invoice'] ?? 0);
}

$pendapatanLunas = 0;
$sqlPendapatanLunas = "
  SELECT COALESCE(SUM(total_tagihan),0) AS total_pendapatan
  FROM tbl_invoice
  WHERE LOWER(status_pembayaran) = 'lunas'
    AND MONTH(tanggal_invoice) = '$bulan'
    AND YEAR(tanggal_invoice) = '$tahun'
";
$qPendapatanLunas = mysqli_query($conn, $sqlPendapatanLunas);
if ($qPendapatanLunas) {
  $r = mysqli_fetch_assoc($qPendapatanLunas);
  $pendapatanLunas = (float)($r['total_pendapatan'] ?? 0);
}

$invoiceBelumDibayarCount = 0;
$invoiceBelumDibayarTotal = 0;
$sqlBelumBayar = "
  SELECT
    COUNT(*) AS total_belum_bayar,
    COALESCE(SUM(total_tagihan),0) AS total_tagihan
  FROM tbl_invoice
  WHERE LOWER(status_pembayaran) IN ('belum dibayar','pending','unpaid')
    AND MONTH(tanggal_invoice) = '$bulan'
    AND YEAR(tanggal_invoice) = '$tahun'
";
$qBelumBayar = mysqli_query($conn, $sqlBelumBayar);
if ($qBelumBayar) {
  $r = mysqli_fetch_assoc($qBelumBayar);
  $invoiceBelumDibayarCount = (int)($r['total_belum_bayar'] ?? 0);
  $invoiceBelumDibayarTotal = (float)($r['total_tagihan'] ?? 0);
}

// ringkasan sertifikat periode
$sertifikatCount = 0;
$sqlSertifikat = "
  SELECT COUNT(*) AS total_sertifikat
  FROM tbl_sertifikat
  WHERE MONTH(dibuat_pada) = '$bulan'
    AND YEAR(dibuat_pada) = '$tahun'
";
$qSertifikat = mysqli_query($conn, $sqlSertifikat);
if ($qSertifikat) {
  $r = mysqli_fetch_assoc($qSertifikat);
  $sertifikatCount = (int)($r['total_sertifikat'] ?? 0);
}

// data tabel laporan (pengajuan + status penawaran + status invoice + status sertifikat)
$listLaporan = [];

$sqlLaporan = "
  SELECT
    pk.id_pengajuan,
    pk.tanggal_pengajuan,
    pk.status_pengajuan,
    pk.catatan,

    u.nama AS nama_pelanggan,

    pn.id_penawaran,
    pn.tanggal_penawaran,
    pn.status_penawaran,
    pn.total_biaya,

    i.id_invoice,
    i.nomor_invoice,
    i.tanggal_invoice,
    i.tanggal_jatuh_tempo,
    i.status_pembayaran,
    i.total_tagihan,

    s.id_sertifikat,
    s.nomor_sertifikat,
    s.tanggal_terbit

  FROM tbl_pengajuan_kalibrasi pk
  INNER JOIN tbl_pelanggan p
    ON p.id_pelanggan = pk.id_pelanggan
  INNER JOIN tbl_users u
    ON u.id_user = p.id_user

  LEFT JOIN tbl_penawaran pn
    ON pn.id_pengajuan = pk.id_pengajuan

  LEFT JOIN tbl_invoice i
    ON i.id_penawaran = pn.id_penawaran

  LEFT JOIN tbl_sertifikat s
    ON s.id_pengajuan = pk.id_pengajuan

  WHERE MONTH(pk.tanggal_pengajuan) = '$bulan'
    AND YEAR(pk.tanggal_pengajuan) = '$tahun'

  ORDER BY pk.tanggal_pengajuan DESC, pn.tanggal_penawaran DESC, i.tanggal_invoice DESC
";

$qLaporan = mysqli_query($conn, $sqlLaporan);
if ($qLaporan) {
  while ($row = mysqli_fetch_assoc($qLaporan)) {
    $listLaporan[] = $row;
  }
}

$base = '../';
include "komponen/header.php";
include "komponen/sidebar.php";
include "komponen/navbar.php";
?>

<div class="content-wrapper">
  <div class="container-fluid p-4">

    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
      <div>
        <h4 class="mb-0">Laporan</h4>
      </div>

      <div class="d-flex flex-wrap gap-2">
        <form method="GET" class="d-flex gap-2">
          <select name="bulan" class="form-select form-select-sm" style="width:140px;">
            <?php for($b=1;$b<=12;$b++): ?>
              <option value="<?= $b; ?>" <?= ($b==$bulan?'selected':''); ?>>Bulan <?= $b; ?></option>
            <?php endfor; ?>
          </select>

          <select name="tahun" class="form-select form-select-sm" style="width:120px;">
            <?php for($t=(int)date('Y')-2;$t<=(int)date('Y')+1;$t++): ?>
              <option value="<?= $t; ?>" <?= ($t==$tahun?'selected':''); ?>><?= $t; ?></option>
            <?php endfor; ?>
          </select>

          <button class="btn btn-sm btn-primary" type="submit">
            <i class="bi bi-funnel me-1"></i>Terapkan
          </button>
        </form>
      </div>
    </div>

    <!-- Ringkasan -->
    <div class="row g-3 mb-3">
      <div class="col-12 col-md-6 col-xl-3">
        <div class="card border-0 shadow-sm">
          <div class="card-body">
            <div class="text-muted small">Total Pengajuan</div>
            <div class="fs-4 fw-bold"><?= $totalPengajuan; ?></div>
            <div class="small text-muted">Selesai: <?= $pengajuanSelesai; ?></div>
          </div>
        </div>
      </div>

      <div class="col-12 col-md-6 col-xl-3">
        <div class="card border-0 shadow-sm">
          <div class="card-body">
            <div class="text-muted small">Total Invoice</div>
            <div class="fs-4 fw-bold"><?= $invoiceCount; ?></div>
            <div class="small text-muted">Belum bayar: <?= $invoiceBelumDibayarCount; ?></div>
          </div>
        </div>
      </div>

      <div class="col-12 col-md-6 col-xl-3">
        <div class="card border-0 shadow-sm">
          <div class="card-body">
            <div class="text-muted small">Pendapatan (Lunas)</div>
            <div class="fs-5 fw-bold"><?= rupiah($pendapatanLunas); ?></div>
            <div class="small text-muted">Periode terpilih</div>
          </div>
        </div>
      </div>

      <div class="col-12 col-md-6 col-xl-3">
        <div class="card border-0 shadow-sm">
          <div class="card-body">
            <div class="text-muted small">Sertifikat Terbit</div>
            <div class="fs-4 fw-bold"><?= $sertifikatCount; ?></div>
            <div class="small text-muted">Periode terpilih</div>
          </div>
        </div>
      </div>
    </div>

    <!-- Tabel laporan -->
    <div class="card border-0 shadow-sm">
      <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <div class="fw-semibold">Detail Laporan</div>
        <div class="small text-muted">Periode: <?= $bulan; ?>/<?= $tahun; ?></div>
      </div>

      <div class="card-body">
        <div class="table-responsive">
          <table class="table table-bordered align-middle">
            <thead class="table-light">
              <tr>
                <th style="width:90px;">ID</th>
                <th style="width:160px;">Tanggal</th>
                <th>Pelanggan</th>

                <th>Status Pengajuan</th>
                <th>Status Penawaran</th>
                <th>Status Invoice</th>
                <th>Status Sertifikat</th>

                <th style="width:160px;" class="text-end">Total Tagihan</th>
              </tr>
            </thead>
            <tbody>
              <?php if(count($listLaporan) == 0): ?>
                <tr>
                  <td colspan="8" class="text-center text-muted py-4">Tidak ada data pada periode ini.</td>
                </tr>
              <?php else: ?>
                <?php foreach($listLaporan as $d): ?>
                  <?php
                    $stPengajuan  = $d['status_pengajuan'] ?? '-';
                    $stPenawaran  = $d['status_penawaran'] ?? '-';
                    $stInvoice    = $d['status_pembayaran'] ?? '-';
                    $stSertifikat = ($d['id_sertifikat'] ?? 0) ? 'Terbit' : '-';
                    $totalTagihan = $d['total_tagihan'] ?? 0;
                  ?>
                  <tr>
                    <td class="fw-semibold">#<?= $d['id_pengajuan']; ?></td>
                    <td><?= $d['tanggal_pengajuan']; ?></td>
                    <td><?= $d['nama_pelanggan']; ?></td>

                    <td><span class="badge <?= badgeStatus($stPengajuan); ?>"><?= $stPengajuan; ?></span></td>
                    <td><span class="badge <?= badgeStatus($stPenawaran); ?>"><?= $stPenawaran; ?></span></td>
                    <td><span class="badge <?= badgeStatus($stInvoice); ?>"><?= $stInvoice; ?></span></td>
                    <td><span class="badge <?= badgeStatus($stSertifikat); ?>"><?= $stSertifikat; ?></span></td>

                    <td class="text-end"><?= rupiah($totalTagihan); ?></td>
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

<?php include "komponen/footer.php"; ?>
