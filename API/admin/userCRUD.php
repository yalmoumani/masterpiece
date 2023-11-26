<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: PUT, GET, POST, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");
// Contains the admins functions to create, edit, delete, get user by id, get all users
include '../connection.php';

class UserOperations {

    // API Testing: http://localhost\masterpiece\API\admin\userCRUD.php
    public function create() {
        /*
        for testing:
            {
    "action": "create",
    "userImg": "{insert text}",
    "username": "{insert text}",
    "email": "{insert text}",
    "role": "{insert text}",
    "mobile": "{insert text}",
    "dob": "{insert text}"
}
            }
        */
        global $con;

        $data = json_decode(file_get_contents('php://input'), true);

        if (!empty($data)) {
            $requiredFields = ['userImg', 'email', 'username', 'mobile', 'dob'];
            $allFieldsPresent = true;

            foreach ($requiredFields as $field) {
                if (!isset($data[$field]) || empty($data[$field])) {
                    $allFieldsPresent = false;
                    break;
                }
            }

            if ($allFieldsPresent) {
                $image = $data['userImg'];
                $username = $data['username'];
                $email = $data['email'];
                $mobile = $data['mobile'];
                $role = $data['role'];
                $dob = $data['dob'];
                $sql = "INSERT INTO users (userImg, username, password, roleId, mobile, email, created_at, updated_at, dob) VALUES (
                    '$image', 
                    '$username',
                    'venus@123',
                    '$role',
                    '$mobile',
                    '$email',
                    NOW(),
                    NOW(),
                    '$dob'
                )";

                if ($con->query($sql) === TRUE) {
                    echo json_encode(array("message" => "User record created successfully."));
                } else {
                    echo json_encode(array("error" => "Error: " . $con->error));
                }
            } else {
                echo json_encode(array("error" => "Please provide all required fields."));
            }
        } else {
            echo json_encode(array("error" => "No data received."));
        }

        $con->close();
    }

    public function delete() {
              /*
        for testing:
            {
    "action": "delete",
    "id": {insert id#},
}
            }
        */
        global $con;

        if ($_SERVER['REQUEST_METHOD'] === "POST") {
            $data = json_decode(file_get_contents('php://input'), true);
            $id = $data['id'];

            if (!empty($id)) {
                $sql = "DELETE FROM users WHERE id = $id";

                if ($con->query($sql) === TRUE) {
                    echo json_encode(array("message" => "User record deleted successfully."));
                } else {
                    echo json_encode(array("error" => "Error: " . $con->error));
                }
            } else {
                echo json_encode(array("message" => "No ID provided for deletion."));
            }
        } else {
            echo json_encode(array("error" => "Invalid request method. Please use POST method."));
        }

        $con->close();
    }

    public function edit() {
        
        global $con;
          /*
        for testing:
            {
    "action": "edit",
    "id": {insert id#},
    "userImg": "{insert text}",
    "username": "{insert text}",
    "email": "{insert text}",
    "role": "{insert text}",
    "mobile": "{insert text}",
    "dob": "{insert text}"
}
            }
        */

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $json_data = file_get_contents('php://input');
            $data = json_decode($json_data, true);

            $userId = $data['id'];

            $image = $data['image'] ?? '';
            $username = $data['username'] ?? '';
            $email = $data['email'] ?? '';
            $dob = $data['dob'] ?? '';
            $role = $data['role'] ?? '';
            $mobile = $data['mobile'] ?? '';
          

            $update_profile_query = "UPDATE users SET ";
            $setClauses = [];

            if (!empty($username)) {
                $setClauses[] = "username = '$username'";
            }
            if (!empty($image)) {
                $setClauses[] = "userImg = '$image'";
            }

            if (!empty($email)) {
                $setClauses[] = "email = '$email'";
            }

            if (!empty($mobile)) {
                $setClauses[] = "mobile = '$mobile'";
            }
            if (!empty($dob)) {
                $setClauses[] = "dob = '$dob'";
            }
            if (!empty($role)) {
                $setClauses[] = "roleId = '$role'";
            }
        
            $setClauses[] = "updated_at = NOW()";

            $update_profile_query .= implode(", ", $setClauses);
            $update_profile_query .= " WHERE id = $userId";

            mysqli_query($con, $update_profile_query);

            $response = array(
                'success' => 'Profile updated successfully.'
            );
            echo json_encode($response);
        } else {
            $response = array(
                'error' => 'Please use the POST method.'
            );
            echo json_encode($response);
        }

        mysqli_close($con);
    }

    public function getUser($id) {
           /*
        for testing:
            {
    "action": "getUser",
    "id": {insert id#},
}
            }
        */
        global $con;
    
        if (!empty($id)) {
            $sql = "SELECT id, username, email, mobile, dob, userImg, created_at, updated_at FROM users WHERE id = $id";
            $result = $con->query($sql);
    
            if ($result->num_rows > 0) {
                $user = $result->fetch_assoc();
                echo json_encode($user);
            } else {
                echo json_encode(array("error" => "User not found."));
            }
        } else {
            echo json_encode(array("error" => "No user ID provided."));
        }
    
        $con->close();
    }
        public function getAll() {
               /*
        for testing:
            {
    "action": "getAll",
}
            }
        */
            global $con;
    
            $sql = "SELECT id, username, email, mobile, dob, userImg, created_at, updated_at FROM users";
            $result = $con->query($sql);
    
            if ($result->num_rows > 0) {
                $users = array();
                while ($row = $result->fetch_assoc()) {
                    $users[] = $row;
                }
                echo json_encode($users);
            } else {
                echo json_encode(array("error" => "No users found."));
            }
    
            $con->close();
        }
    
    }
    
    $userOps = new UserOperations();
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $data = json_decode(file_get_contents('php://input'), true);
    
        if (isset($data['action'])) {
            $action = $data['action'];
    
            switch ($action) {
                case 'create':
                    $userOps->create();
                    break;
                case 'delete':
                    $userOps->delete();
                    break;
                case 'edit':
                    $userOps->edit();
                    break;
                case 'getUser':
                    if (isset($data['id'])) {
                        $userOps->getUser($data['id']);
                    } else {
                        echo json_encode(array("error" => "No user ID provided."));
                    }
                    break;
                case 'getAll': 
                    $userOps->getAll();
                    break;
                default:
                    echo json_encode(array("error" => "Invalid action."));
                    break;
            }
        } else {
            echo json_encode(array("error" => "No action provided."));
        }
    } else {
        echo json_encode(array("error" => "Invalid request method. Please use POST method."));
    }
?>