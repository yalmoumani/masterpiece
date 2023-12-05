<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: PUT, GET, POST, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

include '../connection.php';

class CartOperations {

    // API Testing: http://localhost\masterpiece\API\cart\cartCRUD.php
    public function addItem() {
        /*
        for testing:
            purpose: to allow a user to add items to cart
            method: POST
        {
            "action": "addItem",
            "productId": {insert id#},
            "quantity": {insert #}
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
        $quantity = $data['quantity'] ?? 1;

        if (!empty($userId) && !empty($productId)) {
            $checkQuery = "SELECT * FROM cart WHERE userId = $userId AND productId = $productId";
            $checkResult = $con->query($checkQuery);

            if ($checkResult->num_rows > 0) {
                $updateQuery = "UPDATE cart SET quantity = quantity + $quantity WHERE userId = $userId AND productId = $productId";

                if ($con->query($updateQuery) === TRUE) {
                    echo json_encode(array("message" => "Item quantity updated in the cart."));
                } else {
                    echo json_encode(array("error" => "Error: " . $con->error));
                }
            } else {
                $insertQuery = "INSERT INTO cart (userId, productId, quantity) VALUES ($userId, $productId, $quantity)";

                if ($con->query($insertQuery) === TRUE) {
                    echo json_encode(array("message" => "Item added to the cart."));
                } else {
                    echo json_encode(array("error" => "Error: " . $con->error));
                }
            }
        } else {
            echo json_encode(array("error" => "Please provide the product ID."));
        }

        $con->close();
    }

    public function updateItem() {
        /*
        purpose: to update an item from the cart
        method: POST
        for testing:
        {
            "action": "updateItem",
            "productId": {insert id#},
            "quantity":{insert id#}
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
        $quantity = $data['quantity'] ?? 1;

        if (!empty($userId) && !empty($productId)) {
            $updateQuery = "UPDATE cart SET quantity = $quantity WHERE userId = $userId AND productId = $productId";

            if ($con->query($updateQuery) === TRUE) {
                echo json_encode(array("message" => "Item quantity updated in the cart."));
            } else {
                echo json_encode(array("error" => "Error: " . $con->error));
            }
        } else {
            echo json_encode(array("error" => "Please provide the product ID."));
        }

        $con->close();
    }

    public function removeItem() {
        /*
        purpose: remove item from cart
        method: POST
        for testing:
        {
            "action": "removeItem",
            "productId": {insert id#}
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

        if (!empty($userId) && !empty($productId)) {
            $deleteQuery = "DELETE FROM cart WHERE userId = $userId AND productId = $productId";

            if ($con->query($deleteQuery) === TRUE) {
                echo json_encode(array("message" => "Item removed from the cart."));
            } else {
                echo json_encode(array("error" => "Error: " . $con->error));
            }
        } else {
            echo json_encode(array("error" => "Please provide the product ID."));
        }

        $con->close();
    }

    public function getCart() {
        /*
        purpose: to retrieve a persons cart
        method: POST
        for testing:
        {

    public function getCart($userId) {
        /*
        for testing:
        {
            "action": "getCart"
        }
        */
        global $con;
        session_start();
        if (!isset($_SESSION['id'])) {
            echo json_encode(array("error" => "Please login to continue."));
            return;
        }

        $userId = $_SESSION['id'];

        if (!empty($userId)) {
    
            $sql = "SELECT cart.id, products.name, cart.quantity, products.price 
                    FROM cart 
                    INNER JOIN products ON cart.productId = products.id 
                    WHERE cart.userId = $userId";

            $result = $con->query($sql);

            if ($result->num_rows > 0) {
                $cartItems = array();
                while ($row = $result->fetch_assoc()) {
                    $cartItems[] = $row;
                }
                echo json_encode($cartItems);
            } else {
                echo json_encode(array("error" => "No items found in the cart."));
            }
        } else {
            echo json_encode(array("error" => "No user ID provided."));
        }

        $con->close();
    }


    public function clearCart() {
        /*
        for testing:
            purpose: to clear all items from cart
            method: POST
        {
            "action": "clearCart"
        }
        */
        global $con;
        session_start();
        if (!isset($_SESSION['id'])) {
            echo json_encode(array("error" => "Please login to continue."));
            return;
        }

        $userId = $_SESSION['id'];

        if (!empty($userId)) {
            $deleteQuery = "DELETE FROM cart WHERE userId = $userId";

            if ($con->query($deleteQuery) === TRUE) {
                echo json_encode(array("message" => "Cart cleared successfully."));
            } else {
                echo json_encode(array("error" => "Error: " . $con->error));
            }
        } else {
            echo json_encode(array("error" => "No user ID provided."));
        }

        $con->close();
    }

    public function checkout() {
        /*
        purpose: to move items out of cart into orders
        action: post
        for testing:
        {
            "action": "checkout"
        }
        */
        global $con;
        session_start();
        if (!isset($_SESSION['id'])) {
            echo json_encode(array("error" => "Please login to continue."));
            return;
        }

        $userId = $_SESSION['id'];

        if (!empty($userId)) {
            $cartQuery = "SELECT * FROM cart WHERE userId = $userId";
            $cartResult = $con->query($cartQuery);

            if ($cartResult->num_rows > 0) {
                $con->autocommit(false);

                try {
                    while ($cartItem = $cartResult->fetch_assoc()) {
                        $productId = $cartItem['productId'];
                        $quantity = $cartItem['quantity'];

                        $productQuery = "SELECT * FROM products WHERE id = $productId";
                        $productResult = $con->query($productQuery);

                        if ($productResult->num_rows > 0) {
                            $product = $productResult->fetch_assoc();
                            $price = $product['price'];

                            $totalPrice = $price * $quantity;

                            $insertQuery = "INSERT INTO orders (userId, productId, quantity, total) 
                                            VALUES ($userId, $productId, $quantity, $totalPrice)";

                            if (!$con->query($insertQuery)) {
                                throw new Exception("Error inserting order: " . $con->error);
                            }
                        } else {
                            throw new Exception("Product not found.");
                        }
                    }

                    $deleteQuery = "DELETE FROM cart WHERE userId = $userId";
                    if (!$con->query($deleteQuery)) {
                        throw new Exception("Error deleting cart items: " . $con->error);
                    }

                    $con->commit();

                    echo json_encode(array("message" => "Checkout completed successfully."));
                } catch (Exception $e) {
                    $con->rollback();
                    echo json_encode(array("error" => $e->getMessage()));
                }

                $con->autocommit(true);
            } else {
                echo json_encode(array("error" => "No items found in the cart."));
            }
        } else {
            echo json_encode(array("error" => "Please provide the user ID."));
        }

        $con->close();
    }

}


$action = $_POST['action'] ?? '';

$operation = new CartOperations();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $json_data = file_get_contents('php://input');
    $data = json_decode($json_data, true);

    $action = $data['action'] ?? '';

    if ($action === 'addItem') {
        $operation->addItem();
    } elseif ($action === 'updateItem') {
        $operation->updateItem();
    } elseif ($action === 'removeItem') {
        $operation->removeItem();
    } elseif ($action === 'getCart') {
        $operation->getCart();
    } elseif ($action === 'clearCart') {
        $operation->clearCart();
    } elseif ($action === 'checkout') {
        $operation->checkout();
    } else {
        echo json_encode(array("error" => "Invalid action."));
    }
} else {
    echo json_encode(array("error" => "Invalid request method. Please use POST method."));
}

?>