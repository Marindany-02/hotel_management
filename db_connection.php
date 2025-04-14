<?php
$servername = "localhost"; // Change if your database is hosted elsewhere
$username = "root"; // Update with your database username
$password = ""; // Update with your database password
$database = "hotel_management"; // Ensure this matches your database name

// Create connection
$conn = new mysqli($servername, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
