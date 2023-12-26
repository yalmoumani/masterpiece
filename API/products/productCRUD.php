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
                "name": "{insert text}",
                "description": "{insert text}",
                "image": "{insert text}",
                "price": "{insert text}",
                "sectionId": "{insert text}"
            }
        */
        global $con;

        $data = json_decode(file_get_contents('php://input'), true);

        if (!empty($data)) {
            $requiredFields = ['image', 'name', 'description', 'price', 'sectionId'];
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
                $sectionId = $data['sectionId'];
                $sql = "INSERT INTO products (image, name, description, price, sectionId, created_at, updated_at) VALUES (
                    '$image',
                    '$name',
                    '$description',
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
                "id": {insert id#}
            }
        */
        global $con;

        if ($_SERVER['REQUEST_METHOD'] === "DELETE") {
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
            echo json_encode(array("error" => "Invalid request method. Please use DELETE method."));
        }

        $con->close();
    }

    public function edit() {
        /*
        for testing:
            {
                "id": {insert id#},
                "image": "{insert text}",
                "name": "{insert text}",
                "description": "{insert text}",
                "price": "{insert text}",
                "sectionId": "{insert text}"
            }
        */
        global $con;

        if ($_SERVER['REQUEST_METHOD'] === 'PUT') {
            $data = json_decode(file_get_contents('php://input'), true);
            $id = $data['id'];

            $validFields = ['image', 'name', 'description', 'price', 'sectionId'];
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
            echo json_encode(array("error" =>"Invalid request method. Please use PUT method."));
        }

        $con->close();
    }

    public function getById($id) {
        global $con;

        $sql = "SELECT * FROM products WHERE id = $id";
        $result = $con->query($sql);

        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            echo json_encode($row);
        } else {
            echo json_encode(array("message" => "Product not found."));
        }

        $con->close();
    }

    public function getAll() {
        global $con;

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

        $con->close();
    }
}

$method = $_SERVER['REQUEST_METHOD'];
$product = new ProductOperations();

switch ($method) {
    case 'POST':
        $product->create();
        break;
    case 'DELETE':
        $product->delete();
        break;
    case 'PUT':
        $product->edit();
        break;
    case 'GET':
        $id = isset($_GET['id']) ? $_GET['id'] : null;
        if ($id !== null) {
            $product->getById($id);
        } else {
            $product->getAll();
        }
        break;
    default:
        echo json_encode(array("error" => "Invalid request method."));
        break;
}
?>
