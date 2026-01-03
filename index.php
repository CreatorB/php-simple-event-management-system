<?php
include 'config.php';

if (isset($_SESSION['uid'])) {
    if($_SESSION['role'] == 'admin') {
        header("Location: dashboard_admin.php");
    } else {
        header("Location: dashboard_user.php");
    }
    exit();
}

if (isset($_POST['nohp']) && !empty($_POST['nohp'])) {
    $nohp = mysqli_real_escape_string($conn, $_POST['nohp']);

    $q = mysqli_query($conn, "SELECT * FROM users WHERE nohp='$nohp'");

    if(mysqli_num_rows($q) > 0){
        $d = mysqli_fetch_assoc($q);

        $_SESSION['uid']  = $d['id'];
        $_SESSION['role'] = $d['role'];
        $_SESSION['nama'] = $d['nama'];
        $_SESSION['nohp'] = $d['nohp'];

        if (isset($_SESSION['redirect_after_login'])) {
            $url = $_SESSION['redirect_after_login'];
            unset($_SESSION['redirect_after_login']);
            header("Location: " . $url);
        } else {
            if($d['role'] == 'admin') header("Location: dashboard_admin.php");
            else header("Location: dashboard_user.php");
        }
        exit();
    } else {
        $error = "Phone number not registered!";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Event Attendance</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <style>
        body {
            background: #f0f2f5;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
        }
        .login-card {
            width: 100%;
            max-width: 400px;
            border-radius: 15px;
            border: none;
        }
        .login-header {
            text-align: center;
            margin-bottom: 20px;
        }
        .btn-login {
            background-color: #0d6efd;
            border: none;
            padding: 12px;
            font-size: 16px;
            font-weight: bold;
        }
        .btn-login:hover {
            background-color: #0b5ed7;
        }
    </style>
</head>
<body>

    <div class="container p-3">
        <div class="card shadow login-card mx-auto p-4">
            <div class="card-body">
                <div class="login-header">
                    <h3 class="fw-bold text-primary">Daurah Syariyyah</h3>
                    <p class="text-muted">Silahkan masukan nomor hp Antum untuk absen</p>
                </div>

                <?php if(isset($error)): ?>
                    <div class="alert alert-danger text-center p-2 mb-3">
                        <small><?= $error ?></small>
                    </div>
                <?php endif; ?>

                <form method="POST">
                    <div class="mb-4">
                        <label class="form-label fw-bold small">Nomer Handphone</label>
                        <input type="number" name="nohp" class="form-control form-control-lg" placeholder="e.g. 0812..." required autofocus>
                    </div>

                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary btn-login text-white">MASUK</button>
                    </div>
                </form>

                <!-- <div class="text-center mt-4">
                    <p class="small text-muted mb-1">Not registered yet?</p>
                    <a href="register.php" class="fw-bold text-decoration-none">Create New Account</a>
                </div> -->
            </div>
        </div>
    </div>

</body>
</html>
