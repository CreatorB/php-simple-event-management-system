<?php
include 'config.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    header("Location: index.php");
    exit();
}

$event_id = $_GET['event_id'] ?? 0;

$ev = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM events WHERE id='$event_id'"));
if (!$ev) {
    die("Event not found");
}

$url = base_url("proses_scan.php?event_id=" . $event_id . "&type=static");

$qr_api_url = "https://api.qrserver.com/v1/create-qr-code/?size=400x400&format=png&data=" . urlencode($url);

$image_data = file_get_contents($qr_api_url);

if ($image_data === false) {
    die("Failed to generate QR Code");
}

$nama_file = preg_replace('/[^a-zA-Z0-9_-]/', '_', $ev['nama_event']);
$filename = "qrcode_" . $nama_file . ".png";

header('Content-Type: image/png');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Content-Length: ' . strlen($image_data));

echo $image_data;
exit();
