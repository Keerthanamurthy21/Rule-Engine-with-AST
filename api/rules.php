<?php
// Include the database connection
require_once '../api/database.php';

// Set headers for JSON response
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS, DELETE");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

// Get the HTTP request method (GET, POST, etc.)
$requestMethod = $_SERVER['REQUEST_METHOD'];
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Handle different request methods and actions
$action = isset($_GET['action']) ? $_GET['action'] : '';

switch ($action) {
    case 'create':
        createRule();
        break;
    case 'get':
        getRules();
        break;
    case 'evaluate':
        evaluateRules();
        break;
    case 'combine':
        combineRules();
        break;
    case 'update':
        updateRule();
        break;
    case 'modify':
        modifySubExpression();
        break;
    case 'delete':
        deleteRule();
        break;            
    default:
        echo json_encode(["message" => "Unsupported action"]);
        break;
}


// Function to create a new rule
function createRule() {
    global $db;

    // Get the input data (JSON)
    $inputData = json_decode(file_get_contents('php://input'), true);

    if (!isset($inputData['rule_name']) || !isset($inputData['rule_string'])) {
        echo json_encode(["message" => "Invalid input"]);
        return;
    }

    $ruleName = $inputData['rule_name'];
    $ruleString = $inputData['rule_string'];

    // Insert the rule into the database
    $query = "INSERT INTO rules (rule_name, rule_string, created_at) VALUES (:rule_name, :rule_string, NOW())";
    $statement = $db->prepare($query);
    $statement->bindParam(':rule_name', $ruleName);
    $statement->bindParam(':rule_string', $ruleString);
    if (!validateRuleString($ruleString)) {
        return json_encode(["message" => "Invalid rule format"]);
    }
    try {
        $statement->execute();
        echo json_encode(["message" => "Rule created successfully"]);
    } catch (PDOException $e) {
        echo json_encode(["message" => "Failed to create rule: " . $e->getMessage()]);
    }
    
}
// Function to retrieve all rules
function getRules() {
    global $db;

    $query = "SELECT * FROM rules";
    $statement = $db->prepare($query);
    $statement->execute();

    $rules = $statement->fetchAll(PDO::FETCH_ASSOC);

    // Return a JSON response with the rules key
    echo json_encode([
        "status" => "success",
        "rules" => $rules
    ]);
}
// // Function to evaluate a rule against user-provided data
// function evaluateRule(){
//     global $db;

//     // Get the input data (JSON)
//     $inputData = json_decode(file_get_contents('php://input'), true);

//     if (!isset($inputData['rule_id']) || !isset($inputData['user_data'])) {
//         echo json_encode(["message" => "Invalid input"]);
//         return;
//     }

//     $ruleId = $inputData['rule_id'];
//     $userData = $inputData['user_data'];

//     // Log the user data
//     error_log("User data: " . print_r($userData, true));

//     // Fetch the rule string from the database
//     $query = "SELECT rule_string FROM rules WHERE id = :rule_id";
//     $statement = $db->prepare($query);
//     $statement->bindParam(':rule_id', $ruleId);

//     try {
//         $statement->execute();
//         $rule = $statement->fetch(PDO::FETCH_ASSOC);

//         if (!$rule) {
//             echo json_encode(["message" => "Rule not found"]);
//             return;
//         }

//         $ruleString = $rule['rule_string'];

//         // Log the fetched rule string
//         error_log("Fetched rule string: " . $ruleString);

//         // Evaluate the rule
//         $evaluationResult = evaluateRuleString($ruleString, $userData);

//         // Log the evaluation result
//         error_log("Evaluation result: " . ($evaluationResult ? 'true' : 'false'));

//         echo json_encode(["result" => $evaluationResult]);
//     } catch (PDOException $e) {
//         echo json_encode(["message" => "Error evaluating rule: " . $e->getMessage()]);
//     }
// }

// // Function to evaluate individual conditions
// function evaluateCondition($condition, $userData) {
//     // Split the condition into parts
//     preg_match('/\s*(\w+)\s*([<>=!]+)\s*(.+)/', $condition, $matches);

//     if (count($matches) !== 4) {
//         error_log("Invalid condition format: " . $condition);
//         return false; // Invalid condition format
//     }

