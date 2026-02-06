<?php include 'config.php';
if ($_SESSION['role'] != 'admin')
    header("Location: index.php");

// AMBIL DATA USER UNTUK DITAMPILKAN DI LIST
$query_users = mysqli_query($conn, "SELECT id, nama, nohp, lembaga FROM users WHERE role='user' ORDER BY nama ASC");

if (isset($_POST['simpan_event'])) {
    $nama = mysqli_real_escape_string($conn, $_POST['nama_event']);
    $tgl = $_POST['tanggal'];
    $mode = $_POST['qr_mode'];

    // Upload Config
    $upload_dir = 'uploads/certificates/';
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }

    $cert_template = NULL;
    $cert_font = NULL;
    $cert_font_size = $_POST['cert_font_size'] ?? 30;
    $cert_font_color = $_POST['cert_font_color'] ?? '#000000';

    // Handle Template Upload
    if (isset($_FILES['cert_template']) && $_FILES['cert_template']['error'] == 0) {
        $ext = pathinfo($_FILES['cert_template']['name'], PATHINFO_EXTENSION);
        $new_name = 'tmpl_' . time() . '.' . $ext;
        if (move_uploaded_file($_FILES['cert_template']['tmp_name'], $upload_dir . $new_name)) {
            $cert_template = $upload_dir . $new_name;
        }
    }

    // Handle Font Upload
    if (isset($_FILES['cert_font']) && $_FILES['cert_font']['error'] == 0) {
        $ext = pathinfo($_FILES['cert_font']['name'], PATHINFO_EXTENSION);
        $new_name = 'font_' . time() . '.' . $ext;
        if (move_uploaded_file($_FILES['cert_font']['tmp_name'], $upload_dir . $new_name)) {
            $cert_font = $upload_dir . $new_name;
        }
    }

    // 1. Simpan Event
    $q_insert = "INSERT INTO events (nama_event, tanggal, qr_mode, cert_template, cert_font, cert_font_size, cert_font_color) 
                 VALUES ('$nama', '$tgl', '$mode', '$cert_template', '$cert_font', '$cert_font_size', '$cert_font_color')";
    mysqli_query($conn, $q_insert);
    $event_id = mysqli_insert_id($conn);

    // 2. Simpan Sesi
    $sesi_nama = $_POST['sesi_nama'];
    $jam_mulai = $_POST['jam_mulai'];
    $jam_selesai = $_POST['jam_selesai'];

    for ($i = 0; $i < count($sesi_nama); $i++) {
        if (!empty($sesi_nama[$i])) {
            $nm = mysqli_real_escape_string($conn, $sesi_nama[$i]);
            $s = $jam_mulai[$i];
            $e = $jam_selesai[$i];
            mysqli_query($conn, "INSERT INTO event_sessions (event_id, nama_sesi, jam_mulai, jam_selesai) VALUES ('$event_id', '$nm', '$s', '$e')");
        }
    }

    // 3. FITUR BARU: Broadcast Undangan (Sesuai Pilihan)
    if (isset($_POST['invited_users'])) {
        $selected_users = $_POST['invited_users']; // Ini berbentuk Array ID
        $count_invite = 0;

        foreach ($selected_users as $uid) {
            // Masukkan ke tabel registrasi (Status default: pending)
            mysqli_query($conn, "INSERT INTO event_registrations (event_id, user_id, status) VALUES ('$event_id', '$uid', 'pending')");
            $count_invite++;
        }
        $msg = "Event Dibuat & $count_invite Undangan Terkirim!";
    } else {
        $msg = "Event Dibuat tanpa undangan.";
    }

    echo "<script>alert('$msg'); window.location='dashboard_admin.php';</script>";
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Event</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <style>
        /* Agar list user tidak memanjang ke bawah */
        .user-scroll-box {
            max-height: 300px;
            overflow-y: auto;
            border: 1px solid #dee2e6;
            background: #fff;
        }
    </style>
</head>

