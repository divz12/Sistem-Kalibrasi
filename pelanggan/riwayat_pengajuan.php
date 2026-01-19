<?php
session_start();
include "../koneksi.php";

// proteksi login
if (!isset($_SESSION['id_user']) || ($_SESSION['role'] ?? '') !== 'pelanggan') {
  header("Location: ../login.php");
  exit();
}

$id_user = (int) $_SESSION['id_user'];

// ambil id_pelanggan (tanpa prepare)
$id_pelanggan = 0;
$sqlPelanggan = "SELECT id_pelanggan FROM tbl_pelanggan WHERE id_user = '$id_user' LIMIT 1";
$hasilP = mysqli_query($conn, $sqlPelanggan);
$dataP = mysqli_fetch_assoc($hasilP);
$id_pelanggan = (int)($dataP['id_pelanggan'] ?? 0);

if ($id_pelanggan <= 0) {
  header("Location: profil.php");
  exit();
}

$q = $_GET['q'] ?? '';

// pagination
$per_halaman = 8;
$halaman = (int)($_GET['page'] ?? 1);
if ($halaman < 1) $halaman = 1;
$offset = ($halaman - 1) * $per_halaman;

function badgeStatus($status)
{
  $status = strtolower($status);

  if ($status === 'dikirim') return 'bg-label-primary';
  if ($status === 'diproses') return 'bg-label-warning';
  if ($status === 'selesai') return 'bg-label-success';
  if ($status === 'ditolak') return 'bg-label-danger';

  return 'bg-label-secondary';
}

// hitung total data
$totalData = 0;

if ($q !== '') {
  $sqlCount = "
    SELECT COUNT(*) AS total
    FROM tbl_pengajuan_kalibrasi
    WHERE id_pelanggan = '$id_pelanggan'
      AND (
        CAST(id_pengajuan AS CHAR) LIKE '%$q%'
        OR status_pengajuan LIKE '%$q%'
        OR catatan LIKE '%$q%'
      )
  ";
} else {
  $sqlCount = "
    SELECT COUNT(*) AS total
    FROM tbl_pengajuan_kalibrasi
    WHERE id_pelanggan = '$id_pelanggan'
  ";
}

$hasilCount = mysqli_query($conn, $sqlCount);
$dataCount = mysqli_fetch_assoc($hasilCount);
$totalData = (int)($dataCount['total'] ?? 0);

$totalHalaman = (int)ceil($totalData / $per_halaman);

$riwayat = [];

if ($q !== '') {
  $sql = "
    SELECT id_pengajuan, tanggal_pengajuan, status_pengajuan, catatan
    FROM tbl_pengajuan_kalibrasi
    WHERE id_pelanggan = '$id_pelanggan'
      AND (
        CAST(id_pengajuan AS CHAR) LIKE '%$q%'
        OR status_pengajuan LIKE '%$q%'
        OR catatan LIKE '%$q%'
      )
    ORDER BY id_pengajuan DESC
    LIMIT $per_halaman OFFSET $offset
  ";
} else {
  $sql = "
    SELECT id_pengajuan, tanggal_pengajuan, status_pengajuan, catatan
    FROM tbl_pengajuan_kalibrasi
    WHERE id_pelanggan = '$id_pelanggan'
    ORDER BY id_pengajuan DESC
    LIMIT $per_halaman OFFSET $offset
  ";
}

$hasil = mysqli_query($conn, $sql);
while ($row = mysqli_fetch_assoc($hasil)) {
  $riwayat[] = $row;
}

include 'komponen/header.php';
include 'komponen/sidebar.php';
include 'komponen/navbar.php';
?>

<div class="content-wrapper">
  <div class="container-xxl flex-grow-1 container-p-y">

    <div class="d-flex justify-content-between align-items-center mb-3">
      <div>
        <h4 class="fw-bold mb-1">Riwayat Pengajuan</h4>
        <p class="text-muted mb-0">Lihat daftar pengajuan kalibrasi yang pernah kamu buat.</p>
      </div>
      <a href="pengajuan.php" class="btn btn-primary">
        <i class="bx bx-plus me-1"></i> Ajukan Kalibrasi
      </a>
    </div>

    <!-- Search -->
    <form class="card mb-3" method="get" action="">
      <div class="card-body">
        <div class="row g-2 align-items-center">
          <div class="col-md-10">
            <input type="text" name="q" class="form-control"
              placeholder="Cari berdasarkan ID / status / catatan..."
              value="<?= $q; ?>">
          </div>
          <div class="col-md-2 d-grid">
            <button class="btn btn-outline-primary" type="submit">
              <i class="bx bx-search me-1"></i> Cari
            </button>
          </div>
        </div>
      </div>
    </form>

    <div class="card">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Daftar Pengajuan</h5>
        <span class="text-muted small">Total: <?= $totalData; ?> data</span>
      </div>

      <div class="table-responsive">
        <table class="table align-middle">
          <thead class="table-light">
            <tr>
              <th style="width: 80px;">No</th>
              <th>ID Pengajuan</th>
              <th>Tanggal</th>
              <th>Status</th>
              <th>Catatan</th>
              <th style="width: 120px;">Aksi</th>
            </tr>
          </thead>

          <tbody>
            <?php if (count($riwayat) == 0): ?>
              <tr>
                <td colspan="6" class="text-center text-muted py-4">
                  Belum ada pengajuan.
                </td>
              </tr>
            <?php else: ?>
              <?php foreach ($riwayat as $i => $row): ?>
                <tr>
                  <td><?= $offset + $i + 1; ?></td>
                  <td>#<?= $row['id_pengajuan']; ?></td>
                  <td><?= $row['tanggal_pengajuan']; ?></td>
                  <td>
                    <span class="badge <?= badgeStatus($row['status_pengajuan']); ?>">
                      <?= $row['status_pengajuan']; ?>
                    </span>
                  </td>
                  <td><?= $row['catatan'] ?: '-'; ?></td>
                  <td>
                    <a class="btn btn-sm btn-primary"
                       href="detail_pengajuan.php?id=<?= $row['id_pengajuan']; ?>">
                      Detail
                    </a>
                  </td>
                </tr>
              <?php endforeach; ?>
            <?php endif; ?>
          </tbody>
        </table>
      </div>

      <!-- Pagination -->
      <?php if ($totalHalaman > 1): ?>
        <div class="card-footer">
          <nav>
            <ul class="pagination mb-0">

              <?php
              $prev = $halaman - 1;
              $next = $halaman + 1;

             $linkQ = "";

            if ($q != "") {
              $linkQ = "&q=" . $q;
            }

              ?>

              <li class="page-item <?= ($halaman <= 1) ? 'disabled' : ''; ?>">
                <a class="page-link" href="?page=<?= $prev . $linkQ; ?>">Prev</a>
              </li>

              <?php for ($p = 1; $p <= $totalHalaman; $p++): ?>
                <li class="page-item <?= ($p == $halaman) ? 'active' : ''; ?>">
                  <a class="page-link" href="?page=<?= $p . $linkQ; ?>"><?= $p; ?></a>
                </li>
              <?php endfor; ?>

              <li class="page-item <?= ($halaman >= $totalHalaman) ? 'disabled' : ''; ?>">
                <a class="page-link" href="?page=<?= $next . $linkQ; ?>">Next</a>
              </li>

            </ul>
          </nav>
        </div>
      <?php endif; ?>

    </div>

  </div>
</div>

<?php include 'komponen/footer.php'; ?>
