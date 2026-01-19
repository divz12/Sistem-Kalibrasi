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

$namaAdmin = $_SESSION["nama"] ?? "Admin";

/* ambil data penawaran + pengajuan + pelanggan + user */
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
    tbl_pengajuan_kalibrasi.catatan,

    tbl_pelanggan.id_pelanggan,
    tbl_pelanggan.no_hp,
    tbl_pelanggan.alamat,

    tbl_users.nama,
    tbl_users.email

  FROM tbl_penawaran
  LEFT JOIN tbl_pengajuan_kalibrasi
    ON tbl_pengajuan_kalibrasi.id_pengajuan = tbl_penawaran.id_pengajuan
  LEFT JOIN tbl_pelanggan
    ON tbl_pelanggan.id_pelanggan = tbl_pengajuan_kalibrasi.id_pelanggan
  LEFT JOIN tbl_users
    ON tbl_users.id_user = tbl_pelanggan.id_user
  ORDER BY tbl_penawaran.id_penawaran DESC
";
$hasil = mysqli_query($conn, $sql);
if (!$hasil) {
  die("Query gagal: " . mysqli_error($conn));
}

/* total penawaran */
$sqlTotal = "SELECT COUNT(*) AS total FROM tbl_penawaran";
$hasilTotal = mysqli_query($conn, $sqlTotal);
$dataTotal = mysqli_fetch_assoc($hasilTotal);
$totalPenawaran = $dataTotal["total"] ?? 0;

function badgeStatusPenawaran($status)
{
  if ($status == "dikirim") return "bg-label-primary";
  if ($status == "diterima") return "bg-label-success";
  if ($status == "ditolak") return "bg-label-danger";
  if ($status == "negosiasi") return "bg-label-warning";
  return "bg-label-secondary";
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
        <h4 class="fw-bold mb-1">Data Penawaran</h4>
        <div class="text-muted">Halo, <b><?= $namaAdmin; ?></b> 👋</div>
      </div>

      <div class="d-flex gap-2">
        <a href="tambah_penawaran.php" class="btn btn-primary">
          <i class="bx bx-plus me-1"></i> Tambah Penawaran
        </a>
      </div>
    </div>

    <div class="row g-4 mb-4">
      <div class="col-sm-6 col-lg-3">
        <div class="card">
          <div class="card-body">
            <div class="d-flex align-items-center justify-content-between">
              <div>
                <span class="text-muted">Total Penawaran</span>
                <h3 class="mb-0"><?= $totalPenawaran; ?></h3>
              </div>
              <div class="avatar">
                <span class="avatar-initial rounded bg-label-info"><i class="bx bx-receipt"></i></span>
              </div>
            </div>
            <small class="text-muted">Jumlah penawaran yang dibuat</small>
          </div>
        </div>
      </div>
    </div>

    <div class="card">
      <div class="card-header">
        <h5 class="mb-0">Daftar Penawaran</h5>
      </div>

      <div class="table-responsive">
        <table class="table align-middle">
          <thead>
            <tr>
              <th>No</th>
              <th>ID Penawaran</th>
              <th>ID Pengajuan</th>
              <th>Pelanggan</th>
              <th>Tanggal Penawaran</th>
              <th>Total Biaya</th>
              <th>Status Penawaran</th>
              <th style="width:240px;">Aksi</th>
            </tr>
          </thead>
          <tbody>
            <?php
            $no = 1;

            if (mysqli_num_rows($hasil) == 0) {
              echo "<tr><td colspan='8' class='text-center'>Belum ada penawaran.</td></tr>";
            } else {
              while ($row = mysqli_fetch_assoc($hasil)) {

                $idPenawaran = $row["id_penawaran"];
                $idPengajuan = $row["id_pengajuan"];
                $nama = $row["nama"] ?? "-";
                $email = $row["email"] ?? "-";
                $tanggalPenawaran = $row["tanggal_penawaran"] ?? "-";
                $totalBiaya = $row["total_biaya"] ?? 0;
                $statusPenawaran = $row["status_penawaran"] ?? "-";

                echo "<tr>";
                echo "<td>$no</td>";
                echo "<td>$idPenawaran</td>";
                echo "<td>#".$idPengajuan."</td>";
                echo "<td><b>".$nama."</b><br><small class='text-muted'>".$email."</small></td>";
                echo "<td>".$tanggalPenawaran."</td>";
                echo "<td>Rp ".number_format($totalBiaya, 0, ',', '.')."</td>";
                echo "<td><span class='badge ".badgeStatusPenawaran($statusPenawaran)."'>".$statusPenawaran."</span></td>";
                echo "<td>
                        <a class='btn btn-sm btn-primary' href='detail_penawaran.php?id=".$idPenawaran."'>Detail</a>
                        <a class='btn btn-sm btn-outline-primary' href='edit_penawaran.php?id=".$idPenawaran."'>Edit</a>
                        <a class='btn btn-sm btn-danger' href='hapus_penawaran.php?id=".$idPenawaran."'
                           onclick=\"return confirm('Yakin hapus penawaran ini?')\">Hapus</a>
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
