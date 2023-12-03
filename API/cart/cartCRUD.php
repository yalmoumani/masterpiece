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
        {
            "action": "addItem",
            "user_id": {insert id#},
            "product_id": {insert id#},
            "quantity": {insert #}
        }
        */
        global $con;

        $data = json_decode(file_get_contents('php://input'), true);

        $userId = $data['user_id'] ?? '';
        $productId = $data['product_id'] ?? '';
        $quantity = $data['quantity'] ?? 1;

        if (!empty($userId) && !empty($productId)) {
            // Check if the item already exists in the cart
            $checkQuery = "SELECT * FROM cart WHERE user_id = $userId AND product_id = $productId";
            $checkResult = $con->query($checkQuery);

            if ($checkResult->num_rows > 0) {
                // Update the quantity of the existing item in the cart
                $updateQuery = "UPDATE cart SET quantity = quantity + $quantity WHERE user_id = $userId AND product_id = $productId";

                if ($con->query($updateQuery) === TRUE) {
                    echo json_encode(array("message" => "Item quantity updated in the cart."));
                } else {
                    echo json_encode(array("error" => "Error: " . $con->error));
                }
            } else {
                $insertQuery = "INSERT INTO cart (user_id, product_id, quantity) VALUES ($userId, $productId, $quantity)";

                if ($con->query($insertQuery) === TRUE) {
                    echo json_encode(array("message" => "Item added to the cart."));
                } else {
                    echo json_encode(array("error" => "Error: " . $con->error));
                }
            }
        } else {
            echo json_encode(array("error" => "Please provide the user ID and product ID."));
        }

        $con->close();
    }

    public function updateItem() {
        /*
        for testing:
        {
            "action": "updateItem",
            "user_id": {insert id#}
            "product_id": {insert id#}
            "quantity":{insert i#}
        }
        */
        global $con;

        $data = json_decode(file_get_contents('php://input'), true);

        $userId = $data['user_id'] ?? '';
        $productId = $data['product_id'] ?? '';
        $quantity = $data['quantity'] ?? 1;

        if (!empty($userId) && !empty($productId)) {
            $updateQuery = "UPDATE cart SET quantity = $quantity WHERE user_id = $userId AND product_id = $productId";

            if ($con->query($updateQuery) === TRUE) {
                echo json_encode(array("message" => "Item quantity updated in the cart."));
            } else {
                echo json_encode(array("error" => "Error: " . $con->error));
            }
        } else {
            echo json_encode(array("error" => "Please provide the user ID and product ID."));
        }

        $con->close();
    }

    public function removeItem() {
        /*
        for testing:
        {
            "action": "removeItem",
            "user_id": 1,
            "product_id": 1
        }
        */
        global $con;

        $data = json_decode(file_get_contents('php://input'), true);

        $userId = $data['user_id'] ?? '';
        $productId = $data['product_id'] ?? '';

        if (!empty($userId) && !empty($productId)) {
            $deleteQuery = "DELETE FROM cart WHERE user_id = $userId AND product_id = $productId";

            if ($con->query($deleteQuery) === TRUE) {
                echo json_encode(array("message" => "Item removed from the cart."));
            } else {
                echo json_encode(array("error" => "Error: " . $con->error));
            }
        } else {
            echo json_encode(array("error" => "Please provide the user ID and product ID."));
        }

        $con->close();
    }

    public function getCart($userId) {
        /*
        for testing:
        {
            "action": "getCart",
            "user_id": 1
        }
        */
        global $con;

        if (!empty($userId)) {
    
            $sql = "SELECT cart.id, products.product_name, cart.quantity, products.price 
                    FROM cart 
                    INNER JOIN products ON cart.product_id = products.id 
                    WHERE cart.user_id = $userId";

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


    public function clearCart($userId) {
        /*
        for testing:
        {
            "action": "clearCart",
            "user_id": {insert id#}
        }
        */
        global $con;

        if (!empty($userId)) {
            $deleteQuery = "DELETE FROM cart WHERE user_id = $userId";

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

    public function checkout($userId) {
        /*
        for testing:
        {
            "action": "checkout",
            "user_id": {insert id#}
        }
        */
        global $con;

        if (!empty($userId)) {
            $cartQuery = "SELECT * FROM cart WHERE user_id = $userId";
            $cartResult = $con->query($cartQuery);

            if ($cartResult->num_rows > 0) {
                $con->autocommit(false);

                try {
                    while ($cartItem = $cartResult->fetch_assoc()) {
                        $productId = $cartItem['product_id'];
                        $quantity = $cartItem['quantity'];

                        $productQuery = "SELECT * FROM products WHERE id = $productId";
                        $productResult = $con->query($productQuery);

                        if ($productResult->num_rows > 0) {
                            $product = $productResult->fetch_assoc();
                            $price = $product['price'];

                            $totalPrice = $price * $quantity;

                            $insertQuery = "INSERT INTO orders (user_id, product_id, quantity, total) 
                                            VALUES ($userId, $productId, $quantity, $totalPrice)";

                            if (!$con->query($insertQuery)) {
                                throw new Exception("Error inserting order: " . $con->error);
                            }
                        } else {
                            throw new Exception("Product not found.");
                        }
                    }

                    $deleteQuery = "DELETE FROM cart WHERE user_id = $userId";
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
        $userId = $data['user_id'] ?? '';
        $operation->getCart($userId);
    } elseif ($action === 'clearCart') {
        $userId = $data['user_id'] ?? '';
        $operation->clearCart($userId);
    } elseif ($action === 'checkout') {
        $userId = $data['user_id'] ?? '';
        $operation->checkout($userId);
    } else {
        echo json_encode(array("error" => "Invalid action."));
    }
} else {
    echo json_encode(array("error" => "Invalid request method. Please use POST method."));
}

?>