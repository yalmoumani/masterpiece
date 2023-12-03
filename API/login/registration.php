<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: PUT, GET, POST, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

include ('../connection.php');
// API: http://localhost/masterpiece/API/login/registration.php
class UserRegistration {
    /*
    for testing:
 "password": ""
  "username": "",
  "email": "",
  "mobile": "",
  "dob": "",
  "userImg": ""


    */
    private $con;

    public function __construct($con) {
        $this->con = $con;
    }

    public function register() {
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            $data = json_decode(file_get_contents('php://input'), true);

            if (isset($data["password"]) && isset($data["username"]) && isset($data["email"]) && isset($data["mobile"]) && isset($data["dob"])) {
                $username = $data["username"];
                $userImg = $data["userImg"];
                $mobile = $data["mobile"];
                $dob = $data["dob"];
                $email = $data["email"];
                $password = $data["password"];
                if (!empty($userImg)) {
                    $imageData = base64_decode($userImg);
                    $filename = uniqid() . '.jpg';
                    $path = __DIR__ . '/../images/' . $filename;
                
                    if (file_put_contents($path, $imageData) === false) {
                        $response = array('error' => "Error: Failed to save the image.");
                        http_response_code(400);
                        echo json_encode($response);
                        return;
                    }
                } else {
                    $path = __DIR__ . '/../images/default.jpg';
                }                
               
                $filename = !empty($filename) ? $filename : 'default.jpg';
                $sql = "INSERT INTO users (username, email, password, userImg, mobile, dob) VALUES ('$username', '$email', '$password', '$filename', '$mobile', '$dob')";
                if ($this->con->query($sql) === true) {
                    $response = array('success' => true);
                    http_response_code(200);
                    echo json_encode($response);
                } else {
                    $response = array('error' => "Error: " . $this->con->error);
                    http_response_code(400);
                    echo json_encode($response);
                }
            } else {
                $response = array('error' => "Invalid JSON data.");
                http_response_code(400);
                echo json_encode($response);
            }
        } else {
            http_response_code(405);
            echo "REQUEST_METHOD is not correct, please use POST.";
        }
    }
}

$userRegistration = new UserRegistration($con);
$userRegistration->register();
$con->close();
?>