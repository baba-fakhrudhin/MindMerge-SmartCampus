<?php

include('../config/db.php');

$class_id =
isset($_GET['class_id'])
? (int)$_GET['class_id']
: 0;

$options =
'<option value="">All Sections</option>';

if($class_id > 0){

$query = mysqli_query(

$conn,

"SELECT *

FROM sections

WHERE class_id='$class_id'

AND status='active'

ORDER BY section_name"

);

while($row = mysqli_fetch_assoc($query)){

$options .= '

<option value="' .
$row['section_id'] .
'">' .
htmlspecialchars(
$row['section_name']
) .
'</option>';

}

}

echo $options;