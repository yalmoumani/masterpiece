<?php
header("Content-Type: application/json");
if ($_SERVER["REQUEST_METHOD"] == "OPTIONS") {
    header("Access-Control-Allow-Origin: *");
    header("Access-Control-Allow-Headers: Content-Type");
    header("Access-Control-Allow-Methods: POST");
    exit;
}


include "../connection.php";

class UserAuthentication {
    private $con;

    public function __construct($db) {
        $this->con = $db;
    }

    public function authenticateUser($json_data) {
        $data = json_decode($json_data, true);

        if ($data && isset($data["username"]) && isset($data["password"])) {
            $username = $data["username"];
            $password = $data["password"];

            $query = "SELECT id, role_id FROM users WHERE (username = ? OR email = ?) AND password = ?";
            $stmt = $this->con->prepare($query);
            $stmt->bind_param('sss', $username, $username, $password); 
            $stmt->execute();
            $stmt->store_result();
            
            // Define variables to store results
            $id = null;
            $role_id = null;

            if ($stmt->num_rows > 0) {
                $stmt->bind_result($id, $role_id);  // Bind result variables
                $stmt->fetch();

                session_start();
                $response = array('STATUS' => true, 'role' => $role_id, 'user_id' => $id);
                $_SESSION['userId'] = $id;
            } else {
                $response = array('STATUS' => false);
            }

            $stmt->close();
        } else {
            $response = array('error' => 'Invalid JSON data');
        }

        return $response;
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $json_data = file_get_contents('php://input');
    $authenticator = new UserAuthentication($con);
    $response = $authenticator->authenticateUser($json_data);
} else {
    $response = array('error' => 'Invalid request method');
}

$con->close();

echo json_encode($response);
?>