<?php
// Database connection settings
$host = 'localhost';       // Database server (use '127.0.0.1' if 'localhost' doesn't work)
$username = 'root';        // Database username (default is 'root')
$password = '';            // Database password (default is empty for localhost)
$database = 'MobileBillingSystem'; // Your database name

// Create a connection
$conn = new mysqli($host, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Success message (for testing; remove in production)
// echo "Connected successfully";
?>
