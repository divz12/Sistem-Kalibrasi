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

// ambil daftar pengajuan + status + penawaran
$sql = "
  SELECT
    tbl_pengajuan_kalibrasi.id_pengajuan,
    tbl_pengajuan_kalibrasi.tanggal_pengajuan,
    tbl_pengajuan_kalibrasi.status_pengajuan,
    tbl_pengajuan_kalibrasi.catatan,

    tbl_penawaran.id_penawaran,
    tbl_penawaran.status_penawaran,
    tbl_penawaran.total_biaya,
    tbl_penawaran.tanggal_penawaran

  FROM tbl_pengajuan_kalibrasi
  LEFT JOIN tbl_penawaran 
    ON tbl_penawaran.id_pengajuan = tbl_pengajuan_kalibrasi.id_pengajuan

  WHERE tbl_pengajuan_kalibrasi.id_pelanggan = '$id_pelanggan'
  ORDER BY tbl_pengajuan_kalibrasi.tanggal_pengajuan DESC
";
$data = mysqli_query($conn, $sql);

// fungsi badge status pengajuan
function badgeStatusPengajuan($status) {
  if ($status == 'dikirim' || $status == 'Dikirim' || $status == 'DIKIRIM') return 'bg-primary';
  if ($status == 'diproses' || $status == 'Diproses' || $status == 'DIPROSES') return 'bg-warning text-dark';
  if ($status == 'selesai' || $status == 'Selesai' || $status == 'SELESAI') return 'bg-success';
  if ($status == 'ditolak' || $status == 'Ditolak' || $status == 'DITOLAK') return 'bg-danger';
  return 'bg-secondary';
}

// fungsi badge status penawaran
function badgeStatusPenawaran($status) {
  if ($status == 'dikirim' || $status == 'Dikirim' || $status == 'DIKIRIM') return 'bg-primary';
  if ($status == 'diterima' || $status == 'Diterima' || $status == 'DITERIMA') return 'bg-success';
  if ($status == 'ditolak' || $status == 'Ditolak' || $status == 'DITOLAK') return 'bg-danger';
  if ($status == 'negosiasi' || $status == 'Negosiasi' || $status == 'NEGOSIASI') return 'bg-warning text-dark';
  return 'bg-secondary';
}

// timeline sederhana
function timelineText($status_pengajuan, $status_penawaran) {

  $langkah = [
    ['judul' => 'Pengajuan Masuk', 'desc' => 'Pengajuan kamu sudah diterima sistem.'],
    ['judul' => 'Penawaran', 'desc' => 'Admin menyiapkan penawaran.'],
    ['judul' => 'Proses Kalibrasi', 'desc' => 'Alat diproses sesuai prosedur kalibrasi.'],
    ['judul' => 'Dokumen', 'desc' => 'Sertifikat/invoice disiapkan jika proses selesai.'],
  ];

  $aktif = 1;

  // Pengajuan dikirim
  if ($status_pengajuan == 'dikirim' || $status_pengajuan == 'Dikirim' || $status_pengajuan == 'DIKIRIM') {
    $aktif = 1;
  }

  // Penawaran dikirim / negosiasi
  if (
    $status_penawaran == 'dikirim' || $status_penawaran == 'Dikirim' || $status_penawaran == 'DIKIRIM' ||
    $status_penawaran == 'negosiasi' || $status_penawaran == 'Negosiasi' || $status_penawaran == 'NEGOSIASI'
  ) {
    $aktif = 2;
  }

  // Penawaran diterima / pengajuan diproses
  if (
    $status_penawaran == 'diterima' || $status_penawaran == 'Diterima' || $status_penawaran == 'DITERIMA' ||
    $status_pengajuan == 'diproses' || $status_pengajuan == 'Diproses' || $status_pengajuan == 'DIPROSES'
  ) {
    $aktif = 3;
  }

  // Pengajuan selesai
  if ($status_pengajuan == 'selesai' || $status_pengajuan == 'Selesai' || $status_pengajuan == 'SELESAI') {
    $aktif = 4;
  }

  // Ditolak (pengajuan atau penawaran)
  if (
    $status_pengajuan == 'ditolak' || $status_pengajuan == 'Ditolak' || $status_pengajuan == 'DITOLAK' ||
    $status_penawaran == 'ditolak' || $status_penawaran == 'Ditolak' || $status_penawaran == 'DITOLAK'
  ) {
    $aktif = 2;
  }

  return [$langkah, $aktif];
}
?>

<?php include 'komponen/header.php'; ?>
<?php include 'komponen/sidebar.php'; ?>
<?php include 'komponen/navbar.php'; ?>

