<?php
session_start();
include "../../koneksi.php";

$role = $_SESSION["role"] ?? "";

if (!isset($_SESSION["id_user"])) {
  header("Location: ../../login.php");
  exit();
}

if ($role != "admin" && $role != "owner") {
  header("Location: ../../login.php");
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
        <h4 class="fw-bold mb-1">Tambah Pengguna</h4>
        <p class="text-muted mb-0">Buat akun baru (admin/cs/pelanggan).</p>
      </div>
      <a href="pengguna.php" class="btn btn-outline-primary">
        <i class="bx bx-arrow-back me-1"></i> Kembali
      </a>
    </div>

    <?php if (($_GET["msg"] ?? "") == "err"): ?>
      <div class="alert alert-danger">Gagal menyimpan. Pastikan semua terisi.</div>
    <?php endif; ?>

    <div class="card">
      <div class="card-body">

        <form action="proses_tambah.php" method="post">

          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label">Nama</label>
              <input type="text" name="nama" class="form-control" required>
            </div>

            <div class="col-md-6">
              <label class="form-label">Email</label>
              <input type="email" name="email" class="form-control" required>
            </div>

            <div class="col-md-6">
              <label class="form-label">Password</label>
              <input type="text" name="password" class="form-control" required>
            </div>

            <div class="col-md-6">
              <label class="form-label">Role</label>
              <select name="role" class="form-control" required>
                <option value="">-- Pilih Role --</option>
                <option value="admin">admin</option>
                <option value="owner">owner</option>
                <option value="pelanggan">pelanggan</option>
              </select>
            </div>
          </div>

          <div class="mt-4">
            <button class="btn btn-primary"><i class="bx bx-save me-1"></i> Simpan</button>
            <a href="pengguna.php" class="btn btn-secondary">Batal</a>
          </div>

        </form>

      </div>
    </div>

  </div>
</div>

<?php include "../komponen/footer.php"; ?>
