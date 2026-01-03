<?php
include 'config.php';

if (isset($_POST['login'])) {
    $nohp = $_POST['nohp'];
    $pass = $_POST['password'];

    $q = mysqli_query($conn, "SELECT * FROM users WHERE nohp='$nohp' AND password='$pass'");
    if(mysqli_num_rows($q) > 0){
        $d = mysqli_fetch_assoc($q);
        $_SESSION['uid'] = $d['id'];
        $_SESSION['role'] = $d['role'];
        $_SESSION['nama'] = $d['nama'];

        if($d['role'] == 'admin') header("Location: dashboard_admin.php");
        else header("Location: dashboard_user.php");
    } else {
        $error = "Invalid phone number or password";
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
            <div class="mb-3"><input type="text" name="nohp" class="form-control" placeholder="Phone Number" required></div>
            <div class="mb-3"><input type="password" name="password" class="form-control" placeholder="Password" required></div>
            <button type="submit" name="login" class="btn btn-success w-100">Login</button>
            <div class="mt-2 text-center"><a href="register.php">Don't have an account?</a></div>
        </form>
    </div>
</body>
</html>
