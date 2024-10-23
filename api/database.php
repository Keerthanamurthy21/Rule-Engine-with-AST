<?php
// Database configuration
$host = 'localhost';
$db_name = 'rule_engine_db'; // Replace with your actual database name
$username = 'root'; // Use 'root' as the default username for XAMPP
$password = ''; // Default password for XAMPP is empty

try {
    // Create a new PDO instance
    $db = new PDO("mysql:host=$host;dbname=$db_name", $username, $password);
    // Set the PDO error mode to exception
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    // Display an error message if the connection fails
    echo "Connection failed: " . $e->getMessage();
    exit;
}
?>
