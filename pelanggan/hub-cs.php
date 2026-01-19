<?php
session_start();
include "../koneksi.php";

// proteksi login
if (!isset($_SESSION['id_user']) || ($_SESSION['role'] ?? '') !== 'pelanggan') {
  header("Location: ../login.php");
  exit();
}

$idUser = (int)($_SESSION['id_user'] ?? 0);

$idPelanggan = 0;
$sqlPelanggan = "SELECT id_pelanggan FROM tbl_pelanggan WHERE id_user = '$idUser' LIMIT 1";
$hasilPelanggan = mysqli_query($conn, $sqlPelanggan);
$dataPelanggan = mysqli_fetch_assoc($hasilPelanggan);
$idPelanggan = (int)($dataPelanggan['id_pelanggan'] ?? 0);

if ($idPelanggan <= 0) {
  header("Location: profil.php");
  exit();
}

// ambil chat
$dataChat = [];
$sqlChat = "
  SELECT id_pesan, kontak, pesan, waktu_kirim, balasan_otomatis, status_baca_admin
  FROM tbl_pesan_cs
  WHERE id_pelanggan = '$idPelanggan'
  ORDER BY waktu_kirim ASC, id_pesan ASC
";
$hasilChat = mysqli_query($conn, $sqlChat);

while ($row = mysqli_fetch_assoc($hasilChat)) {
  $dataChat[] = $row;
}

include 'komponen/header.php';
include 'komponen/sidebar.php';
include 'komponen/navbar.php';
?>

<div class="content-wrapper">
  <div class="container-xxl flex-grow-1 container-p-y">

    <div class="d-flex justify-content-between align-items-center mb-3">
      <div>
        <h4 class="fw-bold mb-1">Hubungi CS</h4>
        <p class="text-muted mb-0">Kirim pertanyaan kamu.</p>
      </div>
      <a href="index.php" class="btn btn-outline-primary">
        <i class="bx bx-arrow-back me-1"></i> Kembali
      </a>
    </div>

    <?php if (isset($_GET['msg']) && $_GET['msg'] === 'ok'): ?>
      <div class="alert alert-success">Pesan berhasil dikirim ✅</div>
    <?php elseif (isset($_GET['msg']) && $_GET['msg'] === 'err'): ?>
      <div class="alert alert-danger">Gagal mengirim pesan ❌</div>
    <?php endif; ?>

    <div class="card shadow-sm border-0">
      <div class="card-header d-flex justify-content-between align-items-center">
        <div>
          <h5 class="mb-0">Ruang Chat Customer Service</h5>
        </div>
        <span class="badge bg-label-primary">Online</span>
      </div>

      <!-- AREA CHAT -->
      <div id="chatBox" class="card-body" style="height: 420px; overflow-y:auto; background:#f7f7fb;">

        <?php if (count($dataChat) === 0): ?>
          <div class="text-center text-muted mt-5">
            <i class="bx bx-message-rounded-dots fs-1 mb-2"></i>
            <p class="mb-0">Belum ada pesan.</p>
            <small>Mulai chat dengan mengetik pesan di bawah.</small>
          </div>
        <?php endif; ?>

        <?php foreach ($dataChat as $chat): ?>

          <?php if (!empty($chat['pesan'])): ?>
            <!-- Pesan dari pelanggan -->
            <div class="d-flex justify-content-end mt-4 mb-5">
              <div class="p-3 rounded-4 text-white" style="max-width: 72%; background:#696cff;">
                <div><?= $chat['pesan']; ?></div>
                <div class="small mt-2" style="opacity:.8;">
                  <?= $chat['waktu_kirim']; ?>
                </div>
              </div>
            </div>
          <?php endif; ?>

          <?php if (!empty($chat['balasan_otomatis'])): ?>
            <!-- Balasan CS -->
            <div class="d-flex justify-content-start mb-3">
              <div class="p-3 rounded-4 border bg-white" style="max-width: 72%;">
                <div class="fw-semibold mb-1 text-primary">CS</div>
                <div><?= $chat['balasan_otomatis']; ?></div>
                <div class="small text-muted mt-2">
                  <?= $chat['waktu_kirim']; ?>
                </div>
              </div>
            </div>
          <?php endif; ?>

        <?php endforeach; ?>

      </div>
      <!-- /AREA CHAT -->

      <!-- FORM KIRIM PESAN -->
      <div class="card-footer bg-white">
        <form action="proses_hub_cs.php" method="post" class="row g-2 align-items-end">
          <input type="hidden" name="id_pelanggan" value="<?= $idPelanggan; ?>">

          <div class="col-md-3">
            <label class="form-label fw-semibold mb-1">Kontak</label>
            <input type="text" name="kontak" class="form-control" placeholder="No WA / Email" required>
          </div>

          <div class="col-md-7">
            <label class="form-label fw-semibold mb-1">Pesan</label>
            <input type="text" name="pesan" class="form-control" placeholder="Ketik pesan kamu..." required>
          </div>

          <div class="col-md-2 d-grid">
            <button type="submit" class="btn btn-primary">
              <i class="bx bx-send me-1"></i> Kirim
            </button>
          </div>
        </form>
      </div>
      <!-- /FORM -->

    </div>

  </div>
</div>

<script>
  const chatBox = document.getElementById('chatBox');
  if (chatBox) chatBox.scrollTop = chatBox.scrollHeight;
</script>

<?php include 'komponen/footer.php'; ?>
