<?php

include('../config/auth.php');
include('../config/db.php');

$user = $_SESSION['user'];

$user_id = $user['id'];

$role = $user['role'];

$role_data = [];

if($role == 'student'){

$result =
mysqli_query(
$conn,
"SELECT * FROM students
WHERE user_id='$user_id'"
);

$role_data =
mysqli_fetch_assoc($result);

}

if($role == 'teacher'){

$result =
mysqli_query(
$conn,
"SELECT * FROM teachers
WHERE user_id='$user_id'"
);

$role_data =
mysqli_fetch_assoc($result);

}

if($role == 'parent'){

$result =
mysqli_query(
$conn,
"SELECT * FROM parents
WHERE user_id='$user_id'"
);

$role_data =
mysqli_fetch_assoc($result);

}

/* UPDATE */

if(isset($_POST['save_profile'])){

if(!canEdit('profile')){
    permission_deny_and_exit();
}

$full_name =
mysqli_real_escape_string(
$conn,
$_POST['full_name']
);


$phone =
mysqli_real_escape_string(
$conn,
$_POST['phone']
);

$profile_photo =
$user['profile_photo'];

/* PHOTO */

if(
isset($_FILES['profile_photo'])
&&
$_FILES['profile_photo']['name'] != ""
){

$allowed =
['jpg','jpeg','png','webp'];

$file =
$_FILES['profile_photo'];

$ext =
strtolower(
pathinfo(
$file['name'],
PATHINFO_EXTENSION
)
);

if(in_array($ext,$allowed)){

$file_name =
"time_".time().".".$ext;

$upload_path =
"../assets/uploads/profile/".$file_name;

move_uploaded_file(
$file['tmp_name'],
$upload_path
);

$profile_photo =
$file_name;

}

}

/* UPDATE USER */

mysqli_query($conn,

"UPDATE users
SET

full_name='$full_name',
phone='$phone',
profile_photo='$profile_photo'

WHERE id='$user_id'"

);

/* STUDENT */

if($role == 'student'){

$class_name =
$_POST['class_name'];

$section_name =
$_POST['section_name'];

$address =
$_POST['address'];

mysqli_query($conn,

"UPDATE students
SET

class_name='$class_name',
section_name='$section_name',
address='$address'

WHERE user_id='$user_id'"

);

}

/* TEACHER */

if($role == 'teacher'){

$subject_name =
$_POST['subject_name'];

$qualification =
$_POST['qualification'];

mysqli_query($conn,

"UPDATE teachers
SET

subject_name='$subject_name',
qualification='$qualification'

WHERE user_id='$user_id'"

);

}

/* PARENT */

if($role == 'parent'){

$relationship_name =
$_POST['relationship_name'];

mysqli_query($conn,

"UPDATE parents
SET

relationship_name='$relationship_name'

WHERE user_id='$user_id'"

);

}

/* REFRESH SESSION */

$updated =
mysqli_query(
$conn,
"SELECT * FROM users
WHERE id='$user_id'"
);

$_SESSION['user'] =
mysqli_fetch_assoc($updated);

header("Location:index.php?updated=1");
exit();

}

?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">

<meta
name="viewport"
content="width=device-width, initial-scale=1.0">

<title>
Profile | MindMerge
</title>

<link
rel="stylesheet"
href="../assets/css/global.css">

<link
rel="stylesheet"
href="../assets/css/layout.css">

<link
rel="stylesheet"
href="../assets/css/components.css">

<link
rel="stylesheet"
href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

<style>

.user-profile-layout{
display:grid;
grid-template-columns:320px 1fr;
gap:24px;
}

.user-profile-card,
.user-profile-edit{
background:white;
border-radius:26px;
padding:30px;
box-shadow:var(--shadow);
}

.user-profile-card{
text-align:center;
}

.user-profile-avatar{
width:140px;
height:140px;
border-radius:50%;
object-fit:cover;
display:block;
margin:auto;
margin-bottom:20px;
border:5px solid #dbeafe;
}

.user-profile-letter{
width:140px;
height:140px;
border-radius:50%;
background:var(--primary);
display:flex;
justify-content:center;
align-items:center;
font-size:52px;
font-weight:700;
color:white;
margin:auto;
margin-bottom:20px;
}

