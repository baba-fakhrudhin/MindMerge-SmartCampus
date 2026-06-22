<?php

require_once '../../config/auth.php';
require_once '../../config/db.php';
require_once '../../shared/helpers/portal.php';

portal_require_role(['driver']);

$user_id = (int)$_SESSION['user']['id'];

/*
Get Driver Staff Record
*/

$staff_query = mysqli_query(

$conn,

"SELECT *
FROM transport_staff
WHERE user_id = $user_id
AND staff_type='driver'
LIMIT 1"

);

$staff = mysqli_fetch_assoc($staff_query);

$staff_id = (int)($staff['staff_id'] ?? 0);

/*
Assigned Bus
*/

$bus_query = mysqli_query(

$conn,

"SELECT *
FROM transport_buses
WHERE driver_id = $staff_id
LIMIT 1"

);

$bus = mysqli_fetch_assoc($bus_query);

/*
Defaults
*/

$route = null;

$student_count = 0;
$status_labels = [

'not_started' => 'Not Started',

'running' => 'Running',

'completed' => 'Completed'

];
$tracking_status = 'not_started';

$students = false;
if($bus){

$bus_id = (int)$bus['bus_id'];

/*
Route
*/
$route_query = mysqli_query(

$conn,

"SELECT *

FROM transport_routes

WHERE bus_id = $bus_id

LIMIT 1"

);

$route = mysqli_fetch_assoc($route_query);

/*
Student Count
*/

$student_count = mysqli_fetch_assoc(

mysqli_query(

$conn,

"SELECT COUNT(*) total
FROM transport_student_assignments
WHERE bus_id = $bus_id"

)

)['total'];

/*
Tracking Status
*/

$track_query = mysqli_query(

$conn,

"SELECT status
FROM transport_live_location
WHERE bus_id = $bus_id
LIMIT 1"

);

if(mysqli_num_rows($track_query)){

$track = mysqli_fetch_assoc(
$track_query
);

$tracking_status =
$track['status'];

}

/*
Assigned Students
*/

$students = mysqli_query(

$conn,

"SELECT

u.full_name,

s.class_name,

s.section_name,

COALESCE(st.stop_name,'Not Assigned')
AS stop_name

FROM transport_student_assignments tsa

LEFT JOIN students s
ON tsa.student_id=s.id

LEFT JOIN users u
ON s.user_id=u.id

LEFT JOIN transport_stops st
ON tsa.stop_id=st.stop_id

WHERE tsa.bus_id=$bus_id

ORDER BY u.full_name"

);

}

$driverQuery = mysqli_query(

$conn,

"SELECT

ts.*,

u.email,

u.profile_photo,

b.bus_id,
b.bus_name,
b.bus_number,
b.capacity,
b.status AS bus_status,

r.route_id,
r.route_name,
r.start_time,
r.end_time

FROM transport_staff ts

INNER JOIN users u
ON ts.user_id=u.id

LEFT JOIN transport_buses b
ON b.driver_id=ts.staff_id

LEFT JOIN transport_routes r
ON r.bus_id=b.bus_id

WHERE ts.user_id='$user_id'

LIMIT 1"

);

$driver = mysqli_fetch_assoc($driverQuery);

$hasBus = !empty($driver['bus_id']);

$locationQuery = mysqli_query(

$conn,

"SELECT *

FROM transport_live_location

WHERE bus_id='".intval($driver['bus_id'] ?? 0)."'

LIMIT 1"

);

$liveLocation = mysqli_fetch_assoc($locationQuery);

$routeStatus =
$liveLocation['status'] ?? 'not_started';

?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">

<meta
name="viewport"
content="width=device-width, initial-scale=1.0">

<title>
Driver Dashboard
</title>

<link
rel="stylesheet"
href="../../assets/css/global.css">

<link
rel="stylesheet"
href="../../assets/css/layout.css">

<link
rel="stylesheet"
href="../../assets/css/components.css">

<link
rel="stylesheet"
href="../../assets/css/portals.css">

<link
rel="stylesheet"
href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

<style>


.status-badge{

padding:8px 14px;
border-radius:20px;
font-size:13px;
font-weight:600;

}

.running{
background:#dcfce7;
color:#166534;
}

.not_started{
background:#dbeafe;
color:#1d4ed8;
}

.completed{
background:#fee2e2;
color:#991b1b;
}

.quick-actions{

display:grid;
grid-template-columns:
repeat(auto-fit,minmax(220px,1fr));
gap:16px;

}

.action-card{

background:var(--card);
border-radius:16px;
padding:20px;
box-shadow:var(--shadow);
text-decoration:none;
color:inherit;

}

.action-card:hover{

transform:translateY(-3px);

}

</style>

</head>

<body>

<div class="app-layout">

