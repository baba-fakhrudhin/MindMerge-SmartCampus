<?php

include('../config/auth.php');
include('../config/db.php');

header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename=fee_report.csv');

$output = fopen('php://output', 'w');

fputcsv(

$output,

[
'Student Name',
'Student ID',
'Class',
'Section',
'Fee Name',
'Amount',
'Paid Amount',
'Balance Amount',
'Status',
'Due Date'
]

);

$query = mysqli_query(

$conn,

"SELECT

u.full_name,

st.student_id,

c.class_name,

sec.section_name,

fs.fee_name,

sf.amount,

sf.paid_amount,

sf.balance_amount,

sf.payment_status,

fs.due_date

FROM student_fees sf

INNER JOIN students st
ON sf.student_id=st.id

INNER JOIN users u
ON st.user_id=u.id

INNER JOIN fee_structures fs
ON sf.fee_structure_id=fs.fee_structure_id

LEFT JOIN classes c
ON st.class_id=c.class_id

LEFT JOIN sections sec
ON st.section_id=sec.section_id

ORDER BY

c.class_name,
sec.section_name,
u.full_name"

);

while($row = mysqli_fetch_assoc($query)){

fputcsv(

$output,

[

$row['full_name'],
$row['student_id'],
$row['class_name'],
$row['section_name'],
$row['fee_name'],
$row['amount'],
$row['paid_amount'],
$row['balance_amount'],
ucfirst($row['payment_status']),
$row['due_date']

]

);

}

fclose($output);

exit;

?>