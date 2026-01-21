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
    tbl_sertifikat.id_sertifikat,
    tbl_sertifikat.id_pengajuan,
    tbl_sertifikat.nomor_sertifikat,
    tbl_sertifikat.tanggal_terbit,
    tbl_sertifikat.nama_file_sertifikat,
    tbl_sertifikat.lokasi_file_sertifikat,
    tbl_sertifikat.keterangan_sertifikat,

    tbl_pengajuan_kalibrasi.tanggal_pengajuan,
    tbl_pengajuan_kalibrasi.status_pengajuan,

    tbl_pelanggan.id_pelanggan,
    tbl_users.nama,
    tbl_users.email

  FROM tbl_sertifikat
  LEFT JOIN tbl_pengajuan_kalibrasi
    ON tbl_pengajuan_kalibrasi.id_pengajuan = tbl_sertifikat.id_pengajuan
  LEFT JOIN tbl_pelanggan
    ON tbl_pelanggan.id_pelanggan = tbl_pengajuan_kalibrasi.id_pelanggan
  LEFT JOIN tbl_users
    ON tbl_users.id_user = tbl_pelanggan.id_user
  ORDER BY tbl_sertifikat.id_sertifikat DESC
";
$hasil = mysqli_query($conn, $sql);
if (!$hasil) {
  die("Query gagal: " . mysqli_error($conn));
}

// hitung total sertifikat
$sqlTotal = "SELECT COUNT(*) AS total FROM tbl_sertifikat";
$hasilTotal = mysqli_query($conn, $sqlTotal);
$dataTotal = mysqli_fetch_assoc($hasilTotal);
$totalSertifikat = $dataTotal["total"] ?? 0;

$base = "../../";
include "../komponen/header.php";
include "../komponen/sidebar.php";
include "../komponen/navbar.php";
?>

<div class="content-wrapper">
  <div class="container-xxl flex-grow-1 container-p-y">

    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
      <div>
        <h4 class="fw-bold mb-1">Sertifikat</h4>
        <div class="text-muted">Halo, <b><?= $namaAdmin; ?></b> 👋</div>
      </div>

      <div class="d-flex gap-2">
        <a href="tambah_sertifikat.php" class="btn btn-primary">
          <i class="bx bx-plus me-1"></i> Tambah Sertifikat
        </a>
      </div>
    </div>

    <div class="row g-4 mb-4">
      <div class="col-sm-6 col-lg-3">
        <div class="card">
          <div class="card-body">
            <span class="text-muted">Total Sertifikat</span>
            <h3 class="mb-0"><?= $totalSertifikat; ?></h3>
            <small class="text-muted">Jumlah sertifikat yang tersimpan</small>
          </div>
        </div>
      </div>
    </div>

    <div class="card">
      <div class="card-header">
        <h5 class="mb-0">Daftar Sertifikat</h5>
      </div>

      <div class="table-responsive">
        <table class="table align-middle">
          <thead class="table-light">
            <tr>
              <th>No</th>
              <th>ID Sertifikat</th>
              <th>ID Pengajuan</th>
              <th>Pelanggan</th>
              <th>Nomor Sertifikat</th>
              <th>Tanggal Terbit</th>
              <th>File</th>
              <th style="width:220px;">Aksi</th>
            </tr>
          </thead>
          <tbody>
            <?php
            $no = 1;

            if (mysqli_num_rows($hasil) == 0) {
              echo "<tr><td colspan='8' class='text-center'>Belum ada sertifikat.</td></tr>";
            } else {
              while ($row = mysqli_fetch_assoc($hasil)) {

                $idSertifikat = $row["id_sertifikat"];
                $idPengajuan = $row["id_pengajuan"];

                $nama = $row["nama"] ?? "-";
                $email = $row["email"] ?? "-";

                $nomor = $row["nomor_sertifikat"] ?? "-";
                $tanggal = $row["tanggal_terbit"] ?? "-";

                $namaFile = $row["nama_file_sertifikat"] ?? "-";
                $lokasiFile = $row["lokasi_file_sertifikat"] ?? "";

                echo "<tr>";
                echo "<td>".$no."</td>";
                echo "<td>".$idSertifikat."</td>";
                echo "<td>#".$idPengajuan."</td>";
                echo "<td><b>".$nama."</b><br><small class='text-muted'>".$email."</small></td>";
                echo "<td>".$nomor."</td>";
                echo "<td>".$tanggal."</td>";

                if ($lokasiFile != "") {
                  echo "<td><a class='btn btn-sm btn-outline-success' href='".$lokasiFile."' target='_blank'>Unduh</a></td>";
                } else {
                  echo "<td>-</td>";
                }

                echo "<td>
                        <a class='btn btn-sm btn-warning' href='edit_sertifikat.php?id=".$idSertifikat."'>Edit</a>
                        <a class='btn btn-sm btn-danger' href='hapus_sertifikat.php?id=".$idSertifikat."'
                          onclick=\"return confirm('Yakin hapus sertifikat ini?')\">Hapus</a>
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
