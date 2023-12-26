<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: PUT, GET, POST, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");
include '../connection.php';
include '../authorization.php';

class SectionOperations {
    // This file contains all the functions needed to get, edit, create, delete for categories and for sections.

    // API Testing: http://localhost/masterpiece/API/admin/sectionCRUD.php
   
    public function getSections($id) {
        /*
        purpose: Retrieves sections for a specific category
        method: GET
        {
            "id": {insert id#}
        }
        */
        global $con;
        if (!empty($id)) {
            $sql = "SELECT * FROM sections WHERE categoryId = $id";
            $result = $con->query($sql);
    
            if ($result->num_rows > 0) {
                $sections = array();
                while ($row = $result->fetch_assoc()) {
                    $sections[] = $row;
                }
                echo json_encode($sections);
            } else {
                echo json_encode(array("error" => "No sections found for this category."));
            }
        } else {
            echo json_encode(array("error" => "No category ID provided."));
        }
    
        $con->close();
    }

    public function createSection() {
        /*
        purpose: creates a new section under a category
        method: POST
        for testing:
        {
            "categoryId": 1,
            "section": "insert text"
        }
        */
        global $con;
        $data = json_decode(file_get_contents('php://input'), true);
    
        $id = $data['categoryId'];
        $section = $data['section'];
    
        if (!empty($id) && !empty($section)) {
            $checkSql = "SELECT * FROM sections WHERE categoryId = '$id' AND name = '$section'";
            $checkResult = $con->query($checkSql);
    
            if ($checkResult->num_rows > 0) {
                echo json_encode(array("error" => "This section already exists with this category."));
            } else {
                // Insert the new section
                $insertSql = "INSERT INTO sections (categoryId, name) VALUES ('$id', '$section')";
    
                if ($con->query($insertSql) === TRUE) {
                    echo json_encode(array("message" => "Section created successfully."));
                } else {
                    echo json_encode(array("error" => "Error: " . $con->error));
                }
            }
        } else {
            echo json_encode(array("error" => "Please provide the category ID and section name."));
        }
    
        $con->close();
    }
    public function deleteSection() {
        /*
        purpose: Deletes a section
        method: DELETE
        for testing:
        {
            "sectionId": 1
        }
        */
        global $con;
        $data = json_decode(file_get_contents('php://input'), true);
        $id = $data['sectionId'];

        if (!empty($id)) {
            $sql = "DELETE FROM sections WHERE id = $id";

            if ($con->query($sql) === TRUE) {
                echo json_encode(array("message" => "Section deleted successfully."));
            } else {
                echo json_encode(array("error" => "Error: " . $con->error));
            }
        } else {
            echo json_encode(array("error" => "No section ID provided for deletion."));
        }

        $con->close();
    }

    public function editSection() {
        /*
        purpose: To edit a section.
        method: PUT
        for testing:
        {
            "sectionId": {insert id#},
            "section": "insert text"
        }
        */
        global $con;
        $data = json_decode(file_get_contents('php://input'), true);

        $sectionId = $data['sectionId'];
        $section = $data['section'];

        if (!empty($section) && !empty($sectionId)) {
            $sql = "UPDATE sections SET name = '$section' WHERE id = $sectionId";

            if ($con->query($sql) === TRUE) {
                echo json_encode(array("message" => "Section updated successfully."));
            } else {
                echo json_encode(array("error" => "Error: " . $con->error));
            }
        } else {
            echo json_encode(array("error" => "Please provide the updated section name and section ID."));
        }

        $con->close();
    }
}

$operation = new SectionOperations();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $operation->createSection();
} elseif ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    $operation->deleteSection();
} elseif ($_SERVER['REQUEST_METHOD'] === 'PUT') {
    $operation->editSection();
} elseif ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $id = $_GET['categoryId'] ?? '';
    if (!empty($id)) {
        $operation->getSections($id);
    } else {
        echo json_encode(array("error" => "No category ID provided."));
    }
}
?>