<?php

session_start();

require_once __DIR__ . '/authenticate.php';

if (isset($_SESSION['user'])) {
    portal_redirect_home();
}

$error = '';
$admin_account = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $attempt = auth_attempt_login(
        $conn,
        $_POST['email'] ?? '',
        $_POST['password'] ?? ''
    );

    if ($attempt['success']) {
        header('Location: ' . $attempt['redirect']);
        exit();
    }

    $error = $attempt['error'];
    $admin_account = !empty($attempt['admin_account']);
}

$logged_out = isset($_GET['logout']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Login | MindMerge SmartCampus</title>
<link rel="stylesheet" href="../assets/css/global.css">
<link rel="stylesheet" href="../assets/css/components.css">
<link rel="stylesheet" href="../assets/css/auth.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head><body class="auth-v2">

<main class="auth-container">

<div class="auth-card">

<div class="auth-logo">
<i class="fa-solid fa-graduation-cap"></i>
<div>
<h1>MindMerge</h1>
<p>SmartCampus</p>
</div>
</div>

<div class="auth-heading">
<h2>Sign in</h2>
<p>Welcome back — enter your credentials to continue.</p>
</div>

<?php if ($logged_out) { ?>
<div class="auth-alert success">
<i class="fa-solid fa-circle-check"></i>
<span>You have been signed out.</span>
</div>
<?php } ?>

<?php if ($error !== '') { ?>
<div class="auth-alert error">
<i class="fa-solid fa-triangle-exclamation"></i>
<span>
<?php echo htmlspecialchars($error); ?>
<?php if ($admin_account) { ?>
<a href="admin-login.php">Admin Login</a>
<?php } ?>
</span>
</div>
<?php } ?>

<form method="POST" class="auth-form">

<div class="auth-field">
<label for="email">Email Address</label>

<div class="auth-input-wrap">
<i class="fa-solid fa-envelope"></i>

<input
id="email"
type="email"
name="email"
value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>"
placeholder="Enter your email"
autocomplete="username"
required
>
</div>
</div>

<div class="auth-field">
<label for="password">Password</label>

<div class="auth-input-wrap">
<i class="fa-solid fa-lock"></i>

<input
id="password"
type="password"
name="password"
placeholder="Enter your password"
autocomplete="current-password"
required
>

<button
type="button"
class="auth-eye"
data-password-toggle="password"
>
<i class="fa-solid fa-eye"></i>
</button>

</div>
</div>

<div class="auth-checkbox-row">
<label class="auth-checkbox">
<input type="checkbox" name="remember" value="1">
<span>Remember me</span>
</label>
<a href="forgot-password.php">
<i class="fa-solid fa-rotate-left"></i>
Forgot password?
</a>
</div>

<button type="submit" class="auth-primary-btn">
<i class="fa-solid fa-arrow-right-to-bracket"></i>
Sign In
</button>

</form>

<div class="auth-divider">
<span>New to MindMerge?</span>
</div>

<a href="register.php" class="auth-secondary-btn">
<i class="fa-solid fa-user-plus"></i>
Create Account
</a>

<p class="auth-admin-link">
<a href="admin-login.php">
<i class="fa-solid fa-shield-halved"></i>
Admin Login
</a>
</p>

</div>

</main>

<script src="../assets/js/auth.js"></script>

</body>
</html>
