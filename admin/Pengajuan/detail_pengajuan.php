<?php
session_start();
include "../../koneksi.php";

$role = $_SESSION["role"] ?? "";

if (!isset($_SESSION["id_user"])) {
  header("Location: ../../login.php");
  exit();
}

if ($role != "admin" && $role != "cs" && $role != "owner") {
  header("Location: ../../login.php");
  exit();
}

$idPengajuan = (int)($_GET["id"] ?? 0);
if ($idPengajuan <= 0) {
  header("Location: pengajuan.php");
  exit();
}


$sqlPengajuan = "
  SELECT
    tbl_pengajuan_kalibrasi.id_pengajuan,
    tbl_pengajuan_kalibrasi.id_pelanggan,
    tbl_pengajuan_kalibrasi.tanggal_pengajuan,
    tbl_pengajuan_kalibrasi.status_pengajuan,
    tbl_pengajuan_kalibrasi.catatan,

    tbl_pelanggan.no_hp,
    tbl_pelanggan.alamat,

    tbl_users.nama,
    tbl_users.email

  FROM tbl_pengajuan_kalibrasi
  LEFT JOIN tbl_pelanggan
    ON tbl_pelanggan.id_pelanggan = tbl_pengajuan_kalibrasi.id_pelanggan
  LEFT JOIN tbl_users
    ON tbl_users.id_user = tbl_pelanggan.id_user
  WHERE tbl_pengajuan_kalibrasi.id_pengajuan = '$idPengajuan'
  LIMIT 1
";
$hasilPengajuan = mysqli_query($conn, $sqlPengajuan);
$dataPengajuan = mysqli_fetch_assoc($hasilPengajuan);

if (!$dataPengajuan) {
  header("Location: pengajuan.php");
  exit();
}

$sqlAlat = "
  SELECT
    tbl_pengajuan_alat.id_alat,
    tbl_pengajuan_alat.id_pengajuan,
    tbl_pengajuan_alat.nama_alat,
    tbl_pengajuan_alat.merk_tipe,
    tbl_pengajuan_alat.kapasitas,
    tbl_pengajuan_alat.jumlah_unit,
    tbl_pengajuan_alat.parameter,
    tbl_pengajuan_alat.titik_ukur,
    tbl_pengajuan_alat.keterangan
  FROM tbl_pengajuan_alat
  WHERE tbl_pengajuan_alat.id_pengajuan = '$idPengajuan'
  ORDER BY tbl_pengajuan_alat.id_alat ASC
";
$hasilAlat = mysqli_query($conn, $sqlAlat);

// cek data penawaran terbaru
$sqlPenawaran = "
  SELECT
    tbl_penawaran.id_penawaran,
    tbl_penawaran.tanggal_penawaran,
    tbl_penawaran.total_biaya,
    tbl_penawaran.status_penawaran
  FROM tbl_penawaran
  WHERE tbl_penawaran.id_pengajuan = '$idPengajuan'
  ORDER BY tbl_penawaran.id_penawaran DESC
  LIMIT 1
";
$hasilPenawaran = mysqli_query($conn, $sqlPenawaran);
$dataPenawaran = mysqli_fetch_assoc($hasilPenawaran);

