<?php
include 'komponen/header.php';
include 'komponen/navbar.php';

?>

<main class="main">

  <!-- Hero -->
  <section class="hero section">
    <div class="container" data-aos="fade-up">
      <div class="row align-items-center gy-4">
        <div class="col-lg-7">
          <h1 class="mb-3">Layanan Kalibrasi Alat Ukur</h1>
          <p class="mb-4">
            PT Akbar Tera Abadi menyediakan layanan kalibrasi alat ukur yang dikerjakan oleh teknisi profesional
            untuk memastikan keakuratan dan keandalan pengukuran di perusahaan Anda.
          </p>

          <div class="d-flex flex-wrap gap-2">
            <span class="badge rounded-pill text-bg-primary px-3 py-2">ISO/IEC 17025:2017</span>
            <span class="badge rounded-pill text-bg-light px-3 py-2 border">Cepat</span>
            <span class="badge rounded-pill text-bg-light px-3 py-2 border">Akurat</span>
            <span class="badge rounded-pill text-bg-light px-3 py-2 border">Terjangkau</span>
          </div>

          <div class="mt-4 d-flex flex-column flex-sm-row gap-3">
            <a href="https://wa.me/6285780717207" class="btn btn-primary px-4 py-2">
              <i class="bi bi-whatsapp me-2"></i> Hubungi Kami
            </a>
            <a href="pelanggan/pengajuan.php" class="btn btn-outline-primary px-4 py-2"> Ajukan Kalibrasi
            </a>
          </div>
        </div>

        <div class="col-lg-5 text-lg-end" data-aos="zoom-out" data-aos-delay="200">
          <img src="assets/img/alat.jpg" class="img-fluid rounded-4 shadow-sm" alt="Layanan Kalibrasi">
        </div>
      </div>
    </div>
  </section>

  <!-- Ruang Lingkup -->
  <section class="section">
    <div class="container">

      <div class="section-title" data-aos="fade-up">
        <h2>Ruang Lingkup Kalibrasi</h2>
        <p>Kami melayani berbagai jenis alat ukur sesuai kebutuhan industri dan laboratorium.</p>
      </div>

      <div class="row g-4" data-aos="fade-up" data-aos-delay="100">

        <div class="col-lg-4 col-md-6">
          <div class="p-4 border rounded-4 bg-white h-100 shadow-sm">
            <div class="d-flex align-items-center gap-2 mb-2">
              <i class="bi bi-thermometer-half fs-4 text-primary"></i>
              <h4 class="mb-0">Suhu</h4>
            </div>
            <p class="mb-0">
              Oven, Inkubator, Freezer, Refrigerator, Furnace, Climatic Chamber, Chiller, Thermometer Digital,
              Thermocouple, Infrared Thermometer, Thermometer Gelas.
            </p>
          </div>
        </div>

        <div class="col-lg-4 col-md-6">
          <div class="p-4 border rounded-4 bg-white h-100 shadow-sm">
            <div class="d-flex align-items-center gap-2 mb-2">
              <i class="bi bi-speedometer2 fs-4 text-primary"></i>
              <h4 class="mb-0">Massa</h4>
            </div>
            <p class="mb-0">
              Timbangan Digital/Mekanik, Check Weigher, Anak Timbang, Jembatan Timbang, Tangki Air, Moisture Analyzer.
            </p>
          </div>
        </div>

        <div class="col-lg-4 col-md-6">
          <div class="p-4 border rounded-4 bg-white h-100 shadow-sm">
            <div class="d-flex align-items-center gap-2 mb-2">
              <i class="bi bi-droplet fs-4 text-primary"></i>
              <h4 class="mb-0">Volumetrik</h4>
            </div>
            <p class="mb-0">
              Mikropipet, Glassware (Pipet Gondok, Pipet Volume, Labu Ukur, Piknometer, dll).
            </p>
          </div>
        </div>

        <div class="col-lg-4 col-md-6">
          <div class="p-4 border rounded-4 bg-white h-100 shadow-sm">
            <div class="d-flex align-items-center gap-2 mb-2">
              <i class="bi bi-graph-up fs-4 text-primary"></i>
              <h4 class="mb-0">Instrument Analisa</h4>
            </div>
            <p class="mb-0">
              pH Meter, Conductivity Meter, Viscometer.
            </p>
          </div>
        </div>

        <div class="col-lg-4 col-md-6">
          <div class="p-4 border rounded-4 bg-white h-100 shadow-sm">
            <div class="d-flex align-items-center gap-2 mb-2">
              <i class="bi bi-arrows-expand fs-4 text-primary"></i>
              <h4 class="mb-0">Gaya &amp; Tekanan</h4>
            </div>
            <p class="mb-0">
              Torque Wrench, Pressure Gauge, Vacuum Gauge, Pressure/Vacuum Transmitter, Differential Pressure, Test Gauge.
            </p>
          </div>
        </div>

        <div class="col-lg-4 col-md-6">
          <div class="p-4 border rounded-4 bg-white h-100 shadow-sm">
            <div class="d-flex align-items-center gap-2 mb-2">
              <i class="bi bi-rulers fs-4 text-primary"></i>
              <h4 class="mb-0">Dimensi</h4>
            </div>
            <p class="mb-0">
              Caliper, Micrometer, Dial Indicator, Thickness Gauge, Mistar Baja, Measuring Microscope, Height Gauge.
            </p>
          </div>
        </div>

      </div>
    </div>
  </section>

  <!-- Jenis Layanan -->
  <section class="section light-background">
    <div class="container">
      <div class="section-title" data-aos="fade-up">
        <h2>Jenis Layanan</h2>
        <p>Pilih layanan yang paling sesuai dengan kebutuhan Anda.</p>
      </div>

      <div class="row g-4" data-aos="fade-up" data-aos-delay="100">

        <div class="col-lg-3 col-md-6">
          <div class="p-4 border rounded-4 bg-white h-100">
            <i class="bi bi-building fs-3 text-primary"></i>
            <h4 class="mt-2">Kalibrasi Lab</h4>
            <p class="mb-0">Kalibrasi dilakukan di fasilitas/laboratorium sesuai prosedur.</p>
          </div>
        </div>

        <div class="col-lg-3 col-md-6">
          <div class="p-4 border rounded-4 bg-white h-100">
            <i class="bi bi-geo-alt fs-3 text-primary"></i>
            <h4 class="mt-2">Kalibrasi On Site</h4>
            <p class="mb-0">Kalibrasi dilakukan di lokasi pelanggan untuk efisiensi.</p>
          </div>
        </div>

        <div class="col-lg-3 col-md-6">
          <div class="p-4 border rounded-4 bg-white h-100">
            <i class="bi bi-journal-check fs-3 text-primary"></i>
            <h4 class="mt-2">Sertifikat Kalibrasi</h4>
            <p class="mb-0">Dokumen hasil kalibrasi untuk kebutuhan audit & mutu.</p>
          </div>
        </div>

        <div class="col-lg-3 col-md-6">
          <div class="p-4 border rounded-4 bg-white h-100">
            <i class="bi bi-people fs-3 text-primary"></i>
            <h4 class="mt-2">Konsultasi</h4>
            <p class="mb-0">Bantuan menentukan kebutuhan, jadwal, dan jenis kalibrasi.</p>
          </div>
        </div>

      </div>
    </div>
  </section>

