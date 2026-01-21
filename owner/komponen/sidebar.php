<?php
if (session_status() === PHP_SESSION_NONE) session_start();

$current = basename($_SERVER['PHP_SELF']);

function menuActive($pages, $current) {
  if (is_array($pages)) return in_array($current, $pages, true) ? 'active open' : '';
  return ($pages === $current) ? 'active' : '';
}

$namaOwner = $_SESSION['nama'] ?? 'Owner';
$root = "/Sistem-Kalibrasi/";
?>

<!-- Layout wrapper -->
<div class="layout-wrapper layout-content-navbar">
  <div class="layout-container">

    <!-- Menu (Sidebar) -->
    <aside id="layout-menu" class="layout-menu menu-vertical menu bg-menu-theme">

      <!-- Brand -->
      <div class="app-brand demo">
        <a href="<?= $root; ?>owner/index.php" class="app-brand-link">
          <span class="app-brand-logo demo">
            <img src="<?= $base; ?>assets/img/logo-pt.png" alt="Logo" style="width:34px;height:34px;object-fit:contain;">
          </span>
          <span class="app-brand-text demo menu-text fw-bolder ms-2"><?= $namaOwner; ?></span>
        </a>

        <a href="javascript:void(0);" class="layout-menu-toggle menu-link text-large ms-auto d-block d-xl-none">
          <i class="bx bx-chevron-left bx-sm align-middle"></i>
        </a>
      </div>
      <!-- /Brand -->

      <div class="menu-inner-shadow"></div>

      <ul class="menu-inner py-1">

        <li class="menu-item <?= menuActive('index.php', $current); ?>">
          <a href="<?= $root; ?>owner/index.php" class="menu-link">
            <i class="menu-icon tf-icons bx bx-home-circle"></i>
            <div>Dashboard</div>
          </a>
        </li>

        <li class="menu-header small text-uppercase">
          <span class="menu-header-text">Monitoring</span>
        </li>


        <li class="menu-item <?= menuActive('laporan.php', $current); ?>">
          <a href="<?= $root; ?>owner/laporan.php" class="menu-link">
            <i class="menu-icon tf-icons bx bx-bar-chart-alt-2"></i>
            <div>Laporan</div>
          </a>
        </li>

        <li class="menu-header small text-uppercase">
          <span class="menu-header-text"></span>
        </li>

        <li class="menu-item mt-2">
          <a href="<?= $root; ?>logout.php" class="menu-link" onclick="return confirm('Yakin ingin logout?');">
            <i class="menu-icon tf-icons bx bx-log-out"></i>
            <div>Logout</div>
          </a>
        </li>

      </ul>
    </aside>
    <!-- / Menu -->
