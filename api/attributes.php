<?php
// Include the database connection
require_once 'database.php';
header("Access-Control-Allow-Origin: *"); // Allow requests from any origin
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS"); // Allowed HTTP methods
header("Access-Control-Allow-Headers: Content-Type, Authorization"); // Allowed headers


// Determine the action from the request (e.g., add_attribute, list_attributes, delete_attribute)
$action = $_GET['action'] ?? '';

switch ($action) {
    case 'add':
        addAttribute();
        break;
    case 'list':
        listAttributes();
        break;
    case 'delete':
        deleteAttribute();
        break;
    default:
        echo json_encode(["message" => "Unsupported action"]);
        break;
}

// Function to add a new attribute to the attribute_catalog table
function addAttribute() {
    global $db;

    // Get the input data (JSON)
    $inputData = json_decode(file_get_contents('php://input'), true);

    // Validate input
    if (!isset($inputData['attribute_name']) || !isset($inputData['data_type'])) {
        echo json_encode(["message" => "Invalid input"]);
        return;
    }

    $attributeName = $inputData['attribute_name'];
    $dataType = $inputData['data_type'];

    // Insert the attribute into the database
    $query = "INSERT INTO attribute_catalog (attribute_name, data_type) VALUES (:attribute_name, :data_type)";
    $statement = $db->prepare($query);
    $statement->bindParam(':attribute_name', $attributeName);
    $statement->bindParam(':data_type', $dataType);
    
    try {
        $statement->execute();
        echo json_encode(["message" => "Attribute added successfully"]);
    } catch (PDOException $e) {
        echo json_encode(["message" => "Failed to add attribute: " . $e->getMessage()]);
    }
}

// Function to list all attributes from the attribute_catalog table
function listAttributes() {
    global $db;

    // Retrieve all attributes from the database
    $query = "SELECT * FROM attribute_catalog";
    $statement = $db->prepare($query);
    $statement->execute();
    $attributes = $statement->fetchAll(PDO::FETCH_ASSOC);

    // Return the attributes as a JSON response
    echo json_encode($attributes);
}

// Function to delete an attribute from the attribute_catalog table by its ID
// Function to delete an attribute from the attribute_catalog table
function deleteAttribute() {
    global $db; // Make $db accessible within this function

    // Get the input data (JSON)
    $inputData = json_decode(file_get_contents('php://input'), true);

    if (!isset($inputData['attribute_name'])) {
        echo json_encode(["message" => "Invalid input"]);
        return;
    }

    $attributeName = $inputData['attribute_name'];

    // Delete the attribute from the database
    $query = "DELETE FROM attribute_catalog WHERE attribute_name = :attribute_name";
    $statement = $db->prepare($query);
    $statement->bindParam(':attribute_name', $attributeName);

    try {
        $statement->execute();
        if ($statement->rowCount() > 0) {
            echo json_encode(["message" => "Attribute deleted successfully"]);
        } else {
            echo json_encode(["message" => "Attribute not found"]);
        }
    } catch (PDOException $e) {
        echo json_encode(["message" => "Failed to delete attribute: " . $e->getMessage()]);
    }
}

