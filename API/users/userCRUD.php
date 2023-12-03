<?php



header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: PUT, GET, POST, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

include('../connection.php');

class userSettings {
    private $con;

    public function __construct($dbConnection) {
        $this->con = $dbConnection;
    }

    public function profile() {
        $requestData = json_decode(file_get_contents('php://input'), true);
        $id = $requestData['id'];
        
        // Logic to retrieve user profile based on the provided ID
        $query = "SELECT * FROM users WHERE id = :id";
        $stmt = $this->con->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        $userProfile = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($userProfile) {
            echo json_encode($userProfile);
        } else {
            echo json_encode(array('error' => 'User profile not found'));
        }
    }

    public function editProfile() {
        $requestData = json_decode(file_get_contents('php://input'), true);
        $id = $requestData['id'];
        $name = $requestData['name'];
        $password = $requestData['password'];
        $email = $requestData['email'];
        $mobile = $requestData['mobile'];
        $userImg = $requestData['userImg'];
        
        // Logic to edit user profile based on the provided data
        $query = "UPDATE users SET name = :name, password = :password, email = :email, mobile = :mobile, userImg = :userImg WHERE id = :id";
        $stmt = $this->con->prepare($query);
        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':password', $password);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':mobile', $mobile);
        $stmt->bindParam(':userImg', $userImg);
        $stmt->bindParam(':id', $id);
        $success = $stmt->execute();
        
        if ($success) {
            echo json_encode(array('message' => 'Profile edited successfully'));
        } else {
            echo json_encode(array('error' => 'Failed to edit profile'));
        }
    }

    public function viewCurrentOrders($userId) {
        $sql = "SELECT * FROM orders WHERE user_id = $userId AND (status = 'processing' OR status = 'shipped')";
        $result = $this->con->query($sql);

        if ($result->num_rows > 0) {
            $orders = array();
            while ($row = $result->fetch_assoc()) {
                $orders[] = $row;
            }
            echo json_encode($orders);
        } else {
            echo json_encode(array("message" => "No current orders found for the user."));
        }
    }

    public function cancelOrder() {
        $data = json_decode(file_get_contents('php://input'), true);
        $id = $data['id'] ?? '';

        if (!empty($id)) {
            $sql = "SELECT status FROM orders WHERE id = $id";
            $result = $this->con->query($sql);

            if ($result->num_rows > 0) {
                $row = $result->fetch_assoc();
                $status = $row['status'];

                if ($status === 'processing') {
                    $deleteSql = "DELETE FROM orders WHERE id = $id";

                    if ($this->con->query($deleteSql) === TRUE) {
                        echo json_encode(array("message" => "Order canceled successfully."));
                    } else {
                        echo json_encode(array("error" => "Error: " . $this->con->error));
                    }
                } else {
                    echo json_encode(array("error" => "Unable to cancel order. Order status is not 'processing'."));
                }
            } else {
                echo json_encode(array("error" => "Order not found."));
            }
        } else {
            echo json_encode(array("error" => "No order ID provided for cancellation."));
        }
    }

    public function pastOrders($userId) {
        $sql = "SELECT * FROM orders WHERE user_id = $userId AND status = 'closed'";
        $result = $this->con->query($sql);

        if ($result->num_rows > 0) {
            $orders = array();
            while ($row = $result->fetch_assoc()) {
                $orders[] = $row;
            }
            echo json_encode($orders);
        } else {
            echo json_encode(array("message" => "No past orders found for the user."));
        }
    }
}

$requestMethod = $_SERVER['REQUEST_METHOD'];

if ($requestMethod === 'POST') {
    $requestData = json_decode(file_get_contents('php://input'), true);
    $action = $requestData['action'];

 
    $userSettings = new userSettings($con);

    if ($action === 'cancelOrder') {
        $userSettings->cancelOrder();
    } elseif ($action === 'viewCurrentOrders') {
        $userId = $requestData['user_id'] ?? '';
        $userSettings->viewCurrentOrders($userId);
    } elseif ($action === 'pastOrders') {
        $userId = $requestData['user_id'] ?? '';
        $userSettings->pastOrders($userId);
    } elseif ($action === 'profile') {
        $userSettings->profile();
    } elseif ($action === 'editProfile') {
        $userSettings->editProfile();
    } else {
        echo json_encode(array("error" => "Invalid action."));
    }
}
?>