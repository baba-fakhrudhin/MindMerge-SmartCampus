<?php

session_start();

include('../config/db.php');
include('../config/mail.php');

$error = "";

if(isset($_POST['send_otp'])){

$email =
mysqli_real_escape_string(
$conn,
$_POST['email']
);

$check =
mysqli_query(
$conn,
"SELECT * FROM users
WHERE email='$email'"
);

if(mysqli_num_rows($check) > 0){

$otp =
rand(100000,999999);

$_SESSION['reset_email'] =
$email;

$_SESSION['reset_otp'] =
$otp;

$message = "

<h2>Password Reset OTP</h2>

<p>Your OTP code is:</p>

<h1>$otp</h1>

";

if(
sendMail(
$email,
"MindMerge Password Reset",
$message
)
){

header(
"Location: reset-password.php"
);

exit();

}else{

$error =
"Failed to send OTP";

}

}else{

$error =
"Email not found";

}

}

?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">

<meta
name="viewport"
content="width=device-width, initial-scale=1.0">

<title>Forgot Password | MindMerge</title>

<link
rel="stylesheet"
href="../assets/css/global.css">

<link
rel="stylesheet"
href="../assets/css/components.css">

<link
rel="stylesheet"
href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

<style>

.auth-page{
min-height:100vh;
display:flex;
justify-content:center;
align-items:center;
padding:20px;
background:
linear-gradient(
135deg,
#081028,
#172554
);
}

.auth-card{
width:100%;
max-width:460px;
background:white;
padding:38px;
border-radius:24px;
box-shadow:
0 20px 60px rgba(0,0,0,0.25);
}

.auth-card h1{
margin-bottom:10px;
}

.auth-card p{
margin-bottom:24px;
}

.error-box{
background:#fee2e2;
color:#991b1b;
padding:14px;
border-radius:12px;
margin-bottom:20px;
font-size:14px;
}

.auth-links{
margin-top:20px;
display:flex;
justify-content:space-between;
gap:12px;
flex-wrap:wrap;
}

.auth-links a{
text-decoration:none;
color:var(--primary);
font-weight:600;
font-size:14px;
}

</style>

</head>

<body>

<div class="auth-page">

<div class="auth-card">

<h1>Forgot Password</h1>

<p>
Enter your registered email
to receive OTP verification.
</p>

<?php if($error != ""){ ?>

<div class="error-box">

<?php echo $error; ?>

</div>

<?php } ?>

<form method="POST">

<div class="form-group">

<label class="form-label">
Email Address
</label>

<input
type="email"
name="email"
class="form-input"
required>

</div>

<button
type="submit"
name="send_otp"
class="btn btn-primary"
style="width:100%;">

<i class="fa-solid fa-envelope"></i>

Send OTP

</button>

</form>

<div class="auth-links">

<a href="login.php">

Back To Login

</a>

<a href="register.php">

Create Account

</a>

</div>

</div>

</div>

</body>
</html>