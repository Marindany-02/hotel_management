<?php
// get-stock.php
header('Content-Type: application/json');

// Database connection
require_once 'db_config.php'; // Ensure you have the correct DB configuration

// Get the product ID from the request
$productId = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($productId > 0) {
    try {
        // Prepare and execute query to fetch product stock
        $stmt = $pdo->prepare("SELECT quantity FROM products WHERE id = :id");
        $stmt->bindParam(':id', $productId, PDO::PARAM_INT);
        $stmt->execute();

        // Check if the product exists in the database
        if ($stmt->rowCount() > 0) {
            $product = $stmt->fetch(PDO::FETCH_ASSOC);
            echo json_encode(['stock' => $product['quantity']]);
        } else {
            echo json_encode(['error' => 'No product found with the given ID']);
        }
    } catch (PDOException $e) {
        // If there's a database error, log it and send a response
        error_log('Error fetching stock: ' . $e->getMessage());
        echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['error' => 'Invalid product ID']);
}
?>
