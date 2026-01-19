<?php
session_start();
include "../koneksi.php";

// proteksi login
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

$sql = "
  SELECT
    tbl_pengajuan_kalibrasi.id_pengajuan,
    tbl_pengajuan_kalibrasi.tanggal_pengajuan,
    tbl_pengajuan_kalibrasi.status_pengajuan,
    tbl_pengajuan_kalibrasi.catatan,

    tbl_penawaran.id_penawaran,
    tbl_penawaran.total_biaya,
    tbl_penawaran.status_penawaran

  FROM tbl_pengajuan_kalibrasi
  LEFT JOIN tbl_penawaran 
    ON tbl_penawaran.id_pengajuan = tbl_pengajuan_kalibrasi.id_pengajuan

  WHERE tbl_pengajuan_kalibrasi.id_pelanggan = '$id_pelanggan'
    AND (
      tbl_pengajuan_kalibrasi.status_pengajuan = 'selesai'
      OR tbl_pengajuan_kalibrasi.status_pengajuan = 'Selesai'
      OR tbl_pengajuan_kalibrasi.status_pengajuan = 'SELESAI'
    )
  ORDER BY tbl_pengajuan_kalibrasi.tanggal_pengajuan DESC
";
$data = mysqli_query($conn, $sql);

function badgeStatus($status) {
  if ($status == 'selesai' || $status == 'Selesai' || $status == 'SELESAI') return 'bg-success';
  if ($status == 'diproses' || $status == 'Diproses' || $status == 'DIPROSES') return 'bg-warning text-dark';
  if ($status == 'dikirim' || $status == 'Dikirim' || $status == 'DIKIRIM') return 'bg-primary';
  if ($status == 'ditolak' || $status == 'Ditolak' || $status == 'DITOLAK') return 'bg-danger';
  return 'bg-secondary';
}
?>

<?php include 'komponen/header.php'; ?>
<?php include 'komponen/sidebar.php'; ?>
<?php include 'komponen/navbar.php'; ?>

<div class="content-wrapper">
  <div class="container-xxl flex-grow-1 container-p-y">

    <div class="d-flex justify-content-between align-items-center mb-3">
      <div>
        <h4 class="fw-bold mb-1">Sertifikat</h4>
        <p class="text-muted mb-0">Daftar sertifikat untuk pengajuan yang sudah selesai.</p>
      </div>
      <a href="index.php" class="btn btn-outline-primary">
        <i class="bx bx-arrow-back me-1"></i> Kembali
      </a>
    </div>

    <div class="alert alert-info">
      Sertifikat akan muncul di sini jika status pengajuan sudah <b>selesai</b>.
    </div>

    <?php if (!$data || mysqli_num_rows($data) == 0): ?>
      <div class="card border-0 shadow-sm">
        <div class="card-body text-center py-5">
          <div class="mb-2">
            <i class="bx bx-file fs-1 text-muted"></i>
          </div>
          <h5 class="mb-1">Belum ada sertifikat</h5>
          <p class="text-muted mb-3">Sertifikat akan tersedia setelah proses pengajuan selesai.</p>
          <a href="pengajuan.php" class="btn btn-primary">
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
                  <th>Tanggal</th>
                  <th>Status</th>
                  <th>Info</th>
                  <th>Aksi</th>
                </tr>
              </thead>
              <tbody>
                <?php $no = 1; ?>
                <?php while ($row = mysqli_fetch_assoc($data)): ?>
                  <tr>
                    <td><?= $no++; ?></td>
                    <td><span class="fw-semibold">#<?= $row['id_pengajuan']; ?></span></td>
                    <td><?= $row['tanggal_pengajuan']; ?></td>
                    <td>
                      <span class="badge <?= badgeStatus($row['status_pengajuan']); ?>">
                        <?= $row['status_pengajuan']; ?>
                      </span>
                    </td>
                    <td class="text-muted small">
                      <?php if (!empty($row['id_penawaran'])): ?>
                        Penawaran: <?= $row['status_penawaran']; ?>
                        <?php if (!empty($row['total_biaya'])): ?>
                          <br>Total: Rp <?= number_format($row['total_biaya'], 0, ',', '.'); ?>
                        <?php endif; ?>
                      <?php else: ?>
                        Penawaran: -
                      <?php endif; ?>
                    </td>
                    <td>
                      <div class="d-flex gap-2 flex-wrap">
                        <a class="btn btn-sm btn-primary"
                           href="detail_pengajuan.php?id=<?= $row['id_pengajuan']; ?>">
                          Detail
                        </a>

                        <button type="button" class="btn btn-sm btn-outline-success" disabled
                          title="Aktifkan jika sudah ada file sertifikat di database">
                          Unduh Sertifikat
                        </button>
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
