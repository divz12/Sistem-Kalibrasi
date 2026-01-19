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

$id = (int)($_GET["id"] ?? 0);
if ($id <= 0) {
  header("Location: pelanggan.php");
  exit();
}

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
  WHERE tbl_pelanggan.id_pelanggan = '$id'
  LIMIT 1
";
$hasil = mysqli_query($conn, $sql);
$data = mysqli_fetch_assoc($hasil);

if (!$data) {
  header("Location: pelanggan.php");
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
        <h4 class="fw-bold mb-1">Edit Pelanggan</h4>
        <p class="text-muted mb-0">Ubah data pelanggan.</p>
      </div>
      <a href="index.php" class="btn btn-outline-primary">
        <i class="bx bx-arrow-back me-1"></i> Kembali
      </a>
    </div>

    <?php if (($_GET["msg"] ?? "") == "err"): ?>
      <div class="alert alert-danger">
        Gagal menyimpan. Jangan pakai tanda petik ( ' atau " ).
      </div>
    <?php endif; ?>

    <div class="card">
      <div class="card-body">

        <form action="proses_edit.php" method="post">
          <input type="hidden" name="id_pelanggan" value="<?= $data["id_pelanggan"]; ?>">
          <input type="hidden" name="id_user" value="<?= $data["id_user"]; ?>">

          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label">Nama</label>
              <input type="text" name="nama" class="form-control" value="<?= $data["nama"]; ?>" required>
            </div>

            <div class="col-md-6">
              <label class="form-label">Email</label>
              <input type="email" name="email" class="form-control" value="<?= $data["email"]; ?>" required>
            </div>

            <div class="col-md-6">
              <label class="form-label">No HP / WA</label>
              <input type="text" name="no_hp" class="form-control" value="<?= $data["no_hp"]; ?>" required>
            </div>

            <div class="col-md-6">
              <label class="form-label">Alamat</label>
              <input type="text" name="alamat" class="form-control" value="<?= $data["alamat"]; ?>" required>
            </div>
          </div>

          <div class="mt-4">
            <button class="btn btn-primary"><i class="bx bx-save me-1"></i> Simpan</button>
            <a href="pelanggan.php" class="btn btn-secondary">Batal</a>
          </div>

        </form>

      </div>
    </div>

  </div>
</div>

<?php include "../komponen/footer.php"; ?>
