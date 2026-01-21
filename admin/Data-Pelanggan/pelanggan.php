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
    tbl_pelanggan.id_pelanggan,
    tbl_pelanggan.id_user,
    tbl_pelanggan.no_hp,
    tbl_pelanggan.alamat,
    tbl_users.nama,
    tbl_users.email
  FROM tbl_pelanggan
  LEFT JOIN tbl_users
    ON tbl_users.id_user = tbl_pelanggan.id_user
  ORDER BY tbl_pelanggan.id_pelanggan DESC
";
$hasil = mysqli_query($conn, $sql);
if (!$hasil) {
  die("Query gagal: " . mysqli_error($conn));
}

// hitung total pelanggan
$sqlTotal = "
  SELECT COUNT(*) AS total
  FROM tbl_pelanggan
";
$hasilTotal = mysqli_query($conn, $sqlTotal);
$dataTotal = mysqli_fetch_assoc($hasilTotal);
$totalPelanggan = $dataTotal["total"] ?? 0;

$base = "../../";
include "../komponen/header.php";
include "../komponen/sidebar.php";
include "../komponen/navbar.php";
?>

<div class="content-wrapper">
  <div class="container-xxl flex-grow-1 container-p-y">

    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
      <div>
        <h4 class="fw-bold mb-1">Data Pelanggan</h4>
        <div class="text-muted">Halo, <b><?= $namaAdmin; ?></b> 👋</div>
      </div>

      <div class="d-flex gap-2">
        <a href="tambah_pelanggan.php" class="btn btn-primary">
          <i class="bx bx-plus me-1"></i> Tambah Pelanggan
        </a>
      </div>
    </div>

    <div class="row g-4 mb-4">
      <div class="col-sm-6 col-lg-3">
        <div class="card">
          <div class="card-body">
            <div class="d-flex align-items-center justify-content-between">
              <div>
                <span class="text-muted">Total Pelanggan</span>
                <h3 class="mb-0"><?= $totalPelanggan; ?></h3>
              </div>
              <div class="avatar">
                <span class="avatar-initial rounded bg-label-info"><i class="bx bx-user"></i></span>
              </div>
            </div>
            <small class="text-muted">Jumlah pelanggan terdaftar</small>
          </div>
        </div>
      </div>
    </div>

    <div class="card">
      <div class="card-header">
        <h5 class="mb-0">Daftar Pelanggan</h5>
      </div>

      <div class="table-responsive">
        <table class="table align-middle">
          <thead>
            <tr>
              <th>No</th>
              <th>ID Pelanggan</th>
              <th>Nama</th>
              <th>Email</th>
              <th>No HP / WA</th>
              <th>Alamat</th>
              <th style="width:180px;">Aksi</th>
            </tr>
          </thead>
          <tbody>
            <?php
            $no = 1;

            if (mysqli_num_rows($hasil) == 0) {
              echo "<tr><td colspan='7' class='text-center'>Belum ada data pelanggan.</td></tr>";
            } else {
              while ($row = mysqli_fetch_assoc($hasil)) {

                $idPelanggan = $row["id_pelanggan"];
                $nama = $row["nama"] ?? "-";
                $email = $row["email"] ?? "-";
                $noHp = $row["no_hp"] ?? "-";
                $alamat = $row["alamat"] ?? "-";

                echo "<tr>";
                echo "<td>$no</td>";
                echo "<td>$idPelanggan</td>";
                echo "<td>$nama</td>";
                echo "<td>$email</td>";
                echo "<td>$noHp</td>";
                echo "<td>$alamat</td>";
                echo "<td>
                        <a class='btn btn-sm btn-primary' href='edit_pelanggan.php?id=$idPelanggan'>Edit</a>
                        <a class='btn btn-sm btn-danger' href='hapus_pelanggan.php?id=$idPelanggan'
                           onclick=\"return confirm('Yakin hapus pelanggan ini?')\">Hapus</a>
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
