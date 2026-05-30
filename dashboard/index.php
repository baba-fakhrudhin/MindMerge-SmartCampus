<?php

session_start();

if(!isset($_SESSION['user'])){

header("Location: ../auth/login.php");
exit();

}

?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Dashboard | MindMerge SmartCampus</title>

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

<h1>Dashboard</h1>

<p>
Welcome back Admin 👋
Here is today's school analytics and overview.
</p>

</div>

<button class="btn btn-primary">

<i class="fa-solid fa-plus"></i>
Add New Student

</button>

</div>

<!-- STATS -->

<div class="dashboard-grid">

<div class="dashboard-card">

<div class="card-icon blue">
<i class="fa-solid fa-user-graduate"></i>
</div>

<h3>Total Students</h3>

<h1>1200</h1>

<p>Currently enrolled students</p>

</div>

<div class="dashboard-card">

<div class="card-icon green">
<i class="fa-solid fa-chalkboard-user"></i>
</div>

<h3>Total Teachers</h3>

<h1>120</h1>

<p>Active teaching staff</p>

</div>

<div class="dashboard-card">

<div class="card-icon orange">
<i class="fa-solid fa-calendar-check"></i>
</div>

<h3>Attendance</h3>

<h1>92%</h1>

<p>Today's attendance rate</p>

</div>

<div class="dashboard-card">

<div class="card-icon red">
<i class="fa-solid fa-wallet"></i>
</div>

<h3>Pending Fees</h3>

<h1>₹50K</h1>

<p>Pending fee collections</p>

</div>

</div>

<!-- QUICK ACTIONS -->

<div class="dashboard-section">

<div class="section-header">

<h2>Quick Actions</h2>

</div>

<div class="quick-actions">

<div class="action-card">

<i class="fa-solid fa-user-plus"></i>

<h3>Add Student</h3>

<p>
Register new student into the system
</p>

</div>

<div class="action-card">

<i class="fa-solid fa-user-tie"></i>

<h3>Add Teacher</h3>

<p>
Create and manage teacher profiles
</p>

</div>

<div class="action-card">

<i class="fa-solid fa-file-circle-check"></i>

<h3>Attendance</h3>

<p>
Mark and monitor daily attendance
</p>

</div>

<div class="action-card">

<i class="fa-solid fa-square-poll-vertical"></i>

<h3>Results</h3>

<p>
Upload and manage exam results
</p>

</div>

</div>

</div>

<!-- SCHOOL OVERVIEW -->

<div class="school-overview">

<div class="notice-board">

<div class="section-header">

<h2>Notice Board</h2>

</div>

<div class="notice-item">

<h4>Mid Exams Start Next Week</h4>

<p>
Examinations for all sections begin Monday.
</p>

</div>

<div class="notice-item">

<h4>Parent Meeting Scheduled</h4>

<p>
Parent teacher meeting on Saturday at 10AM.
</p>

</div>

<div class="notice-item">

<h4>Fee Submission Reminder</h4>

<p>
Last date for fee payment is 25th June.
</p>

</div>

</div>

<div class="event-box">

<h2>Upcoming Event</h2>

<h1>
Annual Science Expo
</h1>

<p>
Students from all classes will participate in innovation projects and exhibitions.
</p>

</div>

</div>

<!-- RECENT EXAMS TABLE -->

<div class="dashboard-section">

<div class="section-header">

<h2>Recent Exam Updates</h2>

</div>

<div class="table-responsive">

<table class="custom-table">

<thead>

<tr>

<th>Exam</th>
<th>Class</th>
<th>Date</th>
<th>Status</th>

</tr>

</thead>

<tbody>

<tr>

<td>Mid Term</td>
<td>10-A</td>
<td>12 June</td>
<td>
<span class="status success">
Completed
</span>
</td>

</tr>

<tr>

<td>Science Quiz</td>
<td>9-B</td>
<td>15 June</td>
<td>
<span class="status warning">
Upcoming
</span>
</td>

</tr>

<tr>

<td>Mathematics Test</td>
<td>8-C</td>
<td>18 June</td>
<td>
<span class="status danger">
Pending
</span>
</td>

</tr>

</tbody>

</table>

</div>

</div>

</div>

</div>

</div>
<script src="../assets/js/common.js"></script>

</body>
</html>