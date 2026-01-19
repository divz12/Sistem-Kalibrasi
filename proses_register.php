<?php
include 'koneksi.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $nama      = trim($_POST['nama'] ?? '');
    $email     = trim($_POST['email'] ?? '');
    $password  = $_POST['password'] ?? '';
    $password2 = $_POST['password2'] ?? '';
    $role      = $_POST['role'] ?? 'pelanggan';

    // fungsi popup alert dan kembali
    function alertBack($msg) {
        echo "<script>alert('$msg'); window.history.back();</script>";
        exit();
    }

    // validasi
    if ($nama === '' || $email === '' || $password === '' || $password2 === '') {
        alertBack("Semua field wajib diisi.");
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        alertBack("Email tidak valid.");
    }

    if ($password !== $password2) {
        alertBack("Password dan konfirmasi password tidak sama.");
    }

    if (strlen($password) < 6) {
        alertBack("Password minimal 6 karakter.");
    }

    $nama  = mysqli_real_escape_string($conn, $nama);
    $email = mysqli_real_escape_string($conn, $email);

    $allowed_roles = ['admin', 'pelanggan'];
    if (!in_array($role, $allowed_roles, true)) {
        $role = 'pelanggan';
    }

    // cek email sudah terdaftar
    $cek = mysqli_query($conn, "SELECT id_user FROM tbl_users WHERE email='$email' LIMIT 1");
    if ($cek && mysqli_num_rows($cek) > 0) {
        alertBack("Email sudah terdaftar, silakan login.");
    }

    // hash password
    $hash = password_hash($password, PASSWORD_BCRYPT);

    $sql = "INSERT INTO tbl_users (nama, email, password, role) 
            VALUES ('$nama', '$email', '$hash', '$role')";

    $simpan = mysqli_query($conn, $sql);

    if ($simpan) {
        echo "<script>
                alert('Registrasi berhasil! Silakan login.');
                window.location.href='login.php';
              </script>";
        exit();
    } else {
        alertBack('Registrasi gagal: " . mysqli_error($conn) . "');
    }
}
?>
