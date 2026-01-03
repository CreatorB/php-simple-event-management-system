<?php
include 'config.php';

if($_SESSION['role'] != 'admin') header("Location: index.php");

$id = $_GET['id'] ?? 0;
$q = mysqli_query($conn, "SELECT * FROM users WHERE id='$id'");
$d = mysqli_fetch_assoc($q);
if(!$d) die("User not found");

if(isset($_POST['update_user'])){
    $nama = $_POST['nama'];
    $nohp = $_POST['nohp'];
    $lembaga = $_POST['lembaga'];
    $alamat = $_POST['alamat'];
    $pass = $_POST['password'];

    $sql = "UPDATE users SET nama='$nama', nohp='$nohp', lembaga='$lembaga', alamat='$alamat', password='$pass' WHERE id='$id'";
    mysqli_query($conn, $sql);
    echo "<script>alert('User updated successfully'); window.location='manage_users.php';</script>";
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Edit User - Admin</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body class="bg-light">
    <div class="container mt-5" style="max-width: 500px;">
        <div class="card shadow">
            <div class="card-header bg-primary text-white">Edit Data Peserta</div>
            <div class="card-body">
                <form method="POST">
                    <div class="mb-3"><label>Nama</label><input type="text" name="nama" class="form-control" value="<?= $d['nama'] ?>" required></div>
                    <div class="mb-3"><label>No HP (Login)</label><input type="text" name="nohp" class="form-control" value="<?= $d['nohp'] ?>" required></div>
                    <div class="mb-3"><label>Lembaga</label><input type="text" name="lembaga" class="form-control" value="<?= $d['lembaga'] ?>"></div>
                    <div class="mb-3"><label>Alamat</label><textarea name="alamat" class="form-control"><?= $d['alamat'] ?></textarea></div>
                    <div class="mb-3"><label>Password</label><input type="text" name="password" class="form-control" value="<?= $d['password'] ?>"></div>
                    
                    <button type="submit" name="update_user" class="btn btn-primary w-100">Simpan Perubahan</button>
                    <a href="manage_users.php" class="btn btn-secondary w-100 mt-2">Batal</a>
                </form>
            </div>
        </div>
    </div>
</body>
</html>