<?php
include 'config.php';

if(!isset($_SESSION['uid'])) header("Location: index.php");

$uid = $_SESSION['uid'];
$msg = "";

if(isset($_POST['update'])){
    $nama    = mysqli_real_escape_string($conn, $_POST['nama']);
    $email   = mysqli_real_escape_string($conn, $_POST['email']);
    $alamat  = mysqli_real_escape_string($conn, $_POST['alamat']);
    $lembaga = mysqli_real_escape_string($conn, $_POST['lembaga']);
    $pass    = $_POST['password'];

    $sql = "UPDATE users SET nama='$nama', email='$email', alamat='$alamat', lembaga='$lembaga' WHERE id='$uid'";

    if(!empty($pass)){
        $sql = "UPDATE users SET nama='$nama', email='$email', alamat='$alamat', lembaga='$lembaga', password='$pass' WHERE id='$uid'";
    }

    if(mysqli_query($conn, $sql)){
        $_SESSION['nama'] = $nama;
        echo "<script>alert('Profile updated successfully!'); window.location='dashboard_user.php';</script>";
    } else {
        $msg = "Update failed: " . mysqli_error($conn);
    }
}

$d = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM users WHERE id='$uid'"));
?>
<!DOCTYPE html>
<html>
<head>
    <title>Edit Profil</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body class="bg-light">
    <div class="container mt-4" style="max-width: 600px;">
        <div class="card shadow border-0">
            <div class="card-header bg-white fw-bold">Edit Data Diri</div>
            <div class="card-body">
                <?php if($msg) echo "<div class='alert alert-danger'>$msg</div>"; ?>
                
                <form method="POST">
                    <div class="mb-3">
                        <label>No HP (Tidak bisa diubah)</label>
                        <input type="text" class="form-control bg-light" value="<?= $d['nohp'] ?>" readonly>
                    </div>
                    <div class="mb-3">
                        <label>Nama Lengkap</label>
                        <input type="text" name="nama" class="form-control" value="<?= $d['nama'] ?>" required>
                    </div>
                    <div class="mb-3">
                        <label>Lembaga</label>
                        <input type="text" name="lembaga" class="form-control" value="<?= $d['lembaga'] ?>" required>
                    </div>
                    <div class="mb-3">
                        <label>Alamat</label>
                        <textarea name="alamat" class="form-control" required><?= $d['alamat'] ?></textarea>
                    </div>
                    <div class="mb-3">
                        <label>Email</label>
                        <input type="email" name="email" class="form-control" value="<?= $d['email'] ?>">
                    </div>
                    <hr>
                    <div class="mb-3">
                        <label>Password Baru <small class="text-muted">(Kosongkan jika tidak ingin ganti)</small></label>
                        <input type="password" name="password" class="form-control" placeholder="******">
                    </div>
                    
                    <div class="d-grid gap-2">
                        <button type="submit" name="update" class="btn btn-primary">Simpan Perubahan</button>
                        <a href="dashboard_user.php" class="btn btn-secondary">Batal</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>