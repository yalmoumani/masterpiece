<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: PUT, GET, POST, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");
include '../connection.php';
include '../authorization.php';

// This file contains the search for the categories
class Search {
    public function search($query) {
        /*
        purpose: Searches for categories matching the given query
        method: GET
        for testing:
        {
            "query": "keyword"
        }
        */
        global $con;
        if (!empty($query)) {
            $sql = "SELECT id, category FROM categories WHERE id LIKE '%$query%' OR category LIKE '%$query%'";
            $result = $con->query($sql);
    
            if ($result->num_rows > 0) {
                $categories = array();
                while ($row = $result->fetch_assoc()) {
                    $categories[] = $row;
                }
                echo json_encode($categories);
            } else {
                echo json_encode(array("error" => "No categories found matching the search query."));
            }
        } else {
            echo json_encode(array("error" => "Empty search query."));
        }
    
        $con->close();
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $query = $_GET['query'] ?? '';
    $operation = new Search();
    $operation->search($query);
} else {
    echo json_encode(array("error" => "Invalid request method."));
}
?>