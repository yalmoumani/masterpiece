<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: PUT, GET, POST, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");
include '../connection.php';
include '../authorization.php';

class CategoryOperations {
    // This file contains all the functions needed to get, edit, create, delete for categories and for sections.

    // API Testing: http://localhost/masterpiece/API/admin/categoryCRUD.php
    public function create() {
        /*
        purpose: creates new category
        method: POST
        for testing:
        {
            "category": "insert text"
        }
        */
        global $con;
        $data = json_decode(file_get_contents('php://input'), true);

        if (!empty($data['category'])) {
            $category = $data['category'];

            $sql = "INSERT INTO categories (category) VALUES ('$category')";

            if ($con->query($sql) === TRUE) {
                echo json_encode(array("message" => "Category created successfully."));
            } else {
                echo json_encode(array("error" => "Error: " . $con->error));
            }
        } else {
            echo json_encode(array("error" => "Please provide the category name."));
        }

        $con->close();
    }

    public function delete() {
        /*
        purpose: Deletes a category
        method: DELETE
        for testing:
        {
            "categoryId": 1
        }
        */
        global $con;
        $data = json_decode(file_get_contents('php://input'), true);
        $id = $data['categoryId'];

        if (!empty($id)) {
            $sql = "DELETE FROM categories WHERE id = $id";

            if ($con->query($sql) === TRUE) {
                echo json_encode(array("message" => "Category deleted successfully."));
            } else {
                echo json_encode(array("error" => "Error: " . $con->error));
            }
        } else {
            echo json_encode(array("error" => "No category ID provided for deletion."));
        }

        $con->close();
    }

    public function edit() {
        /*
        purpose: To edit a category.
        method: PUT
        for testing:
        {
            "categoryId": {insert id#},
            "category": "insert text"
        }
        */
        global $con;
        $json_data = file_get_contents('php://input');
        $data = json_decode($json_data, true);

        $categoryId = $data['categoryId'];

        $category = $data['category'] ?? '';

        $update_category_query = "UPDATE categories SET ";

        if (!empty($category)) {
            $update_category_query .= "category = '$category'";
        } else {
            echo json_encode(array("error" => "Please provide the updated category name."));
            return;
        }

        $update_category_query .= " WHERE id = $categoryId";

        if ($con->query($update_category_query) === TRUE) {
            echo json_encode(array("message" => "Category updated successfully."));
        } else {
            echo json_encode(array("error" => "Error: " . $con->error));
        }

        $con->close();
    }

    public function getCategory($categoryId) {
        /*
        purpose: Retrieves one category
        method: GET
        for testing:
        {
            "categoryId": {insert id#}
        }
        */
        global $con;
        if (!empty($categoryId)) {
            $sql = "SELECT id, category FROM categories WHERE id = $categoryId";
            $result = $con->query($sql);

            if ($result->num_rows > 0) {
                $category = $result->fetch_assoc();
                echo json_encode($category);
            } else {
                echo json_encode(array("error" => "Category not found."));
            }
        } else {
            echo json_encode(array("error" => "No category ID provided."));
        }

        $con->close();
    }

    public function getAll() {
        /*
        purpose: To retrieve all categories
        method: GET
        */
        global $con;

        $sql = "SELECT id, category FROM categories";

        $result = $con->query($sql);

        if ($result->num_rows > 0) {
            $categories = array();
            while ($row = $result->fetch_assoc()) {
                $categories[] = $row;
            }
            echo json_encode($categories);
        } else {
            echo json_encode(array("error" => "No categories found."));
        }

        $con->close();
    }
}

$operation = new CategoryOperations();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $operation->create();
} elseif ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    $operation->delete();
} elseif ($_SERVER['REQUEST_METHOD'] === 'PUT') {
    $operation->edit();
} elseif ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $categoryId = $_GET['categoryId'] ?? '';
    if (!empty($categoryId)) {
        $operation->getCategory($categoryId);
    } else {
        $operation->getAll();
    }
} else {
    echo json_encode(array("error" => "Invalid request method."));
}
?>
