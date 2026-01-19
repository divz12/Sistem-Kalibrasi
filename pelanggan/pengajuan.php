<?php
session_start();
include "../koneksi.php";

// proteksi login
if (!isset($_SESSION['id_user']) || ($_SESSION['role'] ?? '') !== 'pelanggan') {
  header("Location: ../login.php");
  exit();
}

$id_user = (int)($_SESSION['id_user'] ?? 0);

$sqlPelanggan = "
  SELECT 
    tbl_pelanggan.id_pelanggan, 
    tbl_pelanggan.alamat, 
    tbl_pelanggan.no_hp, 
    tbl_users.nama, 
    tbl_users.email
  FROM tbl_pelanggan
  JOIN tbl_users 
    ON tbl_users.id_user = tbl_pelanggan.id_user
  WHERE tbl_pelanggan.id_user = '$id_user'
  LIMIT 1
";

$hasil = mysqli_query($conn, $sqlPelanggan);
$data  = mysqli_fetch_assoc($hasil);

$id_pelanggan = (int)($data['id_pelanggan'] ?? 0);

if ($id_pelanggan <= 0) {
  header("Location: profil.php");
  exit();
}

$nama_user  = $data['nama'] ?? '';
$email_user = $data['email'] ?? '';
$no_hp      = $data['no_hp'] ?? '';
$alamat     = $data['alamat'] ?? '';

include 'komponen/header.php';
include 'komponen/sidebar.php';
include 'komponen/navbar.php';
?>

<div class="content-wrapper">
  <div class="container-xxl flex-grow-1 container-p-y">

    <div class="d-flex justify-content-between align-items-center mb-3">
      <div>
        <h4 class="fw-bold mb-1">Ajukan Kalibrasi</h4>
        <p class="text-muted mb-0">Isi data alat yang akan dikalibrasi.</p>
      </div>
      <a href="index.php" class="btn btn-outline-primary">
        <i class="bx bx-arrow-back me-1"></i> Kembali
      </a>
    </div>

    <?php if (isset($_GET['msg']) && $_GET['msg'] === 'ok'): ?>
      <div class="alert alert-success">Pengajuan berhasil dikirim ✅</div>
    <?php elseif (isset($_GET['msg']) && $_GET['msg'] === 'err'): ?>
      <div class="alert alert-danger">Gagal menyimpan pengajuan. Coba lagi.</div>
    <?php endif; ?>

    <form action="proses-pengajuan.php" method="post" class="card shadow-sm border-0">
      <div class="card-body">

        <input type="hidden" name="id_pelanggan" value="<?= $id_pelanggan; ?>">

        <h5 class="fw-bold mb-5">Data Pelanggan</h5>

        <div class="row g-3 mb-3">
          <div class="col-md-6">
            <label class="form-label">Nama</label>
            <input type="text" class="form-control" value="<?= $nama_user; ?>" disabled>
          </div>
          <div class="col-md-6">
            <label class="form-label">Email</label>
            <input type="text" class="form-control" value="<?= $email_user; ?>" disabled>
          </div>
          <div class="col-md-6">
            <label class="form-label">No WhatsApp</label>
            <input type="text" class="form-control" value="<?= $no_hp; ?>" disabled>
          </div>
          <div class="col-md-6">
            <label class="form-label">Alamat</label>
            <input type="text" class="form-control" value="<?= $alamat; ?>" disabled>
          </div>
        </div>

        <hr class="my-4">

        <h5 class="fw-bold mb-2">Catatan Pengajuan</h5>
        <div class="mb-3">
          <label class="form-label">Catatan (opsional)</label>
          <textarea name="catatan" class="form-control" rows="3" placeholder="Tambah catatan..."></textarea>
        </div>

        <hr class="my-4">

        <h5 class="fw-bold mb-2">Data Alat</h5>
        <p class="text-muted small mb-3">Isi minimal 1 alat.</p>

        <?php for ($i = 1; $i <= 4; $i++): ?>
          <div class="border rounded-3 p-3 mb-3 bg-white">
            <div class="d-flex justify-content-between align-items-center mb-2">
              <span class="fw-semibold">Alat #<?= $i; ?></span>
              <?php if ($i === 1): ?>
                <span class="badge bg-primary">Wajib</span>
              <?php else: ?>
                <span class="badge bg-light text-muted border">Opsional</span>
              <?php endif; ?>
            </div>

            <div class="row g-3">
              <div class="col-md-4">
                <label class="form-label">Nama Alat <?= $i === 1 ? '<span class="text-danger">*</span>' : '' ?></label>
                <input type="text" name="nama_alat[]" class="form-control"
                       placeholder="Contoh: Timbangan Digital" <?= $i === 1 ? 'required' : '' ?>>
              </div>

              <div class="col-md-3">
                <label class="form-label">Merk / Tipe</label>
                <input type="text" name="merk_tipe[]" class="form-control" placeholder="Contoh: CAS ER Jr-15">
              </div>

              <div class="col-md-3">
                <label class="form-label">Kapasitas</label>
                <input type="text" name="kapasitas[]" class="form-control" placeholder="Contoh: 0 - 30 kg">
              </div>

              <div class="col-md-2">
                <label class="form-label">Jumlah <?= $i === 1 ? '<span class="text-danger">*</span>' : '' ?></label>
                <input type="number" name="jumlah_unit[]" class="form-control" min="1" value="1" <?= $i === 1 ? 'required' : '' ?>>
              </div>

              <div class="col-md-6">
                <label class="form-label">Parameter <?= $i === 1 ? '<span class="text-danger">*</span>' : '' ?></label>
                <input type="text" name="parameter[]" class="form-control"
                       placeholder="Contoh: Berat / Suhu / Tekanan" <?= $i === 1 ? 'required' : '' ?>>
              </div>

              <div class="col-md-6">
                <label class="form-label">Titik Ukur <?= $i === 1 ? '<span class="text-danger">*</span>' : '' ?></label>
                <input type="text" name="titik_ukur[]" class="form-control"
                       placeholder="Contoh: 1kg, 5kg, 10kg (pisahkan koma)" <?= $i === 1 ? 'required' : '' ?>>
              </div>

              <div class="col-12">
                <label class="form-label">Keterangan (opsional)</label>
                <input type="text" name="keterangan[]" class="form-control" placeholder="Masukkan Keterangan">
              </div>
            </div>
          </div>
        <?php endfor; ?>

        <div class="d-flex gap-2 mt-3">
          <button type="submit" class="btn btn-primary">
            <i class="bx bx-send me-1"></i> Kirim Pengajuan
          </button>
          <a href="index.php" class="btn btn-outline-primary">Batal</a>
        </div>

      </div>
    </form>

  </div>
</div>

<?php include 'komponen/footer.php'; ?>
