<?php include 'config.php';
$event_id = $_GET['event_id'] ?? 0;
$ev = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM events WHERE id='$event_id'"));
if(!$ev) die("Event tidak ditemukan");
$mode = $ev['qr_mode'];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Monitor - <?= $ev['nama_event'] ?></title>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        body { 
            background: #121212; color: white; 
            font-family: sans-serif;
            display: flex; flex-direction: column;
            justify-content: center; align-items: center; 
            min-height: 100vh; margin: 0; padding: 20px;
        }
        /* Judul Responsif */
        h1 { margin: 0; font-size: clamp(1.5rem, 5vw, 3.5rem); text-align: center; }
        
        /* Box Sesi Responsif */
        .session-box { 
            background: #333; padding: 15px 30px; border-radius: 50px; 
            margin: 20px 0; 
            font-size: clamp(1rem, 3vw, 2rem); /* Font menyesuaikan layar */
            font-weight: bold; color: #ffd700;
            box-shadow: 0 4px 15px rgba(0,0,0,0.5);
            text-align: center;
            width: 100%; max-width: 800px;
        }
        
        /* Wrapper QR Responsif */
        .qr-wrapper { 
            background: white; padding: 20px; border-radius: 20px; 
            display: inline-block;
            max-width: 100%; /* Agar tidak melebar keluar layar HP */
        }
        
        /* Gambar QR Responsif */
        .qr-wrapper img {
            width: 100%;
            height: auto;
            max-width: 400px; /* Max lebar di desktop */
            min-width: 250px; /* Min lebar di HP */
        }

        .footer { margin-top: 20px; color: #888; font-size: 0.9rem; text-align: center; }
    </style>
</head>
<body>
    <h1><?= $ev['nama_event'] ?></h1>
    
    <div id="session-info" class="session-box">Memuat Sesi...</div>

    <div class="qr-wrapper">
        <div id="qr-area">
            <?php if($mode == 'static'): 
                $url = base_url("proses_scan.php?event_id=" . $event_id . "&type=static");
            ?>
                <img src="https://api.qrserver.com/v1/create-qr-code/?size=400x400&data=<?= urlencode($url) ?>" alt="QR Code" />
            <?php else: ?>
                <div style="padding:50px; color:black">Loading...</div>
            <?php endif; ?>
        </div>
    </div>
    
    <div class="footer">
        Mode: <?= strtoupper($mode) ?> | Scan untuk Absensi
    </div>

    <script>
        function updateSessionInfo() {
            $.get('ajax_get_session_name.php?event_id=<?= $event_id ?>', function(data) {
                $('#session-info').html(data);
            });
        }
        setInterval(updateSessionInfo, 5000);
        updateSessionInfo();

        <?php if($mode == 'dynamic'): ?>
        function updateQR() {
            $.get('ajax_generate_qr.php?event_id=<?= $event_id ?>', function(data) {
                $('#qr-area').html(data);
            });
        }
        setInterval(updateQR, 10000);
        updateQR(); 
        <?php endif; ?>
    </script>
</body>
</html>