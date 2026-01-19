<!-- Layout container -->
<div class="layout-page">
    <!-- Navbar -->

    <?php
        $idUser = $_SESSION['id_user'];

        $ambilFoto = mysqli_query($conn, "SELECT foto FROM tbl_users WHERE id_user = '$idUser' LIMIT 1");
        $dataFoto = mysqli_fetch_assoc($ambilFoto);

        $fotoUser = $dataFoto['foto'] ?? "";

        $fotoTampil = "../assets/img/logo-pt.png";

        // ambil foto
        if ($fotoUser != "") {
        $fotoTampil = "../uploads/foto/" . $fotoUser;
        }
    ?>

    <nav class="layout-navbar container-xxl navbar navbar-expand-xl navbar-detached align-items-center bg-navbar-theme" id="layout-navbar">
        <div class="layout-menu-toggle navbar-nav align-items-xl-center me-3 me-xl-0 d-xl-none">
            <a class="nav-item nav-link px-0 me-xl-4" href="javascript:void(0)">
            <i class="bx bx-menu bx-sm"></i>
            </a>
        </div>

        <div class="navbar-nav-right d-flex align-items-center" >

            <!-- Search -->
            <form action="riwayat_pengajuan.php" method="get" class="d-flex flex-grow-1 mx-3">
            <div class="input-group w-100">
                <span class="input-group-text bg-transparent border-0">
                <i class="bx bx-search fs-4"></i>
                </span>
                <input 
                type="text" 
                name="q" 
                class="form-control border-0 shadow-none"
                placeholder="Cari pengajuan / penawaran"
                aria-label="Search"
                >
            </div>
            </form>
            <!-- /Search -->

            <ul class="navbar-nav flex-row align-items-center ms-auto">

            <!-- User -->
            <li class="nav-item navbar-dropdown dropdown-user dropdown">
                <a class="nav-link dropdown-toggle hide-arrow" href="javascript:void(0);" data-bs-toggle="dropdown">
                <div class="avatar avatar-online">
                    <img src="<?= $fotoTampil ?>" alt="Avatar" class="w-px-40 h-auto rounded-circle" />
                </div>
                </a>

                <ul class="dropdown-menu dropdown-menu-end">
                <li>
                    <a class="dropdown-item" href="profil.php">
                    <div class="d-flex">
                        <div class="flex-shrink-0 me-3">
                        <div class="avatar avatar-online">
                            <img src="<?= $fotoTampil ?>" alt="Avatar" class="w-px-40 h-auto rounded-circle" />
                        </div>
                        </div>
                        <div class="flex-grow-1">
                        <span class="fw-semibold d-block">
                            <?= $_SESSION['nama'] ?? 'Pelanggan'; ?>
                        </span>
                        <small class="text-muted">
                            <?= ucfirst($_SESSION['role'] ?? 'pelanggan'); ?>
                        </small>
                        </div>
                    </div>
                    </a>
                </li>

                <li><div class="dropdown-divider"></div></li>

                <li>
                    <a class="dropdown-item" href="profil.php">
                    <i class="bx bx-user me-2"></i>
                    <span class="align-middle">Profil Saya</span>
                    </a>
                </li>

                <li>
                    <a class="dropdown-item" href="https://wa.me/6285780717207" target="_blank">
                    <i class="bx bx-support me-2"></i>
                    <span class="align-middle">Hubungi CS</span>
                    </a>
                </li>

                <li><div class="dropdown-divider"></div></li>

                <li>
                    <a class="dropdown-item" href="../logout.php" onclick="return confirm('Yakin ingin logout?');">
                    <i class="bx bx-power-off me-2"></i>
                    <span class="align-middle">Logout</span>
                    </a>
                </li>
                </ul>
            </li>
            <!--/ User -->

            </ul>
        </div>
    </nav>

 <!-- / Navbar -->