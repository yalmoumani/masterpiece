<?php
header("Access-Control-Allow-Origin:*");
header("Access-Control-Allow-Methods: PUT, GET, POST, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");


$con = new mysqli('localhost', 'root', '', 'commerce'); 

if ($con->connect_error) {
  die("Connection failed: " . $con->connect_error);

}

?>