//     $attribute = $matches[1];
//     $operator = $matches[2];
//     $value = trim($matches[3], "'"); // Remove quotes for string comparisons

//     // Retrieve the user data value
//     $userValue = $userData[$attribute] ?? null;

//     // Convert values to numeric if possible
//     if (is_numeric($userValue)) {
//         $userValue = (float)$userValue;
//     }
//     if (is_numeric($value)) {
//         $value = (float)$value;
//     }

//     // Log the comparison details
//     error_log("Comparing: $attribute ($userValue) $operator $value");

//     // Evaluate based on the operator
//     switch ($operator) {
//         case '<':
//             return $userValue < $value;
//         case '<=':
//             return $userValue <= $value;
//         case '>':
//             return $userValue > $value;
//         case '>=':
//             return $userValue >= $value;
//         case '=':
//         case '==':
//             return $userValue == $value;
//         case '!=':
//             return $userValue != $value;
//         default:
//             error_log("Unknown operator: " . $operator);
//             return false; // Unknown operator
//     }
// }

// Function to evaluate rules against user data
function evaluateRules() {
    global $db;

    // Get the input data (JSON)
    $inputData = json_decode(file_get_contents('php://input'), true);

    if (!isset($inputData['user_id'])) {
        echo json_encode(["message" => "Invalid input, user_id is required"]);
        return;
    }

    $userId = $inputData['user_id'];

    // Fetch user data from the user_data table
    $userQuery = "SELECT * FROM user_data WHERE user_id = :user_id";
    $userStmt = $db->prepare($userQuery);
    $userStmt->bindParam(':user_id', $userId);

    try {
        $userStmt->execute();
        $userData = $userStmt->fetch(PDO::FETCH_ASSOC);

        if (!$userData) {
            echo json_encode(["message" => "User not found"]);
            return;
        }

        // Fetch all rules from the rules table
        $rulesQuery = "SELECT * FROM rules";
        $rulesStmt = $db->prepare($rulesQuery);
        $rulesStmt->execute();
        $rules = $rulesStmt->fetchAll(PDO::FETCH_ASSOC);

        if (!$rules) {
            echo json_encode(["message" => "No rules found"]);
            return;
        }

        $evaluationResults = [];

        // Evaluate each rule against the user data
        foreach ($rules as $rule) {
            $ruleId = $rule['id'];
            $ruleString = $rule['rule_string'];
            $ruleName = $rule['rule_name'];

            // Evaluate the rule string
            $evaluationResult = evaluateRuleString($ruleString, $userData) ? 'Passed' : 'Failed';

            // Save the result to the database
            $insertQuery = "INSERT INTO rule_evaluation_results (rule_id, user_id, result, evaluated_at) VALUES (:rule_id, :user_id, :result, NOW())";
            $insertStmt = $db->prepare($insertQuery);
            $insertStmt->bindParam(':rule_id', $ruleId);
            $insertStmt->bindParam(':user_id', $userId);
            $insertStmt->bindParam(':result', $evaluationResult);
            $insertStmt->execute();

            // Add the result to the evaluation results array
            $evaluationResults[] = [
                "rule_name" => $ruleName,
                "result" => $evaluationResult
            ];
        }

        // Return the evaluation results
        echo json_encode(["results" => $evaluationResults]);
    } catch (PDOException $e) {
        echo json_encode(["message" => "Error evaluating rules: " . $e->getMessage()]);
    }
}

// Function to evaluate individual conditions
function evaluateCondition($condition, $userData) {
    // Split the condition into parts
    preg_match('/\s*(\w+)\s*([<>=!]+)\s*(.+)/', $condition, $matches);

    if (count($matches) !== 4) {
        error_log("Invalid condition format: " . $condition);
        return false; // Invalid condition format
    }

    $attribute = $matches[1];
    $operator = $matches[2];
    $value = trim($matches[3], "'"); // Remove quotes for string comparisons

    // Retrieve the user data value
    $userValue = $userData[$attribute] ?? null;

    // Convert values to numeric if possible
    if (is_numeric($userValue)) {
        $userValue = (float)$userValue;
    }
    if (is_numeric($value)) {
        $value = (float)$value;
    }

    // Evaluate based on the operator
    switch ($operator) {
        case '<':
            return $userValue < $value;
        case '<=':
            return $userValue <= $value;
        case '>':
            return $userValue > $value;
        case '>=':
            return $userValue >= $value;
        case '=':
        case '==':
            return $userValue == $value;
        case '!=':
            return $userValue != $value;
        default:
            error_log("Unknown operator: " . $operator);
            return false; // Unknown operator
    }
}

