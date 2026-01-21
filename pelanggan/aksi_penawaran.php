<?php
session_start();
include "../koneksi.php";

// proteksi login pelanggan
if (!isset($_SESSION['id_user']) || ($_SESSION['role'] ?? '') !== 'pelanggan') {
  header("Location: ../login.php");
  exit();
}

$id_user = (int)($_SESSION['id_user'] ?? 0);
$id_penawaran = (int)($_GET['id'] ?? 0);
$aksi = $_GET['aksi'] ?? "";

if ($id_penawaran <= 0) {
  header("Location: penawaran.php?msg=err");
  exit();
}

if ($aksi != "diterima" && $aksi != "ditolak") {
  header("Location: penawaran.php?msg=err");
  exit();
}

$qPel = mysqli_query($conn, "SELECT id_pelanggan FROM tbl_pelanggan WHERE id_user='$id_user' LIMIT 1");
$dataPel = $qPel ? mysqli_fetch_assoc($qPel) : null;
$id_pelanggan = (int)($dataPel['id_pelanggan'] ?? 0);

if ($id_pelanggan <= 0) {
  header("Location: profil.php");
  exit();
}

// cek penawaran milik pelanggan yang login
$sqlCek = "
  SELECT
    tbl_penawaran.id_penawaran,
    tbl_penawaran.id_pengajuan,
    tbl_penawaran.status_penawaran
  FROM tbl_penawaran
  JOIN tbl_pengajuan_kalibrasi
    ON tbl_pengajuan_kalibrasi.id_pengajuan = tbl_penawaran.id_pengajuan
  WHERE tbl_penawaran.id_penawaran = '$id_penawaran'
    AND tbl_pengajuan_kalibrasi.id_pelanggan = '$id_pelanggan'
  LIMIT 1
";
$cek = mysqli_query($conn, $sqlCek);
$data = $cek ? mysqli_fetch_assoc($cek) : null;

if (!$data) {
  header("Location: penawaran.php?msg=err");
  exit();
}

$statusSekarang = $data["status_penawaran"] ?? "";
$id_pengajuan = (int)($data["id_pengajuan"] ?? 0);

if ($statusSekarang != "dikirim" && $statusSekarang != "negosiasi") {
  header("Location: penawaran.php?msg=err");
  exit();
}

function buatNomorInvoice($id_penawaran) {
  $tgl = date("Ymd");
  $ur = str_pad((string)$id_penawaran, 4, "0", STR_PAD_LEFT);
  return "INV-$tgl-$ur";
}

