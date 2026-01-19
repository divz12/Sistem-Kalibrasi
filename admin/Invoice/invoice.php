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

$sql = "
  SELECT
    tbl_invoice.id_invoice,
    tbl_invoice.id_penawaran,
    tbl_invoice.nomor_invoice,
    tbl_invoice.tanggal_invoice,
    tbl_invoice.tanggal_jatuh_tempo,
    tbl_invoice.total_tagihan,
    tbl_invoice.status_pembayaran,
    tbl_invoice.nama_file_invoice,
    tbl_invoice.lokasi_file_invoice,
    tbl_invoice.keterangan_invoice,

    tbl_penawaran.id_pengajuan,
    tbl_penawaran.total_biaya,
    tbl_penawaran.status_penawaran,

    tbl_pengajuan_kalibrasi.id_pelanggan,
    tbl_users.nama,
    tbl_users.email

  FROM tbl_invoice
  LEFT JOIN tbl_penawaran
    ON tbl_penawaran.id_penawaran = tbl_invoice.id_penawaran
  LEFT JOIN tbl_pengajuan_kalibrasi
    ON tbl_pengajuan_kalibrasi.id_pengajuan = tbl_penawaran.id_pengajuan
  LEFT JOIN tbl_pelanggan
    ON tbl_pelanggan.id_pelanggan = tbl_pengajuan_kalibrasi.id_pelanggan
  LEFT JOIN tbl_users
    ON tbl_users.id_user = tbl_pelanggan.id_user
  ORDER BY tbl_invoice.id_invoice DESC
";

$hasil = mysqli_query($conn, $sql);
if (!$hasil) {
  die("Query gagal: " . mysqli_error($conn));
}

$sqlTotal = "SELECT COUNT(*) AS total FROM tbl_invoice";
$hasilTotal = mysqli_query($conn, $sqlTotal);
$dataTotal = mysqli_fetch_assoc($hasilTotal);
$totalInvoice = $dataTotal["total"] ?? 0;

function badgePembayaran($status)
{
  $s = strtolower($status ?? "");
  if ($s == "belum dibayar") return "bg-warning text-dark";
  if ($s == "sudah dibayar") return "bg-success";
  if ($s == "jatuh tempo") return "bg-danger";
  return "bg-secondary";
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
        <h4 class="fw-bold mb-1">Invoice</h4>
        <div class="text-muted">Halo, <b><?= $namaAdmin; ?></b> 👋</div>
      </div>

      <div class="d-flex gap-2">
        <a href="tambah_invoice.php" class="btn btn-primary">
          <i class="bx bx-plus me-1"></i> Tambah Invoice
        </a>
      </div>
    </div>

    <div class="row g-4 mb-4">
      <div class="col-sm-6 col-lg-3">
        <div class="card">
          <div class="card-body">
            <span class="text-muted">Total Invoice</span>
            <h3 class="mb-0"><?= $totalInvoice; ?></h3>
            <small class="text-muted">Jumlah invoice yang tersimpan</small>
          </div>
        </div>
      </div>
    </div>

    <div class="card">
      <div class="card-header">
        <h5 class="mb-0">Daftar Invoice</h5>
      </div>

      <div class="table-responsive">
        <table class="table align-middle">
          <thead class="table-light">
            <tr>
              <th>No</th>
              <th>Nomor Invoice</th>
              <th>Pelanggan</th>
              <th>ID Penawaran</th>
              <th>Tanggal</th>
              <th>Total</th>
              <th>Status</th>
              <th>File</th>
              <th style="width:220px;">Aksi</th>
            </tr>
          </thead>
          <tbody>
            <?php
            $no = 1;

            if (mysqli_num_rows($hasil) == 0) {
              echo "<tr><td colspan='10' class='text-center'>Belum ada invoice.</td></tr>";
            } else {
              while ($row = mysqli_fetch_assoc($hasil)) {

                $idInvoice = $row["id_invoice"] ?? "";
                $nomorInvoice = $row["nomor_invoice"] ?? "-";
                $nama = $row["nama"] ?? "-";
                $email = $row["email"] ?? "-";

                $idPenawaran = $row["id_penawaran"] ?? "-";
                $tanggalInvoice = $row["tanggal_invoice"] ?? "-";

                $totalTagihan = $row["total_tagihan"] ?? 0;
                $statusBayar = $row["status_pembayaran"] ?? "-";

                $lokasiFile = $row["lokasi_file_invoice"] ?? "";

                echo "<tr>";
                echo "<td>".$no."</td>";
                echo "<td>".$nomorInvoice."</td>";
                echo "<td><b>".$nama."</b><br><small class='text-muted'>".$email."</small></td>";
                echo "<td>#".$idPenawaran."</td>";
                echo "<td>".$tanggalInvoice."</td>";
                echo "<td>Rp ".number_format($totalTagihan, 0, ',', '.')."</td>";
                echo "<td><span class='badge ".badgePembayaran($statusBayar)."'>".$statusBayar."</span></td>";

                if ($lokasiFile != "") {
                  echo "<td><a class='btn btn-sm btn-outline-success' href='".$lokasiFile."' target='_blank'>Unduh</a></td>";
                } else {
                  echo "<td>-</td>";
                }

                echo "<td>
                        <a class='btn btn-sm btn-warning' href='edit_invoice.php?id=".$idInvoice."'>Edit</a>
                        <a class='btn btn-sm btn-danger' href='hapus_invoice.php?id=".$idInvoice."'
                          onclick=\"return confirm('Yakin hapus invoice ini?')\">Hapus</a>
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
