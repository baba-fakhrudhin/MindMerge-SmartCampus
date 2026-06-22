<?php

include('../../config/auth.php');
include('../../config/db.php');

$route_id = intval($_GET['id'] ?? 0);

if($route_id <= 0){

header('Location:index.php?error=not_found');
exit;

}

/* ==========================================
   Route Query
========================================== */

$route_query = mysqli_query(

$conn,

"SELECT

r.*,

b.bus_name,
b.bus_number,

d.full_name AS driver_name,

h.full_name AS helper_name

FROM transport_routes r

LEFT JOIN transport_buses b
ON r.bus_id = b.bus_id

LEFT JOIN transport_staff d
ON b.driver_id = d.staff_id

LEFT JOIN transport_staff h
ON b.helper_id = h.staff_id

WHERE r.route_id='$route_id'

LIMIT 1"

);

if(mysqli_num_rows($route_query) == 0){

header('Location:index.php?error=not_found');
exit;

}

$route = mysqli_fetch_assoc(
$route_query
);

/* ==========================================
   Stops Query
========================================== */

$stops_query = mysqli_query(

$conn,

"SELECT *

FROM transport_stops

WHERE route_id='$route_id'

ORDER BY stop_order ASC"

);

$routeStops = [];

while($stop = mysqli_fetch_assoc($stops_query)){

$routeStops[] = [

'stop_id' => $stop['stop_id'],

'stop_name' => $stop['stop_name'],

'latitude' => (float)$stop['latitude'],

'longitude' => (float)$stop['longitude'],

'arrival_time' => $stop['arrival_time'],

'is_start' => (int)$stop['is_start'],

'is_end' => (int)$stop['is_end'],

'stop_order' => (int)$stop['stop_order']

];

}

$total_stops =
count($routeStops);

?>

<!DOCTYPE html>

<html lang="en">

<head>

<meta charset="UTF-8">

<meta
name="viewport"
content="width=device-width, initial-scale=1.0">

<title>

<?php

echo htmlspecialchars(
$route['route_name']
);

?>

| Route Details

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
href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

<link
rel="stylesheet"
href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>

<style>

.route-map{

height:600px;
width:100%;

border-radius:16px;

overflow:hidden;

border:1px solid #e5e7eb;

}

.timeline{

display:flex;

flex-direction:column;

gap:14px;

}

