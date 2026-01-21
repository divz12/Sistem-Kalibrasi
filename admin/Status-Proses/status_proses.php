<?php
session_start();
include "../../koneksi.php";

$role = $_SESSION["role"] ?? "";

if (!isset($_SESSION["id_user"])) {
  header("Location: ../../login.php");
  exit();
}

if ($role != "admin" && $role != "cs") {
  header("Location: ../../login.php");
  exit();
}

$namaAdmin = $_SESSION["nama"] ?? "Admin";

$statusPengajuan = $_GET["status_pengajuan"] ?? "";
$statusPenawaran = $_GET["status_penawaran"] ?? "";

// hitung total pengajuan
$sqlTotalPengajuan = "SELECT COUNT(*) AS total FROM tbl_pengajuan_kalibrasi";
$hasilTotalPengajuan = mysqli_query($conn, $sqlTotalPengajuan);
$dataTotalPengajuan = mysqli_fetch_assoc($hasilTotalPengajuan);
$totalPengajuan = $dataTotalPengajuan["total"] ?? 0;

// hitung total dikirim
$sqlTotalDikirim = "
  SELECT COUNT(*) AS total
  FROM tbl_pengajuan_kalibrasi
  WHERE LOWER(tbl_pengajuan_kalibrasi.status_pengajuan) = 'dikirim'
";
$hasilTotalDikirim = mysqli_query($conn, $sqlTotalDikirim);
$dataTotalDikirim = mysqli_fetch_assoc($hasilTotalDikirim);
$totalDikirim = $dataTotalDikirim["total"] ?? 0;

// hitung total diproses
$sqlTotalDiproses = "
  SELECT COUNT(*) AS total
  FROM tbl_pengajuan_kalibrasi
  WHERE LOWER(tbl_pengajuan_kalibrasi.status_pengajuan) = 'diproses'
";
$hasilTotalDiproses = mysqli_query($conn, $sqlTotalDiproses);
$dataTotalDiproses = mysqli_fetch_assoc($hasilTotalDiproses);
$totalDiproses = $dataTotalDiproses["total"] ?? 0;

// hitung total selesai
$sqlTotalSelesai = "
  SELECT COUNT(*) AS total
  FROM tbl_pengajuan_kalibrasi
  WHERE LOWER(tbl_pengajuan_kalibrasi.status_pengajuan) = 'selesai'
";
$hasilTotalSelesai = mysqli_query($conn, $sqlTotalSelesai);
$dataTotalSelesai = mysqli_fetch_assoc($hasilTotalSelesai);
$totalSelesai = $dataTotalSelesai["total"] ?? 0;

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
  WHERE 1 = 1
";

if ($statusPengajuan != "") {
  $sql .= " AND LOWER(tbl_pengajuan_kalibrasi.status_pengajuan) = LOWER('$statusPengajuan') ";
}

if ($statusPenawaran != "") {
  $sql .= " AND LOWER(tbl_penawaran.status_penawaran) = LOWER('$statusPenawaran') ";
}

$sql .= " ORDER BY tbl_pengajuan_kalibrasi.id_pengajuan DESC ";

$hasil = mysqli_query($conn, $sql);
if (!$hasil) {
  die("Query gagal: " . mysqli_error($conn));
}

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

function tahapProses($statusPengajuanLower, $statusPenawaranLower)
{
  $tahap = "Pengajuan Masuk";

  if ($statusPengajuanLower == "dikirim") {
    $tahap = "Pengajuan Masuk";
  }

  if ($statusPenawaranLower == "dikirim" || $statusPenawaranLower == "negosiasi") {
    $tahap = "Penawaran";
  }

  if ($statusPenawaranLower == "diterima" || $statusPengajuanLower == "diproses") {
    $tahap = "Proses Kalibrasi";
  }

  if ($statusPengajuanLower == "selesai") {
    $tahap = "Dokumen";
  }

  if ($statusPengajuanLower == "ditolak" || $statusPenawaranLower == "ditolak") {
    $tahap = "Dihentikan (Ditolak)";
  }

  return $tahap;
}

$base = "../../";
include "../komponen/header.php";
include "../komponen/sidebar.php";
include "../komponen/navbar.php";
?>

