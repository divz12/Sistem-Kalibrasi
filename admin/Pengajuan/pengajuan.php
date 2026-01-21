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

$sql = "
  SELECT
    tbl_pengajuan_kalibrasi.id_pengajuan,
    tbl_pengajuan_kalibrasi.id_pelanggan,
    tbl_pengajuan_kalibrasi.tanggal_pengajuan,
    tbl_pengajuan_kalibrasi.status_pengajuan,
    tbl_pengajuan_kalibrasi.catatan,

    tbl_pelanggan.no_hp,
    tbl_pelanggan.alamat,

    tbl_users.nama,
    tbl_users.email,

    (
      SELECT COUNT(*)
      FROM tbl_pengajuan_alat
      WHERE tbl_pengajuan_alat.id_pengajuan = tbl_pengajuan_kalibrasi.id_pengajuan
    ) AS jumlah_alat,

    (
      SELECT COUNT(*)
      FROM tbl_penawaran
      WHERE tbl_penawaran.id_pengajuan = tbl_pengajuan_kalibrasi.id_pengajuan
    ) AS jumlah_penawaran

  FROM tbl_pengajuan_kalibrasi
  LEFT JOIN tbl_pelanggan
    ON tbl_pelanggan.id_pelanggan = tbl_pengajuan_kalibrasi.id_pelanggan
  LEFT JOIN tbl_users
    ON tbl_users.id_user = tbl_pelanggan.id_user
  ORDER BY tbl_pengajuan_kalibrasi.id_pengajuan DESC
";
$hasil = mysqli_query($conn, $sql);
if (!$hasil) {
  die("Query gagal: " . mysqli_error($conn));
}

// hitung total pengajuan
$sqlTotal = "SELECT COUNT(*) AS total FROM tbl_pengajuan_kalibrasi";
$hasilTotal = mysqli_query($conn, $sqlTotal);
$dataTotal = mysqli_fetch_assoc($hasilTotal);
$totalPengajuan = $dataTotal["total"] ?? 0;

// hitung total berdasarkan status
$sqlDikirim = "SELECT COUNT(*) AS total FROM tbl_pengajuan_kalibrasi WHERE status_pengajuan = 'dikirim'";
$hasilDikirim = mysqli_query($conn, $sqlDikirim);
$dataDikirim = mysqli_fetch_assoc($hasilDikirim);
$totalDikirim = $dataDikirim["total"] ?? 0;

$sqlDiproses = "SELECT COUNT(*) AS total FROM tbl_pengajuan_kalibrasi WHERE status_pengajuan = 'diproses'";
$hasilDiproses = mysqli_query($conn, $sqlDiproses);
$dataDiproses = mysqli_fetch_assoc($hasilDiproses);
$totalDiproses = $dataDiproses["total"] ?? 0;

$sqlSelesai = "SELECT COUNT(*) AS total FROM tbl_pengajuan_kalibrasi WHERE status_pengajuan = 'selesai'";
$hasilSelesai = mysqli_query($conn, $sqlSelesai);
$dataSelesai = mysqli_fetch_assoc($hasilSelesai);
$totalSelesai = $dataSelesai["total"] ?? 0;

$sqlDitolak = "SELECT COUNT(*) AS total FROM tbl_pengajuan_kalibrasi WHERE status_pengajuan = 'ditolak'";
$hasilDitolak = mysqli_query($conn, $sqlDitolak);
$dataDitolak = mysqli_fetch_assoc($hasilDitolak);
$totalDitolak = $dataDitolak["total"] ?? 0;

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
        <h4 class="fw-bold mb-1">Pengajuan Kalibrasi</h4>
        <div class="text-muted">Halo, <b><?= $namaAdmin; ?></b> 👋</div>
      </div>
    </div>

    <!-- Statistik -->
    <div class="row g-4 mb-4">
      <div class="col-sm-6 col-lg-3">
        <div class="card"><div class="card-body">
          <div class="d-flex justify-content-between align-items-center">
            <div>
              <span class="text-muted">Total</span>
              <h3 class="mb-0"><?= $totalPengajuan; ?></h3>
            </div>
            <div class="avatar">
              <span class="avatar-initial rounded bg-label-info"><i class="bx bx-list-ul"></i></span>
            </div>
          </div>
        </div></div>
      </div>

      <div class="col-sm-6 col-lg-3">
        <div class="card"><div class="card-body">
          <span class="text-muted">Dikirim</span>
          <h3 class="mb-0"><?= $totalDikirim; ?></h3>
        </div></div>
      </div>

      <div class="col-sm-6 col-lg-3">
        <div class="card"><div class="card-body">
          <span class="text-muted">Diproses</span>
          <h3 class="mb-0"><?= $totalDiproses; ?></h3>
        </div></div>
      </div>

      <div class="col-sm-6 col-lg-3">
        <div class="card"><div class="card-body">
          <span class="text-muted">Selesai / Ditolak</span>
          <h3 class="mb-0"><?= $totalSelesai; ?> / <?= $totalDitolak; ?></h3>
        </div></div>
      </div>
    </div>

    <!-- Tabel -->
    <div class="card">
      <div class="card-header">
        <h5 class="mb-0">Daftar Pengajuan</h5>
      </div>

      <div class="table-responsive">
        <table class="table align-middle">
          <thead>
            <tr>
              <th>No</th>
              <th>ID Pengajuan</th>
              <th>Tanggal</th>
              <th>Pelanggan</th>
              <th>Status</th>
              <th>Jumlah Alat</th>
              <th>Penawaran</th>
              <th style="width:220px;">Aksi</th>
            </tr>
          </thead>
          <tbody>
            <?php
            $no = 1;

            if (mysqli_num_rows($hasil) == 0) {
              echo "<tr><td colspan='8' class='text-center'>Belum ada pengajuan.</td></tr>";
            } else {
              while ($row = mysqli_fetch_assoc($hasil)) {

                $idPengajuan = $row["id_pengajuan"];
                $tanggal = $row["tanggal_pengajuan"] ?? "-";
                $nama = $row["nama"] ?? "-";
                $email = $row["email"] ?? "-";
                $status = $row["status_pengajuan"] ?? "-";
                $jumlahAlat = $row["jumlah_alat"] ?? 0;
                $jumlahPenawaran = $row["jumlah_penawaran"] ?? 0;

                echo "<tr>";
                echo "<td>$no</td>";
                echo "<td>#".$idPengajuan."</td>";
                echo "<td>".$tanggal."</td>";
                echo "<td><b>".$nama."</b><br><small class='text-muted'>".$email."</small></td>";
                echo "<td><span class='badge ".badgeStatus($status)."'>".$status."</span></td>";
                echo "<td>".$jumlahAlat."</td>";
                echo "<td>".($jumlahPenawaran > 0 ? "Ada" : "Belum")."</td>";

                $aksiBtn = "<a class='btn btn-sm btn-primary' href='detail_pengajuan.php?id=".$idPengajuan."'>Detail</a>";

                if (strtolower($status) == "diproses") {
                  $aksiBtn .= " <a class='btn btn-sm btn-outline-primary' href='../Penawaran/tambah_penawaran.php?id=".$idPengajuan."'>Buat Penawaran</a>";
                }

                /* $aksiBtn .= " <a class='btn btn-sm btn-danger' href='hapus_pengajuan.php?id=".$idPengajuan."'
                           onclick=\"return confirm('Yakin hapus pengajuan ini?')\">Hapus</a>";
                */

                echo "<td>".$aksiBtn."</td>";

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
