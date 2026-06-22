<?php

include('../../config/auth.php');
include('../../config/db.php');

if(
!isset($_SESSION['user'])
||
strtolower($_SESSION['user']['role']) != 'driver'
){

header('Location:../../auth/login.php');
exit;

}

$user_id = (int)$_SESSION['user']['id'];

$query = mysqli_query(

$conn,

"SELECT

ts.full_name,

b.bus_id,
b.bus_name,
b.bus_number,

r.route_name

FROM transport_staff ts

LEFT JOIN transport_buses b
ON b.driver_id = ts.staff_id

LEFT JOIN transport_routes r
ON r.bus_id = b.bus_id

WHERE ts.user_id='$user_id'

LIMIT 1"

);

$driver = mysqli_fetch_assoc($query);
if(
empty($driver['bus_id'])
){
die(
'No bus assigned. Contact administrator.'
);
}

?>

<!DOCTYPE html>

<html lang="en">

<head>

<meta charset="UTF-8">

<meta
name="viewport"
content="width=device-width,initial-scale=1.0">

<title>
Driver GPS Tracking
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

.mobile-wrapper{

max-width:900px;
margin:auto;

}

.info-grid{

display:grid;

grid-template-columns:
repeat(auto-fit,minmax(220px,1fr));

gap:15px;

}

.info-card{

background:var(--card);

padding:18px;

border-radius:16px;

box-shadow:var(--shadow);

}

#driverMap{

height:500px;

width:100%;

border-radius:16px;

overflow:hidden;

margin-top:20px;

}

.status-box{

padding:12px;

border-radius:12px;

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

.actions{

display:flex;
gap:12px;
flex-wrap:wrap;
margin-top:20px;

}

.gps-data{

margin-top:15px;
font-size:14px;
color:#64748b;

}

</style>

</head>

<body>

<div class="app-layout">

<?php include('../../partials/sidebar.php'); ?>

<div class="main-content">

<?php include('../../partials/topbar.php'); ?>

<div class="page-content">

<div class="mobile-wrapper">

<div class="page-header">

<div>

<h1>
Driver GPS Tracking
</h1>

<p>
Live location broadcasting
</p>

</div>

</div>

<div class="info-grid">

<div class="info-card">

<h4>
Driver
</h4>

<p>

<?php

echo htmlspecialchars(
$driver['full_name'] ?? '-'
);

?>

</p>

</div>

<div class="info-card">

<h4>
Bus
</h4>

<p>

<?php

echo htmlspecialchars(
$driver['bus_number'] ?? '-'
);

?>

</p>

</div>

<div class="info-card">

<h4>
Route
</h4>

<p>

<?php

echo htmlspecialchars(
$driver['route_name'] ?? '-'
);

?>

</p>

</div>

</div>

<div
id="trackingStatus"
class="status-box not_started">

Journey Not Started

</div>

<div class="actions">

<button
id="startJourneyBtn"
class="btn btn-primary">

<i class="fa-solid fa-play"></i>

Start Journey

</button>

<button
id="endJourneyBtn"
class="btn">

<i class="fa-solid fa-stop"></i>

End Journey

</button>
<button
id="resetJourneyBtn"
class="btn btn-danger">

<i class="fa-solid fa-rotate-left"></i>

Reset Trip

</button>

</div>

<div class="gps-data">

Latitude: <span id="latText">
-----------------------------

</span>

|

Longitude: <span id="lngText">
------------------------------

</span>

|

Last Update: <span id="timeText">
---------------------------------

</span>

</div>

<div id="driverMap"></div>

</div>

</div>

</div>

</div>
<?php

$statusQuery = mysqli_query(

$conn,

"SELECT status
FROM transport_live_location
WHERE bus_id='".(int)$driver['bus_id']."'
LIMIT 1"

);

$statusRow =
mysqli_fetch_assoc(
$statusQuery
);

$currentStatus =
$statusRow['status']
?? 'not_started';
?>
<script
src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js">
</script>

<script>

let map =
L.map('driverMap')
.setView(
[13.6288,79.4192],
14
);

L.tileLayer(

'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png',

{
maxZoom:19
}

).addTo(map);

let marker = null;
let trackingStatus =
'<?php echo $currentStatus; ?>';
updateStatusUI(
trackingStatus
);
function updateStatusUI(status){

let box =
document.getElementById(
'trackingStatus'
);

box.className =
'status-box ' + status;

if(status === 'running'){

box.innerHTML =
'Journey Running';

}
else if(status === 'completed'){

box.innerHTML =
'Journey Completed';

}
else{

box.innerHTML =
'Journey Not Started';

}

}

document
.getElementById('startJourneyBtn')
.addEventListener(
'click',
function(){

trackingStatus = 'running';

updateStatusUI(
trackingStatus
);

if(
currentLat !== null
&&
currentLng !== null
){

sendLocation(
currentLat,
currentLng
);

}

}
);

document
.getElementById('endJourneyBtn')
.addEventListener(
'click',
function(){

trackingStatus = 'completed';

updateStatusUI(
trackingStatus
);

if(
currentLat !== null
&&
currentLng !== null
){

sendLocation(
currentLat,
currentLng
);

}

}
);

let currentLat = null;
let currentLng = null;

function sendLocation(lat,lng){

fetch(

'../tracking/update_location.php',

{

method:'POST',

headers:{

'Content-Type':
'application/x-www-form-urlencoded'

},

body:

'latitude='
+
encodeURIComponent(lat)

+

'&longitude='

+

encodeURIComponent(lng)

+

'&status='

+

encodeURIComponent(
trackingStatus
)

}

)

.then(response=>response.json())

.then(data=>{

document
.getElementById(
'timeText'
)
.innerText =
new Date()
.toLocaleTimeString();

})

.catch(console.error);

}
let lastLat = null;
let lastLng = null;

setInterval(function(){

if(
trackingStatus === 'running'
&&
lastLat !== null
&&
lastLng !== null
){

sendLocation(
lastLat,
lastLng
);

}

},15000);
function handlePosition(position){

currentLat =
position.coords.latitude;

currentLng =
position.coords.longitude;

document
.getElementById(
'latText'
)
.innerText =
currentLat.toFixed(6);

document
.getElementById(
'lngText'
)
.innerText =
currentLng.toFixed(6);

if(marker){

map.removeLayer(marker);

}

marker = L.marker(
[currentLat,currentLng]
).addTo(map);

map.setView(
[currentLat,currentLng],
16
);

if(
trackingStatus === 'running'
){

lastLat = currentLat;
lastLng = currentLng;

}

}

function startGPS(){

if(

!navigator.geolocation

){

alert(
'GPS not supported'
);

return;

}

navigator.geolocation.watchPosition(

handlePosition,

function(error){

console.error(error);

},

{

enableHighAccuracy:true,

maximumAge:0,

timeout:10000

}

);

}

startGPS();
document
.getElementById('resetJourneyBtn')
.addEventListener(
'click',
function(){

trackingStatus =
'not_started';

updateStatusUI(
trackingStatus
);

if(
currentLat !== null &&
currentLng !== null
){
    sendLocation(
        currentLat,
        currentLng
    );
}

}
);

</script>

<script src="../../assets/js/common.js"></script>

</body>

</html>
