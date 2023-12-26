
    <?php
    header("Access-Control-Allow-Origin: *");
    header("Access-Control-Allow-Methods: PUT, GET, POST, DELETE, OPTIONS");
    header("Access-Control-Allow-Headers: Content-Type");
    header("Content-Type: application/json");
    
    include ('../connection.php');
    // API: http://localhost/masterpiece/API/login/registration.php

     /*
        A registration page that fills the needed values and encrypts the password by hash and if the user leaves the image empty it  will return the default image, and contains validation.
        for testing:
            {
        "password": "",
        "username": "",
        "email": "",
        "mobile": "",
        "dob": "",
        "userImg": ""
        }
        */
    class UserRegistration {
       
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
                    $usernameCount = null;
                    $emailCount = null;
    
                    if (strlen($mobile) !== 10 || !preg_match('/^0[0-9]{9}$/', $mobile)) {
                        $response = array('error' => "Invalid phone number. The phone number should be 10 digits long and start with '0'.");
                        http_response_code(400);
                        echo json_encode($response);
                        return;
                    }
    
                    $query = "SELECT COUNT(*) FROM users WHERE username = ?";
                    $stmt = $this->con->prepare($query);
                    $stmt->bind_param('s', $username);
                    $stmt->execute();
                    $stmt->bind_result($usernameCount);
                    $stmt->fetch();
                    $stmt->close();
    
                    if ($usernameCount > 0) {
                        $response = array('error' => "Username already exists. Please choose a different username.");
                        http_response_code(400);
                        echo json_encode($response);
                        return;
                    }
    
                    $query = "SELECT COUNT(*) FROM users WHERE email = ?";
                    $stmt = $this->con->prepare($query);
                    $stmt->bind_param('s', $email);
                    $stmt->execute();
                    $stmt->bind_result($emailCount);
                    $stmt->fetch();
                    $stmt->close();
    
                    if ($emailCount > 0) {
                        $response = array('error' => "Email already exists. Please use a different email address.");
                        http_response_code(400);
                        echo json_encode($response);
                        return;
                    }
                    if (strlen($password) < 6) {
                        $response = array('error' => "Invalid password. The password should be at least 6 characters long.");
                        http_response_code(400);
                        echo json_encode($response);
                        return;
                    }
                    
                    if (!preg_match('/^(?=.*[A-Z])(?=.*[a-z])(?=.*\d)(?=.*[^A-Za-z0-9]).{6,}$/', $password)) {
                        $response = array('error' => "Invalid password. The password should be at least 6 characters long, contain at least one capital letter, one lowercase letter, one digit, and one special character.");
                        http_response_code(400);
                        echo json_encode($response);
                        return;
                    }
                    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

                    if (!preg_match('/^\d{4}\/\d{2}\/\d{2}$/', $dob)) {
                        $response = array('error' => "Invalid date of birth format. Please use the format 'yyyy/mm/dd'.");
                        http_response_code(400);
                        echo json_encode($response);
                        return;
                    }
                    
                    $dobTimestamp = strtotime($dob);
                    $currentTimestamp = time();
                    
                    if ($dobTimestamp === false || $dobTimestamp > $currentTimestamp) {
                        $response = array('error' => "Invalid date of birth. Please enter a valid date.");
                        http_response_code(400);
                        echo json_encode($response);
                        return;
                    }
                    
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
                    $sql = "INSERT INTO users (username, email, password, userImg, mobile, dob) VALUES (?, ?, ?, ?, ?, ?)";
                    $stmt = $this->con->prepare($sql);
                    $stmt->bind_param('ssssss', $username, $email, $hashedPassword, $filename, $mobile, $dob);
                    if ($stmt->execute()) {
                        $response = array('success' => true);
                        http_response_code(200);
                        echo json_encode($response);
                    } else {
                        $response = array('error' => "Error: " . $stmt->error);
                        http_response_code(400);
                        echo json_encode($response);
                    }
                    $stmt->close();
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