.timeline-item{

background:var(--card);

border:1px solid var(--border-color,#334155);

border-left:5px solid #2563eb;

padding:16px;

border-radius:12px;

transition:.3s;

}

.timeline-start{

border-left-color:#16a34a;

}

.timeline-end{

border-left-color:#dc2626;

}

.route-color-box{

width:40px;
height:40px;

border-radius:10px;

border:1px solid #e5e7eb;

}

.info-grid{

display:grid;

grid-template-columns:
repeat(auto-fit,minmax(250px,1fr));

gap:16px;

}

</style>

</head>

<body>

<div class="app-layout">

<?php include('../../partials/sidebar.php'); ?>

<div class="main-content">

<?php include('../../partials/topbar.php'); ?>

<div class="page-content">

<!-- PAGE HEADER -->

<div class="page-header">

<div>

<h1>

<?php

echo htmlspecialchars(
$route['route_name']
);

?>

</h1>

<p>

View complete route information and stop locations.

</p>

</div>

<div
style="
display:flex;
gap:10px;
flex-wrap:wrap;
">

<a
href="edit.php?id=<?php echo $route_id; ?>"
class="btn btn-primary">

<i class="fa-solid fa-pen"></i>

Edit Route

</a>

<a
href="delete.php?id=<?php echo $route_id; ?>"
class="btn"
style="
background:#ef4444;
color:white;
"
onclick="return confirm('Delete this route?');">

<i class="fa-solid fa-trash"></i>

Delete

</a>

<a
href="index.php"
class="btn">

<i class="fa-solid fa-arrow-left"></i>

Back

</a>

</div>

</div>

<!-- ROUTE SUMMARY -->

<div class="dashboard-grid">

<div class="dashboard-card">

<h3>Total Stops</h3>

<h2>

<?php
echo $total_stops;
?>

</h2>

</div>

<div class="dashboard-card">

<h3>Bus Assigned</h3>

<h2>

<?php

echo htmlspecialchars(
$route['bus_name']
);

?>

</h2>

</div>

<div class="dashboard-card">

<h3>Driver</h3>

<h2>

<?php

echo !empty($route['driver_name'])

? htmlspecialchars($route['driver_name'])

: 'Not Assigned';

?>

</h2>

</div>

<div class="dashboard-card">

<h3>Status</h3>

<h2>

<?php

echo ucfirst(
$route['status']
);

?>

</h2>

</div>

</div>

<!-- ROUTE DETAILS -->

<div class="dashboard-section">

<div class="section-header">

<h2>
Route Information
</h2>

</div>

<div class="info-grid">

<div>

<strong>
Route Name
</strong>

<br>

<?php

echo htmlspecialchars(
$route['route_name']
);

?>

</div>

<div>

<strong>
Bus
</strong>

<br>

<?php

echo htmlspecialchars(
$route['bus_name']
);

?>

(

<?php

echo htmlspecialchars(
$route['bus_number']
);

?>

)

</div>

<div>

<strong>
Driver
</strong>

<br>

<?php

echo !empty($route['driver_name'])

? htmlspecialchars($route['driver_name'])

: 'Not Assigned';

?>

</div>

<div>

<strong>
Helper
</strong>

<br>

<?php

echo !empty($route['helper_name'])

? htmlspecialchars($route['helper_name'])

: 'Not Assigned';

?>

</div>

<div>

<strong>
Start Time
</strong>

<br>

<?php

echo !empty($route['start_time'])

? date(
'h:i A',
strtotime(
$route['start_time']
)
)

: '-';

?>

</div>

<div>

<strong>
End Time
</strong>

<br>

<?php

echo !empty($route['end_time'])

? date(
'h:i A',
strtotime(
$route['end_time']
)
)

: '-';

?>

</div>

<div>

<strong>
Route Color
</strong>

<br><br>

<div
class="route-color-box"
style="
background:
<?php echo htmlspecialchars($route['route_color']); ?>;
">
</div>

</div>

<div>

<strong>
Status
</strong>

<br><br>

<span
class="status <?php echo ($route['status']=='active') ? 'success' : 'danger'; ?>">

<?php

echo ucfirst(
$route['status']
);

?>

</span>

</div>

</div>

<?php if(!empty($route['route_description'])){ ?>

<div
style="
margin-top:20px;
">

<strong>
Description
</strong>

<p>

<?php

echo nl2br(
htmlspecialchars(
$route['route_description']
)
);

?>

</p>

</div>

<?php } ?>

</div>

<!-- MAP -->

<div class="dashboard-section">

<div class="section-header">

<h2>
Route Map
</h2>

</div>

<div
id="routeMap"
class="route-map">
</div>

</div>

<script>

const routeStops =

<?php

echo json_encode(
$routeStops
);

?>;

const routeColor =

"<?php echo htmlspecialchars($route['route_color']); ?>";

</script>
<!-- ROUTE TIMELINE -->

<div class="dashboard-section">

<div class="section-header">

<h2>
Route Stops Timeline
</h2>

</div>

<div class="timeline">

<?php

foreach($routeStops as $index => $stop){

$timelineClass = '';

if($stop['is_start']){

$timelineClass = 'timeline-start';

}
elseif($stop['is_end']){

$timelineClass = 'timeline-end';

}

?>

<div class="timeline-item <?php echo $timelineClass; ?>">

<div
style="
display:flex;
justify-content:space-between;
align-items:center;
gap:12px;
flex-wrap:wrap;
">

<div>

<strong>

#<?php echo ($index + 1); ?>

-

<?php

echo htmlspecialchars(
$stop['stop_name']
);

?>

</strong>

<br>

<div
style="
margin-top:6px;
font-size:14px;
color:var(--text-muted,#64748b);
">

🕒

<?php

echo !empty($stop['arrival_time'])

? date(
'h:i A',
strtotime(
$stop['arrival_time']
)
)

: '-';

?>

</div>

</div>
<div>

<?php

if($stop['is_start']){

?>

<span class="status success">

START POINT

</span>

<?php

}
elseif($stop['is_end']){

?>

<span class="status danger">

END POINT

</span>

<?php

}
else{

?>

<span class="status">

STOP

</span>

<?php

}

?>

</div>

</div>

</div>

<?php } ?>

</div>

</div>


</div>

</div>

</div>

<script
src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js">
</script>

<script>

const map = L.map(
'routeMap'
);

L.tileLayer(

'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png',

{

maxZoom:19,

attribution:
'© OpenStreetMap'

}

).addTo(map);

let points = [];

routeStops.forEach(

function(stop){

let markerColor =
'#2563eb';

let label =
'STOP';

if(stop.is_start){

markerColor =
'#16a34a';

label =
'START';

}

if(stop.is_end){

markerColor =
'#dc2626';

label =
'END';

}

L.circleMarker(

[

stop.latitude,

stop.longitude

],

{

radius:8,

fillColor:markerColor,

color:markerColor,

weight:2,

fillOpacity:1

}

)

.addTo(map)

.bindPopup(

'<strong>'

+

stop.stop_name

+

'</strong><br>'

+

'Arrival: '

+

(stop.arrival_time || '-')

+

'<br>'

+

label

);

points.push([

stop.latitude,

stop.longitude

]);

}

);

if(points.length > 0){

const routeLine =

L.polyline(

points,

{

color:routeColor,

weight:5

}

)

.addTo(map);

map.fitBounds(

routeLine.getBounds(),

{

padding:[30,30]

}

);

}
else{

map.setView(

[13.6288,79.4192],

13

);

}

</script>

<script src="../../assets/js/common.js"></script>

</body>

</html>