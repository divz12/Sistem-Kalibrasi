<?php
session_start();
include "../koneksi.php";

// ====== PROTEKSI LOGIN PELANGGAN ======
if (!isset($_SESSION['id_user']) || ($_SESSION['role'] ?? '') !== 'pelanggan') {
  header("Location: ../login.php");
  exit();
}

// PATH
require_once __DIR__ . "/../lib/fpdf/fpdf.php";

function rupiah_angka($angka)
{
  return number_format((float)$angka, 0, ',', '.');
}

$id_user = (int)($_SESSION['id_user'] ?? 0);


$id_invoice = (int)($_GET['id_invoice'] ?? 0);
$id_pengajuan_get = (int)($_GET['id_pengajuan'] ?? 0);


$qPel = mysqli_query($conn, "SELECT id_pelanggan FROM tbl_pelanggan WHERE id_user='$id_user' LIMIT 1");
$dataPel = $qPel ? mysqli_fetch_assoc($qPel) : null;
$id_pelanggan = (int)($dataPel['id_pelanggan'] ?? 0);

if ($id_pelanggan <= 0) {
  header("Location: profil.php");
  exit();
}


if ($id_invoice <= 0 && $id_pengajuan_get > 0) {
  $sqlCariInvoice = "
    SELECT i.id_invoice
    FROM tbl_invoice i
    LEFT JOIN tbl_penawaran p ON p.id_penawaran = i.id_penawaran
    LEFT JOIN tbl_pengajuan_kalibrasi pk ON pk.id_pengajuan = p.id_pengajuan
    WHERE pk.id_pengajuan = '$id_pengajuan_get'
      AND pk.id_pelanggan = '$id_pelanggan'
    ORDER BY i.id_invoice DESC
    LIMIT 1
  ";
  $qCari = mysqli_query($conn, $sqlCariInvoice);
  $dCari = $qCari ? mysqli_fetch_assoc($qCari) : null;
  $id_invoice = (int)($dCari['id_invoice'] ?? 0);
}

if ($id_invoice <= 0) {
  header("Location: invoice.php?msg=err");
  exit();
}


$sqlInv = "
  SELECT
    i.id_invoice,
    i.id_penawaran,
    i.nomor_invoice,
    i.tanggal_invoice,
    i.tanggal_jatuh_tempo,
    i.total_tagihan,
    i.status_pembayaran,
    i.keterangan_invoice,
    i.lokasi_file_invoice,

    p.id_pengajuan,
    p.tanggal_penawaran,
    p.total_biaya,
    p.status_penawaran,

    pk.tanggal_pengajuan,
    pk.status_pengajuan,
    pk.catatan,

    pel.id_pelanggan,
    pel.alamat,
    pel.no_hp,

    u.nama AS nama_pelanggan,
    u.email AS email_pelanggan

  FROM tbl_invoice i
  LEFT JOIN tbl_penawaran p ON p.id_penawaran = i.id_penawaran
  LEFT JOIN tbl_pengajuan_kalibrasi pk ON pk.id_pengajuan = p.id_pengajuan
  LEFT JOIN tbl_pelanggan pel ON pel.id_pelanggan = pk.id_pelanggan
  LEFT JOIN tbl_users u ON u.id_user = pel.id_user
  WHERE i.id_invoice = '$id_invoice'
  LIMIT 1
";
$qInv = mysqli_query($conn, $sqlInv);
$inv = $qInv ? mysqli_fetch_assoc($qInv) : null;

if (!$inv) {
  header("Location: invoice.php?msg=notfound");
  exit();
}

// Validasi kepemilikan
if ((int)($inv['id_pelanggan'] ?? 0) !== $id_pelanggan) {
  header("Location: invoice.php?msg=forbidden");
  exit();
}

// $lokasiFile = trim((string)($inv['lokasi_file_invoice'] ?? ''));
// if ($lokasiFile !== '') {
//   // contoh lokasi: "InvoiceFile/invoice_INV-20260121-0007.pdf"
//   $fullPath = realpath(__DIR__ . "/../admin/" . $lokasiFile);

//   if ($fullPath && file_exists($fullPath)) {
//     header('Content-Type: application/pdf');
//     header('Content-Disposition: attachment; filename="' . basename($fullPath) . '"');
//     header('Content-Length: ' . filesize($fullPath));
//     readfile($fullPath);
//     exit();
//   }
// }
// kalau file tidak ada / kosong 

$id_pengajuan = (int)($inv['id_pengajuan'] ?? 0);
if ($id_pengajuan <= 0) {
  header("Location: invoice.php?msg=err");
  exit();
}

