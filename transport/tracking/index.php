<?php

include('../../config/auth.php');
include('../../config/db.php');
$customRole = strtolower(trim($_SESSION['user']['role'] ?? ''));
$isAdmin =
($customRole === 'admin');

$isDriver =
($customRole === 'driver');

$isStudent =
($customRole === 'student');

$isParent =
($customRole === 'parent');
       

$user_id = (int)$_SESSION['user']['id'];

$selected_bus_id = 0;
/* ==========================
   Role Based Bus Selection
========================== */

$drivers = false;

 if($customRole === 'admin'){ 

    $drivers = mysqli_query(

        $conn,

        "SELECT

            ts.staff_id,
            ts.full_name,

            b.bus_id,
            b.bus_name,
            b.bus_number

         FROM transport_staff ts

         INNER JOIN transport_buses b
         ON b.driver_id = ts.staff_id

         WHERE b.driver_id IS NOT NULL

         ORDER BY ts.full_name"

    );
    $selected_bus_id =
(int)($_GET['bus_id'] ?? 0);
if(
$selected_bus_id == 0
&&
mysqli_num_rows($drivers) > 0
){

$firstDriver =
mysqli_fetch_assoc($drivers);

$selected_bus_id =
(int)$firstDriver['bus_id'];

mysqli_data_seek($drivers,0);

}

}

elseif($customRole === 'driver'){

    $driverBus = mysqli_query(

        $conn,

        "SELECT
            b.bus_id

         FROM transport_buses b

         INNER JOIN transport_staff ts
         ON ts.staff_id=b.driver_id

         WHERE ts.user_id='$user_id'

         LIMIT 1"

    );

    $driverRow =
    mysqli_fetch_assoc($driverBus);

    $selected_bus_id =
    (int)($driverRow['bus_id'] ?? 0);

}
elseif($customRole === 'student'){

    $studentQuery = mysqli_query(

        $conn,

        "SELECT id

         FROM students

         WHERE user_id='$user_id'

         LIMIT 1"

    );

    $student =
    mysqli_fetch_assoc($studentQuery);

    $student_id =
    (int)($student['id'] ?? 0);

    $busQuery = mysqli_query(

        $conn,

        "SELECT bus_id

         FROM transport_student_assignments

         WHERE student_id='$student_id'

         LIMIT 1"

    );

    $bus =
    mysqli_fetch_assoc($busQuery);

    $selected_bus_id =
    (int)($bus['bus_id'] ?? 0);
    if($selected_bus_id <= 0){

$transport_message =
'No transport bus assigned to your account.';

}

}

elseif($customRole === 'parent'){

    $parentQuery = mysqli_query(

        $conn,

        "SELECT student_id

         FROM parents

         WHERE user_id='$user_id'

         LIMIT 1"

    );

    $parent =
    mysqli_fetch_assoc($parentQuery);

    $studentCode =
    $parent['student_id'] ?? '';

    $studentQuery = mysqli_query(

        $conn,

        "SELECT id

         FROM students

         WHERE student_id='$studentCode'

         LIMIT 1"

    );

    $student =
    mysqli_fetch_assoc($studentQuery);

    $student_id =
    (int)($student['id'] ?? 0);

    $busQuery = mysqli_query(

        $conn,

        "SELECT bus_id

         FROM transport_student_assignments

         WHERE student_id='$student_id'

         LIMIT 1"

    );

    $bus =
    mysqli_fetch_assoc($busQuery);

    $selected_bus_id =
    (int)($bus['bus_id'] ?? 0);
        if($selected_bus_id <= 0){

        $transport_message =
        'No transport bus assigned for this student.';

        }

}
$has_transport_access =
($selected_bus_id > 0);
?>

<!DOCTYPE html>

<html lang="en">

<head>

<meta charset="UTF-8">

<meta
name="viewport"
content="width=device-width, initial-scale=1.0">

<title>
Live Transport Tracking
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

.tracking-map{

height:650px;
width:100%;

border-radius:16px;

overflow:hidden;

border:1px solid var(--border);

}


.status-badge{

padding:6px 12px;

border-radius:20px;

font-size:12px;

font-weight:600;

}

.running{

background:#dcfce7;
color:#166534;

}

.completed{

background:#fee2e2;
color:#991b1b;

}

.not_started{

background:#dbeafe;
color:#1d4ed8;

}

.bus-list{

margin-top:20px;

}

