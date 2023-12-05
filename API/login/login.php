
<?php 
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Methods: POST");

include "../connection.php";
http://localhost\masterpiece\API\login\login.php
class Login {
    private $con;

    public function __construct($db) {
        $this->con = $db;
    }

    public function authenticateUser($json_data) {
        $data = json_decode($json_data, true);

        if ($data && isset($data["email"]) && isset($data["password"])) {
            $email = $data["email"];
            $password = $data["password"];

            $query = "SELECT id, roleId FROM users WHERE email = ? AND password = ?";
            $stmt = $this->con->prepare($query);
            $stmt->bind_param('ss', $email, $password);
            $stmt->execute();
            $stmt->store_result();

            $id = null;
            $roleId = null;

            if ($stmt->num_rows > 0) {
                $stmt->bind_result($id, $roleId);
                $stmt->fetch();

                session_start();
                $response = array('verified' => true, 'roleId' => $roleId, 'id' => $id);
                $_SESSION['id'] = $id;
                $_SESSION['roleId']= $roleId;
            } else {
                $response = array('verified' => false);
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
    $authenticator = new Login($con);
    $response = $authenticator->authenticateUser($json_data);
} else {
    $response = array('error' => 'Invalid request method');
}

$con->close();

echo json_encode($response);
?>