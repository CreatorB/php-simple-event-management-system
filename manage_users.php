<?php include 'config.php';
if ($_SESSION['role'] != 'admin')
    header("Location: index.php");

// PROSES DELETE USER
if (isset($_GET['delete_id'])) {
    $did = $_GET['delete_id'];
    // Hapus history absen dulu biar tidak error constraint
    mysqli_query($conn, "DELETE FROM attendance WHERE user_id='$did'");
    mysqli_query($conn, "DELETE FROM users WHERE id='$did'");
    header("Location: manage_users.php");
}

// PROSES TAMBAH USER (Manual oleh Admin)
if (isset($_POST['add_user'])) {
    $nama = $_POST['nama'];
    $nohp = $_POST['nohp'];
    $pass = $_POST['password'];
    $lembaga = $_POST['lembaga'];

    $cek = mysqli_query($conn, "SELECT id FROM users WHERE nohp='$nohp'");
    if (mysqli_num_rows($cek) == 0) {
        mysqli_query($conn, "INSERT INTO users (nama, nohp, password, lembaga, role) VALUES ('$nama', '$nohp', '$pass', '$lembaga', 'user')");
        echo "<script>alert('User berhasil ditambahkan'); window.location='manage_users.php';</script>";
    } else {
        echo "<script>alert('Gagal: No HP sudah ada');</script>";
    }
}

