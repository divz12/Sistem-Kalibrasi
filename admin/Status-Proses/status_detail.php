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

$idPengajuan = (int)($_GET["id"] ?? 0);
if ($idPengajuan <= 0) {
  header("Location: status_proses.php");
  exit();
}

/* ambil data pengajuan + penawaran + pelanggan + user */
$sql = "
  SELECT
    tbl_pengajuan_kalibrasi.id_pengajuan,
    tbl_pengajuan_kalibrasi.tanggal_pengajuan,
    tbl_pengajuan_kalibrasi.status_pengajuan,
    LOWER(tbl_pengajuan_kalibrasi.status_pengajuan) AS status_pengajuan_lower,
    tbl_pengajuan_kalibrasi.catatan,

    tbl_penawaran.id_penawaran,
    tbl_penawaran.tanggal_penawaran,
    tbl_penawaran.total_biaya,
    tbl_penawaran.rincian,
    tbl_penawaran.status_penawaran,
    LOWER(tbl_penawaran.status_penawaran) AS status_penawaran_lower,

    tbl_pelanggan.id_pelanggan,
    tbl_pelanggan.no_hp,
    tbl_pelanggan.alamat,

    tbl_users.nama,
    tbl_users.email

  FROM tbl_pengajuan_kalibrasi
  LEFT JOIN tbl_penawaran
    ON tbl_penawaran.id_pengajuan = tbl_pengajuan_kalibrasi.id_pengajuan
  LEFT JOIN tbl_pelanggan
    ON tbl_pelanggan.id_pelanggan = tbl_pengajuan_kalibrasi.id_pelanggan
  LEFT JOIN tbl_users
    ON tbl_users.id_user = tbl_pelanggan.id_user
  WHERE tbl_pengajuan_kalibrasi.id_pengajuan = '$idPengajuan'
  LIMIT 1
";
$hasil = mysqli_query($conn, $sql);
$data = mysqli_fetch_assoc($hasil);

if (!$data) {
  header("Location: status_proses.php");
  exit();
}

/* ambil data alat pada pengajuan */
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

function badgePengajuan($statusLower)
{
  if ($statusLower == "dikirim") return "bg-label-primary";
  if ($statusLower == "diproses") return "bg-label-warning";
  if ($statusLower == "selesai") return "bg-label-success";
  if ($statusLower == "ditolak") return "bg-label-danger";
  return "bg-label-secondary";
}

function badgePenawaran($statusLower)
{
  if ($statusLower == "dikirim") return "bg-label-primary";
  if ($statusLower == "diterima") return "bg-label-success";
  if ($statusLower == "ditolak") return "bg-label-danger";
  if ($statusLower == "negosiasi") return "bg-label-warning";
  return "bg-label-secondary";
}

/* timeline sederhana */
function tahapAktif($statusPengajuanLower, $statusPenawaranLower)
{
  $aktif = 1;

  if ($statusPengajuanLower == "dikirim") $aktif = 1;
  if ($statusPenawaranLower == "dikirim" || $statusPenawaranLower == "negosiasi") $aktif = 2;
  if ($statusPenawaranLower == "diterima" || $statusPengajuanLower == "diproses") $aktif = 3;
  if ($statusPengajuanLower == "selesai") $aktif = 4;

  if ($statusPengajuanLower == "ditolak" || $statusPenawaranLower == "ditolak") $aktif = 2;

  return $aktif;
}

$timeline = [
  ["judul" => "Pengajuan Masuk", "desc" => "Pengajuan masuk ke sistem."],
  ["judul" => "Penawaran", "desc" => "Admin/CS menyiapkan dan mengirim penawaran."],
  ["judul" => "Proses Kalibrasi", "desc" => "Alat diproses sesuai prosedur kalibrasi."],
  ["judul" => "Dokumen", "desc" => "Sertifikat dan invoice disiapkan jika sudah selesai."],
];

$aktif = tahapAktif($data["status_pengajuan_lower"] ?? "", $data["status_penawaran_lower"] ?? "");

$base = "../../";
include "../komponen/header.php";
include "../komponen/sidebar.php";
include "../komponen/navbar.php";
?>

