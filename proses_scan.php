<?php include 'config.php';

// 1. Cek Login User
if (!isset($_SESSION['uid'])) {
    $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
    header("Location: index.php");
    exit();
}

$user_id = $_SESSION['uid'];
$event_id = $_GET['event_id'] ?? 0;
$type = $_GET['type'] ?? 'static';
$token = $_GET['token'] ?? '';
$now_time = date('H:i:s'); 
$now_full = date('Y-m-d H:i:s');

// 2. Validasi Keamanan Token (Khusus Dynamic)
if($type == 'dynamic'){
    $cek_token = mysqli_query($conn, "SELECT * FROM qr_tokens WHERE token='$token' AND expires_at > '$now_full'");
    if(mysqli_num_rows($cek_token) == 0) {
        render_page("danger", "QR Code Expired", "QR Code di layar sudah berganti. Silakan scan ulang.");
    }
}

// 3. CEK SESI BERDASARKAN WAKTU
$q_sesi = mysqli_query($conn, "SELECT * FROM event_sessions 
                               WHERE event_id='$event_id' 
                               AND '$now_time' BETWEEN jam_mulai AND jam_selesai 
                               LIMIT 1");

if(mysqli_num_rows($q_sesi) == 0){
    render_page("warning", "Absensi Tutup", "Saat ini jam: <b>$now_time</b>.<br>Tidak ada sesi absensi yang sedang berjalan.");
}

$sesi = mysqli_fetch_assoc($q_sesi);
$session_id = $sesi['id'];
$session_name = $sesi['nama_sesi'];

// 4. Cek Apakah SUDAH Absen di Sesi Ini?
$cek_absen = mysqli_query($conn, "SELECT * FROM attendance 
                                  WHERE user_id='$user_id' AND session_id='$session_id'");

if(mysqli_num_rows($cek_absen) > 0){
    render_page("info", "Sudah Terdata", "Anda sudah absen untuk sesi:<br><h3>$session_name</h3>");
}

// 5. Simpan Absensi Baru
$insert = mysqli_query($conn, "INSERT INTO attendance (user_id, event_id, session_id, waktu_scan) 
                     VALUES ('$user_id', '$event_id', '$session_id', '$now_full')");

if($insert){
    render_page("success", "Absen Berhasil!", "Terima kasih <b>{$_SESSION['nama']}</b>.<br>Kehadiran tercatat di sesi:<br><h2 class='mt-2'>$session_name</h2>");
} else {
    render_page("danger", "Error Database", "Gagal menyimpan data.");
}

// --- FUNGSI TAMPILAN ---
function render_page($type, $title, $msg){
    $colors = [
        'success' => ['#d1e7dd', '#0f5132', '✅'],
        'danger' => ['#f8d7da', '#842029', '❌'],
        'warning' => ['#fff3cd', '#664d03', '⚠️'],
        'info' => ['#cff4fc', '#055160', 'ℹ️']
    ];
    $bg = $colors[$type][0];
    $text = $colors[$type][1];
    $icon = $colors[$type][2];

    echo '<!DOCTYPE html><html><head>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css"></head>
    <body class="d-flex align-items-center justify-content-center vh-100" style="background-color:'.$bg.'">
        <div class="card shadow border-0 text-center p-4" style="max-width:400px; width:90%">
            <div style="font-size:3rem">'.$icon.'</div>
            <h2 class="fw-bold mt-2" style="color:'.$text.'">'.$title.'</h2>
            <p class="mt-3 text-muted">'.$msg.'</p>
            <a href="dashboard_user.php" class="btn btn-dark w-100 mt-3">Kembali ke Dashboard</a>
        </div>
    </body></html>';
    exit();
}
?>