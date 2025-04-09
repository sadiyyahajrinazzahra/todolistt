<?php
session_start();

// Cek apakah pengguna sudah login
if (!isset($_SESSION['username'])) {
    header("Location: login.html"); // Arahkan kembali ke halaman login jika belum login
    exit();
}

echo "Selamat datang, " . $_SESSION['username'];
?>

<!-- Tambahkan konten lainnya untuk dashboard pengguna -->
