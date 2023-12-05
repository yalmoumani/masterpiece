<?php
session_start();
include 'connection.php';
// causes interruption of access from user to admin areas if file is included
if ($_SESSION['roleId'] == 2) {
    http_response_code(403); 
    echo json_encode(array("error" => "Unauthorized access")); 
    exit;
}
