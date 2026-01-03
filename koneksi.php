<?php
$host = "localhost";
$user = "root";
$pass = "";
$db = "db_dauroh";
$conn = mysqli_connect($host, $user, $pass, $db);

function getSetting($key) {
    global $conn;
    $q = mysqli_query($conn, "SELECT setting_value FROM settings WHERE setting_key='$key'");
    return mysqli_fetch_assoc($q)['setting_value'];
}

$base_url = "http://192.168.1.5/absensi";
?>
