<?php

include('../config/auth.php');
include('../config/db.php');

header("Content-Type: application/vnd.ms-excel");
header("Content-Disposition: attachment; filename=fee_report.xls");

echo "

<table border='1'>

<tr>

<th>Student Name</th>

<th>Student ID</th>

<th>Class</th>

<th>Section</th>

<th>Fee Name</th>

<th>Amount</th>

<th>Paid Amount</th>

<th>Balance Amount</th>

<th>Status</th>

<th>Due Date</th>

</tr>

";

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

while($row=mysqli_fetch_assoc($query)){

echo "

<tr>

<td>{$row['full_name']}</td>

<td>{$row['student_id']}</td>

<td>{$row['class_name']}</td>

<td>{$row['section_name']}</td>

<td>{$row['fee_name']}</td>

<td>{$row['amount']}</td>

<td>{$row['paid_amount']}</td>

<td>{$row['balance_amount']}</td>

<td>{$row['payment_status']}</td>

<td>{$row['due_date']}</td>

</tr>

";

}

echo "</table>";

exit;

?>