<?php
include 'config.php';

if ($_SESSION['role'] != 'admin') {
    header("Location: index.php");
    exit();
}

if (isset($_GET['hapus'])) {
    $id = $_GET['hapus'];
    mysqli_query($conn, "DELETE FROM event_registrations WHERE event_id='$id'");
    mysqli_query($conn, "DELETE FROM event_sessions WHERE event_id='$id'");
    mysqli_query($conn, "DELETE FROM attendance WHERE event_id='$id'");
    mysqli_query($conn, "DELETE FROM events WHERE id='$id'");
    header("Location: dashboard_admin.php");
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>

<body class="bg-light">

    <nav class="navbar navbar-dark bg-dark sticky-top p-3">
        <div class="container-fluid">
            <span class="navbar-brand fw-bold">Admin Dashboard</span>
            <button class="btn btn-outline-light" type="button" data-bs-toggle="offcanvas" data-bs-target="#adminDrawer">
                <i class="fa fa-bars"></i> Menu
            </button>
        </div>
    </nav>

    <div class="offcanvas offcanvas-end" tabindex="-1" id="adminDrawer" aria-labelledby="drawerLabel">
        <div class="offcanvas-header bg-dark text-white">
            <h5 class="offcanvas-title" id="drawerLabel">Management Menu</h5>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas"></button>
        </div>
        <div class="offcanvas-body">
            <div class="list-group list-group-flush">
                <a href="dashboard_admin.php" class="list-group-item list-group-item-action <?= (basename($_SERVER['PHP_SELF']) == 'dashboard_admin.php') ? 'active' : '' ?>">
                    <i class="fa fa-calendar-alt me-2"></i> Manage Events
                </a>
                <a href="manage_users.php" class="list-group-item list-group-item-action <?= (basename($_SERVER['PHP_SELF']) == 'manage_users.php') ? 'active' : '' ?>">
                    <i class="fa fa-users me-2"></i> Manage Participants
                </a>
                <a href="history_daurah.php" class="list-group-item list-group-item-action <?= (basename($_SERVER['PHP_SELF']) == 'history_daurah.php') ? 'active' : '' ?>">
                    <i class="fa fa-file-alt me-2"></i> Reports / History
                </a>
                <a href="logout.php" class="list-group-item list-group-item-action text-danger mt-3">
                    <i class="fa fa-sign-out-alt me-2"></i> Logout
                </a>
            </div>
        </div>
    </div>

    <div class="container mt-4 mb-5">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h3>Event List</h3>
            <a href="tambah_event.php" class="btn btn-primary"><i class="fa fa-plus"></i> Add Event</a>
        </div>

        <div class="card shadow-sm border-0">
            <div class="card-body">
                <div class="table-responsive">
                    <table id="tableEvent" class="table table-hover align-middle">
                        <thead class="table-secondary">
                            <tr>
                                <th>No</th>
                                <th>Event Name</th>
                                <th>Date</th>
                                <th>Sessions</th>
                                <th>Expected Attendance</th>
                                <th>Mode</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $no = 1;
                            $query = "SELECT e.*,
                                      COUNT(DISTINCT s.id) as total_sesi,
                                      (SELECT COUNT(*) FROM event_registrations r WHERE r.event_id = e.id AND r.status = 'confirmed') as total_hadir
                                      FROM events e
                                      LEFT JOIN event_sessions s ON e.id = s.event_id
                                      GROUP BY e.id ORDER BY e.tanggal DESC";

                            $result = mysqli_query($conn, $query);

                            if (!$result) {
                                die("Query Error: " . mysqli_error($conn));
                            }

                            while ($row = mysqli_fetch_assoc($result)): ?>
                                <tr>
                                    <td><?= $no++ ?></td>
                                    <td class="fw-bold"><?= $row['nama_event'] ?></td>
                                    <td><?= date('d M Y', strtotime($row['tanggal'])) ?></td>
                                    <td><span class="badge bg-secondary"><?= $row['total_sesi'] ?> Sessions</span></td>
                                    <td>
                                        <span class="badge bg-success fs-6">
                                            <i class="fa fa-users"></i> <?= $row['total_hadir'] ?> People
                                        </span>
                                    </td>
                                    <td><?= strtoupper($row['qr_mode']) ?></td>
                                    <td>
                                        <a href="monitor.php?event_id=<?= $row['id'] ?>" target="_blank" class="btn btn-warning btn-sm" title="Monitor QR"><i class="fa fa-tv"></i></a>
                                        <a href="download_qr.php?event_id=<?= $row['id'] ?>" class="btn btn-success btn-sm" title="Download QR"><i class="fa fa-qrcode"></i></a>
                                        <a href="edit_event.php?id=<?= $row['id'] ?>" class="btn btn-info btn-sm text-white" title="Edit"><i class="fa fa-pencil"></i></a>
                                        <a href="dashboard_admin.php?hapus=<?= $row['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Delete this event and all its data?')" title="Delete"><i class="fa fa-trash"></i></a>
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
    <script> $(document).ready(function () { $('#tableEvent').DataTable(); }); </script>

</body>
</html>
