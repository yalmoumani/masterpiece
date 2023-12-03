<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: PUT, GET, POST, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

include('../connection.php');

class Coupons
{
    private $con;

    public function __construct($dbConnection)
    {
        $this->con = $dbConnection;
    }

    public function editCoupon($couponId, $couponData)
    {
    
        $query = "UPDATE coupons SET coupon_data = :data WHERE coupon_id = :id";
        $stmt = $this->con->prepare($query);
        $stmt->bindParam(':data', $couponData);
        $stmt->bindParam(':id', $couponId);
        $stmt->execute();

        // Return a response indicating the success or failure of the operation
        $response = array('message' => 'Coupon edited successfully');
        echo json_encode($response);
    }
}

$requestMethod = $_SERVER['REQUEST_METHOD'];

if ($requestMethod === 'POST') {
    $requestData = json_decode(file_get_contents('php://input'), true);
    // Handle POST request and decode JSON data

    // Assuming you have the coupon ID and data in the request, you can call the editCoupon method like this:
    $couponId = $requestData['id'];
    $couponData = $requestData['data'];

    // Create an instance of the Coupons class
    $coupons = new Coupons($con);

    // Call the editCoupon method
    $coupons->editCoupon($couponId, $couponData);
} elseif ($requestMethod === 'GET') {
    $couponId = $_GET['id'];
    // Handle GET request and retrieve coupon by ID
} else {
    // Handle other request methods
}
?>