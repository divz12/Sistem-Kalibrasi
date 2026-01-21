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
  SELECT *
  FROM tbl_invoice
  WHERE id_invoice = '$idInvoice'
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

    <h4 class="fw-bold mb-3">Edit Invoice</h4>

    <div class="card">
      <div class="card-body">

        <form action="proses_edit_invoice.php" method="post" enctype="multipart/form-data">
          <input type="hidden" name="id_invoice" value="<?= $data["id_invoice"]; ?>">

          <div class="row g-3">

            <div class="col-md-6">
              <label>Nomor Invoice</label>
              <input type="text" name="nomor_invoice" class="form-control"
                     value="<?= $data["nomor_invoice"]; ?>" required>
            </div>

            <div class="col-md-6">
              <label>Tanggal Invoice</label>
              <input type="date" name="tanggal_invoice" class="form-control"
                     value="<?= substr($data["tanggal_invoice"],0,10); ?>" required>
            </div>

            <div class="col-md-6">
              <label>Tanggal Jatuh Tempo</label>
              <input type="date" name="tanggal_jatuh_tempo" class="form-control"
                     value="<?= substr($data["tanggal_jatuh_tempo"],0,10); ?>" required>
            </div>

            <div class="col-md-6">
              <label>Total Tagihan</label>
              <input type="number" name="total_tagihan" class="form-control"
                     value="<?= $data["total_tagihan"]; ?>" required>
            </div>

            <div class="col-md-6">
              <label>Status Pembayaran</label>
              <select name="status_pembayaran" class="form-control">
                <option value="belum dibayar" <?= ($data["status_pembayaran"]=="belum dibayar")?"selected":""; ?>>Belum Dibayar</option>
                <option value="sudah dibayar" <?= ($data["status_pembayaran"]=="sudah dibayar")?"selected":""; ?>>Sudah Dibayar</option>
                <option value="jatuh tempo" <?= ($data["status_pembayaran"]=="jatuh tempo")?"selected":""; ?>>Jatuh Tempo</option>
              </select>
            </div>

            <div class="col-md-6">
              <label>Upload File Invoice (PDF)</label>
              <input type="file" name="file_invoice" class="form-control">
              <small class="text-muted">Kosongkan jika tidak ingin ganti file</small>
            </div>

            <div class="col-12">
              <label>Keterangan</label>
              <textarea name="keterangan_invoice" class="form-control" rows="3"><?= $data["keterangan_invoice"]; ?></textarea>
            </div>

          </div>

          <div class="mt-4">
            <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
            <a href="invoice.php" class="btn btn-secondary">Kembali</a>
          </div>

        </form>

      </div>
    </div>

  </div>
</div>

<?php include "../komponen/footer.php"; ?>
