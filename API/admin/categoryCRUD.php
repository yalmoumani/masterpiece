<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: PUT, GET, POST, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");
include '../connection.php';

class CategoryOperations {

    // API Testing: http://localhost\masterpiece\API\admin\categoryCRUD.php
    public function create() {
        /*
        for testing:
        {
            "action": "create",
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
        for testing:
        {
            "action": "delete",
            "id": 1
        }
        */
        global $con;

        if ($_SERVER['REQUEST_METHOD'] === "POST") {
            $data = json_decode(file_get_contents('php://input'), true);
            $id = $data['id'];

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
            "category": "insert text"
        }
        */
        global $con;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $json_data = file_get_contents('php://input');
            $data = json_decode($json_data, true);

            $categoryId = $data['id'];

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
        } else {
            echo json_encode(array("error" => "Invalid request method. Please use POST method."));
        }

        $con->close();
    }

    public function getCategory($id) {
        /*
        for testing:
        {
            "action": "getCategory",
            "id": {insert id#}
        }
        */
        global $con;

        if (!empty($id)) {
            $sql = "SELECT id, category FROM categories WHERE id = $id";
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
        for testing:
        {
{
    "action": "getAll"
}
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

    public function getSections($id) {
            /*
        for testing:
        {
{
    "action": "getSections"
    "id":{inserts id#}
}
*/
        global $con;

        if (!empty($id)) {
            $sql = "SELECT categoryId, sectionId FROM category_section WHERE categoryId = $id";
            $result = $con->query($sql);

            if ($result->num_rows > 0) {
                $sections = array();
                while ($row = $result->fetch_assoc()) {
                    $sections[] = $row;
                }
                echo json_encode($sections);
            } else {
                echo json_encode(array("error" => "No sections found for the category."));
            }
        } else {
            echo json_encode(array("error" => "No category ID provided."));
        }

        $con->close();
    }

    public function createSection() {
        global $con;
    
        $data = json_decode(file_get_contents('php://input'), true);
    
        if (!empty($data['category']) && !empty($data['section'])) {
            $category = $data['category'];
            $section = $data['section'];
    
            $sectionSql = "INSERT INTO sections (name) VALUES ('$section')";
            if ($con->query($sectionSql) === TRUE) {

                $sectionId = $con->insert_id;
    
                $categorySectionSql = "INSERT INTO category_section (categoryId, sectionId) VALUES ('$category', '$sectionId')";
                if ($con->query($categorySectionSql) === TRUE) {
                    echo json_encode(array("message" => "Section created and associated with the category successfully."));
                } else {
                    echo json_encode(array("error" => "Error: " . $con->error));
                }
            } else {
                echo json_encode(array("error" => "Error: " . $con->error));
            }
        } else {
            echo json_encode(array("error" => "Please provide the category and section names."));
        }
    
        $con->close();
    }
}

$operation = new CategoryOperations();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $json_data = file_get_contents('php://input');
    $data = json_decode($json_data, true);

    $action = $data['action'] ?? '';

    if ($action === 'create') {
        $operation->create();
    } elseif ($action === 'delete') {
        $operation->delete();
    } elseif ($action === 'edit') {
        $operation->edit();
    } elseif ($action === 'getCategory') {
        $id = $data['id'] ?? '';
        $operation->getCategory($id);
    } elseif ($action === 'getSections') {
        $id = $data['id'] ?? '';
        $operation->getSections($id); 
    } elseif ($action === 'getAll') {
        $operation->getAll();
    } elseif ($action === 'createSection') {
        $operation->createSection(); 
    } else {
        echo json_encode(array("error" => "Invalid action."));
    }
} else {
    echo json_encode(array("error" => "Invalid request method. Please use POST method."));
}


?>