.user-role-badge{
display:inline-block;
padding:10px 20px;
background:#dbeafe;
color:#2563eb;
border-radius:30px;
font-weight:600;
font-size:14px;
margin-top:14px;
}

.user-profile-info{
margin-top:28px;
text-align:left;
}
.user-profile-info-item{
padding:16px 0;
border-bottom:1px solid #e2e8f0;
}

.user-profile-info-item strong{
display:block;
margin-bottom:8px;
font-size:14px;
}

.user-profile-info-item span{
display:block;
word-break:break-word;
line-height:1.5;
color:var(--muted);
}
.custom-upload-box{
border:2px dashed #cbd5e1;
border-radius:16px;
padding:14px 18px;
display:flex;
align-items:center;
gap:14px;
cursor:pointer;
transition:0.3s;
min-height:58px;
height:58px;
overflow:hidden;
}

.upload-content{
display:flex;
flex-direction:column;
justify-content:center;
overflow:hidden;
}

.upload-content h4{
font-size:14px;
white-space:nowrap;
overflow:hidden;
text-overflow:ellipsis;
}

.upload-content p{
font-size:12px;
color:#64748b;
white-space:nowrap;
overflow:hidden;
text-overflow:ellipsis;
}
.custom-upload-box:hover{
border-color:var(--primary);
background:#eff6ff;
}

.custom-upload-box input{
display:none;
}

.upload-icon-box{
width:50px;
height:50px;
border-radius:14px;
background:#dbeafe;
display:flex;
justify-content:center;
align-items:center;
font-size:20px;
color:#2563eb;
}

.disabled-field{
background:#f1f5f9 !important;
cursor:not-allowed;
opacity:0.85;
}

/* DARK MODE */

body.dark-mode .user-profile-card,
body.dark-mode .user-profile-edit{
background:#0f172a;
color:white;
}

body.dark-mode .user-profile-info-item{
border-bottom:1px solid #1e293b;
}

body.dark-mode .custom-upload-box{
background:#1e293b;
border-color:#334155;
}

body.dark-mode .custom-upload-box:hover{
background:#172554;
}

body.dark-mode .disabled-field{
background:#1e293b !important;
}

body.dark-mode .user-role-badge{
background:#172554;
color:#93c5fd;
}


/* MOBILE */

@media(max-width:950px){

.user-profile-layout{
grid-template-columns:1fr;
}

}

</style>

</head>

<body>

<div class="app-layout">

<?php include('../partials/sidebar.php'); ?>

<div class="main-content">

<?php include('../partials/topbar.php'); ?>

<div class="page-content">

<?php if(isset($_GET['updated'])){ ?>

<div class="success-alert">

<i class="fa-solid fa-circle-check"></i>

Profile Updated Successfully

</div>

<?php } ?>

<div class="user-profile-layout">

<!-- LEFT -->

<div class="user-profile-card">

<?php if(
$user['profile_photo'] != 'default.svg'
&&
file_exists(
"../assets/uploads/profile/".
$user['profile_photo']
)
){ ?>

<img
src="../assets/uploads/profile/<?php echo $user['profile_photo']; ?>"
class="user-profile-avatar">

<?php }else{ ?>

<div class="user-profile-letter">

<?php
echo strtoupper(
substr(
$user['full_name'],
0,
1
)
);
?>

</div>

<?php } ?>

<h2>

<?php echo $user['full_name']; ?>

</h2>

<div class="user-role-badge">

<?php echo ucfirst($role); ?>

</div>

<div class="user-profile-info">

<div class="user-profile-info-item">

<strong>Email</strong>

<span>

<?php echo $user['email']; ?>

</span>

</div>

<div class="user-profile-info-item">

<strong>Phone</strong>

<span>

<?php echo $user['phone']; ?>

</span>

</div>

<div class="user-profile-info-item">

<strong>Role</strong>

<span>

<?php echo ucfirst($role); ?>

</span>

</div>

</div>

</div>

<!-- RIGHT -->

<div class="user-profile-edit">

<h1 style="margin-bottom:28px;">

Edit Profile

</h1>

<form
method="POST"
enctype="multipart/form-data">

<div class="form-grid">

<!-- COMMON -->

<div class="form-group">

<label class="form-label">

