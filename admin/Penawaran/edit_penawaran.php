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

$idPenawaran = (int)($_GET["id"] ?? 0);
if ($idPenawaran <= 0) {
  header("Location: penawaran.php");
  exit();
}

$sql = "
  SELECT
    tbl_penawaran.id_penawaran,
    tbl_penawaran.id_pengajuan,
    tbl_penawaran.tanggal_penawaran,
    tbl_penawaran.total_biaya,
    tbl_penawaran.rincian,
    tbl_penawaran.status_penawaran
  FROM tbl_penawaran
  WHERE tbl_penawaran.id_penawaran = '$idPenawaran'
  LIMIT 1
";
$hasil = mysqli_query($conn, $sql);
$data = mysqli_fetch_assoc($hasil);

if (!$data) {
  header("Location: penawaran.php");
  exit();
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
        <h4 class="fw-bold mb-1">Edit Penawaran</h4>
        <p class="text-muted mb-0">Ubah data penawaran.</p>
      </div>
      <a href="penawaran.php" class="btn btn-outline-primary">
        <i class="bx bx-arrow-back me-1"></i> Kembali
      </a>
    </div>

    <form action="proses_edit.php" method="post" class="card">
      <div class="card-body">

        <input type="hidden" name="id_penawaran" value="<?= $data["id_penawaran"]; ?>">

        <div class="mb-3">
          <label class="form-label">ID Pengajuan</label>
          <input type="text" class="form-control" value="<?= $data["id_pengajuan"]; ?>" disabled>
        </div>

        <div class="row g-3">
          <div class="col-md-6">
            <label class="form-label">Tanggal Penawaran</label>
            <input type="date" name="tanggal_penawaran" class="form-control" value="<?= $data["tanggal_penawaran"]; ?>" required>
          </div>

          <div class="col-md-6">
            <label class="form-label">Total Biaya</label>
            <input type="number" name="total_biaya" class="form-control" value="<?= $data["total_biaya"]; ?>" min="0" required>
          </div>
        </div>

        <div class="mt-3">
          <label class="form-label">Rincian</label>
          <textarea name="rincian" class="form-control" rows="4" required><?= $data["rincian"]; ?></textarea>
        </div>

        <hr class="my-4">

        <button class="btn btn-primary" type="submit">Simpan</button>
        <a href="penawaran.php" class="btn btn-outline-primary">Batal</a>

      </div>
    </form>

  </div>
</div>

<?php include "../komponen/footer.php"; ?>
