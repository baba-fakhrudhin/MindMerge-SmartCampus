<?php

include('../config/auth.php');
include('../config/db.php');

$today = date('Y-m-d');

$total_classes = mysqli_fetch_assoc(

mysqli_query(

$conn,

"SELECT COUNT(*) total
FROM classes
WHERE status='active'"

)

)['total'];

$total_students = mysqli_fetch_assoc(

mysqli_query(

$conn,

"SELECT COUNT(*) total
FROM students"

)

)['total'];

$today_attendance = 0;

if(mysqli_query(
$conn,
"SHOW TABLES LIKE 'attendance'"
)->num_rows > 0){

$today_attendance = mysqli_fetch_assoc(

mysqli_query(

$conn,

"SELECT COUNT(*) total
FROM attendance
WHERE attendance_date='$today'"

)

)['total'];

}

$attendance_percentage = 0;

if($total_classes > 0){

$attendance_percentage =
round(
($today_attendance / $total_classes) * 100
);

if($attendance_percentage > 100){

$attendance_percentage = 100;

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

<title>
Attendance Management | MindMerge SmartCampus
</title>

<link rel="stylesheet" href="../assets/css/global.css">
<link rel="stylesheet" href="../assets/css/layout.css">
<link rel="stylesheet" href="../assets/css/components.css">

<link rel="stylesheet"
href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

<style>

.attendance-banner{

background:linear-gradient(
135deg,
#2563eb,
#1d4ed8
);

padding:28px;

border-radius:18px;

color:white;

margin-bottom:24px;

}

.attendance-banner p{

color:#dbeafe;

margin-top:8px;

}

.quick-grid{

display:grid;

grid-template-columns:
repeat(auto-fit,minmax(240px,1fr));

gap:20px;

}

.quick-card{

background:var(--card);

border-radius:18px;

padding:24px;

box-shadow:var(--shadow);

border:1px solid rgba(148,163,184,.15);

transition:.3s;

text-decoration:none;

color:inherit;

}

.quick-card:hover{

transform:translateY(-4px);

}

.quick-card i{

font-size:28px;

margin-bottom:14px;

color:var(--primary);

}

.quick-card h3{

margin-bottom:8px;

}

.recent-box{

padding:18px;

border-radius:14px;

background:var(--card);

border:1px solid rgba(148,163,184,.15);

}

body.dark-mode .quick-card,
body.dark-mode .recent-box{

background:#111c35;
border-color:#29476d;

}

</style>

</head>

<body>

<div class="app-layout">

<?php include('../partials/sidebar.php'); ?>

<div class="main-content">

<?php include('../partials/topbar.php'); ?>

<div class="page-content">

<div class="attendance-banner">

<h1>
Attendance Management
</h1>

<p>
Manage daily student attendance, reports and attendance history.
</p>

</div>

<div class="dashboard-grid">

<div class="dashboard-card stat-card">

<div class="stat-top">

<div class="card-icon">

<i class="fa-solid fa-calendar-check"></i>

</div>

<h3>

<?php echo $today_attendance; ?>

</h3>

</div>

<p>
Classes Marked Today
</p>

</div>

<div class="dashboard-card stat-card">

<div class="stat-top">

<div class="card-icon">

<i class="fa-solid fa-user-graduate"></i>

</div>

<h3>

<?php echo $total_students; ?>

</h3>

</div>

<p>
Total Students
</p>

</div>

<div class="dashboard-card stat-card">

<div class="stat-top">

<div class="card-icon">

<i class="fa-solid fa-school"></i>

</div>

<h3>

<?php echo $total_classes; ?>

</h3>

</div>

<p>
Active Classes
</p>

</div>

<div class="dashboard-card stat-card">

<div class="stat-top">

<div class="card-icon">

<i class="fa-solid fa-chart-line"></i>

</div>

<h3>

<?php echo $attendance_percentage; ?>%

</h3>

</div>

<p>
Today's Completion
</p>

</div>

</div>
<?php

include('../config/auth.php');
include('../config/db.php');

$today = date('Y-m-d');

$total_classes = mysqli_fetch_assoc(

mysqli_query(

$conn,

"SELECT COUNT(*) total
FROM classes
WHERE status='active'"

)

)['total'];

$total_students = mysqli_fetch_assoc(

mysqli_query(

$conn,

"SELECT COUNT(*) total
FROM students"

)

)['total'];

$today_attendance = 0;

if(mysqli_query(
$conn,
"SHOW TABLES LIKE 'attendance'"
)->num_rows > 0){

$today_attendance = mysqli_fetch_assoc(

mysqli_query(

$conn,

"SELECT COUNT(*) total
FROM attendance
WHERE attendance_date='$today'"

)

)['total'];

}

$attendance_percentage = 0;

if($total_classes > 0){

$attendance_percentage =
round(
($today_attendance / $total_classes) * 100
);

if($attendance_percentage > 100){

$attendance_percentage = 100;

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

<title>
Attendance Management | MindMerge SmartCampus
</title>

<link rel="stylesheet" href="../assets/css/global.css">
<link rel="stylesheet" href="../assets/css/layout.css">
<link rel="stylesheet" href="../assets/css/components.css">

<link rel="stylesheet"
href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

<style>

.attendance-banner{

background:linear-gradient(
135deg,
#2563eb,
#1d4ed8
);

padding:28px;

border-radius:18px;

color:white;

margin-bottom:24px;

}

.attendance-banner p{

color:#dbeafe;

margin-top:8px;

}

.quick-grid{

display:grid;

grid-template-columns:
repeat(auto-fit,minmax(240px,1fr));

gap:20px;

}

.quick-card{

background:var(--card);

border-radius:18px;

padding:24px;

box-shadow:var(--shadow);

border:1px solid rgba(148,163,184,.15);

transition:.3s;

text-decoration:none;

color:inherit;

}

.quick-card:hover{

transform:translateY(-4px);

}

.quick-card i{

font-size:28px;

margin-bottom:14px;

color:var(--primary);

}

.quick-card h3{

margin-bottom:8px;

}

.recent-box{

padding:18px;

border-radius:14px;

background:var(--card);

border:1px solid rgba(148,163,184,.15);

}

body.dark-mode .quick-card,
body.dark-mode .recent-box{

background:#111c35;
border-color:#29476d;

}

</style>

</head>

<body>

<div class="app-layout">

<?php include('../partials/sidebar.php'); ?>

<div class="main-content">

<?php include('../partials/topbar.php'); ?>

<div class="page-content">

<div class="attendance-banner">

<h1>
Attendance Management
</h1>

<p>
Manage daily student attendance, reports and attendance history.
</p>

</div>

<div class="dashboard-grid">

<div class="dashboard-card stat-card">

<div class="stat-top">

<div class="card-icon">

<i class="fa-solid fa-calendar-check"></i>

</div>

<h3>

<?php echo $today_attendance; ?>

</h3>

</div>

<p>
Classes Marked Today
</p>

</div>

<div class="dashboard-card stat-card">

<div class="stat-top">

<div class="card-icon">

<i class="fa-solid fa-user-graduate"></i>

</div>

<h3>

<?php echo $total_students; ?>

</h3>

</div>

<p>
Total Students
</p>

</div>

<div class="dashboard-card stat-card">

<div class="stat-top">

<div class="card-icon">

<i class="fa-solid fa-school"></i>

</div>

<h3>

<?php echo $total_classes; ?>

</h3>

</div>

<p>
Active Classes
</p>

</div>

<div class="dashboard-card stat-card">

<div class="stat-top">

<div class="card-icon">

<i class="fa-solid fa-chart-line"></i>

</div>

<h3>

<?php echo $attendance_percentage; ?>%

</h3>

</div>

<p>
Today's Completion
</p>

</div>

</div>