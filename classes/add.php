<?php

include('../config/auth.php');
include('../config/db.php');

$error = '';

if(isset($_POST['add_class'])){

$class_code = mysqli_real_escape_string(
$conn,
trim($_POST['class_code'])
);

$class_name = mysqli_real_escape_string(
$conn,
trim($_POST['class_name'])
);

$description = mysqli_real_escape_string(
$conn,
trim($_POST['description'])
);

$status = mysqli_real_escape_string(
$conn,
$_POST['status']
);

$check = mysqli_query(
$conn,
"SELECT * FROM classes
WHERE class_code='$class_code'"
);

if(mysqli_num_rows($check) > 0){

$error = "Class Code already exists.";

}
else{

mysqli_query(

$conn,

"INSERT INTO classes
(
class_code,
class_name,
description,
status,
created_at
)

VALUES
(
'$class_code',
'$class_name',
'$description',
'$status',
NOW()
)"

);

header("Location: index.php?success=added");
exit();

}

}

?>

<!DOCTYPE html>
<html>

<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Add Class</title>

<link rel="stylesheet" href="../assets/css/global.css">
<link rel="stylesheet" href="../assets/css/layout.css">
<link rel="stylesheet" href="../assets/css/components.css">

<link rel="stylesheet"
href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

</head>

<body>

<div class="app-layout">

<?php include('../partials/sidebar.php'); ?>

<div class="main-content">

<?php include('../partials/topbar.php'); ?>

<div class="page-content">
<div class="page-header">

<div>

<h1>Add Class</h1>

<p>Create a new class.</p>

</div>

<a
href="index.php"
class="btn">

<i class="fa-solid fa-arrow-left"></i>

Back to Classes

</a>

</div>

<div class="dashboard-section">

<?php if($error != ''){ ?>

<p style="color:red;">
<?php echo $error; ?>
</p>

<?php } ?>

<form method="POST">

<div class="form-grid">

<div class="form-group">

<label class="form-label">
Class Code
</label>

<input
type="text"
name="class_code"
class="form-input"
required>

</div>

<div class="form-group">

<label class="form-label">
Class Name
</label>

<input
type="text"
name="class_name"
class="form-input"
required>

</div>

</div>

<div class="form-group">

<label class="form-label">
Description
</label>

<textarea
name="description"
class="form-textarea"></textarea>

</div>

<div class="form-group">

<label class="form-label">
Status
</label>

<select
name="status"
class="form-select">

<option value="active">
Active
</option>

<option value="inactive">
Inactive
</option>

</select>

</div>
<div
style="
display:flex;
gap:12px;
flex-wrap:wrap;
">

<button
type="submit"
name="add_class"
class="btn btn-primary">

<i class="fa-solid fa-floppy-disk"></i>

Save Class

</button>

<a
href="index.php"
class="btn">

Cancel

</a>

</div>

</form>

</div>

</div>

</div>

</div>

<script src="../assets/js/common.js"></script>

</body>
</html>