<?php
require_once('database.php'); // Include database connection
header("Access-Control-Allow-Origin: *"); // Allow all origins
header("Access-Control-Allow-Methods: GET, POST, OPTIONS"); // Allow specific methods
header("Access-Control-Allow-Headers: Content-Type"); // Allow specific headers

// Function to add evaluation result
function addEvaluationResult() {
    global $db;

    // Get the input data (JSON)
    $inputData = json_decode(file_get_contents('php://input'), true);

    if (!isset($inputData['rule_id']) || !isset($inputData['user_data']) || !isset($inputData['result'])) {
        echo json_encode(["message" => "Invalid input"]);
        return;
    }

    $ruleId = $inputData['rule_id'];
    $userData = json_encode($inputData['user_data']); // Convert user data to JSON
    $result = $inputData['result'];

    // Insert the evaluation result into the database
    $query = "INSERT INTO rule_evaluation_results (rule_id, user_data, result, evaluated_at) VALUES (:rule_id, :user_data, :result, NOW())";
    $statement = $db->prepare($query);
    $statement->bindParam(':rule_id', $ruleId);
    $statement->bindParam(':user_data', $userData);
    $statement->bindParam(':result', $result);

    try {
        $statement->execute();
        echo json_encode(["message" => "Evaluation result added successfully"]);
    } catch (PDOException $e) {
        echo json_encode(["message" => "Failed to add evaluation result: " . $e->getMessage()]);
    }
}

// Function to get evaluation results
function getEvaluationResults() {
    global $db;

    $query = "SELECT * FROM rule_evaluation_results";
    $statement = $db->prepare($query);
    $statement->execute();
    $results = $statement->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode($results);
}
// Function to delete evaluation result
function deleteEvaluationResult() {
    global $db;

    // Get the input data (JSON)
    $inputData = json_decode(file_get_contents('php://input'), true);

    if (!isset($inputData['rule_id'])) {
        echo json_encode(["message" => "Invalid input"]);
        return;
    }

    $resultId = $inputData['rule_id'];

    // Delete the evaluation result from the database
    $query = "DELETE FROM rule_evaluation_results WHERE id = :rule_id";
    $statement = $db->prepare($query);
    $statement->bindParam(':rule_id', $resultId);

    try {
        $statement->execute();
        if ($statement->rowCount() > 0) {
            echo json_encode(["message" => "Evaluation result deleted successfully"]);
        } else {
            echo json_encode(["message" => "Evaluation result not found"]);
        }
    } catch (PDOException $e) {
        echo json_encode(["message" => "Failed to delete evaluation result: " . $e->getMessage()]);
    }
}

// Check for action parameter in the URL
$action = isset($_GET['action']) ? $_GET['action'] : '';

switch ($action) {
    case 'add':
        addEvaluationResult();
        break;
    case 'get':
        getEvaluationResults();
        break;
    case 'delete':
        deleteEvaluationResult();
        break;
    default:
        echo json_encode(["message" => "Unsupported action"]);
}
?>