<!-- Alur Proses Kalibrasi -->
<section class="section">
  <div class="container">

    <div class="section-title" data-aos="fade-up">
      <h2>Alur Proses Kalibrasi</h2>
      <p>Langkah mudah dari pengajuan sampai alat kembali ke pelanggan.</p>
    </div>

    <div class="row g-4" data-aos="fade-up" data-aos-delay="100">

      <div class="col-lg-3 col-md-6">
        <div class="p-4 border rounded-4 h-100 bg-white shadow-sm">
          <span class="badge text-bg-primary mb-2">1</span>
          <h5>Mengajukan Permintaan</h5>
          <p class="mb-0">Pelanggan mengajukan permintaan kalibrasi alat.</p>
        </div>
      </div>

      <div class="col-lg-3 col-md-6">
        <div class="p-4 border rounded-4 h-100 bg-white shadow-sm">
          <span class="badge text-bg-primary mb-2">2</span>
          <h5>Penawaran Disiapkan</h5>
          <p class="mb-0">Kami menyiapkan penawaran sesuai kebutuhan pelanggan.</p>
        </div>
      </div>

      <div class="col-lg-3 col-md-6">
        <div class="p-4 border rounded-4 h-100 bg-white shadow-sm">
          <span class="badge text-bg-primary mb-2">3</span>
          <h5>Kesepakatan</h5>
          <p class="mb-0">Setelah disepakati, proses kerja sama dimulai.</p>
        </div>
      </div>

      <div class="col-lg-3 col-md-6">
        <div class="p-4 border rounded-4 h-100 bg-white shadow-sm">
          <span class="badge text-bg-primary mb-2">4</span>
          <h5>Pengambilan Alat</h5>
          <p class="mb-0">Alat dijemput oleh tim kami sesuai jadwal.</p>
        </div>
      </div>

    </div>

    <div class="row g-4 mt-1" data-aos="fade-up" data-aos-delay="150">

      <div class="col-lg-4 col-md-6">
        <div class="p-4 border rounded-4 h-100 bg-white shadow-sm">
          <span class="badge text-bg-primary mb-2">5</span>
          <h5>Pemeriksaan & Kalibrasi</h5>
          <p class="mb-0">Alat diperiksa dan dikalibrasi oleh teknisi.</p>
        </div>
      </div>

      <div class="col-lg-4 col-md-6">
        <div class="p-4 border rounded-4 h-100 bg-white shadow-sm">
          <span class="badge text-bg-primary mb-2">6</span>
          <h5>Pencatatan Hasil</h5>
          <p class="mb-0">Hasil kalibrasi dicatat dan disiapkan sebagai dokumen.</p>
        </div>
      </div>

      <div class="col-lg-4 col-md-6">
        <div class="p-4 border rounded-4 h-100 bg-white shadow-sm">
          <span class="badge text-bg-primary mb-2">7</span>
          <h5>Sertifikat Terbit</h5>
          <p class="mb-0">Sertifikat kalibrasi resmi diterbitkan.</p>
        </div>
      </div>

      <div class="col-lg-6 col-md-6">
        <div class="p-4 border rounded-4 h-100 bg-white shadow-sm">
          <span class="badge text-bg-primary mb-2">8</span>
          <h5>Pengiriman Tagihan</h5>
          <p class="mb-0">Tagihan dikirim kepada pelanggan.</p>
        </div>
      </div>

      <div class="col-lg-6 col-md-6">
        <div class="p-4 border rounded-4 h-100 bg-white shadow-sm">
          <span class="badge text-bg-primary mb-2">9</span>
          <h5>Pengembalian Alat</h5>
          <p class="mb-0">Alat, sertifikat, dan dokumen dikirim kembali ke pelanggan.</p>
        </div>
      </div>

    </div>

  </div>
</section>



</main>

<?php
    include 'komponen/footer.php';
?>