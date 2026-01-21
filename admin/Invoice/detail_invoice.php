<?php
session_start();
include "../../koneksi.php";

$role = $_SESSION["role"] ?? "";

if (!isset($_SESSION["id_user"])) {
  header("Location: ../../login.php");
  exit();
}

if ($role != "admin" && $role != "cs") {
  header("Location: ../../login.php");
  exit();
}

$idInvoice = (int)($_GET["id"] ?? 0);
if ($idInvoice <= 0) {
  header("Location: invoice.php");
  exit();
}

$sql = "
  SELECT
    tbl_invoice.*,
    tbl_penawaran.id_pengajuan,
    tbl_penawaran.total_biaya,
    tbl_penawaran.status_penawaran,

    tbl_pengajuan_kalibrasi.id_pelanggan,
    tbl_users.nama,
    tbl_users.email

  FROM tbl_invoice
  LEFT JOIN tbl_penawaran
    ON tbl_penawaran.id_penawaran = tbl_invoice.id_penawaran
  LEFT JOIN tbl_pengajuan_kalibrasi
    ON tbl_pengajuan_kalibrasi.id_pengajuan = tbl_penawaran.id_pengajuan
  LEFT JOIN tbl_pelanggan
    ON tbl_pelanggan.id_pelanggan = tbl_pengajuan_kalibrasi.id_pelanggan
  LEFT JOIN tbl_users
    ON tbl_users.id_user = tbl_pelanggan.id_user
  WHERE tbl_invoice.id_invoice = '$idInvoice'
  LIMIT 1
";
$hasil = mysqli_query($conn, $sql);
$data = mysqli_fetch_assoc($hasil);

if (!$data) {
  header("Location: invoice.php");
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
        <h4 class="fw-bold mb-1">Detail Invoice</h4>
        <p class="text-muted mb-0">Invoice ID: <?= $data["id_invoice"]; ?></p>
      </div>
      <a href="invoice.php" class="btn btn-outline-primary">
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
            <div class="text-muted small">Penawaran</div>
            <div>#<?= $data["id_penawaran"]; ?> | Pengajuan #<?= $data["id_pengajuan"]; ?></div>
            <div class="text-muted small">Status Penawaran: <?= $data["status_penawaran"] ?? "-"; ?></div>
          </div>
        </div>

        <hr>

        <div class="row g-3">
          <div class="col-md-4">
            <div class="text-muted small">Nomor Invoice</div>
            <div class="fw-semibold"><?= $data["nomor_invoice"]; ?></div>
          </div>

          <div class="col-md-4">
            <div class="text-muted small">Tanggal Invoice</div>
            <div><?= $data["tanggal_invoice"]; ?></div>
          </div>

          <div class="col-md-4">
            <div class="text-muted small">Jatuh Tempo</div>
            <div><?= $data["tanggal_jatuh_tempo"] ?: "-"; ?></div>
          </div>
        </div>

        <div class="row g-3 mt-1">
          <div class="col-md-4">
            <div class="text-muted small">Total Tagihan</div>
            <div class="fw-semibold">Rp <?= number_format($data["total_tagihan"], 0, ',', '.'); ?></div>
          </div>

          <div class="col-md-4">
            <div class="text-muted small">Status Pembayaran</div>
            <div><?= $data["status_pembayaran"]; ?></div>
          </div>

          <div class="col-md-4">
            <div class="text-muted small">File</div>
            <?php if (($data["lokasi_file_invoice"] ?? "") != ""): ?>
              <a class="btn btn-sm btn-success" href="../../<?= $data["lokasi_file_invoice"]; ?>" target="_blank">
                Unduh Invoice
              </a>
            <?php else: ?>
              <div>-</div>
            <?php endif; ?>
          </div>
        </div>

        <div class="mt-3">
          <div class="text-muted small">Keterangan</div>
          <div><?= $data["keterangan_invoice"] ?: "-"; ?></div>
        </div>

      </div>
    </div>

  </div>
</div>

<?php include "../komponen/footer.php"; ?>