function generateInvoicePdf($conn, $id_invoice, $nomor_invoice) {
  require_once __DIR__ . "/../lib/fpdf/fpdf.php";

  $sql = "
    SELECT
      i.id_invoice, i.id_penawaran, i.nomor_invoice, i.tanggal_invoice, i.tanggal_jatuh_tempo, i.total_tagihan, i.status_pembayaran,
      p.id_penawaran, p.id_pengajuan, p.total_biaya, p.tanggal_penawaran,
      pk.tanggal_pengajuan,
      u.nama AS nama_pelanggan, u.email,
      pel.no_hp, pel.alamat
    FROM tbl_invoice i
    JOIN tbl_penawaran p ON p.id_penawaran = i.id_penawaran
    JOIN tbl_pengajuan_kalibrasi pk ON pk.id_pengajuan = p.id_pengajuan
    JOIN tbl_pelanggan pel ON pel.id_pelanggan = pk.id_pelanggan
    JOIN tbl_users u ON u.id_user = pel.id_user
    WHERE i.id_invoice = '$id_invoice'
    LIMIT 1
  ";
  $q = mysqli_query($conn, $sql);
  $inv = $q ? mysqli_fetch_assoc($q) : null;
  if (!$inv) return [false, "Data invoice tidak ditemukan."];

  // Ambil list alat dari pengajuan
  $id_pengajuan = (int)($inv["id_pengajuan"] ?? 0);
  $alat = [];
  if ($id_pengajuan > 0) {
    $qAlat = mysqli_query($conn, "
      SELECT nama_alat, merk_tipe, kapasitas, jumlah_unit, keterangan
      FROM tbl_pengajuan_alat
      WHERE id_pengajuan = '$id_pengajuan'
      ORDER BY id_alat ASC
    ");
    if ($qAlat) {
      while ($r = mysqli_fetch_assoc($qAlat)) $alat[] = $r;
    }
  }

  // Folder simpan PDF: ../admin/InvoiceFile/
  $saveDir = __DIR__ . "/../admin/InvoiceFile";
  if (!is_dir($saveDir)) {
    @mkdir($saveDir, 0777, true);
  }

  $safeNo = preg_replace('/[^A-Za-z0-9\-]/', '_', $nomor_invoice);
  $fileName = "invoice_" . $safeNo . ".pdf";
  $fullPath = $saveDir . "/" . $fileName;

  // Buat PDF
  $pdf = new FPDF("P", "mm", "A4");
  $pdf->AddPage();
  $pdf->SetAutoPageBreak(true, 15);

  // Header
  $pdf->SetFont("Arial", "B", 14);
  $pdf->Cell(0, 8, "PT AKBAR TERA ABADI", 0, 1, "L");
  $pdf->SetFont("Arial", "", 10);
  $pdf->Cell(0, 6, "INVOICE", 0, 1, "L");
  $pdf->Ln(2);

  // Box info
  $pdf->SetFont("Arial", "B", 10);
  $pdf->Cell(95, 7, "Customer", 1, 0, "L");
  $pdf->Cell(0, 7, "Info Invoice", 1, 1, "L");

  $pdf->SetFont("Arial", "", 10);
  $nama = $inv["nama_pelanggan"] ?? "-";
  $alamat = $inv["alamat"] ?? "-";
  $email = $inv["email"] ?? "-";
  $hp = $inv["no_hp"] ?? "-";

  $pdf->Cell(95, 7, $nama, 1, 0, "L");
  $pdf->Cell(30, 7, "No", 1, 0, "L");
  $pdf->Cell(0, 7, $inv["nomor_invoice"] ?? "-", 1, 1, "L");

  $pdf->Cell(95, 7, "Alamat: " . $alamat, 1, 0, "L");
  $pdf->Cell(30, 7, "Tanggal", 1, 0, "L");
  $pdf->Cell(0, 7, $inv["tanggal_invoice"] ?? "-", 1, 1, "L");

  $pdf->Cell(95, 7, "Telp: " . $hp, 1, 0, "L");
  $pdf->Cell(30, 7, "Jatuh Tempo", 1, 0, "L");
  $pdf->Cell(0, 7, $inv["tanggal_jatuh_tempo"] ?? "-", 1, 1, "L");

  $pdf->Cell(95, 7, "Email: " . $email, 1, 0, "L");
  $pdf->Cell(30, 7, "Status", 1, 0, "L");
  $pdf->Cell(0, 7, $inv["status_pembayaran"] ?? "-", 1, 1, "L");

  $pdf->Ln(5);

  // Tabel item
  $pdf->SetFont("Arial", "B", 10);
  $pdf->Cell(10, 8, "No", 1, 0, "C");
  $pdf->Cell(120, 8, "Deskripsi", 1, 0, "C");
  $pdf->Cell(20, 8, "Qty", 1, 0, "C");
  $pdf->Cell(40, 8, "Keterangan", 1, 1, "C");

  $pdf->SetFont("Arial", "", 9);

  if (count($alat) == 0) {
    $pdf->Cell(10, 8, "1", 1, 0, "C");
    $pdf->Cell(120, 8, "Jasa Kalibrasi", 1, 0, "L");
    $pdf->Cell(20, 8, "1", 1, 0, "C");
    $pdf->Cell(40, 8, "-", 1, 1, "L");
  } else {
    $no = 1;
    foreach ($alat as $a) {
      $desc = ($a["nama_alat"] ?? "-");
      $mt = ($a["merk_tipe"] ?? "");
      $kap = ($a["kapasitas"] ?? "");
      $gab = $desc;
      if ($mt != "") $gab .= " / " . $mt;
      if ($kap != "") $gab .= " (" . $kap . ")";

      $qty = (string)($a["jumlah_unit"] ?? "1");
      $ket = ($a["keterangan"] ?? "-");
      if ($ket == "") $ket = "-";

      $pdf->Cell(10, 8, $no, 1, 0, "C");
      $pdf->Cell(120, 8, $gab, 1, 0, "L");
      $pdf->Cell(20, 8, $qty, 1, 0, "C");
      $pdf->Cell(40, 8, $ket, 1, 1, "L");
      $no++;
    }
  }

  $pdf->Ln(3);

  // Total
  $pdf->SetFont("Arial", "B", 10);
  $pdf->Cell(150, 8, "TOTAL", 1, 0, "R");
  $pdf->Cell(40, 8, "Rp " . number_format((float)($inv["total_tagihan"] ?? 0), 0, ",", "."), 1, 1, "R");

  $pdf->Ln(8);
  $pdf->SetFont("Arial", "", 9);
  $pdf->MultiCell(0, 5, "Catatan:\n- Invoice ini dibuat otomatis ketika penawaran diterima.\n- Silakan lakukan pembayaran sesuai jatuh tempo.");

  // Simpan file
  $pdf->Output("F", $fullPath);

  // simpan path relatif
  $lokasiRel = "InvoiceFile/" . $fileName;

  return [true, ["nama_file" => $fileName, "lokasi_rel" => $lokasiRel]];
}

// mulai transaksi
mysqli_begin_transaction($conn);

try {
  // update status penawaran
  $sqlUpdatePenawaran = "
    UPDATE tbl_penawaran
    SET status_penawaran = '$aksi'
    WHERE id_penawaran = '$id_penawaran'
  ";
  $updatePenawaran = mysqli_query($conn, $sqlUpdatePenawaran);
  if (!$updatePenawaran) {
    throw new Exception("Gagal update penawaran.");
  }

  // jika diterima, update status pengajuan menjadi diproses + buat invoice
  if ($aksi == "diterima" && $id_pengajuan > 0) {

    $qStatus = mysqli_query($conn, "
      SELECT status_pengajuan
      FROM tbl_pengajuan_kalibrasi
      WHERE id_pengajuan = '$id_pengajuan'
      LIMIT 1
    ");
    $ds = $qStatus ? mysqli_fetch_assoc($qStatus) : null;
    $statusPengajuanSekarang = strtolower($ds["status_pengajuan"] ?? "");

    if ($statusPengajuanSekarang != "selesai") {
      $sqlUpdatePengajuan = "
        UPDATE tbl_pengajuan_kalibrasi
        SET status_pengajuan = 'diproses'
        WHERE id_pengajuan = '$id_pengajuan'
      ";
      mysqli_query($conn, $sqlUpdatePengajuan);
    }

    // cek invoice sudah ada atau belum
    $qCekInv = mysqli_query($conn, "
      SELECT id_invoice, nomor_invoice
      FROM tbl_invoice
      WHERE id_penawaran = '$id_penawaran'
      LIMIT 1
    ");
    $invAda = $qCekInv ? mysqli_fetch_assoc($qCekInv) : null;

    if (!$invAda) {
      // ambil total biaya penawaran
      $qPen = mysqli_query($conn, "
        SELECT total_biaya
        FROM tbl_penawaran
        WHERE id_penawaran = '$id_penawaran'
        LIMIT 1
      ");
      $dp = mysqli_fetch_assoc($qPen);
      $totalTagihan = (float)($dp["total_biaya"] ?? 0);

      $nomorInvoice = buatNomorInvoice($id_penawaran);
      $tanggalInvoice = date("Y-m-d");
      $jatuhTempo = date("Y-m-d", strtotime("+14 days"));
      $statusBayar = "belum dibayar";
      $ket = "Invoice otomatis dari penawaran diterima";

      $sqlInsertInv = "
        INSERT INTO tbl_invoice
          (id_penawaran, nomor_invoice, tanggal_invoice, tanggal_jatuh_tempo, total_tagihan, status_pembayaran, nama_file_invoice, lokasi_file_invoice, keterangan_invoice, dibuat_pada)
        VALUES
          ('$id_penawaran', '$nomorInvoice', '$tanggalInvoice', '$jatuhTempo', '$totalTagihan', '$statusBayar', '', '', '$ket', NOW())
      ";
      $ins = mysqli_query($conn, $sqlInsertInv);
      if (!$ins) {
        throw new Exception("Gagal insert invoice: " . mysqli_error($conn));
      }

      $id_invoice = (int)mysqli_insert_id($conn);

      // generate PDF + simpan lokasi file
      $gen = generateInvoicePdf($conn, $id_invoice, $nomorInvoice);
      if ($gen[0]) {
        $fileInfo = $gen[1];
        $namaFile = mysqli_real_escape_string($conn, $fileInfo["nama_file"]);
        $lokRel = mysqli_real_escape_string($conn, $fileInfo["lokasi_rel"]);

        mysqli_query($conn, "
          UPDATE tbl_invoice
          SET nama_file_invoice = '$namaFile',
              lokasi_file_invoice = '$lokRel'
          WHERE id_invoice = '$id_invoice'
        ");
      }
      
    }
  }

  mysqli_commit($conn);
  header("Location: penawaran.php?msg=ok");
  exit();

} catch (Exception $e) {
  mysqli_rollback($conn);
  header("Location: penawaran.php?msg=err");
  exit();
}
?>