<?php
require_once('database.php'); // Make sure this file is present and correctly configured

// CORS headers
header("Access-Control-Allow-Origin: *"); // Allow requests from any origin
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS"); // Allowed HTTP methods
header("Access-Control-Allow-Headers: Content-Type, Authorization"); // Allowed headers   

// Handle the action
$action = isset($_GET['action']) ? $_GET['action'] : '';

switch ($action) {
    case 'add':
        addUserData();
        break;
    case 'get':
        getUserData();
        break;
    case 'delete':
        deleteUserData();
        break;
    default:
        echo json_encode(["message" => "Unsupported action"]);
}

// Function to add user data
function addUserData() {
    global $db;

    // Get the input data (JSON)
    $inputData = json_decode(file_get_contents('php://input'), true);

    // Validate input data
    if (!isset($inputData['user_id']) || !isset($inputData['age']) || 
        !isset($inputData['department']) || !isset($inputData['salary']) || 
        !isset($inputData['experience'])) {
        echo json_encode(["message" => "Invalid input"]);
        return;
    }

    $userId = $inputData['user_id'];
    $age = $inputData['age'];
    $department = $inputData['department'];
    $salary = $inputData['salary'];
    $experience = $inputData['experience'];

    // Insert the user data into the database
    $query = "INSERT INTO user_data (user_id, age, department, salary, experience) VALUES (:user_id, :age, :department, :salary, :experience)";
    $statement = $db->prepare($query);
    $statement->bindParam(':user_id', $userId);
    $statement->bindParam(':age', $age);
    $statement->bindParam(':department', $department);
    $statement->bindParam(':salary', $salary);
    $statement->bindParam(':experience', $experience);

    try {
        $statement->execute();
        echo json_encode(["message" => "User added successfully"]);
    } catch (PDOException $e) {
        echo json_encode(["message" => "Failed to add user: " . $e->getMessage()]);
    }
}

// Function to get all users
function getUserData() {
    global $db;

    // Query to get all user data
    $query = "SELECT * FROM user_data";
    $statement = $db->prepare($query);
    $statement->execute();

    $users = $statement->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($users);
}

// Function to delete user data
function deleteUserData() {
    global $db;

    $inputData = json_decode(file_get_contents('php://input'), true);
    if (!isset($inputData['user_id'])) {
        echo json_encode(["message" => "Invalid input"]);
        return;
    }

    $userId = $inputData['user_id'];

    // Delete the user from the database
    $query = "DELETE FROM user_data WHERE user_id = :user_id";
    $statement = $db->prepare($query);
    $statement->bindParam(':user_id', $userId);

    try {
        $statement->execute();
        if ($statement->rowCount() > 0) {
            echo json_encode(["message" => "User deleted successfully"]);
        } else {
            echo json_encode(["message" => "User not found"]);
        }
    } catch (PDOException $e) {
        echo json_encode(["message" => "Failed to delete user: " . $e->getMessage()]);
    }
}
?>
