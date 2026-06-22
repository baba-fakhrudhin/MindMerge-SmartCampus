/* =====================================================
MindMerge SmartCampus
Route Builder Engine
===================================================== */

document.addEventListener(

'DOMContentLoaded',

function(){

initializeMap();

bindEvents();

}

);

/* =====================================================
Map Initialization
===================================================== */

function initializeMap(){

map = L.map('routeMap').setView(
[13.6288,79.4192],
13
);

L.tileLayer(
'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png',
{
maxZoom:19,
attribution:'© OpenStreetMap Contributors'
}
).addTo(map);

map.on(
'click',
handleMapClick
);

/* EDIT PAGE PRELOAD */

if(

typeof ROUTE_ID !== 'undefined'

&&

routeStops.length > 0

){

routeStops.forEach(

function(stop){

createMarker(stop);

}

);

renderStopsTable();

drawRouteLine();

updateRouteTimes();

}

}

/* =====================================================
Events
===================================================== */

function bindEvents(){


document
.getElementById('markEndBtn')
.addEventListener(
'click',
function(){

pendingStopType = 'end';

alert(
'Next stop added will become END POINT.'
);

}
);

document
.getElementById('clearRouteBtn')
.addEventListener(
'click',
clearRoute
);

const saveBtn =
document.getElementById('saveRouteBtn');

if(saveBtn){

saveBtn.addEventListener(
'click',
saveRoute
);

}

const updateBtn =
document.getElementById('updateRouteBtn');

if(updateBtn){

updateBtn.addEventListener(
'click',
updateRoute
);

}
document
.getElementById('saveStopBtn')
.addEventListener(
'click',
saveModalStop
);

document
.getElementById('cancelStopBtn')
.addEventListener(
'click',
function(){

document
.getElementById(
'stopModal'
)
.style.display='none';

}
);
}

function saveModalStop(){

let stopName =
document
.getElementById(
'modalStopName'
)
.value
.trim();

if(stopName === ''){

alert(
'Stop name required.'
);

return;

}

let arrivalTime =
document
.getElementById(
'modalArrivalTime'
)
.value;

if(arrivalTime === ''){

arrivalTime = '00:00';

}

let stop = {

id: Date.now(),

stop_name: stopName,

arrival_time: arrivalTime,

latitude: pendingLat,

longitude: pendingLng,

is_start:
(routeStops.length === 0)
? 1
: 0,

is_end:
(pendingStopType === 'end')
? 1
: 0

};

if(stop.is_end){

removeExistingEnd();

}

routeStops.push(stop);

createMarker(stop);

renderStopsTable();

drawRouteLine();

pendingStopType = 'normal';

document
.getElementById(
'stopModal'
)
.style.display='none';

updateRouteTimes();

}

/* =====================================================
Add Stop
===================================================== */
let pendingLat = null;
let pendingLng = null;

function handleMapClick(event){

pendingLat =
event.latlng.lat;

pendingLng =
event.latlng.lng;

document
.getElementById(
'modalStopName'
)
.value = '';

document
.getElementById(
'modalArrivalTime'
)
.value = '';

document
.getElementById(
'stopModal'
)
.style.display = 'flex';

}

/* =====================================================
Marker Creation
===================================================== */

function createMarker(stop){

let color = '#2563eb';

let label = 'Stop';

if(stop.is_start){

color = '#16a34a';

label = 'START';

}

if(stop.is_end){

color = '#dc2626';

label = 'END';

}

const marker = L.circleMarker(

[

stop.latitude,

stop.longitude

],

{

radius:8,

fillColor:color,

color:color,

weight:2,

fillOpacity:1

}

).addTo(map);

marker.bindPopup(

'<strong>' +

escapeHtml(stop.stop_name)

+

'</strong><br>'

+

'Arrival: '

+

escapeHtml(stop.arrival_time)

+

'<br>'

+

label

);

marker.stopId = stop.id;

markers.push(marker);

}
/* =====================================================
   Table Rendering
===================================================== */

function renderStopsTable(){

const tbody =
document.getElementById(
'stopsTableBody'
);

const count =
document.getElementById(
'stopCount'
);

count.textContent =
routeStops.length;

tbody.innerHTML = '';

if(routeStops.length === 0){

tbody.innerHTML =

'<tr>' +

'<td colspan="7" style="text-align:center;">' +

'No stops added yet.' +

'</td>' +

'</tr>';

return;

}

routeStops.forEach(

function(stop,index){

let typeBadge =

'<span class="stop-badge normal-stop">Stop</span>';

if(stop.is_start){

typeBadge =

'<span class="stop-badge start-stop">Start</span>';

}

if(stop.is_end){

typeBadge =

'<span class="stop-badge end-stop">End</span>';

}

const lat =

typeof stop.latitude !== 'undefined'

?

Number(stop.latitude).toFixed(6)

:

'-';

const lng =

typeof stop.longitude !== 'undefined'

?

Number(stop.longitude).toFixed(6)

:

'-';

tbody.innerHTML +=

'<tr>' +

'<td>' + (index + 1) + '</td>' +

'<td>' + escapeHtml(stop.stop_name) + '</td>' +

'<td>' + escapeHtml(stop.arrival_time || '-') + '</td>' +

'<td>' + typeBadge + '</td>' +

'<td>' + lat + '</td>' +

'<td>' + lng + '</td>' +

'<td>' +

'<button ' +

'class="btn" ' +

'onclick="removeStop(' + stop.id + ')">' +

'Remove' +

'</button>' +

'</td>' +

'</tr>';

}

);

}
/* =====================================================
Remove Stop
===================================================== */
function removeStop(stopId){

const index =
routeStops.findIndex(
s => s.id == stopId
);

if(index === -1){

return;

}

const removed =
routeStops[index];

routeStops.splice(
index,
1
);

markers.forEach(

function(marker){

if(marker.stopId == stopId){

map.removeLayer(marker);

}

}

);

markers =
markers.filter(
m => m.stopId != stopId
);

/* Reassign Start */

if(routeStops.length > 0){

routeStops.forEach(
s => s.is_start = 0
);

routeStops[0].is_start = 1;

}

/* If END removed */

if(removed.is_end){

alert(
'End point removed. Please select a new END point.'
);

}

renderStopsTable();

drawRouteLine();

updateRouteTimes();

}
function updateRouteTimes(){

    if(routeStops.length === 0){
        return;
    }

    document.getElementById('start_time').value =
    routeStops[0].arrival_time || '';

    const endStop =
    routeStops.find(
        stop => stop.is_end == 1
    );

    if(endStop){

        document.getElementById('end_time').value =
        endStop.arrival_time || '';

    }

}
/* =====================================================
Route Line
===================================================== */

