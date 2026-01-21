<?php
session_start();
$title = "Owner - Dashboard";
$activeDashboard = "active";

include "../koneksi.php";

// proteksi role owner
if (!isset($_SESSION['id_user']) || ($_SESSION['role'] ?? '') !== 'owner') {
  header("Location: ../login.php");
  exit();
}

// helper query single value
function fetchOne($conn, $sql) {
  $q = mysqli_query($conn, $sql);
  if (!$q) return 0;
  $row = mysqli_fetch_row($q);
  return $row ? $row[0] : 0;
}

// tanggal acuan
$today      = date('Y-m-d');
$startWeek  = date('Y-m-d', strtotime('monday this week'));
$endWeek    = date('Y-m-d', strtotime('sunday this week'));
$startMonth = date('Y-m-01');
$endMonth   = date('Y-m-t');

// ======================
// 1) Kartu ringkasan
// ======================

// Pengajuan hari ini
$pengajuanHariIni = (int) fetchOne($conn, "SELECT COUNT(*) FROM tbl_pengajuan_kalibrasi WHERE DATE(tanggal_pengajuan) = '$today'");
$prosesHariIni    = (int) fetchOne($conn, "SELECT COUNT(*) FROM tbl_pengajuan_kalibrasi WHERE DATE(tanggal_pengajuan) = '$today' AND status_pengajuan = 'diproses'");
$selesaiHariIni   = (int) fetchOne($conn, "SELECT COUNT(*) FROM tbl_pengajuan_kalibrasi WHERE DATE(tanggal_pengajuan) = '$today' AND status_pengajuan = 'selesai'");

// Minggu ini
$pengajuanMingguIni = (int) fetchOne($conn, "SELECT COUNT(*) FROM tbl_pengajuan_kalibrasi WHERE DATE(tanggal_pengajuan) BETWEEN '$startWeek' AND '$endWeek'");
$baruMingguIni      = (int) fetchOne($conn, "SELECT COUNT(*) FROM tbl_pengajuan_kalibrasi WHERE DATE(tanggal_pengajuan) BETWEEN '$startWeek' AND '$endWeek' AND status_pengajuan = 'dikirim'");
$terjadwalMingguIni  = (int) fetchOne($conn, "SELECT COUNT(*) FROM tbl_pengajuan_kalibrasi WHERE DATE(tanggal_pengajuan) BETWEEN '$startWeek' AND '$endWeek' AND status_pengajuan IN ('diproses','selesai')");

// Sertifikat bulan ini
$sertifikatBulanIni = (int) fetchOne($conn, "SELECT COUNT(*) FROM tbl_sertifikat WHERE DATE(dibuat_pada) BETWEEN '$startMonth' AND '$endMonth'");

// Pendapatan bulan ini (dari invoice)
$pendapatanBulanIni = (float) fetchOne($conn, "SELECT IFNULL(SUM(total_tagihan),0) FROM tbl_invoice WHERE DATE(dibuat_pada) BETWEEN '$startMonth' AND '$endMonth'");
$invoiceBulanIni    = (int) fetchOne($conn, "SELECT COUNT(*) FROM tbl_invoice WHERE DATE(dibuat_pada) BETWEEN '$startMonth' AND '$endMonth'");

// pelanggan aktif (total pelanggan)
$pelangganAktif = (int) fetchOne($conn, "SELECT COUNT(*) FROM tbl_pelanggan");

// ======================
// 2) Aktivitas terbaru (gabungan beberapa tabel)
// ======================
$activities = [];
$sqlAktivitas = "
  (SELECT 
      pk.tanggal_pengajuan AS waktu,
      CONCAT('Pengajuan kalibrasi #', pk.id_pengajuan) AS aktivitas,
      pk.status_pengajuan AS status,
      u.nama AS petugas
   FROM tbl_pengajuan_kalibrasi pk
   LEFT JOIN tbl_pelanggan p ON p.id_pelanggan = pk.id_pelanggan
   LEFT JOIN tbl_users u ON u.id_user = p.id_user
  )
  UNION ALL
  (SELECT
      pn.tanggal_penawaran AS waktu,
      CONCAT('Penawaran dibuat untuk pengajuan #', pn.id_pengajuan) AS aktivitas,
      pn.status_penawaran AS status,
      u2.nama AS petugas
   FROM tbl_penawaran pn
   LEFT JOIN tbl_users u2 ON u2.id_user = pn.id_admin
  )
  UNION ALL
  (SELECT
      i.dibuat_pada AS waktu,
      CONCAT('Invoice ', i.nomor_invoice) AS aktivitas,
      i.status_pembayaran AS status,
      'Sistem' AS petugas
   FROM tbl_invoice i
  )
  UNION ALL
  (SELECT
      s.dibuat_pada AS waktu,
      CONCAT('Sertifikat ', s.nomor_sertifikat) AS aktivitas,
      'terbit' AS status,
      'Admin' AS petugas
   FROM tbl_sertifikat s
  )
  ORDER BY waktu DESC
  LIMIT 10
