<?php
include 'config.php';

if (!isset($_SESSION['uid']) || $_SESSION['role'] != 'user') {
    header("Location: index.php");
    exit();
}

function tanggal_indo($tanggal)
{
    $hari = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
    $bulan = ['', 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
    $t = strtotime($tanggal);
    return $hari[date('w', $t)] . ', ' . date('d', $t) . ' ' . $bulan[(int) date('m', $t)] . ' ' . date('Y', $t);
}

$uid = $_SESSION['uid'];
$now_time = date('H:i:s');
$today = date('Y-m-d');

if (isset($_POST['hadir']) && isset($_POST['session_id']) && isset($_POST['event_id'])) {
    $session_id = mysqli_real_escape_string($conn, $_POST['session_id']);
    $event_id = mysqli_real_escape_string($conn, $_POST['event_id']);
    $now_full = date('Y-m-d H:i:s');

    $cek_absen = mysqli_query($conn, "SELECT * FROM attendance WHERE user_id='$uid' AND session_id='$session_id'");

    if (mysqli_num_rows($cek_absen) > 0) {
        $msg_type = "warning";
        $msg = "Anda sudah absen untuk sesi ini.";
    } else {
        $insert = mysqli_query($conn, "INSERT INTO attendance (user_id, event_id, session_id, waktu_scan) VALUES ('$uid', '$event_id', '$session_id', '$now_full')");
        if ($insert) {
            $msg_type = "success";
            $msg = "Alhamdulillah, kehadiran Anda berhasil dicatat!";
        } else {
            $msg_type = "danger";
            $msg = "Gagal mencatat kehadiran.";
        }
    }
}

$q_active_session = mysqli_query($conn, "
    SELECT s.*, e.nama_event, e.id as event_id, e.tanggal
    FROM event_sessions s
    JOIN events e ON s.event_id = e.id
    JOIN event_registrations r ON r.event_id = e.id AND r.user_id = '$uid'
    WHERE e.tanggal = '$today'
    AND '$now_time' BETWEEN s.jam_mulai AND s.jam_selesai
    LIMIT 1
");
$active_session = mysqli_fetch_assoc($q_active_session);

$q_next_session = mysqli_query($conn, "
    SELECT s.*, e.nama_event, e.tanggal, e.id as event_id
    FROM event_sessions s
    JOIN events e ON s.event_id = e.id
    JOIN event_registrations r ON r.event_id = e.id AND r.user_id = '$uid'
    WHERE (e.tanggal = '$today' AND s.jam_mulai > '$now_time')
       OR (e.tanggal > '$today')
    ORDER BY e.tanggal ASC, s.jam_mulai ASC
    LIMIT 1
");
$next_session = mysqli_fetch_assoc($q_next_session);

$already_attended = false;
if ($active_session) {
    $cek = mysqli_query($conn, "SELECT * FROM attendance WHERE user_id='$uid' AND session_id='" . $active_session['id'] . "'");
    $already_attended = mysqli_num_rows($cek) > 0;
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Peserta</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            background-color: #f0f2f5;
        }

        .welcome-card {
            background: linear-gradient(135deg, #0d6efd, #0a58ca);
            color: white;
            border-radius: 15px;
        }

        .session-card {
            border-radius: 15px;
        }

        .btn-hadir {
            padding: 15px 40px;
            font-size: 1.2rem;
            border-radius: 50px;
        }

        .session-active {
            border-left: 5px solid #28a745;
        }

        .session-waiting {
            border-left: 5px solid #ffc107;
        }

        .session-closed {
            border-left: 5px solid #6c757d;
        }

        .pulse {
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0% {
                box-shadow: 0 0 0 0 rgba(40, 167, 69, 0.7);
            }

            70% {
                box-shadow: 0 0 0 15px rgba(40, 167, 69, 0);
            }

            100% {
                box-shadow: 0 0 0 0 rgba(40, 167, 69, 0);
            }
        }
    </style>
</head>

<body>

    <nav class="navbar navbar-light bg-white shadow-sm sticky-top">
        <div class="container">
            <a class="navbar-brand fw-bold text-primary" href="#">
                <i class="fa fa-calendar-check me-2"></i>Daurah Syariyyah
            </a>
            <a href="logout.php" class="btn btn-sm btn-outline-danger fw-bold">Keluar</a>
        </div>
    </nav>

    <div class="container mt-4 mb-5">

        <?php if (isset($msg)): ?>
            <div class="alert alert-<?= $msg_type ?> alert-dismissible fade show" role="alert">
                <i
                    class="fa fa-<?= $msg_type == 'success' ? 'check-circle' : ($msg_type == 'warning' ? 'exclamation-circle' : 'times-circle') ?> me-2"></i>
                <?= $msg ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <div class="card welcome-card mb-4 p-3 shadow-sm border-0">
            <div class="d-flex align-items-center">
                <div class="me-3 text-center ms-2"><i class="fa fa-user-circle fa-3x text-white-50"></i></div>
                <div>
                    <h4 class="fw-bold m-0"><?= $_SESSION['nama'] ?></h4>
                    <small class="d-block mt-1"><i class="fa fa-phone me-1"></i> <?= $_SESSION['nohp'] ?? '' ?></small>
                </div>
            </div>
        </div>

        <div
            class="card session-card shadow-sm mb-4 <?= $active_session ? 'session-active' : ($next_session ? 'session-waiting' : 'session-closed') ?>">
            <div class="card-body text-center py-4">
                <?php if ($active_session): ?>
                    <div class="mb-3">
                        <span class="badge bg-success fs-6 px-3 py-2">
                            <i class="fa fa-circle me-1"></i> Sesi Sedang Berlangsung
                        </span>
                    </div>
                    <h3 class="fw-bold text-primary mb-2"><?= $active_session['nama_event'] ?></h3>
                    <p class="text-muted mb-1">
                        <i class="fa fa-calendar me-1"></i><?= tanggal_indo($active_session['tanggal']) ?>
                    </p>
                    <h4 class="text-dark mb-1"><?= $active_session['nama_sesi'] ?></h4>
                    <p class="text-muted mb-4">
                        <i class="fa fa-clock me-1"></i>
                        <?= date('H:i', strtotime($active_session['jam_mulai'])) ?> -
                        <?= date('H:i', strtotime($active_session['jam_selesai'])) ?> WIB
                    </p>

                    <?php if ($already_attended): ?>
                        <button class="btn btn-secondary btn-hadir" disabled>
                            <i class="fa fa-check-circle me-2"></i>Sudah Hadir
                        </button>
                        <p class="text-success mt-3 mb-0"><i class="fa fa-check me-1"></i>Kehadiran Anda sudah tercatat</p>
                    <?php else: ?>
                        <form method="POST">
                            <input type="hidden" name="session_id" value="<?= $active_session['id'] ?>">
                            <input type="hidden" name="event_id" value="<?= $active_session['event_id'] ?>">
                            <button type="submit" name="hadir" class="btn btn-success btn-hadir pulse">
                                <i class="fa fa-hand me-2"></i>HADIR
                            </button>
                        </form>
                        <p class="text-muted mt-3 mb-0"><small>Tekan tombol untuk mencatat kehadiran</small></p>
                    <?php endif; ?>

                <?php elseif ($next_session): ?>
                    <div class="mb-3">
                        <span class="badge bg-warning text-dark fs-6 px-3 py-2">
                            <i class="fa fa-clock me-1"></i> Menunggu Sesi Berikutnya
                        </span>
                    </div>
                    <h3 class="fw-bold text-primary mb-2"><?= $next_session['nama_event'] ?></h3>
                    <p class="text-muted mb-1">
                        <i class="fa fa-calendar me-1"></i><?= tanggal_indo($next_session['tanggal']) ?>
                    </p>
                    <h4 class="fw-bold text-dark mb-2"><?= $next_session['nama_sesi'] ?></h4>
                    <p class="text-muted mb-3">
                        Dimulai pukul <strong><?= date('H:i', strtotime($next_session['jam_mulai'])) ?> WIB</strong>
                    </p>
                    <button class="btn btn-outline-secondary btn-hadir" disabled>
                        <i class="fa fa-hourglass-half me-2"></i>Tunggu Sesi Dibuka
                    </button>
                    <p class="text-muted mt-3 mb-0"><small>Tombol hadir akan aktif saat sesi dimulai</small></p>

                <?php else: ?>
                    <div class="mb-3">
                        <span class="badge bg-secondary fs-6 px-3 py-2">
                            <i class="fa fa-moon me-1"></i> Tidak Ada Sesi Aktif
                        </span>
                    </div>
                    <p class="text-muted mb-3">Saat ini tidak ada sesi yang berlangsung</p>
                    <button class="btn btn-outline-secondary btn-hadir" disabled>
                        <i class="fa fa-calendar-times me-2"></i>Sesi Ditutup
                    </button>
                <?php endif; ?>

                <div class="mt-4 pt-3 border-top">
                    <small class="text-muted">
                        <i class="fa fa-clock me-1"></i>Waktu server: <?= date('H:i:s') ?> WIB
                    </small>
                </div>
            </div>
        </div>

        <div class="card shadow-sm border-0" style="border-radius:15px;">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0 fw-bold"><i class="fa fa-history text-primary me-2"></i>Riwayat Kehadiran</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-striped mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-3">No</th>
                                <th>Waktu</th>
                                <th>Event & Sesi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $no = 1;
                            $q = mysqli_query($conn, "SELECT a.waktu_scan, e.nama_event, s.nama_sesi FROM attendance a JOIN events e ON a.event_id = e.id LEFT JOIN event_sessions s ON a.session_id = s.id WHERE a.user_id = '$uid' ORDER BY a.waktu_scan DESC LIMIT 10");
                            if (mysqli_num_rows($q) > 0):
                                while ($row = mysqli_fetch_assoc($q)): ?>
                                    <tr>
                                        <td class="ps-3"><?= $no++ ?></td>
                                        <td>
                                            <div class="fw-bold"><?= date('H:i', strtotime($row['waktu_scan'])) ?></div>
                                            <small
                                                class="text-muted"><?= date('d/m/Y', strtotime($row['waktu_scan'])) ?></small>
                                        </td>
                                        <td>
                                            <div class="text-primary fw-bold">
                                                <?= $row['nama_event'] ?>
                                            </div>
                                            <span
                                                class="badge bg-success rounded-pill"><?= $row['nama_sesi'] ?? 'Sesi Umum / Hapus' ?></span>
                                        </td>
                                    </tr>
                                <?php endwhile;
                            else: ?>
                                <tr>
                                    <td colspan="3" class="text-center text-muted py-4">Belum ada riwayat kehadiran</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Section Sertifikat -->
        <div class="card shadow-sm border-0 mt-4" style="border-radius:15px;">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0 fw-bold"><i class="fa fa-certificate text-warning me-2"></i>Sertifikat Saya</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <?php
                    $q_cert = mysqli_query($conn, "SELECT DISTINCT e.id, e.nama_event, e.tanggal, e.cert_template 
                                               FROM attendance a 
                                               JOIN events e ON a.event_id = e.id 
                                               WHERE a.user_id = '$uid' 
                                               AND e.cert_template IS NOT NULL 
                                               AND e.cert_template != '' 
                                               ORDER BY e.tanggal DESC");
                    if (mysqli_num_rows($q_cert) > 0):
                        while ($c = mysqli_fetch_assoc($q_cert)):
                            ?>
                            <div class="col-md-6 mb-3">
                                <div class="border rounded p-3 d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="fw-bold mb-1"><?= $c['nama_event'] ?></h6>
                                        <small class="text-muted"><i
                                                class="fa fa-calendar me-1"></i><?= date('d M Y', strtotime($c['tanggal'])) ?></small>
                                    </div>
                                    <a href="certificate.php?event_id=<?= $c['id'] ?>" class="btn btn-sm btn-outline-primary">
                                        <i class="fa fa-print me-1"></i> Cetak
                                    </a>
                                </div>
                            </div>
                        <?php endwhile;
                    else: ?>
                        <div class="col-12 text-center text-muted py-3">
                            <small>Belum ada sertifikat yang tersedia.</small>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        setTimeout(function () { location.reload(); }, 30000);
    </script>
</body>

</html>