function drawRouteLine(){

if(routeLine){

map.removeLayer(routeLine);

}

if(routeStops.length < 2){

return;

}

const routeColor =
document.getElementById(
'route_color'
).value;

const points =
routeStops.map(

function(stop){

return [

stop.latitude,

stop.longitude

];

}

);

routeLine =

L.polyline(

points,

{

color:routeColor,

weight:5

}

).addTo(map);

}

/* =====================================================
Start / End Validation
===================================================== */

function removeExistingStart(){

routeStops.forEach(

function(stop){

stop.is_start = 0;

}

);

}

function removeExistingEnd(){

routeStops.forEach(

function(stop){

stop.is_end = 0;

}

);

}

/* =====================================================
Clear Route
===================================================== */

function clearRoute(){

if(

!confirm(
'Clear entire route?'
)

){

return;

}

routeStops.length = 0;

markers.forEach(

function(marker){

map.removeLayer(marker);

}

);

markers = [];

if(routeLine){

map.removeLayer(
routeLine
);

routeLine = null;

}

renderStopsTable();

}

/* =====================================================
Update Route
===================================================== */

function updateRoute(){

const busId =
document.getElementById(
'bus_id'
).value;

const routeName =
document.getElementById(
'route_name'
).value.trim();

if(busId === ''){

alert(
'Please select a bus.'
);

return;

}

if(routeName === ''){

alert(
'Please enter route name.'
);

return;

}

if(routeStops.length < 2){

alert(
'Add at least 2 stops.'
);

return;

}

const hasStart =
routeStops.some(
s => s.is_start == 1
);

const hasEnd =
routeStops.some(
s => s.is_end == 1
);

if(routeStops.length > 0){

routeStops[0].is_start = 1;

}

if(!hasEnd){

alert(
'Please select one end point.'
);

return;

}

const payload = {

route_id: ROUTE_ID,

bus_id: busId,

route_name: routeName,

route_description:
document.getElementById(
'route_description'
).value,

route_color:
document.getElementById(
'route_color'
).value,

start_time:
document.getElementById(
'start_time'
).value,

end_time:
document.getElementById(
'end_time'
).value,

status:
document.getElementById(
'status'
).value,

stops: routeStops

};

fetch(

UPDATE_ROUTE_URL,

{

method:'POST',

headers:{

'Content-Type':
'application/json'

},

body:
JSON.stringify(
payload
)

}

)

.then(
response =>
response.json()
)

.then(

function(data){

if(data.success){

alert(
'Route saved successfully.'
);

window.location =
'index.php?success=updated';

}
else{

alert(

data.message
||

'Failed to save route.'

);

}

}

)

.catch(

function(error){

console.error(error);

alert(
'Server error occurred.'
);

}

);

}



/* =====================================================
Save Route
===================================================== */

function saveRoute(){

const busId =
document.getElementById(
'bus_id'
).value;

const routeName =
document.getElementById(
'route_name'
).value.trim();

if(busId === ''){

alert(
'Please select a bus.'
);

return;

}

if(routeName === ''){

alert(
'Please enter route name.'
);

return;

}

if(routeStops.length < 2){

alert(
'Add at least 2 stops.'
);

return;

}

const hasStart =
routeStops.some(
s => s.is_start == 1
);

const hasEnd =
routeStops.some(
s => s.is_end == 1
);

if(routeStops.length > 0){

routeStops[0].is_start = 1;

}

if(!hasEnd){

alert(
'Please select one end point.'
);

return;

}

const payload = {

bus_id:
busId,

route_name:
routeName,

route_description:
document
.getElementById(
'route_description'
)
.value,

route_color:
document
.getElementById(
'route_color'
)
.value,

start_time:
document
.getElementById(
'start_time'
)
.value,

end_time:
document
.getElementById(
'end_time'
)
.value,

status:
document
.getElementById(
'status'
)
.value,

stops:
routeStops

};

fetch(

SAVE_ROUTE_URL,

{

method:'POST',

headers:{

'Content-Type':
'application/json'

},

body:
JSON.stringify(
payload
)

}

)

.then(
response =>
response.json()
)

.then(

function(data){

if(data.success){

alert(
'Route saved successfully.'
);

window.location =
'index.php?success=added';

}
else{

alert(

data.message
||

'Failed to save route.'

);

}

}

)

.catch(

function(error){

console.error(error);

alert(
'Server error occurred.'
);

}

);

}

/* =====================================================
Utility
===================================================== */

function escapeHtml(text){

const div =
document.createElement(
'div'
);

div.textContent =
text;

return div.innerHTML;

}