// PROSES IMPORT USER CSV
$import_feedback = "";
if (isset($_POST['import_users'])) {
    if ($_FILES['file_csv']['name']) {
        $filename = explode(".", $_FILES['file_csv']['name']);
        if (end($filename) == "csv") {
            $handle = fopen($_FILES['file_csv']['tmp_name'], "r");
            $success_count = 0;
            $failed_list = []; // Array to store failures

            $row = 0;
            while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                if ($row++ == 0)
                    continue; // Skip header

                // Map CSV index: 0=No, 1=Nama, 2=Lembaga, 3=Domisili, 4=HP
                $nama = mysqli_real_escape_string($conn, $data[1]);
                $lembaga = mysqli_real_escape_string($conn, $data[2]);
                $domisili = mysqli_real_escape_string($conn, $data[3]);
                $raw_hp = trim($data[4]);

                // Normalize HP: 
                // 1. Remove non-numeric characters except +
                $raw_hp = preg_replace('/[^0-9+]/', '', $data[4]);

                // 2. Handle prefixes
                if (substr($raw_hp, 0, 3) == '+62') {
                    $hp = '0' . substr($raw_hp, 3);
                } elseif (substr($raw_hp, 0, 2) == '62') {
                    $hp = '0' . substr($raw_hp, 2);
                } elseif (substr($raw_hp, 0, 1) != '0') {
                    // If doesn't start with 0 (and passed previous checks), prepend 0
                    // This handles cases where Excel/WPS strips the leading zero (e.g. 852... -> 0852...)
                    $hp = '0' . $raw_hp;
                } else {
                    $hp = $raw_hp;
                }
                $hp = mysqli_real_escape_string($conn, $hp);

                // Check uniqueness based on HP
                $cek = mysqli_query($conn, "SELECT id FROM users WHERE nohp='$hp'");
                if (mysqli_num_rows($cek) > 0) {
                    $failed_list[] = "Nama: $nama, HP: $hp (Sudah ada)";
                } else {
                    $pass = '123456'; // Default password
                    $insert = mysqli_query($conn, "INSERT INTO users (nama, nohp, password, lembaga, alamat, role) VALUES ('$nama', '$hp', '$pass', '$lembaga', '$domisili', 'user')");
                    if ($insert) {
                        $success_count++;
                    } else {
                        $failed_list[] = "Nama: $nama, HP: $hp (Gagal Insert DB)";
                    }
                }
            }
            fclose($handle);

            // Generate Feedback Message
            $import_feedback = '<div class="alert alert-info alert-dismissible fade show" role="alert">
                <strong>Import Selesai!</strong><br>
                Berhasil: ' . $success_count . ' Data.<br>';

            if (count($failed_list) > 0) {
                $import_feedback .= '<strong>Gagal (' . count($failed_list) . '):</strong><br><ul>';
                foreach ($failed_list as $fail) {
                    $import_feedback .= '<li>' . $fail . '</li>';
                }
                $import_feedback .= '</ul>';
            }

            $import_feedback .= '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>';

        } else {
            $import_feedback = '<div class="alert alert-danger">Format file harus CSV!</div>';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <title>Kelola User - Admin Daurah</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>

<body class="bg-light">

    <nav class="navbar navbar-dark bg-dark sticky-top p-3">
        <div class="container-fluid">
            <span class="navbar-brand fw-bold">Manajemen User</span>
            <button class="btn btn-outline-light" type="button" data-bs-toggle="offcanvas"
                data-bs-target="#adminDrawer">
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
                <a href="dashboard_admin.php"
                    class="list-group-item list-group-item-action <?= (basename($_SERVER['PHP_SELF']) == 'dashboard_admin.php') ? 'active' : '' ?>">
                    <i class="fa fa-calendar-alt me-2"></i> Kelola Event (Daurah)
                </a>

                <a href="manage_users.php"
                    class="list-group-item list-group-item-action <?= (basename($_SERVER['PHP_SELF']) == 'manage_users.php') ? 'active' : '' ?>">
                    <i class="fa fa-users me-2"></i> Kelola User / Peserta
                </a>

                <a href="history_daurah.php"
                    class="list-group-item list-group-item-action <?= (basename($_SERVER['PHP_SELF']) == 'history_daurah.php') ? 'active' : '' ?>">
                    <i class="fa fa-file-alt me-2"></i> Laporan / History
                </a>

                <a href="logout.php" class="list-group-item list-group-item-action text-danger mt-3">
                    <i class="fa fa-sign-out-alt me-2"></i> Logout
                </a>
            </div>
        </div>
    </div>

    <div class="container mt-4">
        <!-- Feedback Message -->
        <?= $import_feedback ?>

        <div class="d-flex justify-content-between align-items-center mb-3">
            <h3>Data Peserta</h3>
            <div>
                <a href="download_template.php" class="btn btn-warning me-1"><i class="fa fa-download"></i> Template</a>
                <button class="btn btn-success me-1" data-bs-toggle="modal" data-bs-target="#addUserModal"><i
                        class="fa fa-user-plus"></i> Tambah</button>
                <button class="btn btn-info me-1" data-bs-toggle="modal" data-bs-target="#importModal"><i
                        class="fa fa-upload"></i> Import</button>
                <a href="export_users.php" class="btn btn-secondary"><i class="fa fa-file-export"></i> Export</a>
            </div>
        </div>

        <div class="card shadow-sm border-0">
            <div class="card-body">
                <div class="table-responsive">
                    <table id="tableUser" class="table table-striped w-100">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Nama</th>
                                <th>No HP</th>
                                <th>Lembaga</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $no = 1;
                            $q = mysqli_query($conn, "SELECT * FROM users WHERE role='user' ORDER BY id DESC");
                            while ($u = mysqli_fetch_assoc($q)): ?>
                                <tr>
                                    <td><?= $no++ ?></td>
                                    <td><?= $u['nama'] ?></td>
                                    <td><?= $u['nohp'] ?></td>
                                    <td><?= $u['lembaga'] ?></td>
                                    <td>
                                        <a href="edit_user_admin.php?id=<?= $u['id'] ?>" class="btn btn-primary btn-sm"><i
                                                class="fa fa-edit"></i></a>
                                        <a href="manage_users.php?delete_id=<?= $u['id'] ?>" class="btn btn-danger btn-sm"
                                            onclick="return confirm('Yakin hapus user ini?')"><i
                                                class="fa fa-trash"></i></a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="addUserModal">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5>Tambah Peserta Manual</h5><button class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form method="POST">
                        <div class="mb-2"><label>Nama</label><input type="text" name="nama" class="form-control"
                                required></div>
                        <div class="mb-2"><label>No HP</label><input type="number" name="nohp" class="form-control"
                                required></div>
                        <div class="mb-2"><label>Password</label><input type="text" name="password" class="form-control"
                                value="123456" required></div>
                        <div class="mb-3"><label>Lembaga</label><input type="text" name="lembaga" class="form-control"
                                required></div>
                        <button type="submit" name="add_user" class="btn btn-success w-100">Simpan</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Import -->
    <div class="modal fade" id="importModal">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5>Import Peserta (CSV)</h5><button class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form method="POST" enctype="multipart/form-data">
                        <div class="alert alert-warning text-sm">
                            <small>
                                Gunakan format Template CSV. <br>
                                Kolom HP dengan awalan +62 akan otomatis diubah ke 0.<br>
                                User dengan No HP yang sama tidak akan diimport.
                            </small>
                        </div>
                        <div class="mb-3">
                            <label>Pilih File CSV</label>
                            <input type="file" name="file_csv" class="form-control" required accept=".csv">
                        </div>
                        <button type="submit" name="import_users" class="btn btn-primary w-100">Upload & Import</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>
    <script> $(document).ready(function () { $('#tableUser').DataTable(); }); </script>
</body>

</html>