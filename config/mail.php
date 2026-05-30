<?php

function sendMail($to,$subject,$message){

$apiKey =
"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";

$data = [

"sender" => [

"name" => "MindMerge SmartCampus",

"email" => "ibabaprakurddin@gmail.com"

],

"to" => [[

"email" => $to

]],

"subject" => $subject,

"htmlContent" => $message

];

$ch = curl_init();

curl_setopt(
$ch,
CURLOPT_URL,
"https://api.brevo.com/v3/smtp/email"
);

curl_setopt(
$ch,
CURLOPT_RETURNTRANSFER,
true
);

curl_setopt(
$ch,
CURLOPT_POST,
true
);

curl_setopt(
$ch,
CURLOPT_POSTFIELDS,
json_encode($data)
);

curl_setopt(
$ch,
CURLOPT_HTTPHEADER,
[

"accept: application/json",

"api-key: $apiKey",

"content-type: application/json"

]

);

/* IMPORTANT FOR XAMPP SSL ISSUES */

curl_setopt(
$ch,
CURLOPT_SSL_VERIFYPEER,
false
);

curl_setopt(
$ch,
CURLOPT_SSL_VERIFYHOST,
false
);

$response = curl_exec($ch);

$error = curl_error($ch);

$httpCode =
curl_getinfo($ch,CURLINFO_HTTP_CODE);

curl_close($ch);

/* DEBUG */

if($error){

return false;

}

if($httpCode == 201){

return true;

}

return false;

}

?>