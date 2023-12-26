<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: PUT, GET, POST, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

include '../connection.php';

class Reviews {

    // API Testing: http://localhost\masterpiece\API\reviews\reviewsCRUD.php
    public function createReviews() {
        /*
        purpose: Create a review
        method: POST
        for testing:
        {
            "userId":,
            "productId": ,
            "rating": ,
            "comment": ""
        }
        */
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
    
        if (!is_numeric($rating) || floor($rating) != $rating || $rating < 1 || $rating > 5) {
            echo json_encode(array("error" => "Rating must be a whole number between 1 and 5."));
            return;
        }
    
        if (!empty($userId) && !empty($productId) && !empty($rating) && !empty($comment)) {
            $insertQuery = "INSERT INTO reviews (userId, productId, rating, comment, created_at, updated_at) 
                            VALUES ('$userId', '$productId', '$rating', '$comment', NOW(), NOW())";
    
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
    public function editReview() {
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

            $updateQuery = "UPDATE reviews SET rating = '$rating', comment = '$comment', updated_at = NOW() WHERE userId = '$userId' AND productId = '$productId'";

            if ($con->query($updateQuery) === TRUE) {
                echo json_encode(array("message" => "Review updated successfully."));
            } else {
                echo json_encode(array("error" => "Error: " . $con->error));
            }
        } else {
            echo json_encode(array("error" => "Please provide the required information."));
        }

        $con->close();
    }

    public function deleteReview() {
        global $con;

        $data = json_decode(file_get_contents('php://input'), true);

        session_start();
        if (!isset($_SESSION['id'])) {
            echo json_encode(array("error" => "Please login to continue."));
            return;
        }

        $userId = $_SESSION['id'];
        $productId = $data['productId'] ?? '';

        if (!empty($userId) && !empty($productId)) {
            $deleteQuery = "DELETE FROM reviews WHERE userId = '$userId' AND productId = '$productId'";

            if ($con->query($deleteQuery) === TRUE) {
                echo json_encode(array("message" => "Review deleted successfully."));
            } else {
                echo json_encode(array("error" => "Error: " . $con->error));
            }
        } else {
            echo json_encode(array("error" => "Please provide the required information."));
        }

        $con->close();
    }

    public function getReview() {
        global $con;

        $data = json_decode(file_get_contents('php://input'), true);

        session_start();
        if (!isset($_SESSION['id'])) {
            echo json_encode(array("error" => "Please login to continue."));
            return;
        }

        $userId = $_SESSION['id'];
        $productId = $data['productId'] ?? '';

        if (!empty($userId) && !empty($productId)) {
            $selectQuery = "SELECT * FROM reviews WHERE userId = '$userId' AND productId = '$productId'";

            $result = $con->query($selectQuery);
            if ($result) {
                $reviews = array();
                while ($row = $result->fetch_assoc()) {
                    $reviews[] = $row;
                }
                echo json_encode($reviews);
            } else {
                echo json_encode(array("error"=> "Error: " . $con->error));
            }
        } else {
            echo json_encode(array("error" => "Please provide the required information."));
        }

        $con->close();
    }
}

$reviews = new Reviews();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $reviews->createReviews();
} elseif ($_SERVER['REQUEST_METHOD'] === 'PUT') {
    $reviews->editReview();
} elseif ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    $reviews->deleteReview();
} elseif ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $reviews->getReview();
} else {
    echo json_encode(array("error" => "Invalid request method."));
}
?>