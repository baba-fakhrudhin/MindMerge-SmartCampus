<?php

require_once '../../config/auth.php';
require_once '../../config/db.php';
require_once '../../shared/helpers/portal.php';

portal_require_role(['parent']);

$user_id = (int)$_SESSION['user']['id'];

$query = mysqli_query(

$conn,

"SELECT

st.full_name,
st.student_id,
st.class_name,
st.section_name,

b.bus_id,
b.bus_name,
b.bus_number,

d.full_name AS driver_name,
d.phone AS driver_phone,
d.profile_photo,

h.full_name AS helper_name,

r.route_name,

l.latitude,
l.longitude,
l.status,
l.updated_at

FROM parents p

INNER JOIN students st
ON st.student_id = p.student_id

INNER JOIN transport_student_assignments tsa
ON tsa.student_id = st.id

INNER JOIN transport_buses b
ON b.bus_id = tsa.bus_id

LEFT JOIN transport_staff d
ON d.staff_id = b.driver_id

LEFT JOIN transport_staff h
ON h.staff_id = b.helper_id

LEFT JOIN transport_routes r
ON r.bus_id=b.bus_id

LEFT JOIN transport_live_location l
ON l.bus_id = b.bus_id

WHERE p.user_id = $user_id

LIMIT 1"

);

$transport = mysqli_fetch_assoc($query);

?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">

<meta
name="viewport"
content="width=device-width, initial-scale=1.0">

<title>
Parent Transport Portal | MindMerge SmartCampus
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

.transport-map{

height:500px;
width:100%;
border-radius:16px;
overflow:hidden;
border:1px solid #e5e7eb;

}

.driver-card{

display:grid;
grid-template-columns:100px 1fr;
gap:20px;
align-items:center;

}

.driver-avatar{

width:90px;
height:90px;
border-radius:50%;
object-fit:cover;

}

.driver-placeholder{

width:90px;
height:90px;
border-radius:50%;

display:flex;
align-items:center;
justify-content:center;

background:#2563eb;
color:white;

font-size:32px;
font-weight:700;

}

.driver-info p{

margin:6px 0;

}

@media(max-width:768px){

.driver-card{

grid-template-columns:1fr;
text-align:center;

}

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

<h1>
Transport Portal
</h1>

<p>
Track your child's school bus in real-time.
</p>

</div>

</div>

<?php if(!$transport){ ?>

<div class="dashboard-section">

<div
style="
text-align:center;
padding:40px;
">

<h3>
No Transport Assignment Found
</h3>

<p>
No transport information is available for your child.
</p>

</div>

</div>

<?php } else { ?>

<!-- Student Information -->

<div class="dashboard-grid">

<div class="dashboard-card">

<h3>
Student
</h3>

<h2>

<?php
echo htmlspecialchars(
$transport['full_name']
);
?>

</h2>

</div>

<div class="dashboard-card">

<h3>
Student ID
</h3>

<h2>

<?php
echo htmlspecialchars(
$transport['student_id']
);
?>

</h2>

</div>

<div class="dashboard-card">

<h3>
Class
</h3>

<h2>

<?php

echo htmlspecialchars(

$transport['class_name']
. ' - ' .
$transport['section_name']

);

?>

</h2>

</div>

<div class="dashboard-card">

<h3>
Bus Number
</h3>

<h2>

<?php

echo htmlspecialchars(
$transport['bus_number']
);

?>

</h2>

</div>

</div>

<!-- Transport Information -->

<div class="dashboard-grid">

<div class="dashboard-card">

<h3>
Bus Name
</h3>

<h2>

<?php

echo htmlspecialchars(
$transport['bus_name']
);

?>

</h2>

</div>

<div class="dashboard-card">

<h3>
Route
</h3>

<h2>

<?php

echo htmlspecialchars(
$transport['route_name'] ?? '-'
);

?>

</h2>

</div>

<div class="dashboard-card">

<h3>
Status
</h3>

<h2>

<?php

echo ucfirst(
$transport['status'] ?? 'offline'
);

?>

</h2>

</div>

<div class="dashboard-card">

<h3>
Last Updated
</h3>

<h2 style="font-size:16px;">

<?php

echo !empty($transport['updated_at'])

? date(
'd M h:i A',
strtotime($transport['updated_at'])
)

: '-';

?>

</h2>

</div>

</div>

<!-- Driver Information -->

<div class="dashboard-section">

<div class="section-header">

<h2>
Driver Information
</h2>

</div>

<div class="driver-card">

<div>

<?php if(!empty($transport['profile_photo'])){ ?>

<img

src="../../uploads/profiles/<?php echo htmlspecialchars($transport['profile_photo']); ?>"

class="driver-avatar">

<?php } else { ?>

<div class="driver-placeholder">

<?php

echo strtoupper(
substr(
$transport['driver_name'],
0,
1
)
);

?>

</div>

<?php } ?>

</div>

<div class="driver-info">

<p>

<strong>Name:</strong>

<?php

echo htmlspecialchars(
$transport['driver_name']
);

?>

</p>

<p>

<strong>Phone:</strong>

<?php

echo htmlspecialchars(
$transport['driver_phone']
);

?>

</p>

<p>

<strong>Helper:</strong>

<?php

echo htmlspecialchars(
$transport['helper_name'] ?? 'Not Assigned'
);

?>

</p>

</div>

</div>

</div>

<!-- Live Tracking -->

<div class="dashboard-section">

<div class="section-header">

<h2>
Live Bus Location
</h2>

</div>

<div
id="transportMap"
class="transport-map">
</div>

</div>

<?php } ?>

</div>

</div>

</div>

<script
src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js">
</script>

<script>

<?php if($transport){ ?>

const busId =
<?php echo (int)$transport['bus_id']; ?>;

const initialLat =
<?php echo (float)($transport['latitude'] ?? 17.3850); ?>;

const initialLng =
<?php echo (float)($transport['longitude'] ?? 78.4867); ?>;

const map =

L.map('transportMap')
.setView(
[
initialLat,
initialLng
],
13
);

L.tileLayer(

'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png',

{
maxZoom:19
}

).addTo(map);

let marker =

L.marker(
[
initialLat,
initialLng
]
)

.addTo(map)

.bindPopup(
'School Bus'
)

.openPopup();

function loadBusLocation(){

fetch(

'../../transport/tracking/fetch_live_location.php?bus_id='
+
busId

)

.then(response => response.json())

.then(data => {

if(data.success){

const lat =
parseFloat(data.latitude);

const lng =
parseFloat(data.longitude);

marker.setLatLng(
[
lat,
lng
]
);

map.panTo(
[
lat,
lng
]
);

}

})

.catch(error => {

console.log(error);

});

}

setInterval(
loadBusLocation,
5000
);

<?php } ?>

</script>

<script src="../../assets/js/common.js"></script>

</body>
</html>