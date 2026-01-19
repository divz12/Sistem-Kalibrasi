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

/* Ambil semua alat pengajuan + info pengajuan + pelanggan */
$sql = "
  SELECT
    tbl_pengajuan_alat.id_alat,
    tbl_pengajuan_alat.id_pengajuan,
    tbl_pengajuan_alat.nama_alat,
    tbl_pengajuan_alat.merk_tipe,
    tbl_pengajuan_alat.kapasitas,
    tbl_pengajuan_alat.jumlah_unit,
    tbl_pengajuan_alat.parameter,
    tbl_pengajuan_alat.titik_ukur,
    tbl_pengajuan_alat.keterangan,

    tbl_pengajuan_kalibrasi.tanggal_pengajuan,
    tbl_pengajuan_kalibrasi.status_pengajuan,

    tbl_pelanggan.id_pelanggan,
    tbl_pelanggan.no_hp,
    tbl_pelanggan.alamat,

    tbl_users.nama,
    tbl_users.email

  FROM tbl_pengajuan_alat
  LEFT JOIN tbl_pengajuan_kalibrasi
    ON tbl_pengajuan_kalibrasi.id_pengajuan = tbl_pengajuan_alat.id_pengajuan
  LEFT JOIN tbl_pelanggan
    ON tbl_pelanggan.id_pelanggan = tbl_pengajuan_kalibrasi.id_pelanggan
  LEFT JOIN tbl_users
    ON tbl_users.id_user = tbl_pelanggan.id_user
  ORDER BY tbl_pengajuan_alat.id_alat DESC
";
$hasil = mysqli_query($conn, $sql);
if (!$hasil) {
  die("Query gagal: " . mysqli_error($conn));
}

/* Total alat */
$sqlTotal = "SELECT COUNT(*) AS total FROM tbl_pengajuan_alat";
$hasilTotal = mysqli_query($conn, $sqlTotal);
$dataTotal = mysqli_fetch_assoc($hasilTotal);
$totalAlat = $dataTotal["total"] ?? 0;

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

    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
      <div>
        <h4 class="fw-bold mb-1">Data Alat Pengajuan</h4>
        <div class="text-muted">Halo, <b><?= $namaAdmin; ?></b> 👋</div>
      </div>
    </div>

    <!-- Stat -->
    <div class="row g-4 mb-4">
      <div class="col-sm-6 col-lg-3">
        <div class="card">
          <div class="card-body">
            <div class="d-flex align-items-center justify-content-between">
              <div>
                <span class="text-muted">Total Alat</span>
                <h3 class="mb-0"><?= $totalAlat; ?></h3>
              </div>
              <div class="avatar">
                <span class="avatar-initial rounded bg-label-info"><i class="bx bx-cube"></i></span>
              </div>
            </div>
            <small class="text-muted">Jumlah data alat yang masuk</small>
          </div>
        </div>
      </div>
    </div>

    <!-- Tabel -->
    <div class="card">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Daftar Alat</h5>
      </div>

      <div class="table-responsive">
        <table class="table align-middle">
          <thead>
            <tr>
              <th>No</th>
              <th>ID Alat</th>
              <th>ID Pengajuan</th>
              <th>Pelanggan</th>
              <th>Nama Alat</th>
              <th>Jumlah</th>
              <th>Status Pengajuan</th>
              <th style="width:220px;">Aksi</th>
            </tr>
          </thead>
          <tbody>
            <?php
            $no = 1;

            if (mysqli_num_rows($hasil) == 0) {
              echo "<tr><td colspan='10' class='text-center'>Belum ada data alat.</td></tr>";
            } else {
              while ($row = mysqli_fetch_assoc($hasil)) {

                $idAlat = $row["id_alat"];
                $idPengajuan = $row["id_pengajuan"];
                $nama = $row["nama"] ?? "-";
                $email = $row["email"] ?? "-";
                $namaAlat = $row["nama_alat"] ?? "-";
                $jumlah = $row["jumlah_unit"] ?? 0;
                $statusPengajuan = $row["status_pengajuan"] ?? "-";

                echo "<tr>";
                echo "<td>$no</td>";
                echo "<td>$idAlat</td>";
                echo "<td>#".$idPengajuan."</td>";
                echo "<td><b>".$nama."</b><br><small class='text-muted'>".$email."</small></td>";
                echo "<td>".$namaAlat."</td>";
                echo "<td>".$jumlah."</td>";
                echo "<td><span class='badge ".badgeStatus($statusPengajuan)."'>".$statusPengajuan."</span></td>";
                echo "<td>
                        <a class='btn btn-sm btn-primary' href='detail_alat.php?id=".$idAlat."'>Detail</a>
                        <a class='btn btn-sm btn-outline-primary' href='edit_alat.php?id=".$idAlat."'>Edit</a>
                        <a class='btn btn-sm btn-danger' href='hapus_alat.php?id=".$idAlat."'
                           onclick=\"return confirm('Yakin hapus data alat ini?')\">Hapus</a>
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
