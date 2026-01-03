<?php include 'config.php';
if($_SESSION['role'] != 'admin') header("Location: index.php");

// --- LOGIKA FILTER SQL ---
$where_clauses = [];

// 1. Filter Event
$f_event = $_GET['event_id'] ?? '';
if(!empty($f_event)){
    $where_clauses[] = "a.event_id = '$f_event'";
}

// 2. Filter Tanggal (Start - End)
$f_start = $_GET['start_date'] ?? '';
$f_end   = $_GET['end_date'] ?? '';

if(!empty($f_start) && !empty($f_end)){
    $where_clauses[] = "DATE(a.waktu_scan) BETWEEN '$f_start' AND '$f_end'";
} elseif(!empty($f_start)){
    $where_clauses[] = "DATE(a.waktu_scan) >= '$f_start'";
}

// Gabungkan WHERE
$sql_where = "";
if(count($where_clauses) > 0){
    $sql_where = "WHERE " . implode(' AND ', $where_clauses);
}

// Query Utama
$query_utama = "
    SELECT a.waktu_scan, u.nama AS nama_peserta, u.nohp, u.lembaga, e.nama_event, s.nama_sesi
    FROM attendance a
    JOIN users u ON a.user_id = u.id
    JOIN events e ON a.event_id = e.id
    JOIN event_sessions s ON a.session_id = s.id
    $sql_where
    ORDER BY a.waktu_scan DESC
";
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <title>History Absensi - Admin Daurah</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.3.6/css/buttons.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="bg-light">

<nav class="navbar navbar-dark bg-dark sticky-top p-3">
    <div class="container-fluid">
        <span class="navbar-brand fw-bold">Laporan Kehadiran</span>
        <button class="btn btn-outline-light" type="button" data-bs-toggle="offcanvas" data-bs-target="#adminDrawer">
            <i class="fa fa-bars"></i> Menu
        </button>
    </div>
</nav>

<div class="offcanvas offcanvas-end" tabindex="-1" id="adminDrawer">
    <div class="offcanvas-header bg-dark text-white">
        <h5 class="offcanvas-title">Menu Manajemen</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas"></button>
    </div>
    <div class="offcanvas-body">
        <div class="list-group list-group-flush">
            <a href="dashboard_admin.php" class="list-group-item list-group-item-action">
                <i class="fa fa-calendar-alt me-2"></i> Kelola Event
            </a>
            <a href="manage_users.php" class="list-group-item list-group-item-action">
                <i class="fa fa-users me-2"></i> Kelola User
            </a>
            <a href="history_daurah.php" class="list-group-item list-group-item-action active">
                <i class="fa fa-file-alt me-2"></i> Laporan / History
            </a>
            <a href="logout.php" class="list-group-item list-group-item-action text-danger mt-3">
                <i class="fa fa-sign-out-alt me-2"></i> Logout
            </a>
        </div>
    </div>
</div>

<div class="container mt-4 mb-5">
    
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-header bg-white fw-bold"><i class="fa fa-filter me-2"></i>Filter Data</div>
        <div class="card-body">
            <form method="GET" action="history_daurah.php">
                <div class="row g-3 align-items-end">
                    
                    <div class="col-md-4">
                        <label class="form-label small fw-bold">Nama Daurah / Event</label>
                        <select name="event_id" class="form-select">
                            <option value="">-- Semua Event --</option>
                            <?php 
                            $q_ev = mysqli_query($conn, "SELECT id, nama_event FROM events ORDER BY id DESC");
                            while($e = mysqli_fetch_assoc($q_ev)): 
                                $sel = ($f_event == $e['id']) ? 'selected' : '';
                            ?>
                                <option value="<?= $e['id'] ?>" <?= $sel ?>><?= $e['nama_event'] ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label small fw-bold">Dari Tanggal</label>
                        <input type="date" name="start_date" class="form-control" value="<?= $f_start ?>">
                    </div>

                    <div class="col-md-3">
                        <label class="form-label small fw-bold">Sampai Tanggal</label>
                        <input type="date" name="end_date" class="form-control" value="<?= $f_end ?>">
                    </div>

                    <div class="col-md-2 d-grid gap-2">
                        <button type="submit" class="btn btn-primary"><i class="fa fa-search"></i> Tampilkan</button>
                        <a href="history_daurah.php" class="btn btn-outline-secondary btn-sm">Reset</a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-body">
            <div class="table-responsive">
                <table id="tableHistory" class="table table-striped table-hover align-middle w-100">
                    <thead class="table-dark">
                        <tr>
                            <th>No</th>
                            <th>Waktu Scan</th>
                            <th>Nama Peserta</th>
                            <th>Lembaga</th>
                            <th>Event & Sesi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $no = 1;
                        $result = mysqli_query($conn, $query_utama);
                        while($row = mysqli_fetch_assoc($result)): 
                        ?>
                        <tr>
                            <td><?= $no++ ?></td>
                            <td>
                                <div class="fw-bold"><?= date('H:i', strtotime($row['waktu_scan'])) ?></div>
                                <small class="text-muted"><?= date('d/m/Y', strtotime($row['waktu_scan'])) ?></small>
                            </td>
                            <td>
                                <div class="fw-bold"><?= $row['nama_peserta'] ?></div>
                                <small class="text-muted"><i class="fa fa-phone me-1"></i><?= $row['nohp'] ?></small>
                            </td>
                            <td><?= $row['lembaga'] ?></td>
                            <td>
                                <div class="text-primary fw-bold"><?= $row['nama_event'] ?></div>
                                <span class="badge bg-success rounded-pill"><?= $row['nama_sesi'] ?></span>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.3.6/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.3.6/js/buttons.bootstrap5.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script> <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script> <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>
<script src="https://cdn.datatables.net/buttons/2.3.6/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.3.6/js/buttons.print.min.js"></script>

<script>
    $(document).ready(function() {
        $('#tableHistory').DataTable({
            // Konfigurasi Export Button
            dom: 'Bfrtip',
            buttons: [
                { extend: 'excel', className: 'btn btn-success btn-sm', text: '<i class="fa fa-file-excel"></i> Excel' },
                { extend: 'pdf', className: 'btn btn-danger btn-sm', text: '<i class="fa fa-file-pdf"></i> PDF' },
                { extend: 'print', className: 'btn btn-info btn-sm', text: '<i class="fa fa-print"></i> Print' }
            ],
            language: {
                url: "//cdn.datatables.net/plug-ins/1.13.4/i18n/id.json"
            },
            order: [[ 1, "desc" ]] // Urutkan berdasarkan kolom Waktu Scan (desc)
        });
    });
</script>

</body>
</html>