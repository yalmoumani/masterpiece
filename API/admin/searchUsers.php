<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: PUT, GET, POST, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");
include '../connection.php';
include '../authorization.php';

class Search
{
     // API testing: http://localhost\masterpiece\API\admin\searchUsers.php
    public function searchUsers($searchTerm)
    {
        /*
        purpose: to search for users by userId, name, mobile, email, or dob
        method: GET
        for testing:
            {
                "query": "{insert search term}"
            }
        */
        global $con;

        if (!empty($searchTerm)) {
            $sql = "SELECT id, username, email, mobile, dob, created_at, updated_at FROM users WHERE 
                    id LIKE '%$searchTerm%' OR
                    username LIKE '%$searchTerm%' OR
                    mobile LIKE '%$searchTerm%' OR
                    email LIKE '%$searchTerm%' OR
                    dob LIKE '%$searchTerm%'";
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
        } else {
            echo json_encode(array("error" => "Please provide a search term."));
        }

        $con->close();
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $query = $_GET['query'] ?? '';
    $operation = new Search();
    $operation->searchUsers($query);
} else {
    echo json_encode(array("error" => "Invalid request method."));
}
?>