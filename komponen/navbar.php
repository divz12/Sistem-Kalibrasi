<?php
$currentPage = basename($_SERVER['PHP_SELF']);
?>


<body class="index-page">

  <header id="header" class="header d-flex align-items-center fixed-top">
    <div class="container-fluid container-xl position-relative d-flex align-items-center">

      <a href="index.html" class="logo d-flex align-items-center me-auto">
        <img src="assets/img/logo-pt.png" alt="">
        <h1 class="sitename">PT Akbar Tera Abadi</h1>
      </a>

      <nav id="navmenu" class="navmenu">
        <ul>
          <li>
            <a href="index.php" class="<?= ($currentPage == 'index.php') ? 'active' : '' ?>">
              Home
            </a>
          </li>
          <li>
            <a href="about.php" class="<?= ($currentPage == 'about.php') ? 'active' : '' ?>">
              About
            </a>
          </li>
          <li>
            <a href="services.php" class="<?= ($currentPage == 'services.php') ? 'active' : '' ?>">
              Layanan Kalibrasi
            </a>
          </li>
          <li>
            <a href="contact.php" class="<?= ($currentPage == 'contact.php') ? 'active' : '' ?>">
              Contact
            </a>
          </li>
        </ul>
        <i class="mobile-nav-toggle d-xl-none bi bi-list"></i>
      </nav>


      <a class="btn-getstarted" href="login.php">Login</a>

    </div>
  </header>