Full Name

</label>

<input
type="text"
name="full_name"
class="form-input"
value="<?php echo $user['full_name']; ?>"
required>

</div>

<div class="form-group">

<label class="form-label">

Email

</label>


<input
type="email"
class="form-input disabled-field"
value="<?php echo $user['email']; ?>"
disabled>

</div>

<div class="form-group">

<label class="form-label">

Phone Number

</label>

<input
type="text"
name="phone"
class="form-input"
value="<?php echo $user['phone']; ?>"
required>

</div>
<div class="form-group">

<label class="form-label">

Account Role

</label>

<input
type="text"
class="form-input disabled-field"
value="<?php echo ucfirst($role); ?>"
disabled>

</div>

<!-- UPLOAD -->

<div class="form-group">

<label class="form-label">

Profile Photo

</label>
<label class="custom-upload-box">

<div class="upload-icon-box">

<i class="fa-solid fa-cloud-arrow-up"></i>

</div>

<div class="upload-content">

<h4 id="uploadTitle">
Choose Profile Image
</h4>

<p id="uploadSubtext">
PNG, JPG, WEBP
</p>

</div>

<input
type="file"
name="profile_photo"
id="profilePhotoInput"
accept=".png,.jpg,.jpeg,.webp">

</label>
</div>

<!-- ADMIN -->

<?php if($role == 'admin'){ ?>

<div class="form-group">

<label class="form-label">

Admin ID

</label>

<input
type="text"
class="form-input disabled-field"
value="<?php echo $user['admin_id']; ?>"
disabled>

</div>

<?php } ?>

<!-- STUDENT -->

<?php if($role == 'student'){ ?>

<div class="form-group">

<label class="form-label">

Student ID

</label>

<input
type="text"
class="form-input disabled-field"
value="<?php echo $role_data['student_id']; ?>"
disabled>

</div>

<div class="form-group">

<label class="form-label">

Class

</label>

<input
type="text"
name="class_name"
class="form-input"
value="<?php echo $role_data['class_name']; ?>">

</div>

<div class="form-group">

<label class="form-label">

Section

</label>

<input
type="text"
name="section_name"
class="form-input"
value="<?php echo $role_data['section_name']; ?>">

</div>

<div class="form-group">

<label class="form-label">

Address

</label>

<textarea
name="address"
class="form-textarea"><?php echo $role_data['address']; ?></textarea>

</div>

<?php } ?>

<!-- TEACHER -->

<?php if($role == 'teacher'){ ?>

<div class="form-group">

<label class="form-label">

Teacher ID

</label>

<input
type="text"
class="form-input disabled-field"
value="<?php echo $role_data['teacher_id']; ?>"
disabled>

</div>

<div class="form-group">

<label class="form-label">

Subject

</label>

<input
type="text"
name="subject_name"
class="form-input"
value="<?php echo $role_data['subject_name']; ?>">

</div>

<div class="form-group">

<label class="form-label">

Qualification

</label>

<input
type="text"
name="qualification"
class="form-input"
value="<?php echo $role_data['qualification']; ?>">

</div>

<?php } ?>

<!-- PARENT -->

<?php if($role == 'parent'){ ?>

<div class="form-group">

<label class="form-label">

Student ID

</label>

<input
type="text"
class="form-input disabled-field"
value="<?php echo $role_data['student_id']; ?>"
disabled>

</div>

<div class="form-group">

<label class="form-label">

Relationship

</label>

<input
type="text"
name="relationship_name"
class="form-input"
value="<?php echo $role_data['relationship_name']; ?>">

</div>

<?php } ?>

</div>

<button
type="submit"
name="save_profile"
class="btn btn-primary">

<i class="fa-solid fa-floppy-disk"></i>

Save Changes

</button>

</form>

</div>

</div>

</div>

</div>

</div>
<script src="../assets/js/common.js"></script>
<script>

const profileInput =
document.getElementById(
'profilePhotoInput'
);

if(profileInput){

profileInput.addEventListener(
'change',
function(){

const file =
this.files[0];

if(file){

document.getElementById(
'uploadTitle'
).innerText =
file.name;

document.getElementById(
'uploadSubtext'
).innerText =
'File selected successfully';

}

}
);

}

</script>
</body>
</html>
