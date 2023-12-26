<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: PUT, GET, POST, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");
include '../connection.php';
include '../authorization.php';

// This file contains the search for the products
// API Testing: http://localhost\masterpiece/API/products/searchProducts.php?searchTerm={search term}

class Search
{
    public function searchProducts()
    {
        /*
        purpose: to search for products by name, price, id, category, section, or description
        method: GET
        for testing:
            {
                "searchTerm": "{insert search term}"
            }
        */
        global $con;

        $searchTerm = $_GET['searchTerm'];

        if (!empty($searchTerm)) {
            $sql = "SELECT * FROM products WHERE 
                    name LIKE '%$searchTerm%' OR
                    price LIKE '%$searchTerm%' OR
                    id LIKE '%$searchTerm%' OR
                    sectionId LIKE '%$searchTerm%' OR
                    description LIKE '%$searchTerm%'";
            $result = $con->query($sql);

            if ($result->num_rows > 0) {
                $products = array();
                while ($row = $result->fetch_assoc()) {
                    $products[] = $row;
                }
                echo json_encode($products);
            } else {
                echo json_encode(array("error" => "No products found."));
            }
        } else {
            echo json_encode(array("error" => "Please provide a search term."));
        }

        $con->close();
    }
}

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    if (isset($_GET['searchTerm'])) {
        $operation = new Search();
        $operation->searchProducts();
    } else {
        echo json_encode(array("error" => "Please provide a search term."));
    }
} else {
    echo json_encode(array("error" => "Invalid request method."));
}
?>