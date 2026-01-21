<?php
session_start();
include '../koneksi.php';

$base = '../';

// cek login
if (!isset($_SESSION['id_user'])) {
  header("Location: ../login.php");
  exit();
}
if (($_SESSION['role'] ?? '') !== 'owner') {
  header("Location: ../login.php");
  exit();
}


function rupiah($angka)
{
  return "Rp " . number_format((float)$angka, 0, ',', '.');
}

function badgeStatus($text)
{
  $t = strtolower(trim((string)$text));

  if ($t === 'lunas' || $t === 'dibayar') return 'bg-success';
  if ($t === 'belum dibayar' || $t === 'pending' || $t === 'unpaid') return 'bg-warning text-dark';
  if ($t === 'batal' || $t === 'ditolak') return 'bg-danger';
  return 'bg-secondary';
}


$statusFilter = $_GET['status'] ?? '';
$cari = $_GET['q'] ?? '';

$statusFilterLower = strtolower(trim($statusFilter));
$cari = trim($cari);


$listInvoice = [];

$sqlInvoice = "
  SELECT
    tbl_invoice.id_invoice,
    tbl_invoice.nomor_invoice,
    tbl_invoice.tanggal_invoice,
    tbl_invoice.tanggal_jatuh_tempo,
    tbl_invoice.total_tagihan,
    COALESCE(tbl_invoice.status_pembayaran, '-') AS status_pembayaran,
    tbl_invoice.nama_file_invoice,
    tbl_invoice.lokasi_file_invoice,

    tbl_penawaran.id_penawaran,
    tbl_penawaran.id_pengajuan,

    tbl_pengajuan_kalibrasi.tanggal_pengajuan,
    COALESCE(tbl_pengajuan_kalibrasi.status_pengajuan, '-') AS status_pengajuan,

    tbl_users.nama AS nama_pelanggan
  FROM tbl_invoice
  LEFT JOIN tbl_penawaran
    ON tbl_penawaran.id_penawaran = tbl_invoice.id_penawaran
  LEFT JOIN tbl_pengajuan_kalibrasi
    ON tbl_pengajuan_kalibrasi.id_pengajuan = tbl_penawaran.id_pengajuan
  LEFT JOIN tbl_pelanggan
    ON tbl_pelanggan.id_pelanggan = tbl_pengajuan_kalibrasi.id_pelanggan
  LEFT JOIN tbl_users
    ON tbl_users.id_user = tbl_pelanggan.id_user
  WHERE 1=1
";

// filter status pembayaran
if ($statusFilterLower !== '') {
  $statusSafe = mysqli_real_escape_string($conn, $statusFilterLower);
  $sqlInvoice .= " AND LOWER(tbl_invoice.status_pembayaran) = '$statusSafe' ";
}

// search nomor invoice
if ($cari !== '') {
  $cariSafe = mysqli_real_escape_string($conn, $cari);
  $sqlInvoice .= " AND tbl_invoice.nomor_invoice LIKE '%$cariSafe%' ";
}

$sqlInvoice .= " ORDER BY tbl_invoice.tanggal_invoice DESC, tbl_invoice.id_invoice DESC ";

$queryInvoice = mysqli_query($conn, $sqlInvoice);
if ($queryInvoice) {
  while ($row = mysqli_fetch_assoc($queryInvoice)) {
    $listInvoice[] = $row;
  }
}


$totalInvoice = count($listInvoice);

include 'komponen/header.php';
include 'komponen/sidebar.php';
include 'komponen/navbar.php';
?>

<div class="content-wrapper">
  <div class="container-fluid p-4">

    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
      <div>
        <h4 class="mb-1">Semua Invoice</h4>
        <div class="text-secondary small">Total data: <?= $totalInvoice; ?></div>
      </div>

      <a href="index.php" class="btn btn-outline-primary">
        <i class="bx bx-arrow-back me-1"></i> Kembali
      </a>
    </div>

    <div class="card shadow-sm border-0 mb-3">
      <div class="card-body">
        <form method="GET" class="row g-2 align-items-center">

          <div class="col-12 col-md-3">
            <select name="status" class="form-select">
              <option value="">Semua Status</option>
              <option value="lunas" <?= ($statusFilterLower==='lunas' ? 'selected' : ''); ?>>Lunas</option>
              <option value="belum dibayar" <?= ($statusFilterLower==='belum dibayar' ? 'selected' : ''); ?>>Belum Dibayar</option>
              <option value="pending" <?= ($statusFilterLower==='pending' ? 'selected' : ''); ?>>Pending</option>
            </select>
          </div>

          <div class="col-12 col-md-5 d-flex gap-2">
            <button class="btn btn-primary" type="submit">
              <i class="bi bi-search"></i> Filter
            </button>

            <a class="btn btn-outline-secondary" href="index.php">
              <i class="bi bi-x-circle"></i> Reset
            </a>
          </div>
        </form>
      </div>
    </div>

    <div class="card shadow-sm border-0">
      <div class="card-body">

        <div class="table-responsive">
          <table class="table align-middle">
            <thead class="table-light">
              <tr>
                <th>No. Invoice</th>
                <th>Tanggal</th>
                <th>Jatuh Tempo</th>
                <th>Pelanggan</th>
                <th>Status</th>
                <th class="text-end">Total</th>
                <th class="text-end">Aksi</th>
              </tr>
            </thead>
            <tbody>
              <?php if (count($listInvoice) === 0): ?>
                <tr>
                  <td colspan="7" class="text-center text-secondary py-4">Data invoice tidak ditemukan.</td>
                </tr>
              <?php else: ?>
                <?php foreach ($listInvoice as $inv): ?>
                  <?php
                    $st = $inv['status_pembayaran'] ?? '-';
                    $pelanggan = $inv['nama_pelanggan'] ?? '-';
                    if ($pelanggan === '' || $pelanggan === null) $pelanggan = '-';
                  ?>
                  <tr>
                    <td class="fw-semibold"><?= $inv['nomor_invoice']; ?></td>
                    <td class="text-secondary"><?= $inv['tanggal_invoice'] ?? '-'; ?></td>
                    <td class="text-secondary"><?= $inv['tanggal_jatuh_tempo'] ?? '-'; ?></td>
                    <td><?= $pelanggan; ?></td>
                    <td>
                      <span class="badge <?= badgeStatus($st); ?>"><?= $st; ?></span>
                    </td>
                    <td class="text-end fw-semibold"><?= rupiah($inv['total_tagihan']); ?></td>
                    <td class="text-end">
                      <a class="btn btn-sm btn-outline-primary"
                         href="detail.php?id_invoice=<?= (int)$inv['id_invoice']; ?>">
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

    <?php include 'komponen/footer.php'; ?>
  </div>
</div>