<div class="content-wrapper">
  <div class="container-xxl flex-grow-1 container-p-y">

    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
      <div>
        <h4 class="fw-bold mb-1">Status Proses (Admin)</h4>
        <div class="text-muted">Halo, <b><?= $namaAdmin; ?></b> 👋</div>
      </div>
    </div>

    <!-- Stat cards -->
    <div class="row g-4 mb-4">
      <div class="col-sm-6 col-lg-3">
        <div class="card">
          <div class="card-body">
            <span class="text-muted">Total Pengajuan</span>
            <h3 class="mb-0"><?= $totalPengajuan; ?></h3>
          </div>
        </div>
      </div>

      <div class="col-sm-6 col-lg-3">
        <div class="card">
          <div class="card-body">
            <span class="text-muted">Dikirim</span>
            <h3 class="mb-0"><?= $totalDikirim; ?></h3>
          </div>
        </div>
      </div>

      <div class="col-sm-6 col-lg-3">
        <div class="card">
          <div class="card-body">
            <span class="text-muted">Diproses</span>
            <h3 class="mb-0"><?= $totalDiproses; ?></h3>
          </div>
        </div>
      </div>

      <div class="col-sm-6 col-lg-3">
        <div class="card">
          <div class="card-body">
            <span class="text-muted">Selesai</span>
            <h3 class="mb-0"><?= $totalSelesai; ?></h3>
          </div>
        </div>
      </div>
    </div>

    <!-- Filter sederhana -->
    <div class="card mb-3">
      <div class="card-body">
        <form method="get" action="">
          <div class="row g-2 align-items-end">
            <div class="col-md-4">
              <label class="form-label">Filter Status Pengajuan</label>
              <select name="status_pengajuan" class="form-control">
                <option value="">-- semua --</option>
                <option value="dikirim" <?= ($statusPengajuan == "dikirim") ? "selected" : ""; ?>>dikirim</option>
                <option value="diproses" <?= ($statusPengajuan == "diproses") ? "selected" : ""; ?>>diproses</option>
                <option value="selesai" <?= ($statusPengajuan == "selesai") ? "selected" : ""; ?>>selesai</option>
                <option value="ditolak" <?= ($statusPengajuan == "ditolak") ? "selected" : ""; ?>>ditolak</option>
              </select>
            </div>

            <div class="col-md-4">
              <label class="form-label">Filter Status Penawaran</label>
              <select name="status_penawaran" class="form-control">
                <option value="">-- semua --</option>
                <option value="dikirim" <?= ($statusPenawaran == "dikirim") ? "selected" : ""; ?>>dikirim</option>
                <option value="negosiasi" <?= ($statusPenawaran == "negosiasi") ? "selected" : ""; ?>>negosiasi</option>
                <option value="diterima" <?= ($statusPenawaran == "diterima") ? "selected" : ""; ?>>diterima</option>
                <option value="ditolak" <?= ($statusPenawaran == "ditolak") ? "selected" : ""; ?>>ditolak</option>
              </select>
            </div>

            <div class="col-md-4 d-grid">
              <button class="btn btn-outline-primary" type="submit">
                <i class="bx bx-filter me-1"></i> Terapkan Filter
              </button>
            </div>
          </div>
        </form>
      </div>
    </div>

    <!-- Tabel -->
    <div class="card">
      <div class="card-header">
        <h5 class="mb-0">Daftar Status Proses</h5>
      </div>

      <div class="table-responsive">
        <table class="table align-middle">
          <thead class="table-light">
            <tr>
              <th>No</th>
              <th>ID Pengajuan</th>
              <th>Pelanggan</th>
              <th>Tanggal Pengajuan</th>
              <th>Status Pengajuan</th>
              <th>Status Penawaran</th>
              <th>Tahap Proses</th>
              <th style="width:220px;">Aksi</th>
            </tr>
          </thead>
          <tbody>
            <?php
            $no = 1;

            if (mysqli_num_rows($hasil) == 0) {
              echo "<tr><td colspan='8' class='text-center'>Data belum ada.</td></tr>";
            } else {
              while ($row = mysqli_fetch_assoc($hasil)) {

                $idPengajuan = $row["id_pengajuan"];
                $nama = $row["nama"] ?? "-";
                $email = $row["email"] ?? "-";

                $statusPengajuanText = $row["status_pengajuan"] ?? "-";
                $statusPengajuanLower = $row["status_pengajuan_lower"] ?? "";

                $statusPenawaranText = $row["status_penawaran"] ?? "belum ada";
                $statusPenawaranLower = $row["status_penawaran_lower"] ?? "";

                $tahap = tahapProses($statusPengajuanLower, $statusPenawaranLower);

                echo "<tr>";
                echo "<td>".$no."</td>";
                echo "<td>#".$idPengajuan."</td>";
                echo "<td><b>".$nama."</b><br><small class='text-muted'>".$email."</small></td>";
                echo "<td>".$row["tanggal_pengajuan"]."</td>";
                echo "<td><span class='badge ".badgePengajuan($statusPengajuanLower)."'>".$statusPengajuanText."</span></td>";

                if ($row["id_penawaran"] != "") {
                  echo "<td><span class='badge ".badgePenawaran($statusPenawaranLower)."'>".$statusPenawaranText."</span></td>";
                } else {
                  echo "<td><span class='badge bg-label-secondary'>belum ada</span></td>";
                }

                echo "<td>".$tahap."</td>";

                echo "<td>
                        <a class='btn btn-sm btn-primary' href='status_detail.php?id=".$idPengajuan."'>Detail</a>
                      </td>";

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
