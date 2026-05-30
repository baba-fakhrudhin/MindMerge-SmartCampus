<?php

session_start();

include('../config/db.php');
include('../config/mail.php');

if(isset($_SESSION['user'])){

header("Location: ../dashboard/index.php");
exit();

}

$error = "";

/* LOAD CLASSES */

$classQuery = mysqli_query(

$conn,

"SELECT *
FROM classes
WHERE status='active'
ORDER BY class_name ASC"

);

/* LOAD SECTIONS */

$sectionQuery = mysqli_query(

$conn,

"SELECT
sections.*,
classes.class_name

FROM sections

INNER JOIN classes
ON classes.class_id =
sections.class_id

WHERE sections.status='active'

ORDER BY
classes.class_name,
sections.section_name"

);

if(isset($_POST['send_otp'])){

$otp =
rand(100000,999999);

$_SESSION['register_data'] =
$_POST;

$_SESSION['register_otp'] =
$otp;

$email =
$_POST['email'];

$message = "

<h2>MindMerge OTP Verification</h2>

<p>
Your OTP code is:
</p>

<h1>$otp</h1>

<p>
Do not share this OTP.
</p>

";

if(
sendMail(
$email,
"MindMerge OTP Verification",
$message
)
){

header(
"Location: verify-otp.php"
);

exit();

}else{

$error =
"Failed to send OTP";

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

<title>Register | MindMerge</title>

<link
rel="stylesheet"
href="../assets/css/global.css">

<link
rel="stylesheet"
href="../assets/css/components.css">

<link
rel="stylesheet"
href="../assets/css/layout.css">

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

.auth-container{
width:100%;
max-width:1180px;
background:white;
border-radius:26px;
overflow:hidden;
display:grid;
grid-template-columns:1fr 1.2fr;
box-shadow:
0 20px 60px rgba(0,0,0,0.25);
}

.auth-left{

background-color:#172554;

background-image:
linear-gradient(
rgba(0,0,0,0.5),
rgba(0,0,0,0.5)
),
url('../assets/images/classroom.jpg');

background-size:cover;
background-position:center;

padding:50px;

display:flex;
justify-content:center;
align-items:center;
flex-direction:column;

text-align:center;
color:white;

}

.auth-left i{
font-size:70px;
margin-bottom:20px;
}

.auth-left h1{
font-size:44px;
margin-bottom:16px;
}

.auth-left p{
color:#dbeafe;
line-height:1.8;
}

.auth-right{
padding:36px;
max-height:100vh;
overflow-y:auto;
}

.auth-header{
margin-bottom:24px;
}

.role-section{
display:none;
margin-top:20px;
padding-top:10px;
}

.info-box{
background:#dbeafe;
color:#1e3a8a;
padding:14px;
border-radius:12px;
margin-bottom:20px;
font-size:14px;
}

.error-box{
background:#fee2e2;
color:#991b1b;
padding:14px;
border-radius:12px;
margin-bottom:20px;
font-size:14px;
}

@media(max-width:900px){

.auth-container{
grid-template-columns:1fr;
}

.auth-left{
display:none;
}

}

</style>

</head>

<body>

<div class="auth-page">

<div class="auth-container">

<div class="auth-left">

<i class="fa-solid fa-school"></i>

<h1>MindMerge SmartCampus</h1>

<p>
Create your account
and access the complete
school ERP platform.
</p>

</div>

<div class="auth-right">

<div class="auth-header">

<h1>Create Account</h1>

<p>
Register to continue
</p>

</div>

<?php if($error != ""){ ?>

<div class="error-box">

<?php echo $error; ?>

</div>

<?php } ?>

<div class="info-box">

<i class="fa-solid fa-circle-info"></i>

&nbsp;

User IDs are generated automatically
after OTP verification.

</div>

<form method="POST">

<div class="form-grid">

<div class="form-group">

<label class="form-label">
Full Name
</label>

<input
type="text"
name="full_name"
class="form-input"
required>

</div>

<div class="form-group">

<label class="form-label">
Role
</label>

<select
name="role"
class="form-select"
id="roleSelect"
required>

<option value="">
Select Role
</option>

<option value="admin">
Admin
</option>

<option value="teacher">
Teacher
</option>

<option value="student">
Student
</option>

<option value="parent">
Parent
</option>

</select>

</div>

<div class="form-group">

<label class="form-label">
Email
</label>

<input
type="email"
name="email"
class="form-input"
required>

</div>

<div class="form-group">

<label class="form-label">
Phone Number
</label>

<input
type="text"
name="phone"
class="form-input"
required>

</div>

</div>

<!-- ADMIN -->

<div
class="role-section"
id="adminFields">

<div class="form-group">

<label class="form-label">
Admin ID
</label>

<input
type="text"
class="form-input"
value="Generated Automatically"
disabled>

</div>

</div>

<!-- STUDENT -->

<div
class="role-section"
id="studentFields">

<div class="form-grid">

<div class="form-group">

<label class="form-label">
Student ID
</label>

<input
type="text"
class="form-input"
value="Generated Automatically"
disabled>

</div>

<div class="form-group">

<label class="form-label">
Class
</label>

<select
name="class_id"
class="form-select">

<option value="">
Select Class
</option>

<?php

mysqli_data_seek(
$classQuery,
0
);

while(
$class =
mysqli_fetch_assoc(
$classQuery
)
){

?>

<option
value="<?php echo $class['class_id']; ?>">

<?php
echo $class['class_name'];
?>

</option>

<?php } ?>

</select>

</div>

<div class="form-group">

<label class="form-label">
Section
</label>

<select
name="section_id"
class="form-select">

<option value="">
Select Section
</option>

<?php

mysqli_data_seek(
$sectionQuery,
0
);

while(
$section =
mysqli_fetch_assoc(
$sectionQuery
)
){

?>

<option
value="<?php echo $section['section_id']; ?>">

<?php
echo
$section['section_code']
.
' - '
.
$section['section_name'];
?>

</option>

<?php } ?>

</select>

</div>

<div class="form-group">

<label class="form-label">
DOB
</label>

<input
type="date"
name="dob"
class="form-input">

</div>

<div class="form-group">

<label class="form-label">
Gender
</label>

<select
name="gender"
class="form-select">

<option value="">
Select Gender
</option>

<option value="Male">
Male
</option>

<option value="Female">
Female
</option>

</select>

</div>

<div class="form-group">

<label class="form-label">
Parent Phone
</label>

<input
type="text"
name="parent_phone"
class="form-input">

</div>

</div>

<div class="form-group">

<label class="form-label">
Address
</label>

<textarea
name="address"
class="form-textarea"></textarea>

</div>

</div>
<!-- TEACHER -->

<div
class="role-section"
id="teacherFields">

<div class="form-grid">

<div class="form-group">

<label class="form-label">
Teacher ID
</label>

<input
type="text"
class="form-input"
value="Generated Automatically"
disabled>

</div>

<div class="form-group">

<label class="form-label">
Assigned Class
</label>

<select
name="teacher_class_id"
class="form-select">

<option value="">
Select Class
</option>

<?php

mysqli_data_seek(
$classQuery,
0
);

while(
$class =
mysqli_fetch_assoc(
$classQuery
)
){

?>

<option
value="<?php echo $class['class_id']; ?>">

<?php
echo $class['class_name'];
?>

</option>

<?php } ?>

</select>

</div>

<div class="form-group">

<label class="form-label">
Assigned Section
</label>

<select
name="teacher_section_id"
class="form-select">

<option value="">
Select Section
</option>

<?php

mysqli_data_seek(
$sectionQuery,
0
);

while(
$section =
mysqli_fetch_assoc(
$sectionQuery
)
){

?>

<option
value="<?php echo $section['section_id']; ?>">

<?php
echo
$section['section_code']
.
' - '
.
$section['section_name'];
?>

</option>

<?php } ?>

</select>

</div>

<div class="form-group">

<label class="form-label">
Subject
</label>

<input
type="text"
name="subject_name"
class="form-input">

</div>

<div class="form-group">

<label class="form-label">
Qualification
</label>

<input
type="text"
name="qualification"
class="form-input">

</div>

</div>

</div>

<!-- PARENT -->

<div
class="role-section"
id="parentFields">

<div class="form-grid">

<div class="form-group">

<label class="form-label">
Parent ID
</label>

<input
type="text"
class="form-input"
value="Generated Automatically"
disabled>

</div>

<div class="form-group">

<label class="form-label">
Student ID
</label>

<input
type="text"
name="student_id"
class="form-input">

</div>

<div class="form-group">

<label class="form-label">
Relationship
</label>

<input
type="text"
name="relationship_name"
class="form-input"
placeholder="Father / Mother / Guardian">

</div>

</div>

</div>

<div class="form-group"
style="margin-top:20px;">

<label class="form-label">
Password
</label>

<input
type="password"
name="password"
class="form-input"
required>

</div>

<button
type="submit"
name="send_otp"
class="btn btn-primary"
style="width:100%;margin-top:10px;">

<i class="fa-solid fa-envelope"></i>

Send OTP

</button>

<p
style="margin-top:18px;text-align:center;">

Already have an account?

<a href="login.php">
Login
</a>

</p>

</form>

</div>

</div>

</div>

<script>

const roleSelect =
document.getElementById('roleSelect');

const adminFields =
document.getElementById('adminFields');

const studentFields =
document.getElementById('studentFields');

const teacherFields =
document.getElementById('teacherFields');

const parentFields =
document.getElementById('parentFields');

roleSelect.addEventListener('change',()=>{

adminFields.style.display = 'none';
studentFields.style.display = 'none';
teacherFields.style.display = 'none';
parentFields.style.display = 'none';

if(roleSelect.value === 'admin'){
adminFields.style.display = 'block';
}

if(roleSelect.value === 'student'){
studentFields.style.display = 'block';
}

if(roleSelect.value === 'teacher'){
teacherFields.style.display = 'block';
}

if(roleSelect.value === 'parent'){
parentFields.style.display = 'block';
}

});

</script>

</body>
</html>