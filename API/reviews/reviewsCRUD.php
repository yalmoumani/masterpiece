<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: PUT, GET, POST, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

include '../connection.php';

class Reviews {

    // API Testing: http://localhost\masterpiece\API\cart\cartCRUD.php
    public function createReviews() {
        global $con;
    
        $data = json_decode(file_get_contents('php://input'), true);
    
        session_start();
        if (!isset($_SESSION['id'])) {
            echo json_encode(array("error" => "Please login to continue."));
            return;
        }
    
        $userId = $_SESSION['id'];
        $productId = $data['productId'] ?? '';
        $rating = $data['rating'];
        $comment = $data['comment'];
    
        if (!empty($userId) && !empty($productId) && !empty($rating) && !empty($comment)) {
            $insertQuery = "INSERT INTO reviews (userId, productId, rating, comment, created_at, updated_at) 
                            VALUES ($userId, $productId, $rating, '$comment', NOW(), NOW())";
    
            if ($con->query($insertQuery) === TRUE) {
                echo json_encode(array("message" => "Review created successfully."));
            } else {
                echo json_encode(array("error" => "Error: " . $con->error));
            }
        } else {
            echo json_encode(array("error" => "Please provide the required information."));
        }
    
        $con->close();
    }
  
}
?>