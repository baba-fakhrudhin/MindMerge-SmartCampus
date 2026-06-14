<?php

session_start();

include('../config/db.php');

if (isset($_SESSION['user'])) {
    require_once __DIR__ . '/../shared/helpers/portal.php';
    portal_redirect_home();
}

$error = "";

if (isset($_POST['login'])) {

    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = $_POST['password'];

    $query = "SELECT * FROM users WHERE email='$email'";
    $result = mysqli_query($conn, $query);

    if (mysqli_num_rows($result) > 0) {

        $user = mysqli_fetch_assoc($result);

        if (password_verify($password, $user['password'])) {

            $_SESSION['user'] = $user;

            require_once __DIR__ . '/../config/permissions.php';
            permission_load_user($conn, $user);

            require_once __DIR__ . '/../shared/helpers/portal.php';
            header('Location: ' . portal_dashboard_url($user['role']));
            exit();

        } else {
            $error = "Invalid Password";
        }

    } else {
        $error = "Email Not Found";
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Login | MindMerge</title>
<link rel="stylesheet" href="../assets/css/global.css">
<link rel="stylesheet" href="../assets/css/components.css">
<link rel="stylesheet" href="../assets/css/layout.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<style>
.auth-page{min-height:100vh;display:flex;justify-content:center;align-items:center;padding:20px;background:linear-gradient(135deg,#081028,#172554);}
.auth-container{width:100%;max-width:1050px;background:white;border-radius:26px;overflow:hidden;display:grid;grid-template-columns:1fr 1fr;box-shadow:0 20px 60px rgba(0,0,0,0.25);}
.auth-left{background-color:#172554;background-image:linear-gradient(rgba(0,0,0,0.5),rgba(0,0,0,0.5)),url('../assets/images/classroom.jpg');background-size:cover;background-position:center;padding:50px;display:flex;justify-content:center;align-items:center;flex-direction:column;text-align:center;color:white;}
.auth-left i{font-size:70px;margin-bottom:20px;}
.auth-left h1{font-size:44px;margin-bottom:16px;}
.auth-left p{color:#dbeafe;line-height:1.8;}
.auth-right{padding:40px;display:flex;justify-content:center;flex-direction:column;}
.auth-header{margin-bottom:28px;}
.auth-header h1{margin-bottom:8px;}
.error-box{background:#fee2e2;color:#991b1b;padding:14px;border-radius:12px;margin-bottom:20px;font-size:14px;}
.auth-links{margin-top:18px;display:flex;justify-content:space-between;flex-wrap:wrap;gap:10px;}
.auth-links a{text-decoration:none;color:var(--primary);font-size:14px;font-weight:600;}
@media(max-width:900px){.auth-container{grid-template-columns:1fr;}.auth-left{display:none;}}
</style>
</head>
<body>
<div class="auth-page">
<div class="auth-container">
<div class="auth-left">
<i class="fa-solid fa-school"></i>
<h1>MindMerge SmartCampus</h1>
<p>Modern ERP platform for students, teachers, attendance and analytics.</p>
</div>
<div class="auth-right">
<div class="auth-header">
<h1>Welcome Back</h1>
<p>Login to continue</p>
</div>
<?php if ($error != "") { ?>
<div class="error-box"><?php echo $error; ?></div>
<?php } ?>
<form method="POST">
<div class="form-group">
<label class="form-label">Email Address</label>
<input type="email" name="email" class="form-input" required>
</div>
<div class="form-group">
<label class="form-label">Password</label>
<input type="password" name="password" class="form-input" required>
</div>
<button type="submit" name="login" class="btn btn-primary" style="width:100%;">
<i class="fa-solid fa-right-to-bracket"></i> Login
</button>
<div class="auth-links">
<a href="register.php">Create Account</a>
<a href="forgot-password.php">Forgot Password?</a>
</div>
</form>
</div>
</div>
</div>
</body>
</html>
