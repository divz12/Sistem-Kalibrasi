<?php
session_start();
include "../koneksi.php";

// proteksi login
if (!isset($_SESSION['id_user']) || ($_SESSION['role'] ?? '') !== 'pelanggan') {
  header("Location: ../login.php");
  exit();
}

$id_user = (int)($_SESSION['id_user'] ?? 0);
$id_penawaran = (int)($_GET['id'] ?? 0);

if ($id_penawaran <= 0) {
  header("Location: penawaran.php");
  exit();
}

// ambil id_pelanggan
$qPel = mysqli_query($conn, "SELECT id_pelanggan FROM tbl_pelanggan WHERE id_user='$id_user' LIMIT 1");
$dataPel = mysqli_fetch_assoc($qPel);
$id_pelanggan = (int)($dataPel['id_pelanggan'] ?? 0);

if ($id_pelanggan <= 0) {
  header("Location: profil.php");
  exit();
}

/*
  ambil detail penawaran yang MILIK pelanggan ini
*/
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
  WHERE tbl_penawaran.id_penawaran = '$id_penawaran'
    AND tbl_pengajuan_kalibrasi.id_pelanggan = '$id_pelanggan'
  LIMIT 1
";

$hasil = mysqli_query($conn, $sql);
$penawaran = mysqli_fetch_assoc($hasil);

if (!$penawaran) {
  header("Location: penawaran.php");
  exit();
}

// ambil daftar alat dari pengajuan (biar detailnya lengkap)
$id_pengajuan = (int)($penawaran['id_pengajuan'] ?? 0);

$sqlAlat = "
  SELECT
    id_alat,
    nama_alat,
    merk_tipe,
    kapasitas,
    jumlah_unit,
    parameter,
    titik_ukur,
    keterangan
  FROM tbl_pengajuan_alat
  WHERE id_pengajuan = '$id_pengajuan'
  ORDER BY id_alat ASC
";
$alat = mysqli_query($conn, $sqlAlat);

// badge status penawaran
$badge = "bg-secondary";
$statusPenawaran = $penawaran['status_penawaran'] ?? "-";
if ($statusPenawaran == "dikirim") $badge = "bg-primary";
if ($statusPenawaran == "diterima") $badge = "bg-success";
if ($statusPenawaran == "ditolak") $badge = "bg-danger";
if ($statusPenawaran == "negosiasi") $badge = "bg-warning text-dark";

include "komponen/header.php";
include "komponen/sidebar.php";
include "komponen/navbar.php";
?>

<div class="content-wrapper">
  <div class="container-xxl flex-grow-1 container-p-y">

    <div class="d-flex justify-content-between align-items-center mb-3">
      <div>
        <h4 class="fw-bold mb-1">Detail Penawaran</h4>
        <p class="text-muted mb-0">Lihat rincian penawaran untuk pengajuan kamu.</p>
      </div>
      <a href="penawaran.php" class="btn btn-outline-primary">
        <i class="bx bx-arrow-back me-1"></i> Kembali
      </a>
    </div>

    <div class="card shadow-sm border-0 mb-3">
      <div class="card-body">

        <div class="d-flex flex-wrap justify-content-between align-items-start gap-3">
          <div>
            <h5 class="mb-1">Penawaran #<?= $penawaran['id_penawaran']; ?></h5>
            <div class="text-muted small">
              Tanggal Penawaran: <?= $penawaran['tanggal_penawaran']; ?>
            </div>
            <div class="text-muted small">
              ID Pengajuan: <b>#<?= $penawaran['id_pengajuan']; ?></b>
            </div>
          </div>

          <div class="text-end">
            <div class="mb-2">
              <span class="badge <?= $badge; ?>"><?= $statusPenawaran; ?></span>
            </div>
            <div class="fs-5 fw-bold">
              <?php if ($penawaran['total_biaya'] != null && $penawaran['total_biaya'] != ""): ?>
                Rp <?= number_format($penawaran['total_biaya'], 0, ',', '.'); ?>
              <?php else: ?>
                Rp -
              <?php endif; ?>
            </div>
            <div class="text-muted small">Total biaya</div>
          </div>
        </div>

        <hr class="my-3">

        <div class="row g-3">
          <div class="col-md-6">
            <div class="text-muted small mb-1">Catatan Pengajuan</div>
            <div class="p-3 bg-light rounded-3">
              <?= ($penawaran['catatan'] ?? '') != "" ? $penawaran['catatan'] : "-"; ?>
            </div>
          </div>

          <div class="col-md-6">
            <div class="text-muted small mb-1">Rincian Penawaran</div>
            <div class="p-3 bg-light rounded-3" style="white-space:pre-wrap;">
              <?= ($penawaran['rincian'] ?? '') != "" ? $penawaran['rincian'] : "-"; ?>
            </div>
          </div>
        </div>

        <hr class="my-3">

        <div class="d-flex gap-2 flex-wrap">
          <a href="detail_pengajuan.php?id=<?= (int)$penawaran['id_pengajuan']; ?>" class="btn btn-outline-primary">
            Lihat Detail Pengajuan
          </a>

          <?php if ($statusPenawaran == "dikirim" || $statusPenawaran == "negosiasi"): ?>
            <a class="btn btn-success"
               href="aksi_penawaran.php?id=<?= (int)$penawaran['id_penawaran']; ?>&aksi=diterima"
               onclick="return confirm('Yakin terima penawaran ini?');">
              Terima
            </a>
            <a class="btn btn-danger"
               href="aksi_penawaran.php?id=<?= (int)$penawaran['id_penawaran']; ?>&aksi=ditolak"
               onclick="return confirm('Yakin tolak penawaran ini?');">
              Tolak
            </a>
          <?php endif; ?>
        </div>

      </div>
    </div>

    <div class="card shadow-sm border-0">
      <div class="card-header bg-white">
        <h5 class="mb-0">Data Alat pada Pengajuan #<?= (int)$penawaran['id_pengajuan']; ?></h5>
      </div>

      <div class="table-responsive">
        <table class="table align-middle mb-0">
          <thead class="table-light">
            <tr>
              <th>No</th>
              <th>Nama Alat</th>
              <th>Merk/Tipe</th>
              <th>Kapasitas</th>
              <th>Jumlah</th>
              <th>Parameter</th>
              <th>Titik Ukur</th>
              <th>Keterangan</th>
            </tr>
          </thead>
          <tbody>
            <?php if (!$alat || mysqli_num_rows($alat) == 0): ?>
              <tr>
                <td colspan="8" class="text-center text-muted py-4">Data alat belum ada.</td>
              </tr>
            <?php else: ?>
              <?php $no = 1; while ($a = mysqli_fetch_assoc($alat)): ?>
                <tr>
                  <td><?= $no++; ?></td>
                  <td><?= $a['nama_alat']; ?></td>
                  <td><?= $a['merk_tipe'] != "" ? $a['merk_tipe'] : "-"; ?></td>
                  <td><?= $a['kapasitas'] != "" ? $a['kapasitas'] : "-"; ?></td>
                  <td><?= $a['jumlah_unit']; ?></td>
                  <td><?= $a['parameter'] != "" ? $a['parameter'] : "-"; ?></td>
                  <td><?= $a['titik_ukur'] != "" ? $a['titik_ukur'] : "-"; ?></td>
                  <td><?= $a['keterangan'] != "" ? $a['keterangan'] : "-"; ?></td>
                </tr>
              <?php endwhile; ?>
            <?php endif; ?>
          </tbody>
        </table>
      </div>

    </div>

  </div>
</div>

<?php include "komponen/footer.php"; ?>
