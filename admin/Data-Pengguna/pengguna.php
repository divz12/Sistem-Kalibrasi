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
    tbl_users.id_user,
    tbl_users.nama,
    tbl_users.email,
    tbl_users.role,
    tbl_users.created_at,
    tbl_users.updated_at
  FROM tbl_users
  ORDER BY tbl_users.id_user DESC
";
$hasil = mysqli_query($conn, $sql);
if (!$hasil) {
  die("Query gagal: " . mysqli_error($conn));
}

// hitung total pengguna
$sqlTotal = "
  SELECT COUNT(*) AS total
  FROM tbl_users
";
$hasilTotal = mysqli_query($conn, $sqlTotal);
$dataTotal = mysqli_fetch_assoc($hasilTotal);
$totalUser = $dataTotal["total"] ?? 0;

$base = "../../";
include "../komponen/header.php";
include "../komponen/sidebar.php";
include "../komponen/navbar.php";
?>

<div class="content-wrapper">
  <div class="container-xxl flex-grow-1 container-p-y">

    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
      <div>
        <h4 class="fw-bold mb-1">Data Pengguna</h4>
        <div class="text-muted">Halo, <b><?= $namaAdmin; ?></b> 👋</div>
      </div>

      <div class="d-flex gap-2">
        <a href="tambah_pengguna.php" class="btn btn-primary">
          <i class="bx bx-plus me-1"></i> Tambah Pengguna
        </a>
      </div>
    </div>

    <div class="row g-4 mb-4">
      <div class="col-sm-6 col-lg-3">
        <div class="card">
          <div class="card-body">
            <div class="d-flex align-items-center justify-content-between">
              <div>
                <span class="text-muted">Total Pengguna</span>
                <h3 class="mb-0"><?= $totalUser; ?></h3>
              </div>
              <div class="avatar">
                <span class="avatar-initial rounded bg-label-primary"><i class="bx bx-user"></i></span>
              </div>
            </div>
            <small class="text-muted">Jumlah akun di sistem</small>
          </div>
        </div>
      </div>
    </div>

    <div class="card">
      <div class="card-header">
        <h5 class="mb-0">Daftar Pengguna</h5>
      </div>

      <div class="table-responsive">
        <table class="table align-middle">
          <thead>
            <tr>
              <th>No</th>
              <th>ID User</th>
              <th>Nama</th>
              <th>Email</th>
              <th>Role</th>
              <th>Dibuat</th>
              <th style="width:180px;">Aksi</th>
            </tr>
          </thead>
          <tbody>
            <?php
            $no = 1;

            if (mysqli_num_rows($hasil) == 0) {
              echo "<tr><td colspan='7' class='text-center'>Belum ada data pengguna.</td></tr>";
            } else {
              while ($row = mysqli_fetch_assoc($hasil)) {

                $idUser = $row["id_user"];
                $nama = $row["nama"] ?? "-";
                $email = $row["email"] ?? "-";
                $roleUser = $row["role"] ?? "-";
                $created = $row["created_at"] ?? "-";

                echo "<tr>";
                echo "<td>$no</td>";
                echo "<td>$idUser</td>";
                echo "<td>$nama</td>";
                echo "<td>$email</td>";
                echo "<td>$roleUser</td>";
                echo "<td>$created</td>";
                echo "<td>";

                if ((int)$idUser == (int)$_SESSION["id_user"]) {
                  echo "<span class='badge bg-label-secondary'>Akun kamu</span>";
                } else {
                  echo "<a class='btn btn-sm btn-primary' href='edit_pengguna.php?id=$idUser'>Edit</a>
                        <a class='btn btn-sm btn-danger' href='hapus_pengguna.php?id=$idUser'
                           onclick=\"return confirm('Yakin hapus pengguna ini?')\">Hapus</a>";
                }

                echo "</td>";
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
