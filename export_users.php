<?php
include 'config.php';

if ($_SESSION['role'] != 'admin') {
    header("Location: index.php");
    exit();
}

header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="users_data.csv"');

$output = fopen('php://output', 'w');

// Header CSV: No, Nama, Lembaga, Domisili, HP
fputcsv($output, array('No', 'Nama', 'Lembaga', 'Domisili', 'HP'));

$query = "SELECT nama, lembaga, alamat, nohp FROM users WHERE role='user' ORDER BY id DESC";
$result = mysqli_query($conn, $query);

$no = 1;
while ($row = mysqli_fetch_assoc($result)) {
    // Map database columns to CSV columns
    // DB: alamat -> CSV: Domisili
    fputcsv($output, array(
        $no++,
        $row['nama'],
        $row['lembaga'],
        $row['alamat'], // Domisili maps to alamat in DB based on register.php
        $row['nohp']
    ));
}

fclose($output);
exit();
?>