<div class="content-wrapper">
  <div class="container-xxl flex-grow-1 container-p-y">

    <div class="d-flex justify-content-between align-items-center mb-3">
      <div>
        <h4 class="fw-bold mb-1">Status Proses</h4>
        <p class="text-muted mb-0">Pantau perkembangan pengajuan kalibrasi kamu.</p>
      </div>
      <a href="index.php" class="btn btn-outline-primary">
        <i class="bx bx-arrow-back me-1"></i> Kembali
      </a>
    </div>

    <?php if (!$data || mysqli_num_rows($data) == 0): ?>
      <div class="alert alert-info">
        Kamu belum punya pengajuan. Silakan buat pengajuan dulu di menu <b>Ajukan Kalibrasi</b>.
      </div>
    <?php else: ?>

      <?php while ($row = mysqli_fetch_assoc($data)): ?>
        <?php
          $statusPengajuan = $row['status_pengajuan'] ?? '-';
          $statusPenawaran = $row['status_penawaran'] ?? '-';

          $badgePengajuan = badgeStatusPengajuan($statusPengajuan);
          $badgePenawaran = badgeStatusPenawaran($statusPenawaran);

          list($timeline, $aktif) = timelineText($statusPengajuan, $statusPenawaran);
        ?>

        <div class="card shadow-sm border-0 mb-3">
          <div class="card-body">

            <div class="d-flex flex-wrap justify-content-between align-items-start gap-2">
              <div>
                <h5 class="mb-1">Pengajuan #<?= $row['id_pengajuan']; ?></h5>
                <div class="text-muted small">
                  Tanggal Pengajuan: <?= $row['tanggal_pengajuan']; ?>
                </div>
              </div>

              <div class="text-end">
                <div class="mb-1">
                  <span class="badge <?= $badgePengajuan; ?>">Status Pengajuan: <?= $statusPengajuan; ?></span>
                </div>

                <?php if (!empty($row['id_penawaran'])): ?>
                  <div>
                    <span class="badge <?= $badgePenawaran; ?>">Status Penawaran: <?= $statusPenawaran; ?></span>
                  </div>
                <?php else: ?>
                  <div class="text-muted small">Penawaran: belum ada</div>
                <?php endif; ?>
              </div>
            </div>

            <?php if (!empty($row['catatan'])): ?>
              <div class="mt-3 p-3 bg-light rounded-3">
                <div class="fw-semibold mb-1">Catatan</div>
                <div class="text-muted"><?= $row['catatan']; ?></div>
              </div>
            <?php endif; ?>

            <hr class="my-3">

            <!-- Timeline sederhana -->
            <div class="row g-3">
              <?php foreach ($timeline as $i => $t): ?>
                <?php $step = $i + 1; ?>
                <div class="col-md-3">
                  <div class="border rounded-3 p-3 h-100 <?= ($step <= $aktif) ? 'bg-white' : 'bg-light'; ?>">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                      <span class="badge <?= ($step <= $aktif) ? 'bg-success' : 'bg-secondary'; ?>">
                        <?= $step; ?>
                      </span>
                      <?php if ($step == $aktif): ?>
                        <span class="badge bg-warning text-dark">Sedang berjalan</span>
                      <?php elseif ($step < $aktif): ?>
                        <span class="badge bg-success">Selesai</span>
                      <?php else: ?>
                        <span class="badge bg-secondary">Menunggu</span>
                      <?php endif; ?>
                    </div>

                    <div class="fw-semibold"><?= $t['judul']; ?></div>
                    <div class="text-muted small"><?= $t['desc']; ?></div>
                  </div>
                </div>
              <?php endforeach; ?>
            </div>

            <div class="d-flex gap-2 mt-3 flex-wrap">
              <a href="detail_pengajuan.php?id=<?= $row['id_pengajuan']; ?>" class="btn btn-primary btn-sm">
                Detail Pengajuan
              </a>

              <?php if (!empty($row['id_penawaran'])): ?>
                <a href="detail_penawaran.php?id=<?= $row['id_penawaran']; ?>" class="btn btn-outline-primary btn-sm">
                  Lihat Penawaran
                </a>
              <?php else: ?>
                <a href="penawaran.php" class="btn btn-outline-primary btn-sm">
                  Cek Penawaran
                </a>
              <?php endif; ?>

            </div>

          </div>
        </div>

      <?php endwhile; ?>

    <?php endif; ?>

  </div>
</div>

<?php include 'komponen/footer.php'; ?>
