<?php

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "webs";

$domain = "https://localhost:8080/";

$conn = new mysqli($servername, $username, $password, $dbname);

if(!$conn){
    echo 'FAILED TO CONNECT WITH DATABASE ' . mysqli_connect_error(); 
}

?>