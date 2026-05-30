<?php

include('../config/auth.php');
include('../config/db.php');

$id = intval($_GET['id']);

$class = mysqli_query(
$conn,
"SELECT * FROM classes
WHERE class_id='$id'"
);

$row = mysqli_fetch_assoc($class);

if(!$row){

header("Location:index.php");
exit();

}

if(isset($_POST['update_class'])){

$class_code =
mysqli_real_escape_string(
$conn,
$_POST['class_code']
);

$class_name =
mysqli_real_escape_string(
$conn,
$_POST['class_name']
);

$description =
mysqli_real_escape_string(
$conn,
$_POST['description']
);

$status =
mysqli_real_escape_string(
$conn,
$_POST['status']
);

mysqli_query(

$conn,

"UPDATE classes

SET

class_code='$class_code',
class_name='$class_name',
description='$description',
status='$status'

WHERE class_id='$id'"

);

header("Location:index.php?success=updated");
exit();

}

?>

<!DOCTYPE html>
<html>

<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Edit Class</title>

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

<h1>Edit Class</h1>

<p>Update class information.</p>

</div>

<a
href="index.php"
class="btn">

<i class="fa-solid fa-arrow-left"></i>

Back to Classes

</a>

</div>

<div class="dashboard-section">

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
value="<?php echo htmlspecialchars($row['class_code']); ?>"
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
value="<?php echo htmlspecialchars($row['class_name']); ?>"
required>

</div>

</div>

<div class="form-group">

<label class="form-label">
Description
</label>

<textarea
name="description"
class="form-textarea"><?php echo htmlspecialchars($row['description']); ?></textarea>

</div>

<div class="form-group">

<label class="form-label">
Status
</label>

<select
name="status"
class="form-select">

<option
value="active"
<?php if($row['status'] == 'active') echo 'selected'; ?>>

Active

</option>

<option
value="inactive"
<?php if($row['status'] == 'inactive') echo 'selected'; ?>>

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
name="update_class"
class="btn btn-primary">

<i class="fa-solid fa-floppy-disk"></i>

Update Class

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