<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: PUT, GET, POST, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

include ('../connection.php');
http://localhost\masterpiece\API\login\logout.php
class Logout {
    public function logoutUser() {
        session_start();


        if (isset($_SESSION['id'])) {
            $_SESSION = array();
            session_destroy();

            $response = array('success' => true, 'message' => 'Logged out successfully');
        } else {
            $response = array('success' => false, 'message' => 'User is not logged in');
        }

        return $response;
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $logout = new Logout();
    $response = $logout->logoutUser();
} else {
    $response = array('success' => false, 'message' => 'Invalid request method');
}

echo json_encode($response);
?>