function badgeStatus($status)
{
  if ($status == "dikirim") return "bg-label-primary";
  if ($status == "diproses") return "bg-label-warning";
  if ($status == "selesai") return "bg-label-success";
  if ($status == "ditolak") return "bg-label-danger";
  return "bg-label-secondary";
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
        <h4 class="fw-bold mb-1">Detail Pengajuan</h4>
        <p class="text-muted mb-0">Lihat data pelanggan, catatan, dan data alat yang diajukan.</p>
      </div>
      <a href="pengajuan.php" class="btn btn-outline-primary">
        <i class="bx bx-arrow-back me-1"></i> Kembali
      </a>
    </div>

    <!-- Ringkasan -->
    <div class="card mb-3">
      <div class="card-body">
        <div class="d-flex flex-wrap justify-content-between align-items-start gap-3">
          <div>
            <h5 class="mb-1">Pengajuan #<?= $dataPengajuan["id_pengajuan"]; ?></h5>
            <div class="text-muted small">Tanggal: <?= $dataPengajuan["tanggal_pengajuan"]; ?></div>
          </div>
          <div>
            <span class="badge <?= badgeStatus($dataPengajuan["status_pengajuan"]); ?>">
              <?= $dataPengajuan["status_pengajuan"]; ?>
            </span>
          </div>
        </div>

        <hr>

        <div class="row g-3">
          <div class="col-md-6">
            <div class="text-muted small">Nama Pelanggan</div>
            <div class="fw-semibold"><?= $dataPengajuan["nama"]; ?></div>
          </div>
          <div class="col-md-6">
            <div class="text-muted small">Email</div>
            <div><?= $dataPengajuan["email"]; ?></div>
          </div>
          <div class="col-md-6">
            <div class="text-muted small">No HP / WA</div>
            <div><?= $dataPengajuan["no_hp"]; ?></div>
          </div>
          <div class="col-md-6">
            <div class="text-muted small">Alamat</div>
            <div><?= $dataPengajuan["alamat"]; ?></div>
          </div>
        </div>

        <hr>

        <div class="text-muted small">Catatan Pelanggan</div>
        <div><?= $dataPengajuan["catatan"] != "" ? $dataPengajuan["catatan"] : "-"; ?></div>

        <hr>

        <div class="d-flex gap-2 flex-wrap">
          <a class="btn btn-primary" href="../Penawaran/tambah_penawaran.php?id=<?= $idPengajuan; ?>">
            Buat Penawaran
          </a>
          <?php if ($dataPenawaran): ?>
            <a class="btn btn-outline-primary" href="../penawaran_detail.php?id=<?= $dataPenawaran["id_penawaran"]; ?>">
              Lihat Penawaran
            </a>
          <?php else: ?>
            <span class="badge bg-label-secondary">Penawaran: belum ada</span>
          <?php endif; ?>
        </div>
      </div>
    </div>

    <!-- Ubah Status -->
    <div class="card mb-3">
      <div class="card-header">
        <h5 class="mb-0">Ubah Status Pengajuan</h5>
      </div>
      <div class="card-body">
        <form action="proses_update_status.php" method="post">
          <input type="hidden" name="id_pengajuan" value="<?= $idPengajuan; ?>">

          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label">Status Pengajuan</label>
              <select name="status_pengajuan" class="form-control" required>
                <option value="">-- Pilih Status --</option>
                <option value="dikirim" <?= ($dataPengajuan["status_pengajuan"] == "dikirim") ? "selected" : ""; ?>>dikirim</option>
                <option value="diproses" <?= ($dataPengajuan["status_pengajuan"] == "diproses") ? "selected" : ""; ?>>diproses</option>
                <option value="selesai" <?= ($dataPengajuan["status_pengajuan"] == "selesai") ? "selected" : ""; ?>>selesai</option>
                <option value="ditolak" <?= ($dataPengajuan["status_pengajuan"] == "ditolak") ? "selected" : ""; ?>>ditolak</option>
              </select>
            </div>

            <div class="col-md-6 d-flex align-items-end">
              <button class="btn btn-primary" type="submit">
                <i class="bx bx-save me-1"></i> Simpan Status
              </button>
            </div>
          </div>
        </form>
      </div>
    </div>

    <!-- Data Alat -->
    <div class="card">
      <div class="card-header">
        <h5 class="mb-0">Data Alat yang Diajukan</h5>
      </div>
      <div class="card-body">

        <?php if (!$hasilAlat || mysqli_num_rows($hasilAlat) == 0): ?>
          <div class="alert alert-warning mb-0">Belum ada data alat untuk pengajuan ini.</div>
        <?php else: ?>

          <div class="table-responsive">
            <table class="table align-middle">
              <thead>
                <tr>
                  <th>No</th>
                  <th>Nama Alat</th>
                  <th>Merk / Tipe</th>
                  <th>Kapasitas</th>
                  <th>Jumlah</th>
                  <th>Parameter</th>
                  <th>Titik Ukur</th>
                  <th>Keterangan</th>
                </tr>
              </thead>
              <tbody>
                <?php $no = 1; ?>
                <?php while ($alat = mysqli_fetch_assoc($hasilAlat)): ?>
                  <tr>
                    <td><?= $no; ?></td>
                    <td><?= $alat["nama_alat"]; ?></td>
                    <td><?= $alat["merk_tipe"]; ?></td>
                    <td><?= $alat["kapasitas"]; ?></td>
                    <td><?= $alat["jumlah_unit"]; ?></td>
                    <td><?= $alat["parameter"]; ?></td>
                    <td><?= $alat["titik_ukur"]; ?></td>
                    <td><?= $alat["keterangan"] != "" ? $alat["keterangan"] : "-"; ?></td>
                  </tr>
                  <?php $no++; ?>
                <?php endwhile; ?>
              </tbody>
            </table>
          </div>

        <?php endif; ?>

      </div>
    </div>

  </div>
</div>

<?php include "../komponen/footer.php"; ?>
