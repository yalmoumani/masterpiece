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
            // Update query instead of insert query
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
            // Delete query instead of insert query
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
            // Fetch query instead of insert query
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

// Check the HTTP request method and call the appropriate function
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