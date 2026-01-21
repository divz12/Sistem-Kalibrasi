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

$urut = $_GET["urut"] ?? "status"; // default: urut berdasarkan status

// default order by (status)
$orderBySql = "
  ORDER BY 
    CASE 
      WHEN tbl_penawaran.status_penawaran = 'negosiasi' THEN 1
      WHEN tbl_penawaran.status_penawaran = 'dikirim' THEN 2
      WHEN tbl_penawaran.status_penawaran = 'diterima' THEN 3
      WHEN tbl_penawaran.status_penawaran = 'ditolak' THEN 4
      ELSE 5
    END ASC,
    tbl_penawaran.id_penawaran DESC
";

// kalau user pilih urutan lain
if ($urut == "terbaru") {
  $orderBySql = "ORDER BY tbl_penawaran.id_penawaran DESC";
} elseif ($urut == "terlama") {
  $orderBySql = "ORDER BY tbl_penawaran.id_penawaran ASC";
} elseif ($urut == "biaya_tertinggi") {
  $orderBySql = "ORDER BY tbl_penawaran.total_biaya DESC, tbl_penawaran.id_penawaran DESC";
} elseif ($urut == "biaya_terendah") {
  $orderBySql = "ORDER BY tbl_penawaran.total_biaya ASC, tbl_penawaran.id_penawaran DESC";
} else {
  // tetap status (default)
}

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
    tbl_users.email,

    /* ===== TAMBAHAN YANG DIPERLUKAN: ambil data invoice by id_penawaran ===== */
    tbl_invoice.id_invoice,
    tbl_invoice.status_pembayaran,
    tbl_invoice.nomor_invoice

  FROM tbl_penawaran
  LEFT JOIN tbl_pengajuan_kalibrasi
    ON tbl_pengajuan_kalibrasi.id_pengajuan = tbl_penawaran.id_pengajuan
  LEFT JOIN tbl_pelanggan
    ON tbl_pelanggan.id_pelanggan = tbl_pengajuan_kalibrasi.id_pelanggan
  LEFT JOIN tbl_users
    ON tbl_users.id_user = tbl_pelanggan.id_user

  /* ===== TAMBAHAN: join invoice pakai id_penawaran ===== */
  LEFT JOIN tbl_invoice
    ON tbl_invoice.id_penawaran = tbl_penawaran.id_penawaran

  $orderBySql
";
$hasil = mysqli_query($conn, $sql);
if (!$hasil) {
  die("Query gagal: " . mysqli_error($conn));
}

// hitung total penawaran
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

function badgeStatusBayar($status)
{
  $s = strtolower((string)$status);
  if ($s == "sudah dibayar" || $s == "dibayar" || $s == "paid") return "bg-label-success";
  if ($s == "belum dibayar" || $s == "unpaid") return "bg-label-warning";
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
      <div class="card-header d-flex flex-wrap gap-3 justify-content-between align-items-center">
        <h5 class="mb-0">Daftar Penawaran</h5>

        <!-- FITUR URUTKAN -->
        <form method="get" class="d-flex align-items-center gap-2">
          <label class="text-muted small mb-0">Urutkan:</label>
          <select name="urut" class="form-select form-select-sm" style="width:220px;" onchange="this.form.submit()">
            <option value="status" <?= ($urut == "status") ? "selected" : ""; ?>>Berdasarkan Status</option>
            <option value="terbaru" <?= ($urut == "terbaru") ? "selected" : ""; ?>>Terbaru</option>
            <option value="terlama" <?= ($urut == "terlama") ? "selected" : ""; ?>>Terlama</option>
            <option value="biaya_tertinggi" <?= ($urut == "biaya_tertinggi") ? "selected" : ""; ?>>Total Biaya Tertinggi</option>
            <option value="biaya_terendah" <?= ($urut == "biaya_terendah") ? "selected" : ""; ?>>Total Biaya Terendah</option>
          </select>
          <noscript><button class="btn btn-sm btn-primary" type="submit">Terapkan</button></noscript>
        </form>
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
              <th>Status Invoice</th>

              <th style="width:240px;">Aksi</th>
            </tr>
          </thead>
          <tbody>
            <?php
            $no = 1;

            if (mysqli_num_rows($hasil) == 0) {
              echo "<tr><td colspan='9' class='text-center'>Belum ada penawaran.</td></tr>";
            } else {
              while ($row = mysqli_fetch_assoc($hasil)) {

                $idPenawaran = $row["id_penawaran"];
                $idPengajuan = $row["id_pengajuan"];
                $nama = $row["nama"] ?? "-";
                $email = $row["email"] ?? "-";
                $tanggalPenawaran = $row["tanggal_penawaran"] ?? "-";
                $totalBiaya = $row["total_biaya"] ?? 0;
                $statusPenawaran = $row["status_penawaran"] ?? "-";

                $idInvoice = (int)($row["id_invoice"] ?? 0);
                $statusBayar = $row["status_pembayaran"] ?? "";
                $statusBayarLower = strtolower((string)$statusBayar);

                // tampil badge invoice
                if ($idInvoice > 0) {
                  $labelInv = ($statusBayar != "") ? $statusBayar : "belum dibayar";
                  $badgeInv = badgeStatusBayar($labelInv);
                  $kolomInvoiceHtml = "<span class='badge $badgeInv'>$labelInv</span>";
                } else {
                  $kolomInvoiceHtml = "<span class='badge bg-label-secondary'>belum tersedia</span>";
                }

                $statusLower = strtolower($statusPenawaran);

                // default edit muncul
                $btnEdit = "<a class='btn btn-sm btn-outline-primary' href='edit_penawaran.php?id=".$idPenawaran."'>Edit</a>";

                // diterima -> hilangkan edit
                if ($statusLower == "diterima") {
                  $btnEdit = "";
                }

                // ditolak -> tawar kembali
                if ($statusLower == "ditolak") {
                  $btnEdit = "<a class='btn btn-sm btn-outline-warning' href='edit_penawaran.php?id=".$idPenawaran."'>Tawar Kembali</a>";
                }

                
                $btnKonfirmasi = "";
                if ($statusLower == "diterima" && $idInvoice > 0 && ($statusBayarLower == "belum dibayar" || $statusBayarLower == "unpaid" || $statusBayarLower == "")) {
                  $btnKonfirmasi = "<a class='btn btn-sm btn-success' href='konfirmasi_bayar.php?id_invoice=".$idInvoice."'
                    onclick=\"return confirm('Konfirmasi pembayaran untuk invoice ini?');\">Konfirmasi Bayar</a>";
                }

                echo "<tr>";
                echo "<td>$no</td>";
                echo "<td>$idPenawaran</td>";
                echo "<td>#".$idPengajuan."</td>";
                echo "<td><b>".$nama."</b><br><small class='text-muted'>".$email."</small></td>";
                echo "<td>".$tanggalPenawaran."</td>";
                echo "<td>Rp ".number_format($totalBiaya, 0, ',', '.')."</td>";
                echo "<td><span class='badge ".badgeStatusPenawaran($statusPenawaran)."'>".$statusPenawaran."</span></td>";

                // tampil status invoice
                echo "<td>".$kolomInvoiceHtml."</td>";

                echo "<td>
                        <a class='btn btn-sm btn-primary' href='detail_penawaran.php?id=".$idPenawaran."'>Detail</a>
                        ".$btnEdit."
                        ".$btnKonfirmasi."
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
