<?php
include 'config.php';

if(!isset($_SESSION['uid']) || $_SESSION['role'] != 'user') {
    header("Location: index.php");
    exit();
}

$event_id = $_GET['event_id'] ?? 0;
$uid = $_SESSION['uid'];

// 1. Cek apakah user benar-benar hadir di event ini
$cek_hadir = mysqli_query($conn, "SELECT * FROM attendance WHERE user_id='$uid' AND event_id='$event_id'");
if(mysqli_num_rows($cek_hadir) == 0){
    die("Anda belum terdaftar hadir di event ini.");
}

// 2. Ambil data event & template
$q_event = mysqli_query($conn, "SELECT * FROM events WHERE id='$event_id'");
$event = mysqli_fetch_assoc($q_event);

if(!$event || empty($event['cert_template'])){
    die("Template sertifikat tidak tersedia.");
}

// 3. Data Font
$font_path = !empty($event['cert_font']) ? $event['cert_font'] : ''; // URL path
$font_size = $event['cert_font_size']; // px
$font_color = $event['cert_font_color'];
$user_name = $_SESSION['nama'];

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cetak Sertifikat - <?= $event['nama_event'] ?></title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
<style>
        body { background: #f0f2f5; font-family: sans-serif; }
        
        <?php if(!empty($font_path)): ?>
        @font-face {
            font-family: 'CustomFont';
            src: url('<?= $font_path ?>');
        }
        <?php endif; ?>

        #cert-wrapper {
            width: 100%;
            overflow: hidden;
            position: relative;
            background: #666; /* Backdrop gelap biar fokus */
            padding: 10px;
            display: flex;
            justify-content: center;
        }

        #cert-container {
            position: relative;
            display: inline-block;
            box-shadow: 0 4px 8px rgba(0,0,0,0.5);
            background: white;
            transform-origin: top center; /* Scale dari atas tengah */
        }
        
        #cert-image {
            display: block;
            pointer-events: none; /* Biar gak ke-drag gambarnya */
        }

        #draggable-name {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%); /* Default centering logic */
            font-family: <?= !empty($font_path) ? "'CustomFont', sans-serif" : "sans-serif" ?>;
            font-size: <?= $font_size ?>px;
            color: <?= $font_color ?>;
            font-weight: bold;
            white-space: nowrap;
            cursor: move;
            border: 2px dashed rgba(255, 0, 0, 0.5);
            padding: 5px;
            user-select: none;
            z-index: 10;
        }

        /* Saat didownload / preview bersih */
        .clean-mode #draggable-name {
            border: none !important;
            cursor: default;
        }
    </style>
</head>
<body class="py-4">

<div class="container text-center">
    <div class="card shadow border-0 mb-4 mx-auto" style="max-width: 100%;">
        <div class="card-body">
            <h4 class="fw-bold mb-3">Editor Sertifikat</h4>
            
            <div class="row justify-content-center mb-3">
                <div class="col-md-6">
                    <label class="form-label fw-bold">Edit Nama:</label>
                    <input type="text" id="input-name" class="form-control text-center" value="<?= $user_name ?>">
                    <small class="text-muted">Ubah nama jika ada kesalahan ketik.</small>
                </div>
            </div>

            <div class="alert alert-info py-2 small">
                <i class="fa fa-info-circle"></i> Geser nama di gambar bawah ini ke posisi yang pas.
            </div>

            <!-- Area Sertifikat -->
            <div id="cert-wrapper">
                <div id="cert-container">
                    <img id="cert-image" src="<?= $event['cert_template'] ?>" alt="Sertifikat">
                    <div id="draggable-name"><?= $user_name ?></div>
                </div>
            </div>

            <div class="mt-4">
                <a href="dashboard_user.php" class="btn btn-secondary me-2">Kembali</a>
                <button id="btn-download" class="btn btn-primary fw-bold px-4">
                    <i class="fa fa-download me-2"></i>Download Sertifikat
                </button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