.driver-photo{

width:50px;
height:50px;

border-radius:50%;

object-fit:cover;

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
Live Transport Tracking
</h1>

<p>

<?php

if($customRole === 'admin'){

echo 'Monitor all transport operations.';

}
elseif($customRole === 'driver'){

echo 'View your assigned bus tracking.';

}
elseif($customRole === 'student'){

echo 'Track your assigned school bus.';

}
elseif($customRole === 'parent'){

echo 'Track your child transport bus.';

}
else{

echo 'Live transport tracking.';

}

?>

</p>
<?php if(!empty($transport_message)){ ?>

<div class="alert-banner warning">

<?php echo htmlspecialchars($transport_message); ?>

</div>

<?php } ?>
</div>

</div>
<?php if($customRole === 'admin'){ ?>

<div class="dashboard-section">

<form method="GET">

<div class="form-group">

<label>Select Driver</label>

<select
name="bus_id"
class="form-input"
onchange="this.form.submit()">

<?php if(mysqli_num_rows($drivers) > 0){ ?>

<option value="" disabled>
Select Driver
</option>

<?php } else { ?>

<option value="" disabled selected>
No Drivers Available
</option>

<?php } ?>

<?php
while($driver=mysqli_fetch_assoc($drivers)){
?>

<option
value="<?php echo $driver['bus_id']; ?>"

<?php
echo $selected_bus_id ==
$driver['bus_id']
? 'selected'
: '';
?>>

<?php
echo htmlspecialchars(
$driver['full_name']
);
?>

-

<?php
echo htmlspecialchars(
$driver['bus_number']
);
?>

</option>

<?php } ?>

</select>

</div>

</form>

</div>

<?php } ?>
<?php if($has_transport_access){ ?>
<div class="dashboard-section">

<div class="section-header">

<h2>
Live Map
</h2>

</div>

<div
id="trackingMap"
class="tracking-map">
</div>

</div>
<?php } ?>

<?php if($isAdmin){ ?>

<div class="dashboard-section bus-list">

<div class="section-header">

<h2>
Live Bus Status
</h2>

</div>

<div class="table-responsive">

<table class="custom-table">

<thead>

<tr>

<th>
Bus
</th>

<th>
Driver
</th>

<th>
Phone
</th>

<th>
Route
</th>

<th>
Status
</th>

<th>
Last Update
</th>

<th>
Location
</th>

</tr>

</thead>

<tbody id="busTableBody">

<tr>

<td
colspan="7"
style="text-align:center;">

Loading...

</td>

</tr>

</tbody>

</table>

</div>

</div>
<?php } ?>

</div>

</div>

</div>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"> </script>
<script src="../../assets/js/common.js"></script>

<script>

let map = null;

if(
document.getElementById(
'trackingMap'
)
){

map = L.map('trackingMap')
.setView(
[13.6288,79.4192],
11
);

L.tileLayer(
'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png',
{
maxZoom:19
}
).addTo(map);

}

L.tileLayer(
    'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png',
    {
        maxZoom:19
    }
).addTo(map);

const busIcon = L.divIcon({
    html:'<i class="fa-solid fa-bus" style="font-size:28px;color:#2563eb;"></i>',
    className:'',
    iconSize:[30,30]
});

let markers = [];
let stopMarkers = [];
let routeLines = [];

function clearMapLayers(){

    markers.forEach(marker=>{
        map.removeLayer(marker);
    });

    stopMarkers.forEach(marker=>{
        map.removeLayer(marker);
    });

    routeLines.forEach(line=>{
        map.removeLayer(line);
    });

    markers = [];
    stopMarkers = [];
    routeLines = [];
}

function loadTrackingData(){

    fetch(
        'fetch_live_location.php?bus_id=<?php echo $selected_bus_id; ?>'
    )

    .then(response=>response.json())

    .then(data=>{

        clearMapLayers();

        let running = 0;
        let completed = 0;
        let notStarted = 0;

        let html = '';

        if(document.getElementById('totalBuses')){
            document.getElementById('totalBuses').innerText =
            data.count || 0;
        }

            const buses =
                Array.isArray(data.data)
                ? data.data
                : [data.data];

                buses.forEach(bus => {

            if(bus.status === 'running'){
                running++;
            }
            else if(bus.status === 'completed'){
                completed++;
            }
            else{
                notStarted++;
            }

            /*
            ==================================
            Route Polyline
            ==================================
            */

            if(bus.stops && bus.stops.length > 0){

                let routeCoords = [];

                bus.stops.forEach(stop=>{

                    routeCoords.push([
                        parseFloat(stop.latitude),
                        parseFloat(stop.longitude)
                    ]);

                });

                let routeLine = L.polyline(
                    routeCoords,
                    {
                        color:
                        bus.route.route_color || '#2563eb',
                        weight:6,
                        opacity:0.9
                    }
                ).addTo(map);

                routeLines.push(routeLine);

                map.fitBounds(
                    routeLine.getBounds(),
                    {
                        padding:[40,40]
                    }
                );

                /*
                ==============================
                Stops
                ==============================
                */

                bus.stops.forEach(stop=>{

                    let iconHtml =
                    '<i class="fa-solid fa-location-dot" style="color:#dc2626;font-size:18px;"></i>';

                    if(parseInt(stop.is_start) === 1){

                        iconHtml =
                        '<i class="fa-solid fa-flag-checkered" style="color:#16a34a;font-size:20px;"></i>';

                    }
                    else if(parseInt(stop.is_end) === 1){

                        iconHtml =
                        '<i class="fa-solid fa-flag" style="color:#dc2626;font-size:20px;"></i>';

                    }

                    const stopIcon = L.divIcon({
                        html:iconHtml,
                        className:'',
                        iconSize:[20,20]
                    });

                    let stopMarker = L.marker(
                        [
                            stop.latitude,
                            stop.longitude
                        ],
                        {
                            icon:stopIcon
                        }
                    ).addTo(map);

                    stopMarker.bindTooltip(
                        stop.stop_name,
                        {
                            permanent:true,
                            direction:'top'
                        }
                    );

                    stopMarkers.push(stopMarker);

                });

            }

            /*
            ==================================
            Bus Marker
            ==================================
            */

            if(bus.latitude && bus.longitude){

                let busMarker = L.marker(
                    [
                        bus.latitude,
                        bus.longitude
                    ],
                    {
                        icon:busIcon
                    }
                ).addTo(map);

                busMarker.bindPopup(

                    '<b>'+ (bus.bus_number || '-') +'</b><br>' +

                    (bus.bus_name || '-') +

                    '<br><strong>Driver:</strong> ' +

                    (bus.driver.name || '-') +

                    '<br><strong>Route:</strong> ' +

                    (bus.route.route_name || '-')

                );

                markers.push(busMarker);

            }

            /*
            ==================================
            Table
            ==================================
            */

            html += `

            <tr>

                <td>
                    <strong>${bus.bus_number || '-'}</strong>
                    <br>
                    ${bus.bus_name || '-'}
                </td>

                <td>
                    ${bus.driver.name || '-'}
                </td>

                <td>
                    ${bus.driver.phone || '-'}
                </td>

                <td>
                    ${bus.route.route_name || '-'}
                </td>

                <td>

                    <span class="status-badge ${bus.status}">

                        ${
                            bus.status === 'not_started'
                            ? 'Not Started'
                            :
                            bus.status === 'completed'
                            ? 'Completed'
                            :
                            'Running'
                        }

                    </span>

                </td>

                <td>

                    ${
                        bus.updated_at
                        ?
                        new Date(bus.updated_at)
                        .toLocaleString()
                        :
                        '-'
                    }

                </td>

                <td>

                    ${
                        bus.latitude ?? '-'
                    },

                    ${
                        bus.longitude ?? '-'
                    }

                </td>

            </tr>

            `;

        });

        if(document.getElementById('runningBuses')){
    document.getElementById('runningBuses').innerText =
    running;
}
if(document.getElementById('completedBuses')){
    document.getElementById('completedBuses').innerText =
    completed;
}
       if(document.getElementById('notStartedBuses')){
    document.getElementById('notStartedBuses').innerText =
    notStarted;
}

        document.getElementById(
            'busTableBody'
        ).innerHTML =
        html ||
        '<tr><td colspan="7" style="text-align:center;">No buses found.</td></tr>';

    })

    .catch(error=>{

        console.error(error);

    });

}

<?php if($selected_bus_id > 0){ ?>

loadTrackingData();

setInterval(
loadTrackingData,
15000
);

<?php } else { ?>
const tableBody =
document.getElementById(
'busTableBody'
);

if(tableBody){

tableBody.innerHTML =
html ||
'<tr><td colspan="7" style="text-align:center;">No buses found.</td></tr>';

}
<?php } ?>

</script>

</body>

</html>
