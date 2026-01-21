<?php
session_start();
include "../koneksi.php";

$rolePengguna = isset($_SESSION['role']) ? $_SESSION['role'] : '';

if (!isset($_SESSION['id_user'])) {
  header("Location: ../login.php");
  exit();
}

if ($rolePengguna != 'admin' && $rolePengguna != 'cs') {
  header("Location: ../login.php");
  exit();
}

$idUser = (int) $_SESSION['id_user'];

$namaAdmin = isset($_SESSION['nama']) ? $_SESSION['nama'] : '';

if ($namaAdmin == '') {
  $sqlNamaAdmin = "
    SELECT nama
    FROM tbl_users
    WHERE id_user = $idUser
    LIMIT 1
  ";
  $hasilNamaAdmin = mysqli_query($conn, $sqlNamaAdmin);

  if ($hasilNamaAdmin && mysqli_num_rows($hasilNamaAdmin) > 0) {
    $dataAdmin = mysqli_fetch_assoc($hasilNamaAdmin);
    $namaAdmin = isset($dataAdmin['nama']) ? $dataAdmin['nama'] : 'Admin';
  } else {
    $namaAdmin = 'Admin';
  }

  $_SESSION['nama'] = $namaAdmin;
}


function badgeStatusAdmin($status)
{
  if ($status == 'dikirim' || $status == 'Dikirim') return 'bg-label-primary';
  if ($status == 'diproses' || $status == 'Diproses') return 'bg-label-warning';
  if ($status == 'selesai' || $status == 'Selesai') return 'bg-label-success';
  if ($status == 'ditolak' || $status == 'Ditolak') return 'bg-label-danger';

  return 'bg-label-secondary';
}

// statistik
$totalPengajuan = 0;
$totalPenawaran = 0;
$totalPelanggan = 0;
$totalSelesai = 0;

// Total pengajuan
$sqlTotalPengajuan = "
  SELECT COUNT(*) AS total
  FROM tbl_pengajuan_kalibrasi
";
$hasil = mysqli_query($conn, $sqlTotalPengajuan);
if ($hasil) {
  $data = mysqli_fetch_assoc($hasil);
  $totalPengajuan = (int)($data['total'] ?? 0);
}

// Total penawaran
$sqlTotalPenawaran = "
  SELECT COUNT(*) AS total
  FROM tbl_penawaran
";
$hasil = mysqli_query($conn, $sqlTotalPenawaran);
if ($hasil) {
  $data = mysqli_fetch_assoc($hasil);
  $totalPenawaran = (int)($data['total'] ?? 0);
}

// Total pelanggan
$sqlTotalPelanggan = "
  SELECT COUNT(*) AS total
  FROM tbl_pelanggan
";
$hasil = mysqli_query($conn, $sqlTotalPelanggan);
if ($hasil) {
  $data = mysqli_fetch_assoc($hasil);
  $totalPelanggan = (int)($data['total'] ?? 0);
}

// Total pengajuan selesai
$sqlTotalSelesai = "
  SELECT COUNT(*) AS total
  FROM tbl_pengajuan_kalibrasi
  WHERE status_pengajuan = 'selesai' OR status_pengajuan = 'Selesai'
";
$hasil = mysqli_query($conn, $sqlTotalSelesai);
if ($hasil) {
  $data = mysqli_fetch_assoc($hasil);
  $totalSelesai = (int)($data['total'] ?? 0);
}

// pengajuan terbaru
$pengajuanTerbaru = null;

$sqlPengajuanTerbaru = "
  SELECT
    tbl_pengajuan_kalibrasi.id_pengajuan,
    tbl_pengajuan_kalibrasi.tanggal_pengajuan,
    tbl_pengajuan_kalibrasi.status_pengajuan,
    tbl_pengajuan_kalibrasi.catatan,
    tbl_users.nama AS nama_pelanggan
  FROM tbl_pengajuan_kalibrasi
  LEFT JOIN tbl_pelanggan
    ON tbl_pelanggan.id_pelanggan = tbl_pengajuan_kalibrasi.id_pelanggan
  LEFT JOIN tbl_users
    ON tbl_users.id_user = tbl_pelanggan.id_user
  ORDER BY tbl_pengajuan_kalibrasi.tanggal_pengajuan DESC
  LIMIT 1
";
$hasil = mysqli_query($conn, $sqlPengajuanTerbaru);
if ($hasil && mysqli_num_rows($hasil) > 0) {
  $pengajuanTerbaru = mysqli_fetch_assoc($hasil);
}

