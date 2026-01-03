<?php
include 'config.php';

$event_id = $_GET['event_id'];
$token = bin2hex(random_bytes(16));
$now = date('Y-m-d H:i:s');
$expire = date('Y-m-d H:i:s', strtotime('+15 seconds'));

mysqli_query($conn, "INSERT INTO qr_tokens (token, event_id, expires_at) VALUES ('$token', '$event_id', '$expire')");

$url = base_url("proses_scan.php?event_id=$event_id&type=dynamic&token=$token");

echo '<img src="https://api.qrserver.com/v1/create-qr-code/?size=400x400&data='.urlencode($url).'" />';