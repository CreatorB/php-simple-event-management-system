<?php
session_start();
include 'koneksi.php';

if (!isset($_SESSION['user_id'])) {
    die("Silakan Login Dulu"); // Arahkan ke login.php
}

$user_id = $_SESSION['user_id'];
$tipe    = $_GET['tipe'] ?? '';
$token   = $_GET['token'] ?? '';
$now     = date('Y-m-d H:i:s');

// --- LOGIC VALIDASI ---
$valid = false;

if ($tipe == 'static') {
    // 1. Cek Mode Statis
    $token_db = getSetting('static_token'); // Ambil kunci rahasia dari DB
    if ($token === $token_db) {
        $valid = true;
    } else {
        die("QR Code Salah / Tidak Dikenali.");
    }

} elseif ($tipe == 'dynamic') {
    // 2. Cek Mode Dinamis (Expire time)
    $cek = mysqli_query($conn, "SELECT * FROM qr_tokens WHERE token='$token' AND expires_at > '$now'");
    if (mysqli_num_rows($cek) > 0) {
        $valid = true;
    } else {
        die("QR Code Kadaluwarsa. Scan ulang di layar TV.");
    }
} else {
    die("Parameter Salah.");
}

// --- SIMPAN ABSEN JIKA VALID ---
if ($valid) {
    // Cek Duplikat Absen Harian
    $cek_absen = mysqli_query($conn, "SELECT * FROM attendance WHERE user_id='$user_id' AND DATE(waktu_absen) = CURDATE()");
    
    if (mysqli_num_rows($cek_absen) > 0) {
        echo "<h1>Sudah Absen!</h1><p>Anda sudah tercatat hari ini.</p>";
    } else {
        mysqli_query($conn, "INSERT INTO attendance (user_id, waktu_absen) VALUES ('$user_id', '$now')");
        echo "<h1>Sukses!</h1><p>Selamat datang, " . $_SESSION['nama'] . "</p>";
    }
}
?>