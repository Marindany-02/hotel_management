<?php
header('Content-Type: application/json');
include 'db_connection.php';

if ($conn->connect_error) {
    echo json_encode(["success" => false, "message" => "DB connection failed"]);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);

$seller_id = $data['seller_id'];
$client_name = $conn->real_escape_string($data['client_name']);
$client_phone = $conn->real_escape_string($data['client_phone']);
$payment_method = $data['payment_method'];
$amount_paid = $data['amount_paid'];
$balance = $data['balance'];
$total = $data['total'];
$items = $data['items'];

$date = date('Y-m-d H:i:s');

$conn->begin_transaction(); // START TRANSACTION

try {
    // 1. Insert order
    $stmt = $conn->prepare("INSERT INTO orders (seller_id, client_name, client_phone, payment_method, amount_paid, balance, total, created_at)
                            VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("isssddds", $seller_id, $client_name, $client_phone, $payment_method, $amount_paid, $balance, $total, $date);
    if (!$stmt->execute()) {
        throw new Exception("Failed to insert order.");
    }

    $order_id = $conn->insert_id;

    // 2. Insert order items and update product quantities
    $item_stmt = $conn->prepare("INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)");
    $update_product_stmt = $conn->prepare("UPDATE products SET quantity = quantity - ? WHERE id = ? AND quantity >= ?");

    foreach ($items as $item) {
        $product_id = $item['id'];
        $quantity = $item['quantity'];
        $price = $item['price'];

        // Insert into order_items
        $item_stmt->bind_param("iiid", $order_id, $product_id, $quantity, $price);
        if (!$item_stmt->execute()) {
            throw new Exception("Failed to insert order item.");
        }

        // Update products table (only if enough stock)
        $update_product_stmt->bind_param("iii", $quantity, $product_id, $quantity);
        $update_product_stmt->execute();

        if ($update_product_stmt->affected_rows === 0) {
            throw new Exception("Not enough stock for product ID $product_id.");
        }
    }

    $conn->commit(); // ✅ All good
    echo json_encode(["success" => true, "order_id" => $order_id]);

} catch (Exception $e) {
    $conn->rollback(); // ❌ Rollback on failure
    echo json_encode(["success" => false, "message" =>  $e->getMessage()]);
}

$conn->close();
?>
