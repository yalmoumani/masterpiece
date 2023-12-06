<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: PUT, GET, POST, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");
// Contains the admins functions to create, edit, delete, get product by id, get all products
include '../connection.php';

class ProductOperations {

    // API Testing: http://localhost\masterpiece\API\products\productCRUD.php
    public function create() {
        /*
        for testing:
            {
                "action": "create",
                "name": "{insert text}",
                "description": "{insert text}",
                "image": "{insert text}",
                "price": "{insert text}",
                "categoryId": "{insert text}",
                "sectionId": "{insert text}"
            }
        */
        global $con;

        $data = json_decode(file_get_contents('php://input'), true);

        if (!empty($data)) {
            $requiredFields = ['image', 'name', 'description', 'categoryId', 'price', 'sectionId'];
            $allFieldsPresent = true;

            foreach ($requiredFields as $field) {
                if (!isset($data[$field]) || empty($data[$field])) {
                    $allFieldsPresent = false;
                    break;
                }
            }

            if ($allFieldsPresent) {
                $image = $data['image'];
                $name = $data['name'];
                $description = $data['description'];
                $price = $data['price'];
                $categoryId = $data['categoryId'];
                $sectionId = $data['sectionId'];
                $sql = "INSERT INTO products (image, name, description, categoryId, price, sectionId, created_at, updated_at) VALUES (
                    '$image',
                    '$name',
                    '$description',
                    '$categoryId',
                    '$price',
                    '$sectionId',
                    NOW(),
                    NOW()
                )";

                if ($con->query($sql) === TRUE) {
                    echo json_encode(array("message" => "Product created successfully."));
                } else {
                    echo json_encode(array("error" => "Error: " . $con->error));
                }
            } else {
                echo json_encode(array("error" => "Please provide all required fields."));
            }
        } else {
            echo json_encode(array("error" => "No data received."));
        }

        $con->close();
    }

    public function delete() {
        /*
        for testing:
            {
                "action": "delete",
                "id": {insert id#}
            }
        */
        global $con;

        if ($_SERVER['REQUEST_METHOD'] === "POST") {
            $data = json_decode(file_get_contents('php://input'), true);
            $id = $data['id'];

            if (!empty($id)) {
                $sql = "DELETE FROM products WHERE id = $id";

                if ($con->query($sql) === TRUE) {
                    echo json_encode(array("message" => "Product deleted successfully."));
                } else {
                    echo json_encode(array("error" => "Error: " . $con->error));
                }
            } else {
                echo json_encode(array("message" => "No ID provided for deletion."));
            }
        } else {
            echo json_encode(array("error" => "Invalid request method. Please use POST method."));
        }

        $con->close();
    }

    public function edit() {
        /*
        for testing:
            {
                "action": "edit",
                "id": {insert id#},
                "image": "{insert text}",
                "name": "{insert text}",
                "description": "{insert text}",
                "price": "{insert text}",
                "categoryId": "{insert text}",
                "sectionId": "{insert text}"
            }
        */
        global $con;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = json_decode(file_get_contents('php://input'), true);
            $id = $data['id'];

            $validFields = ['image', 'name', 'description', 'price', 'categoryId', 'sectionId'];
            $setClauses = [];

            foreach ($validFields as $field) {
                if (isset($data[$field])) {
                    $value = $data[$field];
                    $setClauses[] = "$field = '$value'";
                }
            }

            if (!empty($setClauses)) {
                $setClauses[] = "updated_at = NOW()";

                $updateSql = "UPDATE products SET " . implode(', ', $setClauses) . " WHERE id = $id";
                if ($con->query($updateSql) === TRUE) {
                    echo json_encode(array("message"=> "Product updated successfully."));
                } else {
                    echo json_encode(array("error" => "Error: " . $con->error));
                }
            } else {
                echo json_encode(array("error" => "No valid fields provided for update."));
            }
        } else {
            echo json_encode(array("error" => "Invalid request method. Please use POST method."));
        }

        $con->close();
    }

    public function getProduct() {
        /*
        for testing:
            {
                "action": "getProduct",
                "id": {insert id#}
            }
        */
        global $con;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = json_decode(file_get_contents('php://input'), true);
            $id = $data['id'];

            if (!empty($id)) {
                $sql = "SELECT * FROM products WHERE id = $id";
                $result = $con->query($sql);

                if ($result->num_rows > 0) {
                    $row = $result->fetch_assoc();
                    echo json_encode($row);
                } else {
                    echo json_encode(array("message" => "No product found with the provided ID."));
                }
            } else {
                echo json_encode(array("message" => "No ID provided for fetching product."));
            }
        } else {
            echo json_encode(array("error" => "Invalid request method. Please use POST method."));
        }

        $con->close();
    }

    public function getAll() {
        /*
        for testing:
            {
                "action": "getAll"
            }
        */
        global $con;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $sql = "SELECT * FROM products";
            $result = $con->query($sql);

            if ($result->num_rows > 0) {
                $rows = array();
                while ($row = $result->fetch_assoc()) {
                    $rows[] = $row;
                }
                echo json_encode($rows);
            } else {
                echo json_encode(array("message" => "No products found."));
            }
        } else {
            echo json_encode(array("error" => "Invalid request method. Please use POST method."));
        }

        $con->close();
    }
public function searchProducts($searchTerm) {
    /*
    purpose: to search for products by name, price, id, category, section, or description
    method: POST
    for testing:
        {
            "action": "searchProducts",
            "searchTerm": "{insert search term}"
        }
    */
    global $con;

    if (!empty($searchTerm)) {
        $sql = "SELECT * FROM products WHERE 
                name LIKE '%$searchTerm%' OR
                price LIKE '%$searchTerm%' OR
                id LIKE '%$searchTerm%' OR
                categoryId LIKE '%$searchTerm%' OR
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

$data = json_decode(file_get_contents('php://input'), true);
$action = isset($data['action']) ? $data['action'] : '';

$productOperations = new ProductOperations();

switch ($action) {
    case 'create':
        $productOperations->create();
        break;
    case 'delete':
        $productOperations->delete();
        break;
    case 'edit':
        $productOperations->edit();
        break;
    case 'getProduct':
        $productOperations->getProduct();
        break;
    case 'getAll':
        $productOperations->getAll();
        break;
    case 'searchProducts':
        if (isset($data['searchTerm'])) {
            $productOperations->searchProducts($data['searchTerm']);
        } else {
            echo json_encode(array("error" => "No search term provided."));
        }
        break;
    default:
        echo json_encode(array("error" => "Invalid action specified."));
        break;
}
?>