<?php
session_start();
include "../koneksi.php";

// proteksi login pelanggan
if (!isset($_SESSION['id_user']) || ($_SESSION['role'] ?? '') !== 'pelanggan') {
  header("Location: ../login.php");
  exit();
}

$id_user = (int)($_SESSION['id_user'] ?? 0);

$id_pelanggan = 0;
$sqlPelanggan = "SELECT id_pelanggan FROM tbl_pelanggan WHERE id_user='$id_user' LIMIT 1";
$queryPelanggan = mysqli_query($conn, $sqlPelanggan);

if ($queryPelanggan) {
  $rowPel = mysqli_fetch_assoc($queryPelanggan);
  $id_pelanggan = (int)($rowPel['id_pelanggan'] ?? 0);
}

if ($id_pelanggan <= 0) {
  header("Location: profil.php");
  exit();
}

function badgeStatus($status)
{
  $s = strtolower(trim($status ?? ''));

  if ($s == 'selesai') return 'bg-success';
  if ($s == 'diproses') return 'bg-warning text-dark';
  if ($s == 'dikirim') return 'bg-primary';
  if ($s == 'ditolak') return 'bg-danger';

  return 'bg-secondary';
}

// ambil data sertifikat untuk pelanggan yang login
$sql = "
  SELECT
    tbl_pengajuan_kalibrasi.id_pengajuan,
    tbl_pengajuan_kalibrasi.tanggal_pengajuan,
    tbl_pengajuan_kalibrasi.status_pengajuan,
    tbl_pengajuan_kalibrasi.catatan,

    tbl_penawaran.id_penawaran,
    tbl_penawaran.total_biaya,
    tbl_penawaran.status_penawaran,

    tbl_sertifikat.id_sertifikat,
    tbl_sertifikat.nomor_sertifikat,
    tbl_sertifikat.lokasi_file_sertifikat,
    tbl_sertifikat.nama_file_sertifikat,
    tbl_sertifikat.dibuat_pada

  FROM tbl_pengajuan_kalibrasi

  LEFT JOIN tbl_penawaran 
    ON tbl_penawaran.id_pengajuan = tbl_pengajuan_kalibrasi.id_pengajuan

  LEFT JOIN tbl_sertifikat
    ON tbl_sertifikat.id_pengajuan = tbl_pengajuan_kalibrasi.id_pengajuan

  WHERE tbl_pengajuan_kalibrasi.id_pelanggan = '$id_pelanggan'
    AND (
      tbl_pengajuan_kalibrasi.status_pengajuan = 'selesai'
      OR tbl_pengajuan_kalibrasi.status_pengajuan = 'Selesai'
      OR tbl_pengajuan_kalibrasi.status_pengajuan = 'SELESAI'
    )
  ORDER BY tbl_pengajuan_kalibrasi.tanggal_pengajuan DESC
";

$data = mysqli_query($conn, $sql);
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

    <?php if (isset($_GET['msg']) && $_GET['msg'] == 'filenotfound'): ?>
      <div class="alert alert-danger">
        File sertifikat belum ditemukan di server. Silakan hubungi admin.
      </div>
    <?php endif; ?>


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
                  <th>Tanggal Pengajuan</th>
                  <th>Status</th>
                  <th>Penawaran</th>
                  <th>Sertifikat</th>
                  <th>Aksi</th>
                </tr>
              </thead>
              <tbody>

                <?php $no = 1; ?>
                <?php while ($row = mysqli_fetch_assoc($data)): ?>

                  <?php
                  $statusPengajuan = $row['status_pengajuan'] ?? '-';
                  $statusPenawaran = $row['status_penawaran'] ?? '-';
                  $totalBiaya = $row['total_biaya'] ?? 0;

                  $nomorSertifikat = $row['nomor_sertifikat'] ?? '-';
                  $lokasiFile = $row['lokasi_file_sertifikat'] ?? '';
                  $namaFile = $row['nama_file_sertifikat'] ?? '';
                  ?>

                  <tr>
                    <td><?= $no++; ?></td>

                    <td>
                      <span class="fw-semibold">#<?= $row['id_pengajuan']; ?></span>
                    </td>

                    <td><?= $row['tanggal_pengajuan'] ?? '-'; ?></td>

                    <td>
                      <span class="badge <?= badgeStatus($statusPengajuan); ?>">
                        <?= $statusPengajuan; ?>
                      </span>
                    </td>

                    <td class="text-muted small">
                      <?php if (!empty($row['id_penawaran'])): ?>
                        Status: <b><?= $statusPenawaran; ?></b>
                        <br>
                        Total: <b>Rp <?= number_format((float)$totalBiaya, 0, ',', '.'); ?></b>
                      <?php else: ?>
                        Penawaran: -
                      <?php endif; ?>
                    </td>

                    <td class="text-muted small">
                      <?php if (!empty($row['id_sertifikat'])): ?>
                        No: <b><?= $nomorSertifikat; ?></b>
                        <br>
                        Tanggal: <?= $row['dibuat_pada'] ?? '-'; ?>
                      <?php else: ?>
                        Sertifikat: -
                      <?php endif; ?>
                    </td>

                    <td>
                      <div class="d-flex gap-2 flex-wrap">

                        <a class="btn btn-sm btn-primary"
                           href="detail_pengajuan.php?id=<?= $row['id_pengajuan']; ?>">
                          Detail
                        </a>

                        <?php if (!empty($row['id_pengajuan'])): ?>
                          <a class="btn btn-sm btn-outline-success"
                            href="unduh_sertifikat.php?id_pengajuan=<?= $row['id_pengajuan']; ?>">
                            Unduh Sertifikat
                          </a>
                        <?php else: ?>
                          <button type="button" class="btn btn-sm btn-outline-success" disabled>Unduh Sertifikat</button>
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
