<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: PUT, GET, POST, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

include '../connection.php';

class OrderOperations {

    // API Testing: http://localhost\masterpiece\API\order\orderCRUD.php

    public function cancelOrder() {
        global $con;
        $data = json_decode(file_get_contents('php://input'), true);
        $id = $data['id'] ?? '';

        if (!empty($id)) {
            $sql = "SELECT status FROM orders WHERE id = $id";
            $result = $con->query($sql);

            if ($result->num_rows > 0) {
                $row = $result->fetch_assoc();
                $status = $row['status'];

                if ($status === 'processing') {
                    $deleteSql = "DELETE FROM orders WHERE id = $id";

                    if ($con->query($deleteSql) === TRUE) {
                        echo json_encode(array("message" => "Order canceled successfully."));
                    } else {
                        echo json_encode(array("error" => "Error: " . $con->error));
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

    public function viewCurrentOrders($userId) {
        global $con;
        $sql = "SELECT * FROM orders WHERE user_id = $userId AND status = 'processing' OR status = 'shipped'";
        $result = $con->query($sql);

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

    public function pastOrders() {
        global $con;
        $userId = $_POST['user_id'] ?? '';
        
        if (!empty($userId)) {
            $sql = "SELECT * FROM orders WHERE user_id = $userId AND status = 'closed'";
            $result = $con->query($sql);

            if ($result->num_rows > 0) {
                $orders = array();
                while ($row = $result->fetch_assoc()) {
                    $orders[] = $row;
                }
                echo json_encode($orders);
            } else {
                echo json_encode(array("message" => "No past orders found for the user."));
            }
        } else {
            echo json_encode(array("error" => "No user ID provided for retrieving past orders."));
        }
    }

    public function getOrder($orderId) {
        global $con;
        $sql = "SELECT * FROM orders WHERE id = $orderId";
        $result = $con->query($sql);

        if ($result->num_rows > 0) {
            $order = $result->fetch_assoc();
            echo json_encode($order);
        } else {
            echo json_encode(array("error" => "Order not found."));
        }
    }
}

$action = $_POST['action'] ?? '';

$operation = new OrderOperations();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $json_data = file_get_contents('php://input');
    $data = json_decode($json_data, true);

    $action = $data['action'] ?? '';

    if ($action === 'cancelOrder') {
        $operation->cancelOrder();
    } elseif ($action === 'viewCurrentOrders') {
        $userId = $data['user_id'] ?? '';
        $operation->viewCurrentOrders($userId);
    } elseif ($action === 'pastOrders') {
        $operation->pastOrders();
    } elseif ($action === 'getOrder') {
        $orderId = $data['order_id'] ?? '';
        $operation->getOrder($orderId);
    } else {
        echo json_encode(array("error" => "Invalid action."));
    }
}
?>