// ====== AMBIL LIST ALAT ======
$items = [];
$qAlat = mysqli_query($conn, "
  SELECT nama_alat, merk_tipe, kapasitas, jumlah_unit
  FROM tbl_pengajuan_alat
  WHERE id_pengajuan='$id_pengajuan'
  ORDER BY id_alat ASC
");
if ($qAlat) {
  while ($r = mysqli_fetch_assoc($qAlat)) {
    $items[] = $r;
  }
}

// ====== BIKIN HARGA PER ITEM ======
$totalTagihan = (float)($inv['total_tagihan'] ?? 0);
$totalQty = 0;
foreach ($items as $it) {
  $totalQty += (int)($it['jumlah_unit'] ?? 1);
}
if ($totalQty <= 0) $totalQty = 1;

$hargaSatuan = floor($totalTagihan / $totalQty);
$running = 0;

// ====== PDF ======
class PDF extends FPDF
{
  function Header() {}
  function Footer()
  {
    $this->SetY(-15);
    $this->SetFont('Arial', 'I', 8);
    $this->Cell(0, 10, 'Halaman ' . $this->PageNo() . '/{nb}', 0, 0, 'C');
  }

  function NbLines($w, $txt)
  {
    $cw = &$this->CurrentFont['cw'];
    if ($w == 0) $w = $this->w - $this->rMargin - $this->x;
    $wmax = ($w - 2 * $this->cMargin) * 1000 / $this->FontSize;
    $s = str_replace("\r", '', (string)$txt);
    $nb = strlen($s);
    if ($nb > 0 && $s[$nb - 1] == "\n") $nb--;
    $sep = -1;
    $i = 0;
    $j = 0;
    $l = 0;
    $nl = 1;
    while ($i < $nb) {
      $c = $s[$i];
      if ($c == "\n") {
        $i++;
        $sep = -1;
        $j = $i;
        $l = 0;
        $nl++;
        continue;
      }
      if ($c == ' ') $sep = $i;
      $l += $cw[$c] ?? 0;
      if ($l > $wmax) {
        if ($sep == -1) {
          if ($i == $j) $i++;
        } else $i = $sep + 1;
        $sep = -1;
        $j = $i;
        $l = 0;
        $nl++;
      } else $i++;
    }
    return $nl;
  }

  function Row($data, $widths, $aligns)
  {
    $nb = 0;
    for ($i = 0; $i < count($data); $i++) {
      $nb = max($nb, $this->NbLines($widths[$i], $data[$i]));
    }
    $h = 6 * $nb;

    if ($this->GetY() + $h > $this->PageBreakTrigger) $this->AddPage($this->CurOrientation);

    for ($i = 0; $i < count($data); $i++) {
      $w = $widths[$i];
      $a = $aligns[$i] ?? 'L';
      $x = $this->GetX();
      $y = $this->GetY();
      $this->Rect($x, $y, $w, $h);
      $this->MultiCell($w, 6, $data[$i], 0, $a);
      $this->SetXY($x + $w, $y);
    }
    $this->Ln($h);
  }
}

$pdf = new PDF('P', 'mm', 'A4');
$pdf->AliasNbPages();
$pdf->SetMargins(10, 10, 10);
$pdf->AddPage();

// ====== HEADER ATAS ======
$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(0, 6, 'PT AKBAR TERA ABADI', 0, 1, 'L');

$pdf->SetFont('Arial', '', 9);
$pdf->Cell(0, 5, 'Testing & Calibration Services | Maintenance & Technical Services', 0, 1, 'L');
$pdf->Ln(2);

// ====== KOTAK CUSTOMER ======
$yTop = $pdf->GetY();
$pdf->Rect(10, $yTop, 110, 32);

$pdf->SetXY(12, $yTop + 2);
$pdf->SetFont('Arial', 'B', 9);
$pdf->Cell(20, 5, 'Customer', 0, 0);
$pdf->SetFont('Arial', '', 9);
$pdf->Cell(0, 5, ': ' . ($inv['nama_pelanggan'] ?? '-'), 0, 1);

$pdf->SetX(12);
$pdf->SetFont('Arial', 'B', 9);
$pdf->Cell(20, 5, 'Alamat', 0, 0);
$pdf->SetFont('Arial', '', 9);
$pdf->MultiCell(86, 5, ': ' . ($inv['alamat'] ?? '-'), 0, 'L');

$pdf->SetX(12);
$pdf->SetFont('Arial', 'B', 9);
$pdf->Cell(20, 5, 'Telp', 0, 0);
$pdf->SetFont('Arial', '', 9);
$pdf->Cell(0, 5, ': ' . ($inv['no_hp'] ?? '-'), 0, 1);

$pdf->SetX(12);
$pdf->SetFont('Arial', 'B', 9);
$pdf->Cell(20, 5, 'Email', 0, 0);
$pdf->SetFont('Arial', '', 9);
$pdf->Cell(0, 5, ': ' . ($inv['email_pelanggan'] ?? '-'), 0, 1);

// ====== KOTAK INVOICE ======
$pdf->SetXY(125, $yTop);
$pdf->Rect(125, $yTop, 75, 32);

$pdf->SetXY(127, $yTop + 2);
$pdf->SetFont('Arial', 'B', 9);
$pdf->Cell(22, 6, 'No', 0, 0);
$pdf->SetFont('Arial', '', 9);
$pdf->Cell(0, 6, ': ' . ($inv['nomor_invoice'] ?? '-'), 0, 1);

$pdf->SetX(127);
$pdf->SetFont('Arial', 'B', 9);
$pdf->Cell(22, 6, 'Tanggal', 0, 0);
$pdf->SetFont('Arial', '', 9);
$pdf->Cell(0, 6, ': ' . ($inv['tanggal_invoice'] ?? '-'), 0, 1);

$pdf->SetX(127);
$pdf->SetFont('Arial', 'B', 9);
$pdf->Cell(22, 6, 'Jatuh Tempo', 0, 0);
$pdf->SetFont('Arial', '', 9);
$pdf->Cell(0, 6, ': ' . ($inv['tanggal_jatuh_tempo'] ?? '-'), 0, 1);

$pdf->SetX(127);
$pdf->SetFont('Arial', 'B', 9);
$pdf->Cell(22, 6, 'Status', 0, 0);
$pdf->SetFont('Arial', '', 9);
$pdf->Cell(0, 6, ': ' . ($inv['status_pembayaran'] ?? '-'), 0, 1);

// ====== SALAM ======
$pdf->Ln(4);
$pdf->SetFont('Arial', '', 9);
$pdf->MultiCell(0, 5, "Dengan hormat,\nBerikut kami sampaikan tagihan jasa kalibrasi/pengujian:", 0, 'L');
$pdf->Ln(2);

// ====== TABEL ITEM ======
$w = [10, 85, 22, 12, 30, 31];
$align = ['C', 'L', 'C', 'C', 'R', 'R'];

$pdf->SetFont('Arial', 'B', 9);
$pdf->Row(['No', 'Nama Alat dan Spesifikasi', 'Remarks', 'Qty', 'Harga (Rp)', 'Total (Rp)'], $w, ['C', 'C', 'C', 'C', 'C', 'C']);

$pdf->SetFont('Arial', '', 9);

if (count($items) == 0) {
  $lineTotal = $totalTagihan;
  $pdf->Row(['1', 'Jasa Kalibrasi', 'In-Lab', '1', rupiah_angka($totalTagihan), rupiah_angka($lineTotal)], $w, $align);
  $running = $lineTotal;
} else {
  $no = 1;
  foreach ($items as $idx => $it) {
    $qty = (int)($it['jumlah_unit'] ?? 1);
    if ($qty <= 0) $qty = 1;

    $nama = trim(($it['nama_alat'] ?? '-') . " " . ($it['merk_tipe'] ?? ''));
    $spek = trim((string)($it['kapasitas'] ?? ''));
    if ($spek !== '') $nama .= " | Kap: " . $spek;

    $harga = $hargaSatuan;
    $line = $harga * $qty;
    $running += $line;

    if ($idx == count($items) - 1) {
      $selisih = $totalTagihan - $running;
      $line += $selisih;
      $running += $selisih;
    }

    $pdf->Row([(string)$no, $nama, 'In-Lab', (string)$qty, rupiah_angka($harga), rupiah_angka($line)], $w, $align);
    $no++;
  }
}

// ====== TOTAL BOX ======
$pdf->SetFont('Arial', 'B', 9);
$y = $pdf->GetY();
$pdf->Rect(129, $y, 71, 18);

$pdf->SetXY(129, $y);
$pdf->Cell(40, 6, 'SUBTOTAL', 1, 0, 'L');
$pdf->Cell(31, 6, rupiah_angka($totalTagihan), 1, 1, 'R');

$pdf->SetX(129);
$pdf->Cell(40, 6, 'PAJAK', 1, 0, 'L');
$pdf->Cell(31, 6, '-', 1, 1, 'R');

$pdf->SetX(129);
$pdf->Cell(40, 6, 'GRAND TOTAL', 1, 0, 'L');
$pdf->Cell(31, 6, rupiah_angka($totalTagihan), 1, 1, 'R');

// ====== KETERANGAN ======
$pdf->Ln(6);
$pdf->SetFont('Arial', 'B', 9);
$pdf->Cell(0, 5, 'Keterangan', 0, 1);

$pdf->SetFont('Arial', '', 9);
$ket = trim((string)($inv['keterangan_invoice'] ?? ''));
if ($ket === '') $ket = "-";
$pdf->MultiCell(0, 5, $ket, 0, 'L');

// ====== TTD ======
$pdf->Ln(10);
$ySign = $pdf->GetY();
$pdf->Rect(10, $ySign, 95, 28);
$pdf->Rect(105, $ySign, 95, 28);

$pdf->SetFont('Arial', 'B', 9);
$pdf->SetXY(10, $ySign + 2);
$pdf->Cell(95, 6, 'Marketing', 0, 0, 'C');
$pdf->SetXY(105, $ySign + 2);
$pdf->Cell(95, 6, 'Approval Customer', 0, 1, 'C');

$pdf->SetFont('Arial', '', 9);
$pdf->SetXY(12, $ySign + 22);
$pdf->Cell(0, 5, 'Nama: ____________________', 0, 1);
$pdf->SetXY(107, $ySign + 22);
$pdf->Cell(0, 5, 'Nama: ____________________', 0, 1);

$filename = 'INVOICE-' . ($inv['nomor_invoice'] ?? $id_invoice) . '.pdf';
$pdf->Output('D', $filename);
exit();
?>