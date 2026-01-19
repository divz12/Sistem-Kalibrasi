<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <title>Login - PT Akbar Tera Abadi</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <link href="assets/img/logo2.png" rel="icon">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
</head>

<body class="min-vh-100 d-flex align-items-center justify-content-center bg-gradient"
      style="background: linear-gradient(135deg,#d16fff,#6a5cff);">

  <div class="container">
    <div class="row justify-content-center">
      <div class="col-11 col-sm-9 col-md-6 col-lg-4">

        <div class="position-relative" style="z-index:2;">

          <!-- CARD -->
          <div class="card border-0 shadow-lg rounded-4 text-center pt-5">
            <div class="card-body px-4 pb-4">

              <h4 class="fw-bold mb-5">Sign In</h4>

              <form action="proses_login.php" method="post" class="text-start">

                <div class="mb-3">
                  <label class="form-label fw-semibold">Email</label>
                  <input type="email" name="email" class="form-control" placeholder=" Masukkan Email" required>
                </div>

                <div class="mb-2">
                  <label class="form-label fw-semibold">Password</label>
                  <input type="password" name="password" class="form-control" placeholder="Masukkan Password" required>
                </div>

                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="remember" id="remember">
                        <label class="form-check-label small" for="remember">
                        Remember Me
                        </label>
                    </div>
                    <a href="forgot_password.php" class="text-decoration-none small text-muted">Forget password?</a>
                </div>

                <button type="submit" class="btn btn-primary w-100 fw-semibold py-2 mt-4">
                  SIGN IN
                </button>
                <a href="index.php" class="btn btn-outline-primary w-100 fw-semibold py-2 mt-2">
                    BACK
                </a>

                <div class="text-center mt-3 small">
                  <span class="text-muted">Do not have an account?</span>
                  <a href="register.php" class="text-decoration-none fw-semibold">Sign Up</a>
                </div>

              </form>

            </div>
          </div>

        <!-- Logo -->
        <div class="position-absolute top-0 start-50 translate-middle" style="pointer-events:none;">
        <div class="bg-white rounded-circle shadow d-flex align-items-center justify-content-center"
            style="width:86px;height:86px;">
            <div class="bg-light rounded-circle d-flex align-items-center justify-content-center overflow-hidden"
                style="width:74px;height:74px;">
            <img src="assets/img/logo-pt.png" alt="Logo PT"
                class="img-fluid"
                style="max-width:60px; max-height:60px;">
            </div>
        </div>
        </div>



        </div>
        <!-- /position-relative -->

      </div>
    </div>
  </div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
