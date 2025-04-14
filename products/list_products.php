<?php
include 'db.php';

$sql = "SELECT * FROM products";
$result = $conn->query($sql);

echo "<h2>Product List</h2><a href='add_product_form.php'>Add Product</a><table border='1'>
<tr><th>ID</th><th>Name</th><th>Category</th><th>Price</th><th>Quantity</th><th>Actions</th></tr>";

while ($row = $result->fetch_assoc()) {
    echo "<tr>
        <td>{$row['id']}</td>
        <td>{$row['name']}</td>
        <td>{$row['category']}</td>
        <td>\${$row['price']}</td>
        <td>{$row['quantity']}</td>
        <td>
            <a href='edit_product_form.php?id={$row['id']}'>Edit</a> |
            <a href='delete_product.php?id={$row['id']}' onclick=\"return confirm('Are you sure?')\">Delete</a>
        </td>
    </tr>";
}
echo "</table>";
?>
