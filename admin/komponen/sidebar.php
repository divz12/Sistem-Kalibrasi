<?php
if (session_status() === PHP_SESSION_NONE) session_start();

$current = basename($_SERVER['PHP_SELF']);

function menuActive($pages, $current) {
  if (is_array($pages)) return in_array($current, $pages, true) ? 'active open' : '';
  return ($pages === $current) ? 'active' : '';
}

$namaAdmin = $_SESSION['nama'] ?? 'Admin';

$root = "https://akbarteraabadi.page.gd/";
?>

<!-- Layout wrapper -->
<div class="layout-wrapper layout-content-navbar">
  <div class="layout-container">


<!-- Menu (Sidebar) -->
<aside id="layout-menu" class="layout-menu menu-vertical menu bg-menu-theme">

  <!-- Brand -->
  <div class="app-brand demo">
    <a href="index.php" class="app-brand-link">
      <span class="app-brand-logo demo">
        <img src="<?= $base; ?>assets/img/logo-pt.png" alt="Logo"
          style="width:34px;height:34px;object-fit:contain;">
      </span>
      <span class="app-brand-text demo menu-text fw-bolder ms-2">
        <?= $namaAdmin; ?>
      </span>
    </a>

    <a href="javascript:void(0);" class="layout-menu-toggle menu-link text-large ms-auto d-block d-xl-none">
      <i class="bx bx-chevron-left bx-sm align-middle"></i>
    </a>
  </div>
  <!-- /Brand -->

  <div class="menu-inner-shadow"></div>

  <ul class="menu-inner py-1">

    <!-- Dashboard -->
    <li class="menu-item <?= menuActive('index.php', $current); ?>">
      <a href="<?= $root; ?>admin/index.php" class="menu-link">
        <i class="menu-icon tf-icons bx bx-home-circle"></i>
        <div>Dashboard</div>
      </a>
    </li>

    <!-- DATA MASTER -->
    <li class="menu-header small text-uppercase">
      <span class="menu-header-text">Data Master</span>
    </li>

    <li class="menu-item <?= menuActive(['pelanggan.php','detail_pelanggan.php'], $current); ?>">
      <a href="<?= $root; ?>admin/Data-Pelanggan/pelanggan.php" class="menu-link">
        <i class="menu-icon tf-icons bx bx-group"></i>
        <div>Data Pelanggan</div>
      </a>
    </li>

    <li class="menu-item <?= menuActive(['pengguna.php','users_detail.php'], $current); ?>">
      <a href="<?= $root; ?>admin/Data-Pengguna/pengguna.php" class="menu-link">
        <i class="menu-icon tf-icons bx bx-user"></i>
        <div>Data Pengguna</div>
      </a>
    </li>

    <!-- KALIBRASI -->
    <li class="menu-header small text-uppercase">
      <span class="menu-header-text">Kalibrasi</span>
    </li>

    <li class="menu-item <?= menuActive(['pengajuan.php','pengajuan_detail.php'], $current); ?>">
      <a href="<?= $root; ?>admin/Pengajuan/pengajuan.php" class="menu-link">
        <i class="menu-icon tf-icons bx bx-edit"></i>
        <div>Pengajuan Kalibrasi</div>
      </a>
    </li>

    <li class="menu-item <?= menuActive(['alat_pengajuan.php','alat_detail.php'], $current); ?>">
      <a href="<?= $root; ?>admin/Data-Alat/alat_pengajuan.php" class="menu-link">
        <i class="menu-icon tf-icons bx bx-cube"></i>
        <div>Data Alat Pengajuan</div>
      </a>
    </li>

    <li class="menu-item <?= menuActive(['penawaran.php','penawaran_detail.php'], $current); ?>">
      <a href="<?= $root; ?>admin/Penawaran/penawaran.php" class="menu-link">
        <i class="menu-icon tf-icons bx bx-receipt"></i>
        <div>Penawaran</div>
      </a>
    </li>

    <!-- PROSES & DOKUMEN -->
    <li class="menu-header small text-uppercase">
      <span class="menu-header-text">Proses & Dokumen</span>
    </li>

    <li class="menu-item <?= menuActive(['status_proses.php','status_detail.php'], $current); ?>">
      <a href="<?= $root; ?>admin/Status-Proses/status_proses.php" class="menu-link">
        <i class="menu-icon tf-icons bx bx-loader-circle"></i>
        <div>Status Proses</div>
      </a>
    </li>

    <li class="menu-item <?= menuActive(['sertifikat.php','sertifikat_detail.php'], $current); ?>">
      <a href="<?= $root; ?>admin/Sertifikat/sertifikat.php" class="menu-link">
        <i class="menu-icon tf-icons bx bx-certification"></i>
        <div>Sertifikat</div>
      </a>
    </li>

    <li class="menu-item <?= menuActive(['invoice.php','invoice_detail.php'], $current); ?>">
      <a href="<?= $root; ?>admin/Invoice/invoice.php" class="menu-link">
        <i class="menu-icon tf-icons bx bx-credit-card"></i>
        <div>Invoice</div>
      </a>
    </li>

    <li class="menu-header small text-uppercase">
      <span class="menu-header-text"></span>
    </li>

    <!-- Logout -->
    <li class="menu-item mt-2">
      <a href="<?= $root; ?>logout.php" class="menu-link" onclick="return confirm('Yakin ingin logout?');">
        <i class="menu-icon tf-icons bx bx-log-out"></i>
        <div>Logout</div>
      </a>
    </li>

  </ul>
</aside>
<!-- / Menu -->
