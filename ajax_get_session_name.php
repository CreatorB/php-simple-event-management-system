<?php
include 'config.php';

$event_id = $_GET['event_id'] ?? 0;
$now = date('H:i:s');

$query = "SELECT * FROM event_sessions
          WHERE event_id = '$event_id'
          AND '$now' BETWEEN jam_mulai AND jam_selesai
          LIMIT 1";

$result = mysqli_query($conn, $query);

if(mysqli_num_rows($result) > 0){
    $row = mysqli_fetch_assoc($result);
    echo "<span style='color: #4caf50;'>Active Session: " . $row['nama_sesi'] . "</span>";
} else {
    echo "<span style='color: #f44336;'>Attendance Closed (No active session)</span>";
}