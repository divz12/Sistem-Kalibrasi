<?php
session_start();
include "../koneksi.php";

// proteksi login pelanggan
if (!isset($_SESSION['id_user']) || ($_SESSION['role'] ?? '') !== 'pelanggan') {
  header("Location: ../login.php");
  exit();
}

$id_user = (int)($_SESSION['id_user'] ?? 0);

$qPel = mysqli_query($conn, "SELECT id_pelanggan FROM tbl_pelanggan WHERE id_user='$id_user' LIMIT 1");
$dataPel = mysqli_fetch_assoc($qPel);
$id_pelanggan = (int)($dataPel['id_pelanggan'] ?? 0);

if ($id_pelanggan <= 0) {
  header("Location: profil.php");
  exit();
}

function kolomAda($conn, $namaTabel, $namaKolom)
{
  $sql = "
    SELECT COUNT(*) AS total
    FROM information_schema.columns
    WHERE table_schema = DATABASE()
      AND table_name = '$namaTabel'
      AND column_name = '$namaKolom'
  ";
  $q = mysqli_query($conn, $sql);
  if (!$q) return false;
  $r = mysqli_fetch_assoc($q);
  return ((int)($r['total'] ?? 0) > 0);
}

$invoicePunyaIdPengajuan = kolomAda($conn, "tbl_invoice", "id_pengajuan");
$invoicePunyaIdPenawaran = kolomAda($conn, "tbl_invoice", "id_penawaran");

$kolomNamaFile = "";
$kolomLokasiFile = "";

if (kolomAda($conn, "tbl_invoice", "nama_file_invoice")) $kolomNamaFile = "nama_file_invoice";
else if (kolomAda($conn, "tbl_invoice", "nama_file")) $kolomNamaFile = "nama_file";
else if (kolomAda($conn, "tbl_invoice", "file_invoice")) $kolomNamaFile = "file_invoice";

if (kolomAda($conn, "tbl_invoice", "lokasi_file_invoice")) $kolomLokasiFile = "lokasi_file_invoice";
else if (kolomAda($conn, "tbl_invoice", "lokasi_file")) $kolomLokasiFile = "lokasi_file";
else if (kolomAda($conn, "tbl_invoice", "path_invoice")) $kolomLokasiFile = "path_invoice";

$selectInvoice = "";
$joinInvoice   = "";

if ($invoicePunyaIdPengajuan || $invoicePunyaIdPenawaran) {

  $namaFileSelect = ($kolomNamaFile != "") ? "tbl_invoice.$kolomNamaFile AS nama_file_invoice" : "NULL AS nama_file_invoice";
  $lokFileSelect  = ($kolomLokasiFile != "") ? "tbl_invoice.$kolomLokasiFile AS lokasi_file_invoice" : "NULL AS lokasi_file_invoice";

  $selectInvoice = "
    tbl_invoice.id_invoice,
    tbl_invoice.nomor_invoice,
    tbl_invoice.status_pembayaran,
    tbl_invoice.total_tagihan,
    tbl_invoice.tanggal_invoice,
    $namaFileSelect,
    $lokFileSelect
  ";

  if ($invoicePunyaIdPenawaran) {
    $joinInvoice = "
      LEFT JOIN tbl_invoice
        ON tbl_invoice.id_penawaran = tbl_penawaran.id_penawaran
    ";
  } else {
    $joinInvoice = "
      LEFT JOIN tbl_invoice
        ON tbl_invoice.id_pengajuan = tbl_pengajuan_kalibrasi.id_pengajuan
    ";
  }

} else {

  $selectInvoice = "
    NULL AS id_invoice,
    NULL AS nomor_invoice,
    NULL AS status_pembayaran,
    NULL AS total_tagihan,
    NULL AS tanggal_invoice,
    NULL AS nama_file_invoice,
    NULL AS lokasi_file_invoice
  ";

  $joinInvoice = "";
}

$sql = "
  SELECT
    tbl_pengajuan_kalibrasi.id_pengajuan,
    tbl_pengajuan_kalibrasi.tanggal_pengajuan,
    tbl_pengajuan_kalibrasi.status_pengajuan,
    tbl_pengajuan_kalibrasi.catatan,

    tbl_penawaran.id_penawaran,
    tbl_penawaran.total_biaya,
    tbl_penawaran.status_penawaran,
    tbl_penawaran.tanggal_penawaran,

    $selectInvoice

  FROM tbl_pengajuan_kalibrasi

  LEFT JOIN tbl_penawaran
    ON tbl_penawaran.id_pengajuan = tbl_pengajuan_kalibrasi.id_pengajuan

  $joinInvoice

  WHERE tbl_pengajuan_kalibrasi.id_pelanggan = '$id_pelanggan'
  ORDER BY tbl_pengajuan_kalibrasi.tanggal_pengajuan DESC
";

$data = mysqli_query($conn, $sql);

