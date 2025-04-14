<?php
include 'db.php';

$id = $_POST['id'];
$name = $_POST['name'];
$desc = $_POST['description'];
$category = $_POST['category'];
$price = $_POST['price'];
$quantity = $_POST['quantity'];
$image_url = $_POST['image_url'];

$stmt = $conn->prepare("UPDATE products SET name=?, description=?, category=?, price=?, quantity=?, image_url=? WHERE id=?");
$stmt->bind_param("sssdssi", $name, $desc, $category, $price, $quantity, $image_url, $id);

if ($stmt->execute()) {
    echo "Product updated successfully. <a href='list_products.php'>Back to List</a>";
} else {
    echo "Error: " . $conn->error;
}
?>