// Function to evaluate a rule string against user data
function evaluateRuleString($ruleString, $userData) {
    // Split the rule string into individual conditions based on AND/OR
    $conditions = preg_split('/\s+(AND|OR)\s+/', $ruleString, -1, PREG_SPLIT_DELIM_CAPTURE);
    $result = null; // Initialize result
    $currentOperator = null;

    foreach ($conditions as $condition) {
        // If the condition is an operator (AND/OR), set it
        if (trim($condition) === 'AND' || trim($condition) === 'OR') {
            $currentOperator = trim($condition);
            continue;
        }

        // Evaluate the condition
        $conditionResult = evaluateCondition(trim($condition), $userData);

        // Initialize the result for the first condition
        if ($result === null) {
            $result = $conditionResult;
        } else {
            // Combine results based on the current operator
            if ($currentOperator === 'AND') {
                $result = $result && $conditionResult;
            } elseif ($currentOperator === 'OR') {
                $result = $result || $conditionResult;
            }
        }
    }

    // Return the result as a boolean
    return $result === true;
}


// Function to combine multiple rules into a single AST
function combineRules() {
    global $db;
    $inputData = json_decode(file_get_contents('php://input'), true);

    if (!isset($inputData['rule_ids']) || !is_array($inputData['rule_ids']) || !isset($inputData['operator'])) {
        echo json_encode(["message" => "Invalid input, rule_ids array and operator are required"]);
        return;
    }

    $ruleIds = $inputData['rule_ids'];
    $operator = strtoupper($inputData['operator']); // Convert to uppercase for consistency

    if (!in_array($operator, ['AND', 'OR', 'NOT'])) {
        echo json_encode(["message" => "Invalid operator. Use AND, OR, or NOT"]);
        return;
    }

    // Fetch the rule strings and rule names from the database for the specified rule IDs
    $placeholders = implode(',', array_fill(0, count($ruleIds), '?'));
    $query = "SELECT rule_name, rule_string FROM rules WHERE id IN ($placeholders)";
    $statement = $db->prepare($query);

    try {
        $statement->execute($ruleIds);
        $rules = $statement->fetchAll(PDO::FETCH_ASSOC);

        if (empty($rules)) {
            echo json_encode(["message" => "No rules found for the provided IDs"]);
            return;
        }

        // Convert each rule string into an AST node
        $astNodes = array_map(function ($rule) {
            return createASTNode($rule['rule_name'], $rule['rule_string']);
        }, $rules);

        // Combine the AST nodes using the specified operator
        $combinedAST = combineASTNodes($astNodes, $operator);

        // Return the root node of the combined AST
        echo json_encode(["combined_ast" => $combinedAST]);
    } catch (PDOException $e) {
        echo json_encode(["message" => "Error combining rules: " . $e->getMessage()]);
    }
}

// Helper function to create an AST node from a rule name and rule string
function createASTNode($ruleName, $ruleString) {
    return [
        "type" => "rule",
        "name" => $ruleName,
        "value" => $ruleString
    ];
}

// Helper function to combine multiple AST nodes based on an operator
function combineASTNodes($astNodes, $operator) {
    if ($operator === 'NOT') {
        // For NOT, wrap each node with a NOT operator
        return [
            "type" => "NOT",
            "children" => $astNodes
        ];
    } else {
        // For AND/OR, create a parent node with the operator
        return [
            "type" => $operator,
            "children" => $astNodes
        ];
    }
}


