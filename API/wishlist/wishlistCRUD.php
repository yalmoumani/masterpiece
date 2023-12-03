<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: PUT, GET, POST, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

// Contains the necessary includes and database connection
include '../connection.php';

class Wishlist
{
    public function addProduct()
    {
        global $con;

        $data = json_decode(file_get_contents('php://input'), true);
        $productId = $data['productId'];
        $userId = $data['userId'];

        $sql = "INSERT INTO wishlist (productId, userId) VALUES ('$productId', '$userId')";

        if ($con->query($sql) === TRUE) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'error' => $con->error]);
        }
    }

    public function removeProduct()
    {
        global $con;

        $data = json_decode(file_get_contents('php://input'), true);
        $productId = $data['productId'];
        $userId = $data['userId'];

        $sql = "DELETE FROM wishlist WHERE productId = '$productId' AND userId = '$userId'";

        if ($con->query($sql) === TRUE) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'error' => $con->error]);
        }
    }

    public function getWishlist()
    {
        global $con;

        $data = json_decode(file_get_contents('php://input'), true);
        $userId = $data['userId'];

        $sql = "SELECT * FROM wishlist WHERE userId = '$userId'";
        $result = $con->query($sql);

        if ($result->num_rows > 0) {
            $wishlistItems = [];
            while ($row = $result->fetch_assoc()) {
                $wishlistItems[] = $row['productId'];
            }
            echo json_encode(['success' => true, 'wishlist' => $wishlistItems]);
        } else {
            echo json_encode(['success' => true, 'wishlist' => []]);
        }
    }

    public function emptyWishlist()
    {
        global $con;

        $data = json_decode(file_get_contents('php://input'), true);
        $userId = $data['userId'];

        $sql = "DELETE FROM wishlist WHERE userId = '$userId'";

        if ($con->query($sql) === TRUE) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'error' => $con->error]);
        }
    }
}

// Handle API requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $wishlist = new Wishlist();

    $action = $_POST['action'];

    switch ($action) {
        case 'addProduct':
            $wishlist->addProduct();
            break;
        case 'removeProduct':
            $wishlist->removeProduct();
            break;
        case 'getWishlist':
            $wishlist->getWishlist();
            break;
        case 'emptyWishlist':
            $wishlist->emptyWishlist();
            break;
        default:
            echo json_encode(['success' => false, 'error' => 'Invalid action specified.']);
            break;
    }
} else {
    echo json_encode(['success' => false, 'error' => 'Invalid request method']);
}

$con->close();
?>