<body class="bg-light">

    <div class="container mt-5 mb-5" style="max-width: 900px;">
        <div class="card shadow border-0">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">Buat Event Baru</h5>
            </div>
            <div class="card-body">
                <form method="POST" enctype="multipart/form-data">

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="fw-bold">Nama Event</label>
                            <input type="text" name="nama_event" class="form-control" required
                                placeholder="Contoh: Daurah Akbar">
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="fw-bold">Tanggal</label>
                            <input type="date" name="tanggal" class="form-control" required>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="fw-bold">Mode QR</label>
                            <select name="qr_mode" class="form-select">
                                <option value="static">Statis (Tempel)</option>
                                <option value="dynamic">Dinamis (Layar)</option>
                            </select>
                        </div>
                    </div>

                    <!-- Bagian Template Sertifikat -->
                    <h5 class="mt-3 border-bottom pb-2 text-info">Pengaturan Sertifikat</h5>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="fw-bold">Template Sertifikat (JPG/PNG)</label>
                            <input type="file" name="cert_template" class="form-control" accept="image/*">
                            <small class="text-muted">Kosongkan jika tidak ada sertifikat.</small>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="fw-bold">Font Nama (TTF/OTF)</label>
                            <input type="file" name="cert_font" class="form-control" accept=".ttf,.otf">
                            <small class="text-muted">Default: Arial/System default jika kosong.</small>
                        </div>
                        <div class="col-md-2 mb-3">
                            <label class="fw-bold">Ukuran Font</label>
                            <input type="number" name="cert_font_size" class="form-control" value="30">
                        </div>
                        <div class="col-md-2 mb-3">
                            <label class="fw-bold">Warna Font</label>
                            <input type="color" name="cert_font_color" class="form-control form-control-color"
                                value="#000000" title="Pilih warna font">
                        </div>
                    </div>

                    <h5 class="mt-3 border-bottom pb-2">Jadwal Sesi</h5>
                    <div class="table-responsive mb-3">
                        <table class="table table-bordered">
                            <thead class="table-light">
                                <tr>
                                    <th>Nama Sesi</th>
                                    <th>Mulai</th>
                                    <th>Selesai</th>
                                    <th>#</th>
                                </tr>
                            </thead>
                            <tbody id="sesiContainer">
                                <tr>
                                    <td><input type="text" name="sesi_nama[]" class="form-control" required
                                            placeholder="Sesi 1"></td>
                                    <td><input type="time" name="jam_mulai[]" class="form-control" required></td>
                                    <td><input type="time" name="jam_selesai[]" class="form-control" required></td>
                                    <td><button type="button" class="btn btn-danger btn-sm" disabled>X</button></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <button type="button" class="btn btn-success btn-sm" onclick="tambahSesi()">+ Tambah Sesi</button>

                    <h5 class="mt-4 border-bottom pb-2 text-primary">Kirim Undangan (RSVP)</h5>
                    <p class="text-muted small">Pilih peserta yang akan diundang. Centang "Pilih Semua" untuk mengundang
                        seluruh user.</p>

                    <div class="card bg-light border-0">
                        <div
                            class="card-header bg-secondary text-white d-flex justify-content-between align-items-center">
                            <span>Daftar User</span>

                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="checkAll">
                                <label class="form-check-label fw-bold text-white" for="checkAll"
                                    style="cursor:pointer">
                                    Pilih Semua
                                </label>
                            </div>
                        </div>

                        <div class="user-scroll-box p-0">
                            <table class="table table-hover table-striped mb-0">
                                <thead class="sticky-top bg-light">
                                    <tr>
                                        <th width="40" class="text-center">#</th>
                                        <th>Nama Peserta</th>
                                        <th>Lembaga</th>
                                        <th>No HP</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (mysqli_num_rows($query_users) > 0): ?>
                                        <?php while ($u = mysqli_fetch_assoc($query_users)): ?>
                                            <tr>
                                                <td class="text-center">
                                                    <input class="form-check-input user-checkbox" type="checkbox"
                                                        name="invited_users[]" value="<?= $u['id'] ?>">
                                                </td>
                                                <td><?= $u['nama'] ?></td>
                                                <td class="small text-muted"><?= $u['lembaga'] ?></td>
                                                <td class="small text-muted"><?= $u['nohp'] ?></td>
                                            </tr>
                                        <?php endwhile; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="4" class="text-center text-muted p-4">Belum ada data user.</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                        <div class="card-footer text-muted small">
                            * User yang dicentang akan melihat info event di dashboard mereka untuk konfirmasi
                            kehadiran.
                        </div>
                    </div>

                    <hr class="mt-4">
                    <div class="d-grid gap-2">
                        <button type="submit" name="simpan_event" class="btn btn-primary btn-lg fw-bold">Simpan Event &
                            Kirim Undangan</button>
                        <a href="dashboard_admin.php" class="btn btn-secondary">Batal</a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        // 1. Logic Tambah Sesi
        function tambahSesi() {
            var row = `<tr><td><input type="text" name="sesi_nama[]" class="form-control"></td><td><input type="time" name="jam_mulai[]" class="form-control"></td><td><input type="time" name="jam_selesai[]" class="form-control"></td><td><button type="button" class="btn btn-danger btn-sm" onclick="this.closest('tr').remove()">X</button></td></tr>`;
            document.getElementById('sesiContainer').insertAdjacentHTML('beforeend', row);
        }

        // 2. Logic Check All / Uncheck All
        document.getElementById('checkAll').addEventListener('change', function () {
            var checkboxes = document.querySelectorAll('.user-checkbox');
            for (var checkbox of checkboxes) {
                checkbox.checked = this.checked;
            }
        });
    </script>
</body>

</html>