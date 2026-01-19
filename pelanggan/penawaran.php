<?php
session_start();
include "../koneksi.php";

// proteksi login
if (!isset($_SESSION['id_user']) || ($_SESSION['role'] ?? '') !== 'pelanggan') {
  header("Location: ../login.php");
  exit();
}

$id_user = (int)($_SESSION['id_user'] ?? 0);

$q1 = mysqli_query($conn, "SELECT id_pelanggan FROM tbl_pelanggan WHERE id_user='$id_user' LIMIT 1");
$dataPelanggan = mysqli_fetch_assoc($q1);
$id_pelanggan = (int)($dataPelanggan['id_pelanggan'] ?? 0);

if ($id_pelanggan <= 0) {
  header("Location: profil.php");
  exit();
}

$sql = "
  SELECT 
    tbl_penawaran.id_penawaran,
    tbl_penawaran.id_pengajuan,
    tbl_penawaran.tanggal_penawaran,
    tbl_penawaran.total_biaya,
    tbl_penawaran.rincian,
    tbl_penawaran.status_penawaran,

    tbl_pengajuan_kalibrasi.tanggal_pengajuan,
    tbl_pengajuan_kalibrasi.status_pengajuan,
    tbl_pengajuan_kalibrasi.catatan

  FROM tbl_penawaran
  JOIN tbl_pengajuan_kalibrasi 
    ON tbl_pengajuan_kalibrasi.id_pengajuan = tbl_penawaran.id_pengajuan

  WHERE tbl_pengajuan_kalibrasi.id_pelanggan = '$id_pelanggan'
  ORDER BY tbl_penawaran.tanggal_penawaran DESC
";

$penawaran = mysqli_query($conn, $sql);
?>

<?php include 'komponen/header.php'; ?>
<?php include 'komponen/sidebar.php'; ?>
<?php include 'komponen/navbar.php'; ?>

<div class="content-wrapper">
  <div class="container-xxl flex-grow-1 container-p-y">

    <div class="d-flex justify-content-between align-items-center mb-3">
      <div>
        <h4 class="fw-bold mb-1">Penawaran</h4>
        <p class="text-muted mb-0">Daftar penawaran yang sudah dibuat untuk pengajuan kamu.</p>
      </div>
      <a href="index.php" class="btn btn-outline-primary">
        <i class="bx bx-arrow-back me-1"></i> Kembali
      </a>
    </div>

    <?php if (isset($_GET['msg']) && $_GET['msg'] == 'ok'): ?>
      <div class="alert alert-success">Status penawaran berhasil diperbarui ✅</div>
    <?php elseif (isset($_GET['msg']) && $_GET['msg'] == 'err'): ?>
      <div class="alert alert-danger">Gagal memperbarui status penawaran ❌</div>
    <?php endif; ?>

    <div class="card shadow-sm border-0">
      <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Daftar Penawaran</h5>
        <a href="riwayat_pengajuan.php" class="btn btn-sm btn-outline-primary">
          Lihat Riwayat Pengajuan
        </a>
      </div>

      <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
          <thead class="table-light">
            <tr>
              <th>No</th>
              <th>ID Pengajuan</th>
              <th>Tanggal Penawaran</th>
              <th>Total Biaya</th>
              <th>Status</th>
              <th style="width:220px;">Aksi</th>
            </tr>
          </thead>
          <tbody>

            <?php if (!$penawaran || mysqli_num_rows($penawaran) == 0): ?>
              <tr>
                <td colspan="6" class="text-center text-muted py-4">
                  Belum ada penawaran. Tunggu admin membuat penawaran dari pengajuan kamu.
                </td>
              </tr>
            <?php else: ?>

              <?php $no = 1; while ($row = mysqli_fetch_assoc($penawaran)): ?>

                <?php
                  $badge = "bg-secondary";
                  if ($row['status_penawaran'] == 'dikirim') $badge = "bg-primary";
                  if ($row['status_penawaran'] == 'diterima') $badge = "bg-success";
                  if ($row['status_penawaran'] == 'ditolak') $badge = "bg-danger";
                  if ($row['status_penawaran'] == 'negosiasi') $badge = "bg-warning text-dark";
                ?>

                <tr>
                  <td><?= $no++; ?></td>
                  <td>#<?= $row['id_pengajuan']; ?></td>
                  <td><?= $row['tanggal_penawaran']; ?></td>
                  <td>
                    <?php if ($row['total_biaya'] != null): ?>
                      Rp <?= number_format($row['total_biaya'], 0, ',', '.'); ?>
                    <?php else: ?>
                      -
                    <?php endif; ?>
                  </td>
                  <td>
                    <span class="badge <?= $badge; ?>">
                      <?= $row['status_penawaran']; ?>
                    </span>
                  </td>
                  <td>
                    <a class="btn btn-sm btn-outline-primary"
                       href="detail_penawaran.php?id=<?= $row['id_penawaran']; ?>">
                      Detail
                    </a>

                    <?php if ($row['status_penawaran'] == 'dikirim' || $row['status_penawaran'] == 'negosiasi'): ?>
                      <a class="btn btn-sm btn-success"
                         href="aksi_penawaran.php?id=<?= $row['id_penawaran']; ?>&aksi=diterima"
                         onclick="return confirm('Yakin terima penawaran ini?');">
                        Terima
                      </a>
                      <a class="btn btn-sm btn-danger"
                         href="aksi_penawaran.php?id=<?= $row['id_penawaran']; ?>&aksi=ditolak"
                         onclick="return confirm('Yakin tolak penawaran ini?');">
                        Tolak
                      </a>
                    <?php endif; ?>
                  </td>
                </tr>

              <?php endwhile; ?>

            <?php endif; ?>

          </tbody>
        </table>
      </div>
    </div>

  </div>
</div>

<?php include 'komponen/footer.php'; ?>
