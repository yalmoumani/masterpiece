<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: PUT, GET, POST, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

include '../connection.php';
// API Testing: http://localhost\masterpiece/API/wishlist/wishlistCRUD.php

class Wishlist
{
    /*
    Add Product to Wishlist:
        Purpose: Add a product to the user's wishlist.
        Method: POST
        Testing:
        {
            "action": "addProduct",
            "productId": {insert id#}
        }
    */
    public function addProduct()
    {
        global $con;
        session_start();
    
        $data = json_decode(file_get_contents('php://input'), true);
        $productId = $data['productId'];
        $userId = $_SESSION['id'];
    
        $existingProductQuery = "SELECT * FROM wishlist WHERE productId = '$productId' AND userId = '$userId'";
        $existingProductResult = $con->query($existingProductQuery);
    
        if ($existingProductResult->num_rows > 0) {
            echo json_encode(['success' => false, 'error' => 'Sorry, the product is already in your wishlist.']);
        } else {
            $sql = "INSERT INTO wishlist (productId, userId) VALUES ('$productId', '$userId')";
    
            if ($con->query($sql) === TRUE) {
                echo json_encode(['success' => true]);
            } else {
                echo json_encode(['success' => false, 'error' => $con->error]);
            }
        }
    }

    /*
    Remove Product from Wishlist:
        Purpose: Remove a product from the user's wishlist.
        Method: POST
        Testing:
        {
            "action": "removeProduct",
            "productId": {insert id#}
        }
    */
    public function removeProduct()
    {
        global $con;
        session_start();

        $data = json_decode(file_get_contents('php://input'), true);
        $productId = $data['productId'];
        $userId = $_SESSION['id'];

        $sql = "DELETE FROM wishlist WHERE productId = '$productId' AND userId = '$userId'";

        if ($con->query($sql) === TRUE) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'error' => $con->error]);
        }
    }

    /*
    Get Wishlist:
        Purpose: Retrieve the user's wishlist.
        Method: POST
        Testing:
        {
            "action": "getWishlist"
        }
    */
    public function getWishlist()
    {
        global $con;
        session_start();
        $userId = $_SESSION['id'];
    
        $sql = "
            SELECT w.productId, p.name, p.price, p.image
            FROM wishlist AS w
            INNER JOIN products AS p ON w.productId = p.id
            WHERE w.userId = $userId
        ";
        $result = $con->query($sql);
    
        if ($result->num_rows > 0) {
            $wishlistItems = [];
            while ($row = $result->fetch_assoc()) {
                $wishlistItems[] = [
                    'productId' => $row['productId'],
                    'name' => $row['name'],
                    'price' => $row['price'],
                    'image' => $row['image']
                ];
            }
            echo json_encode(['wishlist' => $wishlistItems]);
        } else {
            echo json_encode(['error' => 'Sorry, the wishlist is empty.']);
        }
    }

    /*
    Empty Wishlist:
        Purpose: Remove all products from the user's wishlist.
        Method: POST
        Testing:
        {
            "action": "emptyWishlist"
        }
    */
    public function emptyWishlist()
    {
        global $con;
        session_start();
        $userId = $_SESSION['id'];

        $sql = "DELETE FROM wishlist WHERE userId = '$userId'";

        if ($con->query($sql) === TRUE) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'error' => $con->error]);
        }
    }
}


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $json_data = file_get_contents('php://input');
    $data = json_decode($json_data, true);

    $action = $data['action'] ?? '';

    $wishlist = new Wishlist();

    if ($action === 'addProduct') {
        $wishlist->addProduct();
    } elseif ($action === 'removeProduct') {
        $wishlist->removeProduct();
    } elseif ($action === 'getWishlist') {
        $wishlist->getWishlist();
    } elseif ($action === 'emptyWishlist') {
        $wishlist->emptyWishlist();
    } else {
        echo json_encode(['success' => false, 'error' => 'Invalid action.']);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'Invalid request method. Please use the POST method.']);
}

$con->close();
?>