<div class="content-wrapper">
  <div class="container-xxl flex-grow-1 container-p-y">

    <div class="d-flex justify-content-between align-items-center mb-3">
      <div>
        <h4 class="fw-bold mb-1">Detail Status Proses</h4>
        <p class="text-muted mb-0">Pengajuan #<?= $data["id_pengajuan"]; ?></p>
      </div>
      <a href="status_proses.php" class="btn btn-outline-primary">
        <i class="bx bx-arrow-back me-1"></i> Kembali
      </a>
    </div>

    <div class="card mb-3">
      <div class="card-body">

        <div class="row g-3">
          <div class="col-md-4">
            <div class="text-muted small">Pelanggan</div>
            <div class="fw-semibold"><?= $data["nama"] ?? "-"; ?></div>
            <div class="text-muted small"><?= $data["email"] ?? "-"; ?></div>
          </div>

          <div class="col-md-4">
            <div class="text-muted small">Kontak</div>
            <div><?= $data["no_hp"] ?? "-"; ?></div>
            <div class="text-muted small"><?= $data["alamat"] ?? "-"; ?></div>
          </div>

          <div class="col-md-4">
            <div class="text-muted small">Tanggal Pengajuan</div>
            <div><?= $data["tanggal_pengajuan"] ?? "-"; ?></div>

            <div class="mt-2">
              <span class="badge <?= badgePengajuan($data["status_pengajuan_lower"] ?? ""); ?>">
                Status Pengajuan: <?= $data["status_pengajuan"] ?? "-"; ?>
              </span>
            </div>

            <div class="mt-2">
              <?php if (($data["id_penawaran"] ?? "") != ""): ?>
                <span class="badge <?= badgePenawaran($data["status_penawaran_lower"] ?? ""); ?>">
                  Status Penawaran: <?= $data["status_penawaran"] ?? "-"; ?>
                </span>
              <?php else: ?>
                <span class="badge bg-label-secondary">Penawaran: belum ada</span>
              <?php endif; ?>
            </div>
          </div>
        </div>

        <?php if (($data["catatan"] ?? "") != ""): ?>
          <div class="mt-3 p-3 bg-light rounded">
            <div class="fw-semibold mb-1">Catatan Pengajuan</div>
            <div><?= $data["catatan"]; ?></div>
          </div>
        <?php endif; ?>

        <hr class="my-4">

        <!-- Timeline -->
        <div class="row g-3">
          <?php foreach ($timeline as $i => $t): ?>
            <?php $step = $i + 1; ?>
            <div class="col-md-3">
              <div class="border rounded-3 p-3 h-100 <?= ($step <= $aktif) ? "bg-white" : "bg-light"; ?>">
                <div class="d-flex justify-content-between align-items-center mb-2">
                  <span class="badge <?= ($step <= $aktif) ? "bg-success" : "bg-secondary"; ?>"><?= $step; ?></span>

                  <?php if ($step == $aktif): ?>
                    <span class="badge bg-warning text-dark">Sedang berjalan</span>
                  <?php elseif ($step < $aktif): ?>
                    <span class="badge bg-success">Selesai</span>
                  <?php else: ?>
                    <span class="badge bg-secondary">Menunggu</span>
                  <?php endif; ?>
                </div>

                <div class="fw-semibold"><?= $t["judul"]; ?></div>
                <div class="text-muted small"><?= $t["desc"]; ?></div>
              </div>
            </div>
          <?php endforeach; ?>
        </div>

        <div class="d-flex gap-2 mt-4 flex-wrap">
          <a class="btn btn-primary" href="../Pengajuan/detail_pengajuan.php?id=<?= $data["id_pengajuan"]; ?>">
            Lihat Detail Pengajuan
          </a>

          <a class="btn btn-outline-primary" href="../Penawaran/tambah_penawaran.php?id_pengajuan=<?= $data["id_pengajuan"]; ?>">
            Buat Penawaran
          </a>

          <?php if (($data["id_penawaran"] ?? "") != ""): ?>
            <a class="btn btn-outline-primary" href="../Penawaran/detail_penawaran.php?id=<?= $data["id_penawaran"]; ?>">
              Lihat Penawaran
            </a>
          <?php endif; ?>
        </div>

      </div>
    </div>

    <!-- Data Alat -->
    <div class="card">
      <div class="card-header">
        <h5 class="mb-0">Data Alat Pengajuan</h5>
      </div>

      <div class="table-responsive">
        <table class="table align-middle">
          <thead class="table-light">
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
            <?php
            $no = 1;

            if (!$hasilAlat || mysqli_num_rows($hasilAlat) == 0) {
              echo "<tr><td colspan='8' class='text-center'>Data alat belum ada.</td></tr>";
            } else {
              while ($a = mysqli_fetch_assoc($hasilAlat)) {
                echo "<tr>";
                echo "<td>".$no."</td>";
                echo "<td>".$a["nama_alat"]."</td>";
                echo "<td>".$a["merk_tipe"]."</td>";
                echo "<td>".$a["kapasitas"]."</td>";
                echo "<td>".$a["jumlah_unit"]."</td>";
                echo "<td>".$a["parameter"]."</td>";
                echo "<td>".$a["titik_ukur"]."</td>";
                echo "<td>".$a["keterangan"]."</td>";
                echo "</tr>";
                $no++;
              }
            }
            ?>
          </tbody>
        </table>
      </div>
    </div>

  </div>
</div>

<?php include "../komponen/footer.php"; ?>
