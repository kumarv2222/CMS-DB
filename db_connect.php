<?php 
// Database configuration
$host = 'localhost:4307';  // Let MySQL use default port
$username = 'root';   // Your MySQL username
$password = '';       // Your MySQL password (blank in this case)
$database = 'cms_db1'; // Your database name

try {
    $conn = new mysqli($host, $username, $password, $database);
    
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    
    // Set charset to ensure proper handling of special characters
    $conn->set_charset("utf8mb4");
    
} catch (Exception $e) {
    die("Connection failed: " . $e->getMessage());
}
?>