<?php include('../../partials/sidebar.php'); ?>

<div class="main-content">

<?php include('../../partials/topbar.php'); ?>

<div class="page-content">

<div class="page-header">

<div>

<h1>Driver Dashboard</h1>

<p>
Welcome,
<strong>
<?php echo htmlspecialchars($driver['full_name']); ?>
</strong>
</p>

</div>

</div>
    
<!-- Statistics -->
 <div class="portal-stats-grid">

<div class="dashboard-card">
<div class="card-icon blue">
<i class="fa-solid fa-bus"></i>
</div>
<h3>Assigned Bus</h3>
<h1>
<?php echo htmlspecialchars(
$bus['bus_number'] ?? '--'
); ?>
</h1>
</div>

<div class="dashboard-card">
<div class="card-icon green">
<i class="fa-solid fa-route"></i>
</div>
<h3>Route</h3>
<h1>
<?php echo htmlspecialchars(
$route['route_name'] ?? '--'
); ?>
</h1>
</div>

<div class="dashboard-card">
<div class="card-icon orange">
<i class="fa-solid fa-users"></i>
</div>
<h3>Students</h3>
<h1>
<?php echo $student_count; ?>
</h1>
</div>
<div class="dashboard-card">

<div class="card-icon red">
<i class="fa-solid fa-location-dot"></i>
</div>

<h3>Status</h3>

<div style="margin-top:10px;">

<span class="status-badge <?php echo $tracking_status; ?>">

<?php
echo $status_labels[$tracking_status] ?? 'Unknown';
?>

</span>

</div>

</div>
</div>

<!-- Assignment -->
<div class="dashboard-section">

<div class="section-header">
<h2>Current Assignment</h2>
</div>

<?php if($bus){ ?>

<div class="info-grid">

<div class="info-item">
<span class="info-label">
Bus Number
</span>
<span class="info-value">
<?php echo htmlspecialchars(
$bus['bus_number']
); ?>
</span>
</div>

<div class="info-item">
<span class="info-label">
Bus Name
</span>
<span class="info-value">
<?php echo htmlspecialchars(
$bus['bus_name']
); ?>
</span>
</div>

<div class="info-item">
<span class="info-label">
Route
</span>
<span class="info-value">
<?php echo htmlspecialchars(
$route['route_name'] ?? '-'
); ?>
</span>
</div>

<div class="info-item">
<span class="info-label">
Students
</span>
<span class="info-value">
<?php echo $student_count; ?>
</span>
</div>

</div>

<?php } else { ?>

<div class="alert-banner warning">
No bus assigned yet.
Please contact administrator.
</div>

<?php } ?>

</div>
<div class="dashboard-section">

<div class="section-header">
<h2>Live Tracking</h2>
</div>

<div
style="
display:flex;
gap:12px;
flex-wrap:wrap;
">

<a
href="../../transport/mobile/location.php"
class="btn btn-primary">

<i class="fa-solid fa-play"></i>

Start Trip

</a>

<a
href="../../transport/tracking/index.php"
class="btn">

<i class="fa-solid fa-location-dot"></i>

Tracking Panel

</a>

</div>

</div>

<div class="dashboard-section">

<div class="section-header">
<h2>Assigned Students</h2>
</div>

<div class="table-responsive">

<table class="custom-table">

<thead>

<tr>
<th>Student</th>
<th>Class</th>
<th>Stop</th>
</tr>

</thead>

<tbody>

<?php if($students && mysqli_num_rows($students)>0){ ?>

<?php while($student=mysqli_fetch_assoc($students)){ ?>

<tr>

<td>
<?php echo htmlspecialchars(
$student['full_name']
); ?>
</td>

<td>
<?php echo htmlspecialchars(
$student['class_name']
);
?>
-
<?php echo htmlspecialchars(
$student['section_name']
);
?>
</td>

<td>
<?php echo htmlspecialchars(
$student['stop_name']
);
?>
</td>

</tr>

<?php } ?>

<?php } else { ?>

<tr>
<td colspan="3" style="text-align:center;">
No students assigned.
</td>
</tr>

<?php } ?>

</tbody>

</table>

</div>

</div>
<!-- Quick Actions -->

<div class="dashboard-section">

<div class="section-header">

<h2>
Quick Actions
</h2>

</div>

<div class="quick-actions">

<a
href="../../profile/index.php"
class="action-card">

<i class="fa-solid fa-user"></i>

<h3>
My Profile
</h3>

</a>

<a
href="../../transport/tracking/index.php"
class="action-card">

<i class="fa-solid fa-map-location-dot"></i>

<h3>
View Live Map
</h3>

</a>

</div>

</div>

</div>

</div>

</div>

<script src="../../assets/js/common.js"></script>

</body>
</html>