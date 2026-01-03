<?php
include 'config.php';

if(!isset($_SESSION['uid']) || $_SESSION['role'] != 'user') {
    header("Location: index.php");
    exit();
}

$uid = $_SESSION['uid'];

if(isset($_POST['reg_id']) && isset($_POST['status'])){
    $reg_id = mysqli_real_escape_string($conn, $_POST['reg_id']);
    $status = mysqli_real_escape_string($conn, $_POST['status']);

    $update = mysqli_query($conn, "UPDATE event_registrations SET status='$status' WHERE id='$reg_id' AND user_id='$uid'");

    if($update){
        $pesan = ($status == 'confirmed') ? "Your attendance has been confirmed." : "Thank you for your response.";
        echo "<script>alert('$pesan'); window.location='dashboard_user.php';</script>";
    } else {
        echo "<script>alert('Failed to update data.');</script>";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Participant Dashboard</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://unpkg.com/html5-qrcode" type="text/javascript"></script>
    <style>
        body { background-color: #f0f2f5; padding-bottom: 80px; }
        .welcome-card { background: linear-gradient(135deg, #0d6efd, #0a58ca); color: white; border-radius: 15px; }
        .fab-scan { position: fixed; bottom: 30px; right: 30px; background-color: #ffc107; color: #000; width: 65px; height: 65px; border-radius: 50%; display: flex; align-items: center; justify-content: center; box-shadow: 0 4px 15px rgba(0,0,0,0.3); border: none; z-index: 1000; }
        #reader { width: 100%; background: #000; border-radius: 10px; }
        .card-invite { border-left: 5px solid #ffc107; }
    </style>
</head>
<body>

<nav class="navbar navbar-light bg-white shadow-sm sticky-top">
    <div class="container">
        <a class="navbar-brand fw-bold text-primary" href="#">
            <i class="fa fa-calendar-check me-2"></i>Event Attendance
        </a>
        <a href="logout.php" class="btn btn-sm btn-outline-danger fw-bold">Logout</a>
    </div>
</nav>

<div class="container mt-4 mb-5">

    <div class="card welcome-card mb-4 p-3 shadow-sm border-0">
        <div class="d-flex align-items-center justify-content-between">
            <div class="d-flex align-items-center">
                <div class="me-3 text-center ms-2"><i class="fa fa-user-circle fa-3x text-white-50"></i></div>
                <div>
                    <h4 class="fw-bold m-0"><?= $_SESSION['nama'] ?></h4>
                    <small class="d-block mt-1 mb-2"><i class="fa fa-phone me-1"></i> <?= isset($_SESSION['nohp'])?$_SESSION['nohp']:'' ?></small>
                    <a href="edit_profile.php" class="btn btn-sm btn-light text-primary fw-bold rounded-pill px-3"><i class="fa fa-edit"></i> Edit Profile</a>
                </div>
            </div>
            <button class="btn btn-warning fw-bold d-none d-md-block" data-bs-toggle="modal" data-bs-target="#scanModal">SCAN QR</button>
        </div>
    </div>

    <?php
    $q_inv = mysqli_query($conn, "SELECT r.id as reg_id, e.nama_event, e.tanggal
                                  FROM event_registrations r
                                  JOIN events e ON r.event_id = e.id
                                  WHERE r.user_id = '$uid' AND r.status = 'pending'
                                  ORDER BY e.tanggal ASC");

    if(mysqli_num_rows($q_inv) > 0):
    ?>
    <div class="mb-4">
        <h5 class="fw-bold text-dark mb-3"><i class="fa fa-envelope-open-text text-warning me-2"></i>Event Invitations</h5>
        <?php while($inv = mysqli_fetch_assoc($q_inv)): ?>
        <div class="card card-invite shadow-sm mb-3">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <h5 class="fw-bold text-primary mb-1"><?= $inv['nama_event'] ?></h5>
                        <p class="text-muted mb-2"><i class="fa fa-calendar-alt me-2"></i><?= date('d M Y', strtotime($inv['tanggal'])) ?></p>
                        <small class="text-danger">* Please confirm your attendance.</small>
                    </div>
                    <div class="col-md-4 mt-3 mt-md-0 text-end">
                        <form method="POST" class="d-flex gap-2 justify-content-end">
                            <input type="hidden" name="reg_id" value="<?= $inv['reg_id'] ?>">
                            <button type="submit" name="status" value="declined" class="btn btn-outline-secondary btn-sm" onclick="return confirm('Are you sure you cannot attend?')">
                                Cannot<br>Attend
                            </button>
                            <button type="submit" name="status" value="confirmed" class="btn btn-success btn-sm fw-bold">
                                Will<br>Attend
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <?php endwhile; ?>
    </div>
    <?php endif; ?>

    <div class="card shadow-sm border-0" style="border-radius:15px;">
        <div class="card-header bg-white py-3"><h5 class="mb-0 fw-bold"><i class="fa fa-history text-primary me-2"></i>Attendance History</h5></div>
        <div class="card-body p-0 p-md-3">
            <div class="table-responsive">
                <table id="historyTable" class="table table-striped w-100">
                    <thead class="table-light"><tr><th class="ps-3">No</th><th>Time</th><th>Event & Session</th></tr></thead>
                    <tbody>
                        <?php
                        $no = 1;
                        $q = mysqli_query($conn, "SELECT a.waktu_scan, e.nama_event, s.nama_sesi FROM attendance a JOIN events e ON a.event_id = e.id JOIN event_sessions s ON a.session_id = s.id WHERE a.user_id = '$uid' ORDER BY a.waktu_scan DESC");
                        while($row = mysqli_fetch_assoc($q)): ?>
                        <tr>
                            <td class="ps-3"><?= $no++ ?></td>
                            <td><div class="fw-bold"><?= date('H:i', strtotime($row['waktu_scan'])) ?></div><small><?= date('d/m/y', strtotime($row['waktu_scan'])) ?></small></td>
                            <td><div class="fw-bold text-primary"><?= $row['nama_event'] ?></div><span class="badge bg-success rounded-pill"><?= $row['nama_sesi'] ?></span></td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<button class="fab-scan d-md-none" data-bs-toggle="modal" data-bs-target="#scanModal"><i class="fa fa-qrcode fa-lg"></i></button>

<div class="modal fade" id="scanModal" tabindex="-1" aria-labelledby="scanModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-dark text-white"><h5 class="modal-title"><i class="fa fa-qrcode me-2"></i>Scan QR</h5><button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button></div>
            <div class="modal-body bg-black text-center p-0"><div id="reader"></div></div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>
<script>
$(document).ready(function(){
    $('#historyTable').DataTable({ordering:false, lengthChange:false, pageLength:5});

    let html5QrcodeScanner;
    const onScanSuccess = (decodedText) => { html5QrcodeScanner.clear(); $('#scanModal').modal('hide'); window.location.href = decodedText; }
    $('#scanModal').on('shown.bs.modal', function () { html5QrcodeScanner = new Html5QrcodeScanner("reader", { fps: 10, qrbox: 250 }, false); html5QrcodeScanner.render(onScanSuccess); });
    $('#scanModal').on('hidden.bs.modal', function () { if(html5QrcodeScanner) html5QrcodeScanner.clear(); });
});
</script>
</body>
</html>
