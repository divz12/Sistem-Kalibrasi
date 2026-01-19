<?php
session_start();
include "../../koneksi.php";

$role = $_SESSION["role"] ?? "";

if (!isset($_SESSION["id_user"])) {
  header("Location: ../../login.php");
  exit();
}

if ($role != "admin" && $role != "cs" && $role != "admin_cs") {
  header("Location: ../../login.php");
  exit();
}

/* ambil penawaran yang sudah diterima */
$sqlPenawaran = "
  SELECT
    tbl_penawaran.id_penawaran,
    tbl_penawaran.id_pengajuan,
    tbl_penawaran.total_biaya,
    tbl_penawaran.status_penawaran,

    tbl_pengajuan_kalibrasi.id_pelanggan,
    tbl_users.nama,
    tbl_users.email

  FROM tbl_penawaran
  LEFT JOIN tbl_pengajuan_kalibrasi
    ON tbl_pengajuan_kalibrasi.id_pengajuan = tbl_penawaran.id_pengajuan
  LEFT JOIN tbl_pelanggan
    ON tbl_pelanggan.id_pelanggan = tbl_pengajuan_kalibrasi.id_pelanggan
  LEFT JOIN tbl_users
    ON tbl_users.id_user = tbl_pelanggan.id_user
  WHERE tbl_penawaran.status_penawaran = 'diterima'
  ORDER BY tbl_penawaran.id_penawaran DESC
";
$hasilPenawaran = mysqli_query($conn, $sqlPenawaran);
if (!$hasilPenawaran) {
  die("Query gagal: " . mysqli_error($conn));
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
        <h4 class="fw-bold mb-1">Tambah Invoice</h4>
        <p class="text-muted mb-0">Pilih penawaran yang sudah diterima, lalu unggah file invoice.</p>
      </div>
      <a href="invoice.php" class="btn btn-outline-primary">
        <i class="bx bx-arrow-back me-1"></i> Kembali
      </a>
    </div>

    <div class="card">
      <div class="card-body">

        <form action="proses_tambah.php" method="post" enctype="multipart/form-data">

          <div class="mb-3">
            <label class="form-label">Pilih Penawaran (status diterima)</label>
            <select name="id_penawaran" class="form-control" required>
              <option value="">-- pilih penawaran --</option>
              <?php while ($p = mysqli_fetch_assoc($hasilPenawaran)): ?>
                <option value="<?= $p["id_penawaran"]; ?>">
                  #<?= $p["id_penawaran"]; ?> | Pengajuan #<?= $p["id_pengajuan"]; ?> | <?= $p["nama"]; ?> | Rp <?= number_format($p["total_biaya"], 0, ',', '.'); ?>
                </option>
              <?php endwhile; ?>
            </select>
          </div>

          <div class="mb-3">
            <label class="form-label">Nomor Invoice</label>
            <input type="text" name="nomor_invoice" class="form-control" placeholder="Contoh: INV-001/2026" required>
          </div>

          <div class="row">
            <div class="col-md-6 mb-3">
              <label class="form-label">Tanggal Invoice</label>
              <input type="date" name="tanggal_invoice" class="form-control" required>
            </div>

            <div class="col-md-6 mb-3">
              <label class="form-label">Tanggal Jatuh Tempo (opsional)</label>
              <input type="date" name="tanggal_jatuh_tempo" class="form-control">
            </div>
          </div>

          <div class="mb-3">
            <label class="form-label">Total Tagihan</label>
            <input type="number" name="total_tagihan" class="form-control" placeholder="Contoh: 1500000" required>
          </div>

          <div class="mb-3">
            <label class="form-label">Status Pembayaran</label>
            <select name="status_pembayaran" class="form-control" required>
              <option value="belum dibayar">Belum Dibayar</option>
              <option value="sudah dibayar">Sudah Dibayar</option>
              <option value="jatuh tempo">Jatuh Tempo</option>
            </select>
          </div>

          <div class="mb-3">
            <label class="form-label">Keterangan (opsional)</label>
            <textarea name="keterangan_invoice" class="form-control" rows="3" placeholder="Catatan singkat..."></textarea>
          </div>

          <div class="mb-3">
            <label class="form-label">File Invoice</label>
            <input type="file" name="file_invoice" class="form-control" required>
            <small class="text-muted">Boleh PDF/JPG/PNG.</small>
          </div>

          <button type="submit" class="btn btn-primary">
            <i class="bx bx-save me-1"></i> Simpan
          </button>

        </form>

      </div>
    </div>

  </div>
</div>

<?php include "../komponen/footer.php"; ?>
