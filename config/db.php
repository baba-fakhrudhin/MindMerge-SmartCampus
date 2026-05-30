<?php

$host = "localhost";
$user = "root";
$password = "";
$database = "mindmerge";

$conn = mysqli_connect(
$host,
$user,
$password,
$database
);

if(!$conn){

die("Database Connection Failed");

}

?>