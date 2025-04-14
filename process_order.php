<?php
header('Content-Type: application/json');
include 'db_connection.php';

$data = json_decode(file_get_contents("php://input"), true);

if (!$data) {
    echo json_encode(["success" => false, "message" => "Invalid request."]);
    exit();
}

$customer_name = $data["customer_name"] ?? "";
$room_number = $data["room_number"] ?? "";
$staff_id = $data["staff_id"] ?? "";
$cart = $data["cart"] ?? [];

if (!$customer_name || !$staff_id || empty($cart)) {
    echo json_encode(["success" => false, "message" => "Missing required order details."]);
    exit();
}

$conn->begin_transaction();
try {
    $stmt = $conn->prepare("INSERT INTO orders (customer_name, room_number, staff_id, order_date) VALUES (?, ?, ?, NOW())");
    $stmt->bind_param("ssi", $customer_name, $room_number, $staff_id);
    $stmt->execute();
    $order_id = $stmt->insert_id;

    $stmt = $conn->prepare("INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)");
    foreach ($cart as $item) {
        $stmt->bind_param("iiid", $order_id, $item["id"], $item["quantity"], $item["price"]);
        $stmt->execute();

        $stmt_stock = $conn->prepare("UPDATE products SET quantity = quantity - ? WHERE id = ?");
        $stmt_stock->bind_param("ii", $item["quantity"], $item["id"]);
        $stmt_stock->execute();
    }

    $conn->commit();

    // Return receipt data
    echo json_encode([
        "success" => true,
        "message" => "Order processed successfully!",
        "order_id" => $order_id,
        "customer_name" => $customer_name,
        "room_number" => $room_number,
        "date" => date("Y-m-d H:i:s"),
        "cart" => $cart
    ]);
} catch (Exception $e) {
    $conn->rollback();
    echo json_encode(["success" => false, "message" => $e->getMessage()]);
}
?>