";
$qAkt = mysqli_query($conn, $sqlAktivitas);
if ($qAkt) {
  while ($r = mysqli_fetch_assoc($qAkt)) {
    $activities[] = $r;
  }
}

// badge status
function badgeStatus($status) {
  $s = strtolower(trim((string)$status));
  if (in_array($s, ['selesai','diterima','dibayar','terbit'])) return ['success','Selesai'];
  if (in_array($s, ['diproses','negosiasi','proses'])) return ['warning','Proses'];
  if (in_array($s, ['dikirim','masuk'])) return ['primary','Masuk'];
  if (in_array($s, ['ditolak','gagal','batal'])) return ['danger','Ditolak'];
  return ['secondary', ucfirst($status ?: 'info')];
}

// format rupiah
function rupiah($angka) {
  return "Rp " . number_format((float)$angka, 0, ',', '.');
}

include 'komponen/header.php';
include 'komponen/navbar.php';
include 'komponen/sidebar.php';
?>

<style>
  :root {
    --owner-blue: #1f5aa6;
    --owner-blue-soft: #e9f1ff;
  }
  .owner-hero {
    background: linear-gradient(135deg, #ffffff 0%, #f4f8ff 55%, #e9f1ff 100%);
    border: 1px solid #e3ecff;
  }
  .owner-card { border: 1px solid #e7efff; }
  .owner-badge { background: var(--owner-blue-soft); color: var(--owner-blue); }
  .owner-table thead th { background: #f2f6ff; }
  .btn-owner { background: var(--owner-blue); border-color: var(--owner-blue); }
  .btn-owner:hover { background: #194a8a; border-color: #194a8a; }
  @media print {
    .no-print { display: none !important; }
    body { background: #ffffff !important; }
  }
</style>

<div class="container-fluid py-3">
  <div class="owner-hero rounded-4 p-4 mb-4">
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
      <div>
        <h4 class="mb-1">Dashboard Owner</h4>
        <p class="mb-0 text-muted">Ringkasan aktivitas sistem kalibrasi hari ini.</p>
      </div>
      <div class="d-flex gap-2 no-print">
        <a href="laporan.php" class="btn btn-outline-primary">Lihat Laporan</a>
        <button type="button" class="btn btn-owner text-white" onclick="window.print()">Print</button>
      </div>
    </div>
  </div>

  <!-- 4 Card ringkasan -->
  <div class="row g-3 mb-4">
    <div class="col-12 col-md-6 col-xl-3">
      <div class="card owner-card h-100">
        <div class="card-body">
          <div class="d-flex justify-content-between align-items-center mb-2">
            <span class="badge owner-badge">Aktifitas</span>
            <span class="text-primary fw-semibold">Hari Ini</span>
          </div>
          <h5 class="mb-1"><?= $pengajuanHariIni ?> Pengajuan</h5>
          <p class="text-muted mb-0"><?= $prosesHariIni ?> proses berjalan, <?= $selesaiHariIni ?> selesai</p>
        </div>
      </div>
    </div>

    <div class="col-12 col-md-6 col-xl-3">
      <div class="card owner-card h-100">
        <div class="card-body">
          <div class="d-flex justify-content-between align-items-center mb-2">
            <span class="badge owner-badge">Antrian</span>
            <span class="text-primary fw-semibold">Minggu Ini</span>
          </div>
          <h5 class="mb-1"><?= $pengajuanMingguIni ?> Permintaan</h5>
          <p class="text-muted mb-0"><?= $baruMingguIni ?> baru, <?= $terjadwalMingguIni ?> terjadwal</p>
        </div>
      </div>
    </div>

    <div class="col-12 col-md-6 col-xl-3">
      <div class="card owner-card h-100">
        <div class="card-body">
          <div class="d-flex justify-content-between align-items-center mb-2">
            <span class="badge owner-badge">Sertifikat</span>
            <span class="text-primary fw-semibold">Bulan Ini</span>
          </div>
          <h5 class="mb-1"><?= $sertifikatBulanIni ?> Dokumen</h5>
          <p class="text-muted mb-0">Jumlah sertifikat terbit bulan berjalan</p>
        </div>
      </div>
    </div>

    <div class="col-12 col-md-6 col-xl-3">
      <div class="card owner-card h-100">
        <div class="card-body">
          <div class="d-flex justify-content-between align-items-center mb-2">
            <span class="badge owner-badge">Pendapatan</span>
            <span class="text-primary fw-semibold">Bulan Ini</span>
          </div>
          <h5 class="mb-1"><?= rupiah($pendapatanBulanIni) ?></h5>
          <p class="text-muted mb-0"><?= $invoiceBulanIni ?> invoice tercatat bulan ini</p>
        </div>
      </div>
    </div>
  </div>

  <!-- Aktivitas terbaru + Ringkasan laporan -->
  <div class="row g-3">
    <div class="col-12 col-lg-7">
      <div class="card owner-card h-100">
        <div class="card-body">
          <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="mb-0">Aktifitas Terbaru</h5>
            <span class="text-muted small">Realtime dari database</span>
          </div>
          <div class="table-responsive">
            <table class="table table-bordered owner-table align-middle mb-0">
              <thead>
                <tr>
                  <th>Waktu</th>
                  <th>Aktifitas</th>
                  <th>Status</th>
                  <th>Petugas</th>
                </tr>
              </thead>
              <tbody>
                <?php if (count($activities) == 0): ?>
                  <tr>
                    <td colspan="4" class="text-center text-muted py-4">Tidak ada data aktivitas.</td>
                  </tr>
                <?php else: ?>
                  <?php foreach ($activities as $a): 
                    $waktu = date('d/m/Y H:i', strtotime($a['waktu']));
                    [$badgeClass, $label] = badgeStatus($a['status']);
                  ?>
                    <tr>
                      <td><?= htmlspecialchars($waktu) ?></td>
                      <td><?= htmlspecialchars($a['aktivitas']) ?></td>
                      <td><span class="badge bg-<?= $badgeClass ?> <?= $badgeClass=='warning' ? 'text-dark' : '' ?>"><?= htmlspecialchars($label) ?></span></td>
                      <td><?= htmlspecialchars($a['petugas'] ?: '-') ?></td>
                    </tr>
                  <?php endforeach; ?>
                <?php endif; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>

    <div class="col-12 col-lg-5">
      <div class="card owner-card h-100">
        <div class="card-body">
          <h5 class="mb-3">Ringkasan Laporan</h5>

          <div class="mb-3 p-3 rounded-3" style="background: var(--owner-blue-soft);">
            <div class="d-flex justify-content-between">
              <div>
                <div class="text-muted small">Pendapatan Bulan Ini</div>
                <div class="h5 mb-0"><?= rupiah($pendapatanBulanIni) ?></div>
              </div>
              <div class="text-primary fw-semibold"><?= $invoiceBulanIni ?> invoice</div>
            </div>
          </div>

          <div class="mb-3 p-3 rounded-3 border">
            <div class="d-flex justify-content-between">
              <div>
                <div class="text-muted small">Sertifikat Terbit</div>
                <div class="h5 mb-0"><?= $sertifikatBulanIni ?> Dokumen</div>
              </div>
              <div class="text-primary fw-semibold">Bulan ini</div>
            </div>
          </div>

          <div class="p-3 rounded-3 border">
            <div class="d-flex justify-content-between">
              <div>
                <div class="text-muted small">Pelanggan Aktif</div>
                <div class="h5 mb-0"><?= $pelangganAktif ?> Perusahaan</div>
              </div>
              <div class="text-primary fw-semibold">Total</div>
            </div>
          </div>

          <div class="mt-4 no-print">
            <a href="laporan.php" class="btn btn-owner text-white w-100">Lihat Laporan Detail</a>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<?php include 'komponen/footer.php'; ?>
