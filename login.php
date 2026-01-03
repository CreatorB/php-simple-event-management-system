<?php
include 'config.php';

if (isset($_POST['nohp']) && !empty($_POST['nohp'])) {
    $nohp = mysqli_real_escape_string($conn, $_POST['nohp']);

    $q = mysqli_query($conn, "SELECT * FROM users WHERE nohp='$nohp'");
    if(mysqli_num_rows($q) > 0){
        $d = mysqli_fetch_assoc($q);
        $_SESSION['uid'] = $d['id'];
        $_SESSION['role'] = $d['role'];
        $_SESSION['nama'] = $d['nama'];
        $_SESSION['nohp'] = $d['nohp'];

        if($d['role'] == 'admin') header("Location: dashboard_admin.php");
        else header("Location: dashboard_user.php");
    } else {
        $error = "Phone number not registered";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Login</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body class="d-flex align-items-center justify-content-center vh-100 bg-light">
    <div class="card p-4 shadow" style="width: 350px;">
        <h4 class="text-center mb-4">Login Attendance</h4>
        <?php if(isset($error)) echo "<div class='alert alert-danger'>$error</div>"; ?>
        <form method="POST">
            <div class="mb-4"><input type="text" name="nohp" class="form-control" placeholder="Phone Number" required></div>
            <button type="submit" class="btn btn-success w-100">Continue</button>
            <div class="mt-2 text-center"><a href="register.php">Don't have an account?</a></div>
        </form>
    </div>
</body>
</html>