function updateRule() {
    global $db;

    // Get the input data (JSON)
    $inputData = json_decode(file_get_contents('php://input'), true);

    if (!isset($inputData['rule_id']) || !isset($inputData['rule_string'])) {
        echo json_encode(["message" => "Invalid input, rule_id and rule_string are required"]);
        return;
    }

    $ruleId = $inputData['rule_id'];
    $ruleString = $inputData['rule_string'];

    // Update the rule in the database
    $query = "UPDATE rules SET rule_string = :rule_string WHERE id = :rule_id";
    $statement = $db->prepare($query);
    $statement->bindParam(':rule_string', $ruleString);
    $statement->bindParam(':rule_id', $ruleId);

    try {
        $statement->execute();
        echo json_encode(["message" => "Rule updated successfully"]);
    } catch (PDOException $e) {
        echo json_encode(["message" => "Error updating rule: " . $e->getMessage()]);
    }
}
function modifySubExpression() {
    global $db;

    $inputData = json_decode(file_get_contents('php://input'), true);

    if (!isset($inputData['rule_id']) || !isset($inputData['old_expression']) || !isset($inputData['new_expression'])) {
        echo json_encode(["message" => "Invalid input, rule_id, old_expression, and new_expression are required"]);
        return;
    }

    $ruleId = $inputData['rule_id'];
    $oldExpression = $inputData['old_expression'];
    $newExpression = $inputData['new_expression'];

    // Fetch the current rule string from the database
    $query = "SELECT rule_string FROM rules WHERE id = :rule_id";
    $statement = $db->prepare($query);
    $statement->bindParam(':rule_id', $ruleId);
    $statement->execute();
    $ruleString = $statement->fetchColumn();

    if (!$ruleString) {
        echo json_encode(["message" => "Rule not found"]);
        return;
    }

    // Replace the old expression with the new expression
    $updatedRuleString = str_replace($oldExpression, $newExpression, $ruleString);

    // Update the rule in the database
    $updateQuery = "UPDATE rules SET rule_string = :rule_string WHERE id = :rule_id";
    $updateStatement = $db->prepare($updateQuery);
    $updateStatement->bindParam(':rule_string', $updatedRuleString);
    $updateStatement->bindParam(':rule_id', $ruleId);
    if (!validateRuleString($ruleString)) {
        return json_encode(["message" => "Invalid rule format"]);
    }
    try {
        $updateStatement->execute();

        // Check the number of affected rows
        if ($updateStatement->rowCount() > 0) {
            echo json_encode(["message" => "Sub-expression modified successfully"]);
        } else {
            echo json_encode(["message" => "No rows updated. Check if the rule ID exists."]);
        }
    } catch (PDOException $e) {
        echo json_encode(["message" => "Error modifying sub-expression: " . $e->getMessage()]);
    }
}
function deleteRule() {
    global $db;

    // Get the input data (JSON)
    $inputData = json_decode(file_get_contents('php://input'), true);

    if (!isset($inputData['rule_id'])) {
        echo json_encode(["message" => "Invalid input, rule_id is required"]);
        return;
    }

    $ruleId = $inputData['rule_id'];

    // Delete the rule from the database
    $query = "DELETE FROM rules WHERE id = :rule_id";
    $statement = $db->prepare($query);
    $statement->bindParam(':rule_id', $ruleId);

    try {
        $statement->execute();
        echo json_encode(["message" => "Rule deleted successfully"]);
    } catch (PDOException $e) {
        echo json_encode(["message" => "Error deleting rule: " . $e->getMessage()]);
    }
}
function validateRuleString($ruleString) {
    // Basic validation for rule string format
    return preg_match('/^((\w+\s*[<>=!]+\s*(\'[^\']*\'|\d+))\s*(AND|OR)\s*)*(\w+\s*[<>=!]+\s*(\'[^\']*\'|\d+))$/', $ruleString);
}
function modify_rule_condition($rule_id, $new_condition) {
    // Validate new condition
    if (!validateRuleString($new_condition)) {
        return json_encode(["message" => "Invalid condition format"]);
    }

    // Update the rule in the database
    // Example SQL: UPDATE rules SET rule_string = '$new_condition' WHERE id = $rule_id
}
function isValidAttribute($attribute) {
    global $db;
    $query = "SELECT COUNT(*) FROM attributes_catalog WHERE attribute_name = :attribute";
    $statement = $db->prepare($query);
    $statement->bindParam(':attribute', $attribute);
    $statement->execute();
    $count = $statement->fetchColumn();
    return $count > 0;
}
?> 