function badgeStatus($status) {
  $s = strtolower($status ?? '');
  if ($s == 'selesai') return 'bg-success';
  if ($s == 'diproses') return 'bg-warning text-dark';
  if ($s == 'dikirim') return 'bg-primary';
  if ($s == 'ditolak') return 'bg-danger';
  return 'bg-secondary';
}

function statusInvoiceDariData($id_invoice, $status_pembayaran) {
  $id = (int)($id_invoice ?? 0);
  if ($id <= 0) return ['Belum tersedia', 'bg-secondary'];

  $s = strtolower(trim((string)($status_pembayaran ?? '')));
  if ($s == 'sudah dibayar' || $s == 'dibayar' || $s == 'paid') return ['Sudah dibayar', 'bg-success'];
  return ['Belum dibayar', 'bg-warning text-dark'];
}
?>

<?php include 'komponen/header.php'; ?>
<?php include 'komponen/sidebar.php'; ?>
<?php include 'komponen/navbar.php'; ?>

<div class="content-wrapper">
  <div class="container-xxl flex-grow-1 container-p-y">

    <div class="d-flex justify-content-between align-items-center mb-3">
      <div>
        <h4 class="fw-bold mb-1">Invoice</h4>
        <p class="text-muted mb-0">Daftar invoice berdasarkan pengajuan yang kamu buat.</p>
      </div>
      <a href="index.php" class="btn btn-outline-primary">
        <i class="bx bx-arrow-back me-1"></i> Kembali
      </a>
    </div>

    <div class="alert alert-info">
      Invoice akan <b>tersedia</b> setelah penawaran <b>diterima</b> (invoice dibuat otomatis oleh sistem).
    </div>

    <?php if (!$data || mysqli_num_rows($data) == 0): ?>
      <div class="card border-0 shadow-sm">
        <div class="card-body text-center py-5">
          <div class="mb-2">
            <i class="bx bx-receipt fs-1 text-muted"></i>
          </div>
          <h5 class="mb-1">Belum ada invoice</h5>
          <p class="text-muted mb-3">Mulai dari membuat pengajuan kalibrasi dulu.</p>
          <a href="ajukan_kalibrasi.php" class="btn btn-primary">
            <i class="bx bx-plus me-1"></i> Ajukan Kalibrasi
          </a>
        </div>
      </div>
    <?php else: ?>

      <div class="card border-0 shadow-sm">
        <div class="card-body">
          <div class="table-responsive">
            <table class="table align-middle">
              <thead class="table-light">
                <tr>
                  <th>No</th>
                  <th>ID Pengajuan</th>
                  <th>Tanggal Pengajuan</th>
                  <th>Status Pengajuan</th>
                  <th>Total Biaya</th>
                  <th>Status Invoice</th>
                  <th>Aksi</th>
                </tr>
              </thead>
              <tbody>
                <?php $no = 1; ?>
                <?php while ($row = mysqli_fetch_assoc($data)): ?>
                  <?php
                    $total = (float)($row['total_biaya'] ?? 0);

                    $idInvoice = (int)($row['id_invoice'] ?? 0);
                    $statusBayar = $row['status_pembayaran'] ?? '';

                    $infoInv = statusInvoiceDariData($idInvoice, $statusBayar);
                    $labelInv = $infoInv[0];
                    $badgeInv = $infoInv[1];

                    $bisaUnduh = ($idInvoice > 0);
                  ?>
                  <tr>
                    <td><?= $no++; ?></td>
                    <td><span class="fw-semibold">#<?= $row['id_pengajuan']; ?></span></td>
                    <td><?= $row['tanggal_pengajuan']; ?></td>
                    <td>
                      <span class="badge <?= badgeStatus($row['status_pengajuan']); ?>">
                        <?= $row['status_pengajuan']; ?>
                      </span>
                    </td>
                    <td>
                      <?php if ($total > 0): ?>
                        Rp <?= number_format($total, 0, ',', '.'); ?>
                      <?php else: ?>
                        <span class="text-muted">-</span>
                      <?php endif; ?>
                    </td>
                    <td>
                      <span class="badge <?= $badgeInv; ?>"><?= $labelInv; ?></span>
                    </td>
                    <td>
                      <div class="d-flex gap-2 flex-wrap">
                        <a class="btn btn-sm btn-primary"
                           href="detail_pengajuan.php?id=<?= $row['id_pengajuan']; ?>">
                          Detail
                        </a>

                        <?php if ($bisaUnduh): ?>
                          <a class="btn btn-sm btn-outline-success"
                             href="unduh_invoice.php?id_invoice=<?= $idInvoice; ?>">
                            Unduh Invoice
                          </a>
                        <?php else: ?>
                          <button type="button" class="btn btn-sm btn-outline-secondary" disabled>
                            Unduh Invoice
                          </button>
                        <?php endif; ?>

                      </div>
                    </td>
                  </tr>
                <?php endwhile; ?>
              </tbody>
            </table>
          </div>

        </div>
      </div>

    <?php endif; ?>

  </div>
</div>

<?php include 'komponen/footer.php'; ?>
