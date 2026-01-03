<?php
include 'config.php';

if (isset($_SESSION['uid'])) {
    header("Location: dashboard_user.php");
    exit();
}

if (isset($_POST['daftar'])) {
    $nama = mysqli_real_escape_string($conn, $_POST['nama']);
    $nohp = mysqli_real_escape_string($conn, $_POST['nohp']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $alamat = mysqli_real_escape_string($conn, $_POST['alamat']);
    $lembaga = mysqli_real_escape_string($conn, $_POST['lembaga']);

    $cek = mysqli_query($conn, "SELECT id FROM users WHERE nohp='$nohp'");
    if(mysqli_num_rows($cek) > 0){
        $error = "Phone number already registered.";
    } else {
        $q = "INSERT INTO users (nama, nohp, email, alamat, lembaga, role)
              VALUES ('$nama', '$nohp', '$email', '$alamat', '$lembaga', 'user')";

        if(mysqli_query($conn, $q)){
            echo "<script>alert('Registration successful! Please login.'); window.location='index.php';</script>";
        } else {
            $error = "Registration failed: " . mysqli_error($conn);
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Event Attendance</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <style>
        body { background: #f8f9fa; }
        .register-container {
            max-width: 500px;
            margin: 40px auto;
            padding: 20px;
        }
        .form-label { font-weight: 600; font-size: 0.9rem; }
        .card { border: none; border-radius: 15px; box-shadow: 0 4px 20px rgba(0,0,0,0.05); }
    </style>
</head>
<body>

<div class="container register-container">
    <div class="text-center mb-4">
        <h3 class="fw-bold text-primary">Participant Registration</h3>
        <p class="text-muted">Fill in your details correctly</p>
    </div>

    <div class="card">
        <div class="card-body p-4">

            <?php if(isset($error)): ?>
                <div class="alert alert-danger text-center"><?= $error ?></div>
            <?php endif; ?>

            <form method="POST">
                <div class="mb-3">
                    <label class="form-label">Full Name</label>
                    <input type="text" name="nama" class="form-control form-control-lg" required placeholder="As per ID">
                </div>

                <div class="mb-3">
                    <label class="form-label">Phone Number (For Login)</label>
                    <input type="number" name="nohp" class="form-control form-control-lg" required placeholder="e.g. 08123456789">
                </div>

                <div class="row">
                    <div class="col-12 col-md-6 mb-3">
                        <label class="form-label">Organization</label>
                        <input type="text" name="lembaga" class="form-control" required placeholder="Company/School">
                    </div>
                    <div class="col-12 col-md-6 mb-3">
                        <label class="form-label">Email (Optional)</label>
                        <input type="email" name="email" class="form-control" placeholder="name@email.com">
                    </div>
                </div>

                <div class="mb-4">
                    <label class="form-label">Address</label>
                    <textarea name="alamat" class="form-control" rows="2" required placeholder="City/District"></textarea>
                </div>

                <div class="d-grid gap-2">
                    <button type="submit" name="daftar" class="btn btn-primary btn-lg fw-bold">REGISTER NOW</button>
                    <a href="index.php" class="btn btn-outline-secondary">Already have an account? Login</a>
                </div>
            </form>
        </div>
    </div>
</div>

</body>
</html>
