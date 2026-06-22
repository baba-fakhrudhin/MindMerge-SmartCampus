<?php

include('../../config/auth.php');
include('../../config/db.php');

if(
!isset($_GET['id'])
||
!is_numeric($_GET['id'])
){

header('Location:index.php');
exit;

}

$staff_id = (int)$_GET['id'];

/*
Get Staff Details
*/

$staffQuery = mysqli_query(

$conn,

"SELECT

staff_id,
staff_type,
user_id

FROM transport_staff

WHERE staff_id='$staff_id'

LIMIT 1"

);

if(mysqli_num_rows($staffQuery) == 0){

header('Location:index.php');
exit;

}

$staff = mysqli_fetch_assoc(
$staffQuery
);

/*
Prevent deleting staff
if assigned to a bus
*/

$check = mysqli_query(

$conn,

"SELECT bus_id

FROM transport_buses

WHERE

driver_id='$staff_id'

OR

helper_id='$staff_id'

LIMIT 1"

);

if(mysqli_num_rows($check) > 0){

header(
'Location:index.php?error=in_use'
);

exit;

}

/*
Delete linked user account
(Drivers only)
*/

if(

!empty($staff['user_id'])

){

mysqli_query(

$conn,

"DELETE FROM users

WHERE id='" . (int)$staff['user_id'] . "'"

);

}

/*
Delete transport staff record
*/

mysqli_query(

$conn,

"DELETE FROM transport_staff

WHERE staff_id='$staff_id'"

);

header(
'Location:index.php?success=deleted'
);

exit;

?>