<?php
if (session_status() === PHP_SESSION_NONE) session_start();

$current = basename($_SERVER['PHP_SELF']);

function isActive($pages, $current)
{
  if (is_array($pages)) return in_array($current, $pages, true) ? 'active open' : '';
  return $pages === $current ? 'active' : '';
}
?>

<!-- Layout wrapper -->
<div class="layout-wrapper layout-content-navbar">
    <div class="layout-container">
    <!-- Menu -->

    <aside id="layout-menu" class="layout-menu menu-vertical menu bg-menu-theme">

        <!-- Brand -->
        <div class="app-brand demo">
            <a href="index.php" class="app-brand-link">
            <span class="app-brand-logo demo">
                <img src="../assets/img/logo-pt.png" alt="Logo PT"
                    style="width:34px;height:34px;object-fit:contain;">
            </span>

            <span class="app-brand-text demo menu-text fw-bolder ms-2">
                <?= $_SESSION['nama'] ?? 'Pelanggan'; ?>
            </span>
            </a>

            <!-- Toggle (mobile) -->
            <a href="javascript:void(0);" class="layout-menu-toggle menu-link text-large ms-auto d-block d-xl-none">
            <i class="bx bx-chevron-left bx-sm align-middle"></i>
            </a>
        </div>
        <!-- /Brand -->

        <div class="menu-inner-shadow"></div>

        <ul class="menu-inner py-1">

            <!-- Dashboard -->
            <li class="menu-item <?= isActive('index.php', $current); ?>">
            <a href="index.php" class="menu-link">
                <i class="menu-icon tf-icons bx bx-home-circle"></i>
                <div>Dashboard</div>
            </a>
            </li>

            <li class="menu-header small text-uppercase">
            <span class="menu-header-text">Kalibrasi</span>
            </li>

            <!-- Ajukan -->
            <li class="menu-item <?= isActive(['pengajuan.php'], $current); ?>">
            <a href="pengajuan.php" class="menu-link">
                <i class="menu-icon tf-icons bx bx-edit"></i>
                <div>Ajukan Kalibrasi</div>
            </a>
            </li>

            <!-- Riwayat -->
            <li class="menu-item <?= isActive(['riwayat_pengajuan.php', 'detail_pengajuan.php'], $current); ?>">
            <a href="riwayat_pengajuan.php" class="menu-link">
                <i class="menu-icon tf-icons bx bx-history"></i>
                <div>Riwayat Pengajuan</div>
            </a>
            </li>

            <!-- Penawaran -->
            <li class="menu-item <?= isActive(['penawaran.php', 'detail_penawaran.php'], $current); ?>">
            <a href="penawaran.php" class="menu-link">
                <i class="menu-icon tf-icons bx bx-receipt"></i>
                <div>Penawaran</div>
            </a>
            </li>

            <!-- Status Proses -->
            <li class="menu-item <?= isActive('status_proses.php', $current); ?>">
            <a href="status_proses.php" class="menu-link">
                <i class="menu-icon tf-icons bx bx-loader-circle"></i>
                <div>Status Proses</div>
            </a>
            </li>

            <li class="menu-header small text-uppercase">
            <span class="menu-header-text">Dokumen</span>
            </li>

            <!-- Sertifikat -->
            <li class="menu-item <?= isActive(['sertifikat.php', 'detail_sertifikat.php'], $current); ?>">
            <a href="sertifikat.php" class="menu-link">
                <i class="menu-icon tf-icons bx bx-certification"></i>
                <div>Sertifikat</div>
            </a>
            </li>

            <!-- Invoice -->
            <li class="menu-item <?= isActive(['invoice.php', 'detail_invoice.php'], $current); ?>">
            <a href="invoice.php" class="menu-link">
                <i class="menu-icon tf-icons bx bx-credit-card"></i>
                <div>Invoice</div>
            </a>
            </li>

            <li class="menu-header small text-uppercase">
            <span class="menu-header-text">Akun</span>
            </li>

            <!-- Profil -->
            <li class="menu-item <?= isActive(['profil.php', 'ubah_password.php'], $current); ?>">
            <a href="profil.php" class="menu-link">
                <i class="menu-icon tf-icons bx bx-user"></i>
                <div>Profil</div>
            </a>
            </li>

            <!-- Hubungi CS -->
            <li class="menu-item <?= isActive(['hub-cs.php', 'detail-hub-cs.php'], $current); ?>">
            <a href="https://wa.me/6285780717207" class="menu-link">
                <i class="menu-icon tf-icons bx bx-support"></i>
                <div>Hubungi CS</div>
            </a>
            </li>

            <!-- Logout -->
            <li class="menu-item">
            <a href="../logout.php" class="menu-link" onclick="return confirm('Yakin ingin logout?');">
                <i class="menu-icon tf-icons bx bx-log-out"></i>
                <div>Logout</div>
            </a>
            </li>

        </ul>
    </aside>
