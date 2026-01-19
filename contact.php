<?php
include 'komponen/header.php';
include 'komponen/navbar.php';
?>

<main class="main">

  <!-- Contact Section -->
  <section id="contact" class="contact section">
    <div class="container section-title pt-5 mt-5" data-aos="fade-up">
      <h2>Contact</h2>
      <p>Silakan kirim pesan, kami akan segera membalas.</p>
    </div>

    <div class="container" data-aos="fade-up" data-aos-delay="100">

     <?php if (($_GET['msg'] ?? '') == 'ok'): ?>
      <script>
        alert("Terima kasih, pesan kamu sudah kami terima.\nAdmin/CS akan segera membalas.");

        // bersihkan parameter msg dari URL
        if (window.history.replaceState) {
          const url = new URL(window.location.href);
          url.searchParams.delete('msg');
          window.history.replaceState({}, document.title, url.pathname + url.search);
        }
      </script>
    <?php endif; ?>

    <?php if (($_GET['msg'] ?? '') == 'err'): ?>
      <script>
        alert("Pesan gagal dikirim. Silakan coba lagi.");

        if (window.history.replaceState) {
          const url = new URL(window.location.href);
          url.searchParams.delete('msg');
          window.history.replaceState({}, document.title, url.pathname + url.search);
        }
      </script>
    <?php endif; ?>


      <!-- Info -->
      <div class="row gy-4">
        <div class="col-lg-6">
          <div class="info-item d-flex flex-column justify-content-center align-items-center"
               data-aos="fade-up" data-aos-delay="200">
            <i class="bi bi-geo-alt"></i>
            <h3>Address</h3>
            <p>Kab. Bogor, Jawa Barat, Indonesia</p>
          </div>
        </div>

        <div class="col-lg-3 col-md-6">
          <div class="info-item d-flex flex-column justify-content-center align-items-center"
               data-aos="fade-up" data-aos-delay="300">
            <i class="bi bi-telephone"></i>
            <h3>Call Us</h3>
            <p>+62 857-8071-7207</p>
          </div>
        </div>

        <div class="col-lg-3 col-md-6">
          <div class="info-item d-flex flex-column justify-content-center align-items-center"
               data-aos="fade-up" data-aos-delay="400">
            <i class="bi bi-envelope"></i>
            <h3>Email Us</h3>
            <p>akbarteraabadi@gmail.com</p>
          </div>
        </div>
      </div>

      <!-- Form -->
      <div class="row gy-4 mt-1">
        <div class="col">
          <form action="proses_contact.php" method="post"
                class="php-email-form" data-aos="fade-up" data-aos-delay="400">
            <div class="row gy-4">

              <div class="col-md-6">
                <input type="text" name="name" class="form-control" placeholder="Your Name" required>
              </div>

              <div class="col-md-6">
                <input type="email" class="form-control" name="email" placeholder="Your Email" required>
              </div>

              <div class="col-md-12">
                <input type="text" class="form-control" name="subject" placeholder="Subject" required>
              </div>

              <div class="col-md-12">
                <textarea class="form-control" name="message" rows="6" placeholder="Message" required></textarea>
              </div>

              <div class="col-md-12 text-center">
                <button type="submit">Send Message</button>
              </div>

            </div>
          </form>
        </div>
      </div>

    </div>
  </section>
  <!-- /Contact Section -->

</main>

<?php include 'komponen/footer.php'; ?>

<script>
document.addEventListener("DOMContentLoaded", function () {
  const form = document.querySelector("form.php-email-form");
  if (!form) return;

  // hapus data form setelah submit untuk mencegah pengiriman ulang
  const clone = form.cloneNode(true);
  form.parentNode.replaceChild(clone, form);
});
</script>
