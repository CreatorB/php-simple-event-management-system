<?php
header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="template_users.csv"');

$output = fopen('php://output', 'w');

// Header
fputcsv($output, array('No', 'Nama', 'Lembaga', 'Domisili', 'HP'));

// Sample Data
fputcsv($output, array('1', 'Contoh Nama', 'Contoh Lembaga', 'Contoh Kota', '08123456789'));

fclose($output);
exit();
?>