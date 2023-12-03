<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: PUT, GET, POST, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

class Database {
    private $servername = "localhost";
    private $username = "root";
    private $password = "";
    private $dbname = "project-4";
    private $conn;

    public function __construct() {
        $this->connect();
    }

    private function connect() {
        $this->conn = new mysqli($this->servername, $this->username, $this->password, $this->dbname);
        if ($this->conn->connect_error) {
            die("Connection failed: " . $this->conn->connect_error);
        }
    }

    public function insertUser($username, $email, $password) {
        $insert_query = "INSERT INTO users (username, email, password) VALUES (?, ?, ?)";
        $stmt = $this->conn->prepare($insert_query);
        // $stmt->bind_param($username, $email, $password);
        $stmt->execute([$username, $email, $password]);
        if ($stmt) {
            return true;
        } else {
            return $stmt->error;
        }
    }

    public function close() {
        $this->conn->close();
    }
}

class UserRegistration {
    private $db;

    public function __construct(Database $db) {
        $this->db = $db;
    }
   
    public function register() {
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            $data = json_decode(file_get_contents('php://input'), true);

            if ($data && isset($data["username"]) && isset($data["email"]) && isset($data["password"])) {
                $username = $data["username"];
                $email = $data["email"];
                $password = $data["password"];

                $result = $this->db->insertUser($username, $email, $password);

                if ($result === true) {
                    $response = array('success' => true);
                    echo json_encode($response);
                } else {
                    $response = array('error' => "Error: " . $result);
                    echo json_encode($response);
                }
            } else {
                $response = array('error' => "Invalid JSON data.");
                echo json_encode($response);
            }
        }else{
          
            echo "REQUEST_METHOD is not correct plece use post";
        }
    }

    
}

$db = new Database();
$userRegistration = new UserRegistration($db);
$userRegistration->register();
$db->close();
?>
