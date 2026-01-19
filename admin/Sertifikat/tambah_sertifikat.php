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

/* ambil pengajuan yang selesai */
$sqlPengajuanSelesai = "
  SELECT
    tbl_pengajuan_kalibrasi.id_pengajuan,
    tbl_pengajuan_kalibrasi.tanggal_pengajuan,
    tbl_pengajuan_kalibrasi.status_pengajuan,

    tbl_pelanggan.id_pelanggan,
    tbl_users.nama,
    tbl_users.email

  FROM tbl_pengajuan_kalibrasi
  LEFT JOIN tbl_pelanggan
    ON tbl_pelanggan.id_pelanggan = tbl_pengajuan_kalibrasi.id_pelanggan
  LEFT JOIN tbl_users
    ON tbl_users.id_user = tbl_pelanggan.id_user
  WHERE tbl_pengajuan_kalibrasi.status_pengajuan = 'selesai'
  ORDER BY tbl_pengajuan_kalibrasi.id_pengajuan DESC
";
$hasilPengajuan = mysqli_query($conn, $sqlPengajuanSelesai);
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
        <h4 class="fw-bold mb-1">Tambah Sertifikat</h4>
        <p class="text-muted mb-0">Pilih pengajuan yang sudah selesai lalu unggah file sertifikat.</p>
      </div>
      <a href="sertifikat.php" class="btn btn-outline-primary">
        <i class="bx bx-arrow-back me-1"></i> Kembali
      </a>
    </div>

    <div class="card">
      <div class="card-body">

        <form action="proses_tambah.php" method="post" enctype="multipart/form-data">

          <div class="mb-3">
            <label class="form-label">Pilih Pengajuan (status selesai)</label>
            <select name="id_pengajuan" class="form-control" required>
              <option value="">-- pilih pengajuan --</option>
              <?php while ($p = mysqli_fetch_assoc($hasilPengajuan)): ?>
                <option value="<?= $p["id_pengajuan"]; ?>">
                  #<?= $p["id_pengajuan"]; ?> - <?= $p["nama"]; ?> (<?= $p["email"]; ?>)
                </option>
              <?php endwhile; ?>
            </select>
          </div>

          <div class="mb-3">
            <label class="form-label">Nomor Sertifikat</label>
            <input type="text" name="nomor_sertifikat" class="form-control" placeholder="Contoh: CERT-001/2026" required>
          </div>

          <div class="mb-3">
            <label class="form-label">Tanggal Terbit</label>
            <input type="date" name="tanggal_terbit" class="form-control" required>
          </div>

          <div class="mb-3">
            <label class="form-label">Keterangan (opsional)</label>
            <textarea name="keterangan_sertifikat" class="form-control" rows="3" placeholder="Catatan singkat..."></textarea>
          </div>

          <div class="mb-3">
            <label class="form-label">File Sertifikat</label>
            <input type="file" name="file_sertifikat" class="form-control" required>
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
