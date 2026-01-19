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

/* daftar pengajuan untuk dipilih */
$sqlPengajuan = "
  SELECT
    tbl_pengajuan_kalibrasi.id_pengajuan,
    tbl_pengajuan_kalibrasi.tanggal_pengajuan,
    tbl_pengajuan_kalibrasi.status_pengajuan,

    tbl_users.nama,
    tbl_users.email

  FROM tbl_pengajuan_kalibrasi
  LEFT JOIN tbl_pelanggan
    ON tbl_pelanggan.id_pelanggan = tbl_pengajuan_kalibrasi.id_pelanggan
  LEFT JOIN tbl_users
    ON tbl_users.id_user = tbl_pelanggan.id_user
  ORDER BY tbl_pengajuan_kalibrasi.id_pengajuan DESC
";
$hasilPengajuan = mysqli_query($conn, $sqlPengajuan);
if (!$hasilPengajuan) {
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
        <h4 class="fw-bold mb-1">Tambah Penawaran</h4>
        <p class="text-muted mb-0">Buat penawaran untuk pengajuan pelanggan.</p>
      </div>
      <a href="penawaran.php" class="btn btn-outline-primary">
        <i class="bx bx-arrow-back me-1"></i> Kembali
      </a>
    </div>

    <form action="proses_tambah.php" method="post" class="card">
      <div class="card-body">

        <div class="mb-3">
          <label class="form-label">Pilih Pengajuan</label>
          <select name="id_pengajuan" class="form-control" required>
            <option value="">-- pilih pengajuan --</option>
            <?php while ($p = mysqli_fetch_assoc($hasilPengajuan)): ?>
              <option value="<?= $p["id_pengajuan"]; ?>">
                #<?= $p["id_pengajuan"]; ?> - <?= $p["nama"]; ?> (<?= $p["status_pengajuan"]; ?>)
              </option>
            <?php endwhile; ?>
          </select>
        </div>

        <div class="row g-3">
          <div class="col-md-6">
            <label class="form-label">Tanggal Penawaran</label>
            <input type="date" name="tanggal_penawaran" class="form-control" required>
          </div>

          <div class="col-md-6">
            <label class="form-label">Total Biaya</label>
            <input type="number" name="total_biaya" class="form-control" min="0" value="0" required>
          </div>
        </div>

        <div class="mt-3">
          <label class="form-label">Rincian Penawaran</label>
          <textarea name="rincian" class="form-control" rows="4" placeholder="Tulis rincian biaya / detail pekerjaan" required></textarea>
        </div>

        <div class="mt-3">
          <label class="form-label">Status Penawaran</label>
          <select name="status_penawaran" class="form-control" required>
            <option value="dikirim">dikirim</option>
            <option value="negosiasi">negosiasi</option>
            <option value="diterima">diterima</option>
            <option value="ditolak">ditolak</option>
          </select>
        </div>

        <hr class="my-4">

        <button class="btn btn-primary" type="submit">
          <i class="bx bx-save me-1"></i> Simpan
        </button>
        <a href="penawaran.php" class="btn btn-outline-primary">Batal</a>

      </div>
    </form>

  </div>
</div>

<?php include "../komponen/footer.php"; ?>