<script>
    const dragName = document.getElementById('draggable-name');
    const container = document.getElementById('cert-container');
    const img = document.getElementById('cert-image');
    const inputName = document.getElementById('input-name');
    const wrapper = document.getElementById('cert-wrapper');

    // Update Text Realtime
    inputName.addEventListener('input', function(){
        dragName.innerText = this.value;
    });

    // Responsive Scaling Logic
    function resizeCert() {
        // Balikin transform dulu biar dapet ukuran asli
        container.style.transform = 'none';
        
        let naturalWidth = img.naturalWidth || img.width;
        let naturalHeight = img.naturalHeight || img.height;
        
        // Jika gambar belum load sempurna (naturalWidth 0), tunggu dulu
        if(naturalWidth === 0) return; 

        // Set ukuran container ke ukuran ASLI gambar (biar resolusi terjaga)
        container.style.width = naturalWidth + 'px';
        container.style.height = naturalHeight + 'px';

        // Hitung scale factor berdasarkan lebar layar HP/Desktop
        let wrapperWidth = wrapper.clientWidth - 20; // minus padding
        let scaleStr = 1;

        if(naturalWidth > wrapperWidth){
            scaleStr = wrapperWidth / naturalWidth;
        }

        container.style.transform = `scale(${scaleStr})`;
        
        // Sesuaikan tinggi wrapper biar gak ada ruang kosong berlebih di bawah
        wrapper.style.height = (naturalHeight * scaleStr) + 'px';
    }

    // Jalankan resize saat gambar load & window resize
    img.onload = resizeCert;
    window.addEventListener('resize', resizeCert);
    // Jalankan juga sekarang jaga-jaga kalau cached
    if(img.complete) resizeCert();


    // Dragging Logic (Mouse & Touch)
    let isDragging = false;
    let startX, startY, initialLeft, initialTop;

    function startDrag(e) {
        isDragging = true;
        let clientX = e.type.includes('touch') ? e.touches[0].clientX : e.clientX;
        let clientY = e.type.includes('touch') ? e.touches[0].clientY : e.clientY;

        // Dapatkan posisi relative element saat ini (dalam pixel asli sertifikat, bukan screen pixel)
        // Karena ada scale, gerakan mouse harus dibagi scale_factor
        
        startX = clientX;
        startY = clientY;
        
        // Ambil posisi left/top saat ini (parse px) or computed style
        const style = window.getComputedStyle(dragName);
        // Hati-hati dengan transform: translate(-50%, -50%). 
        // Lebih aman kita mainkan style.left & top langsung.
        
        // Kita butuh matriks transform kalau mau super presisi, tapi kita pakai offset sederhana:
        initialLeft = parseFloat(style.left) || 0;
        initialTop = parseFloat(style.top) || 0;
        
        dragName.style.cursor = 'grabbing';
    }

    function doDrag(e) {
        if (!isDragging) return;
        e.preventDefault(); // Prevent scroll di HP

        let clientX = e.type.includes('touch') ? e.touches[0].clientX : e.clientX;
        let clientY = e.type.includes('touch') ? e.touches[0].clientY : e.clientY;

        // Hitung delta gerakan mouse/finger
        let deltaX = clientX - startX;
        let deltaY = clientY - startY;

        // AMBIL CURRENT SCALE
        // container.style.transform = "scale(0.5)" -> kita butuh angka 0.5
        let currentScale = 1;
        let match = container.style.transform.match(/scale\(([^)]+)\)/);
        if(match && match[1]) currentScale = parseFloat(match[1]);

        // Gerakan elemen harus dibagi scale biar sinkron dengan kursor
        // Misal scale 0.5, mouse gerak 10px, elemen harus gerak 20px di "dunia asli" biar visualnya geser 10px
        let realDeltaX = deltaX / currentScale;
        let realDeltaY = deltaY / currentScale;

        dragName.style.left = (initialLeft + realDeltaX) + 'px';
        dragName.style.top = (initialTop + realDeltaY) + 'px';
        
        // Matikan transform translate centering setelah drag pertama kali biar gak bingungin
        dragName.style.transform = 'none'; 
    }

    function stopDrag() {
        isDragging = false;
        dragName.style.cursor = 'move';
    }

    dragName.addEventListener('mousedown', startDrag);
    document.addEventListener('mousemove', doDrag);
    document.addEventListener('mouseup', stopDrag);

    dragName.addEventListener('touchstart', startDrag);
    document.addEventListener('touchmove', doDrag);
    document.addEventListener('touchend', stopDrag);


    // Download Logic
    document.getElementById('btn-download').addEventListener('click', () => {
        // Toggle class clean-mode di container untuk hidden border
        container.classList.add('clean-mode');

        // Capture
        html2canvas(container, {
            scale: 2, // Export res tinggi
            useCORS: true,
            onclone: (clonedDoc) => {
                // Di dalam clone, kita reset transform biar yang dicapture full size
                let clonedContainer = clonedDoc.getElementById('cert-container');
                clonedContainer.style.transform = 'none';
                
                // Pastikan nama posisi aman (karena kita main left/top absolute, harusnya aman)
            }
        }).then(canvas => {
            container.classList.remove('clean-mode');

            let link = document.createElement('a');
            link.download = 'Sertifikat-<?= str_replace(" ", "-", $user_name) ?>.png';
            link.href = canvas.toDataURL("image/png");
            link.click();
        });
    });
</script>

</body>
</html>