//riwayat pengajuan terbaru
$riwayat = [];

$sqlRiwayat = "
  SELECT
    tbl_pengajuan_kalibrasi.id_pengajuan,
    tbl_pengajuan_kalibrasi.tanggal_pengajuan,
    tbl_pengajuan_kalibrasi.status_pengajuan,
    tbl_pengajuan_kalibrasi.catatan,
    tbl_users.nama AS nama_pelanggan
  FROM tbl_pengajuan_kalibrasi
  LEFT JOIN tbl_pelanggan
    ON tbl_pelanggan.id_pelanggan = tbl_pengajuan_kalibrasi.id_pelanggan
  LEFT JOIN tbl_users
    ON tbl_users.id_user = tbl_pelanggan.id_user
  ORDER BY tbl_pengajuan_kalibrasi.tanggal_pengajuan DESC
  LIMIT 5
";
$hasil = mysqli_query($conn, $sqlRiwayat);
if ($hasil) {
  while ($row = mysqli_fetch_assoc($hasil)) {
    $riwayat[] = $row;
  }
}
$base = "../";
include "komponen/header.php";
include "komponen/sidebar.php";
include "komponen/navbar.php";
?>

<div class="content-wrapper">
  <div class="container-xxl flex-grow-1 container-p-y">

    <!-- Header -->
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
      <div>
        <h4 class="fw-bold mb-1">Dashboard Admin</h4>
        <div class="text-muted">Halo, <b><?= $namaAdmin; ?></b> 👋</div>
      </div>

      <div class="d-flex gap-2">
        <a href="Pengajuan/pengajuan.php" class="btn btn-primary">
          <i class="bx bx-list-check me-1"></i> Data Pengajuan
        </a>
        <a href="Data-Pelanggan/pelanggan.php" class="btn btn-outline-primary">
          <i class="bx bx-user me-1"></i> Data Pelanggan
        </a>
      </div>
    </div>

    <!-- Stat Cards -->
    <div class="row g-4 mb-4">

      <div class="col-sm-6 col-lg-3">
        <div class="card">
          <div class="card-body">
            <div class="d-flex align-items-center justify-content-between">
              <div>
                <span class="text-muted">Pengajuan</span>
                <h3 class="mb-0"><?= $totalPengajuan; ?></h3>
              </div>
              <div class="avatar">
                <span class="avatar-initial rounded bg-label-primary"><i class="bx bx-edit"></i></span>
              </div>
            </div>
            <small class="text-muted">Total pengajuan masuk</small>
          </div>
        </div>
      </div>

      <div class="col-sm-6 col-lg-3">
        <div class="card">
          <div class="card-body">
            <div class="d-flex align-items-center justify-content-between">
              <div>
                <span class="text-muted">Penawaran</span>
                <h3 class="mb-0"><?= $totalPenawaran; ?></h3>
              </div>
              <div class="avatar">
                <span class="avatar-initial rounded bg-label-info"><i class="bx bx-receipt"></i></span>
              </div>
            </div>
            <small class="text-muted">Total penawaran dibuat</small>
          </div>
        </div>
      </div>

      <div class="col-sm-6 col-lg-3">
        <div class="card">
          <div class="card-body">
            <span class="text-muted">Pelanggan</span>
            <h3 class="mb-0"><?= $totalPelanggan; ?></h3>
            <small class="text-muted">Total data pelanggan</small>
          </div>
        </div>
      </div>

      <div class="col-sm-6 col-lg-3">
        <div class="card">
          <div class="card-body">
            <span class="text-muted">Selesai</span>
            <h3 class="mb-0"><?= $totalSelesai; ?></h3>
            <small class="text-muted">Pengajuan yang sudah selesai</small>
          </div>
        </div>
      </div>

    </div>

    <!-- Status terbaru + panduan -->
    <div class="row g-4 mb-4">
      <div class="col-lg-7">
        <div class="card h-100">
          <div class="card-header d-flex align-items-center justify-content-between">
            <h5 class="mb-0">Pengajuan Terbaru</h5>
            <a href="Pengajuan/pengajuan.php" class="btn btn-sm btn-outline-primary">Lihat semua</a>
          </div>

          <div class="card-body">
            <?php if (!$pengajuanTerbaru): ?>
              <div class="alert alert-info mb-0">
                Belum ada pengajuan masuk.
              </div>
            <?php else: ?>
              <div class="d-flex justify-content-between align-items-start gap-3">
                <div>
                  <div class="text-muted small">Tanggal</div>
                  <div class="fw-semibold mb-2"><?= $pengajuanTerbaru['tanggal_pengajuan']; ?></div>

                  <div class="text-muted small">Pelanggan</div>
                  <div class="fw-semibold mb-2"><?= $pengajuanTerbaru['nama_pelanggan']; ?></div>

                  <div class="text-muted small">Catatan</div>
                  <div><?= $pengajuanTerbaru['catatan'] ?? '-'; ?></div>
                </div>

                <span class="badge <?= badgeStatusAdmin($pengajuanTerbaru['status_pengajuan']); ?>">
                  <?= $pengajuanTerbaru['status_pengajuan']; ?>
                </span>
              </div>

              <hr class="my-3">

              <div class="d-flex gap-2 flex-wrap">
                <a href="detail_pengajuan.php?id=<?= (int)$pengajuanTerbaru['id_pengajuan']; ?>" class="btn btn-primary">
                  Detail Pengajuan
                </a>
                <!-- <a href="buat_penawaran.php?id=<?= (int)$pengajuanTerbaru['id_pengajuan']; ?>" class="btn btn-outline-primary">
                  Buat Penawaran
                </a>
                <a href="ubah_status_pengajuan.php?id=<?= (int)$pengajuanTerbaru['id_pengajuan']; ?>" class="btn btn-outline-primary">
                  Ubah Status
                </a> -->
              </div>
            <?php endif; ?>
          </div>
        </div>
      </div>

      <div class="col-lg-5">
        <div class="card h-100">
          <div class="card-header">
            <h5 class="mb-0">Panduan Singkat</h5>
          </div>
          <div class="card-body">
            <ul class="list-unstyled mb-0">
              <li class="d-flex gap-2 mb-3">
                <i class="bx bx-list-check text-primary fs-4"></i>
                <div>
                  <b>Cek Pengajuan</b>
                  <div class="text-muted small">Lihat pengajuan yang masuk dari pelanggan.</div>
                </div>
              </li>
              <li class="d-flex gap-2 mb-3">
                <i class="bx bx-receipt text-info fs-4"></i>
                <div>
                  <b>Buat Penawaran</b>
                  <div class="text-muted small">Buat quotation berdasarkan pengajuan pelanggan.</div>
                </div>
              </li>
              <li class="d-flex gap-2 mb-3">
                <i class="bx bx-loader-circle text-warning fs-4"></i>
                <div>
                  <b>Ubah Status</b>
                  <div class="text-muted small">Atur status pengajuan (dikirim, diproses, selesai, ditolak).</div>
                </div>
              </li>
              <li class="d-flex gap-2">
                <i class="bx bx-user text-success fs-4"></i>
                <div>
                  <b>Kelola Pelanggan</b>
                  <div class="text-muted small">Lihat dan kelola data pelanggan.</div>
                </div>
              </li>
            </ul>
          </div>
        </div>
      </div>
    </div>

    <!-- Riwayat terbaru -->
    <div class="card">
      <div class="card-header d-flex align-items-center justify-content-between">
        <h5 class="mb-0">Riwayat Pengajuan Terbaru</h5>
        <a href="Pengajuan/pengajuan.php" class="btn btn-sm btn-outline-primary">Lihat semua</a>
      </div>

      <div class="table-responsive">
        <table class="table">
          <thead>
            <tr>
              <th>No</th>
              <th>Tanggal</th>
              <th>Pelanggan</th>
              <th>Status</th>
              <th>Catatan</th>
              <th>Aksi</th>
            </tr>
          </thead>
          <tbody>
            <?php if (empty($riwayat)): ?>
              <tr>
                <td colspan="6" class="text-center text-muted py-4">Belum ada data pengajuan.</td>
              </tr>
            <?php else: ?>
              <?php foreach ($riwayat as $i => $row): ?>
                <tr>
                  <td><?= $i + 1; ?></td>
                  <td><?= $row['tanggal_pengajuan']; ?></td>
                  <td><?= $row['nama_pelanggan']; ?></td>
                  <td>
                    <span class="badge <?= badgeStatusAdmin($row['status_pengajuan']); ?>">
                      <?= $row['status_pengajuan']; ?>
                    </span>
                  </td>
                  <td><?= $row['catatan'] ?? '-'; ?></td>
                  <td>
                    <a class="btn btn-sm btn-primary"
                       href="Pengajuan/detail_pengajuan.php?id=<?= (int)$row['id_pengajuan']; ?>">
                      Detail
                    </a>
                  </td>
                </tr>
              <?php endforeach; ?>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>

  </div>
</div>

<?php include "komponen/footer.php"; ?>
