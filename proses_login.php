<?php
include 'koneksi.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = $_POST['password'];

    $sql = "SELECT * FROM tbl_users WHERE email='$email' LIMIT 1";

    $hasil = mysqli_query($conn, $sql);

    if ($hasil && mysqli_num_rows($hasil) == 1) {

        $row = mysqli_fetch_assoc($hasil);

        if (password_verify($password, $row['password'])) {

            $_SESSION['login_user'] = $row['email']; 
            $_SESSION['id_user']    = $row['id_user'];
            $_SESSION['nama']    = $row['nama'];
            $_SESSION['role']       = $row['role'];

            if ($row['role'] == 'admin') {
                header("Location: admin/index.php");
            } elseif ($row['role'] == 'owner') {
                header("Location: owner/index.php");
            } else {
                header("Location: pelanggan/index.php");
            }

            exit();

        } else {
            echo "<script>alert('Email atau Password salah'); window.location.href='login.php';</script>";
            exit();
        }

    } else {
        echo "<script>alert('Email atau Password salah'); window.location.href='login.php';</script>";
        exit();
    }
}
?>
