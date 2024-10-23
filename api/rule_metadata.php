<?php
require_once('database.php'); // Make sure this file is present and correctly configured

// Handle the action
$action = isset($_GET['action']) ? $_GET['action'] : '';

switch ($action) {
    case 'add_metadata':
        addRuleMetadata();
        break;
    case 'get_metadata':
        getRuleMetadata();
        break;
    case 'delete_metadata':
        deleteRuleMetadata();
        break;
    default:
        echo json_encode(["message" => "Unsupported action"]);
}

// Function to add rule metadata
function addRuleMetadata() {
    global $db;
    $inputData = json_decode(file_get_contents('php://input'), true);
    
    // Check for required fields
    if (!isset($inputData['rule_id']) || !isset($inputData['metadata_key']) || !isset($inputData['metadata_value'])) {
        echo json_encode(["message" => "Invalid input"]);
        return;
    }

    $ruleId = $inputData['rule_id'];
    $metadataKey = $inputData['metadata_key'];
    $metadataValue = $inputData['metadata_value'];

    // Ensure rule_id exists
    $ruleExists = $db->query("SELECT COUNT(*) FROM rules WHERE id = $ruleId")->fetchColumn();

    if (!$ruleExists) {
        echo json_encode(["message" => "Invalid rule ID"]);
        return;
    }

    // Insert rule metadata
    $query = "INSERT INTO rule_metadata (rule_id, metadata_key, metadata_value) VALUES (:rule_id, :metadata_key, :metadata_value)";
    $statement = $db->prepare($query);
    $statement->bindParam(':rule_id', $ruleId);
    $statement->bindParam(':metadata_key', $metadataKey);
    $statement->bindParam(':metadata_value', $metadataValue);
    $statement->execute();
    
    echo json_encode(["message" => "Rule metadata added successfully"]);
}


// Function to get all metadata for a specific rule
function getRuleMetadata() {
    global $db;

    $inputData = json_decode(file_get_contents('php://input'), true);
    if (!isset($inputData['rule_id'])) {
        echo json_encode(["message" => "Invalid input"]);
        return;
    }

    $ruleId = $inputData['rule_id'];

    // Query to get the metadata
    $query = "SELECT * FROM rule_metadata WHERE rule_id = :rule_id";
    $statement = $db->prepare($query);
    $statement->bindParam(':rule_id', $ruleId);
    $statement->execute();

    $metadata = $statement->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($metadata);
}

// Function to delete rule metadata
function deleteRuleMetadata() {
    global $db;

    $inputData = json_decode(file_get_contents('php://input'), true);
    if (!isset($inputData['metadata_id'])) {
        echo json_encode(["message" => "Invalid input"]);
        return;
    }

    $metadataId = $inputData['metadata_id'];

    // Delete the metadata from the database
    $query = "DELETE FROM rule_metadata WHERE id = :metadata_id";
    $statement = $db->prepare($query);
    $statement->bindParam(':metadata_id', $metadataId);

    try {
        $statement->execute();
        if ($statement->rowCount() > 0) {
            echo json_encode(["message" => "Metadata deleted successfully"]);
        } else {
            echo json_encode(["message" => "Metadata not found"]);
        }
    } catch (PDOException $e) {
        echo json_encode(["message" => "Failed to delete metadata: " . $e->getMessage()]);
    }
}
?>
