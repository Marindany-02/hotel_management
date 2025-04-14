<?php
include 'db_connection.php';

$category = isset($_GET['category']) ? $_GET['category'] : 'all';

// Query to fetch products based on the category
if ($category == 'all') {
    $sql = "SELECT * FROM products";
} else {
    $sql = "SELECT * FROM products WHERE category = ?";
}

$stmt = $conn->prepare($sql);

// If the category is not 'all', bind the parameter
if ($category != 'all') {
    $stmt->bind_param('s', $category);
}

$stmt->execute();
$result = $stmt->get_result();

$products = [];
while ($row = $result->fetch_assoc()) {
    $products[] = $row;
}

echo json_encode($products);

$stmt->close();
$conn->close();
?>
