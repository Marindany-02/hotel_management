<?php
ob_start();
include('sidebar.php');

// Database connection
$host = 'localhost';
$user = 'root';
$pass = '';
$dbname = 'hotel_management';
$conn = new mysqli($host, $user, $pass, $dbname);
if ($conn->connect_error) {
    die('Connection failed: ' . $conn->connect_error);
}

$selected_report = $_GET['report_type'] ?? 'orders';
$customer_name = $_GET['customer_name'] ?? '';
$start_date = $_GET['start_date'] ?? '';
$end_date = $_GET['end_date'] ?? '';
$filter = '';

// Build filters
$date_filter = '';
$name_filter = '';

if (!empty($customer_name)) {
    if ($selected_report === 'orders') {
        $name_filter = "o.client_name LIKE '%$customer_name%'";
    } elseif ($selected_report === 'bookings') {
        $name_filter = "b.client_name LIKE '%$customer_name%'";
    } elseif ($selected_report === 'products') {
        $name_filter = "p.name LIKE '%$customer_name%'";
    }
}

// Handle date range
if (!empty($start_date) && !empty($end_date)) {
    if ($selected_report === 'orders') {
        $date_filter = "o.created_at BETWEEN '$start_date 00:00:00' AND '$end_date 23:59:59'";
    } elseif ($selected_report === 'bookings') {
        $date_filter = "b.check_in_date BETWEEN '$start_date' AND '$end_date'";
    }
} elseif (!empty($start_date)) {
    if ($selected_report === 'orders') {
        $date_filter = "DATE(o.created_at) = '$start_date'";
    } elseif ($selected_report === 'bookings') {
        $date_filter = "DATE(b.check_in_date) = '$start_date'";
    }
}

// Combine filters
$conditions = [];
if (!empty($name_filter)) $conditions[] = $name_filter;
if (!empty($date_filter)) $conditions[] = $date_filter;

if (!empty($conditions)) {
    $filter = 'WHERE ' . implode(' AND ', $conditions);
}

// Queries
$order_query = "
    SELECT id, seller_id, client_name, client_phone, payment_method, amount_paid, balance, total, created_at
    FROM orders o
    $filter
    ORDER BY created_at DESC
";

$booking_query = "
    SELECT id AS booking_id, client_name, room_name, number_of_days, expected_check_out, price, status
    FROM bookings b
    $filter
    ORDER BY expected_check_out DESC
";

$product_query = "
    SELECT p.id AS product_id, p.name, p.price, p.quantity
    FROM products p
    $filter
    ORDER BY p.name
";

// Fetch data
if ($selected_report == 'orders') {
    $result = $conn->query($order_query);
    $sum_query = "SELECT SUM(o.total) AS total_price FROM orders o $filter";
    $sum_result = $conn->query($sum_query);
    $total_price = $sum_result->fetch_assoc()['total_price'] ?? 0;
} elseif ($selected_report == 'bookings') {
    $result = $conn->query($booking_query);
    $sum_query = "SELECT SUM(b.price) AS total_price FROM bookings b $filter";
    $sum_result = $conn->query($sum_query);
    $total_price = $sum_result->fetch_assoc()['total_price'] ?? 0;
} elseif ($selected_report == 'products') {
    $result = $conn->query($product_query);
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Reports</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.19/jspdf.plugin.autotable.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
</head>
<body>

<div class="container mt-4">
    <h2 class="text-center">Reports</h2>

    <form method="get" class="mb-3">
        <div class="input-group">
            <select name="report_type" class="form-select" onchange="this.form.submit()">
                <option value="orders" <?= ($selected_report == 'orders') ? 'selected' : '' ?>>Orders Report</option>
                <option value="bookings" <?= ($selected_report == 'bookings') ? 'selected' : '' ?>>Bookings Report</option>
                <option value="products" <?= ($selected_report == 'products') ? 'selected' : '' ?>>Products Report</option>
            </select>
            <input type="text" name="customer_name" class="form-control" placeholder="Search by Name" value="<?= htmlspecialchars($customer_name) ?>">
            <input type="date" name="start_date" class="form-control" value="<?= htmlspecialchars($start_date) ?>">
            <input type="date" name="end_date" class="form-control" value="<?= htmlspecialchars($end_date) ?>">
            <button type="submit" class="btn btn-primary">Search</button>
        </div>
    </form>

    <div class="mb-3">
        <button id="exportToPdf" class="btn btn-success">Export to PDF</button>
    </div>

    <div id="reportContent">
        <?php
        if ($selected_report == 'orders') {
            echo '<h3>Orders Report</h3>';
            echo '<table class="table table-bordered" id="orderReportTable"><thead class="table-dark"><tr>
                    <th>Order ID</th><th>Seller ID</th><th>Client Name</th><th>Client Phone</th>
                    <th>Payment Method</th><th>Amount Paid</th><th>Balance</th><th>Total</th><th>Order Date</th>
                </tr></thead><tbody>';
            if ($result && $result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    echo "<tr>
                            <td>{$row['id']}</td><td>{$row['seller_id']}</td><td>{$row['client_name']}</td>
                            <td>{$row['client_phone']}</td><td>{$row['payment_method']}</td>
                            <td>" . number_format($row['amount_paid'], 2) . "</td>
                            <td>" . number_format($row['balance'], 2) . "</td>
                            <td>" . number_format($row['total'], 2) . "</td>
                            <td>{$row['created_at']}</td>
                        </tr>";
                }
            } else {
                echo "<tr><td colspan='9' class='text-center'>No orders found</td></tr>";
            }
            echo '</tbody></table>';
            echo "<h4>Total Revenue: " . number_format($total_price, 2) . "</h4>";

        } elseif ($selected_report == 'bookings') {
            echo '<h3>Bookings Report</h3>';
            echo '<table class="table table-bordered" id="bookingReportTable"><thead class="table-dark"><tr>
                    <th>Booking ID</th><th>Client Name</th><th>Room Number</th>
                    <th>Number of Days</th><th>Price</th><th>Status</th>
                </tr></thead><tbody>';
            if ($result && $result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    echo "<tr>
                            <td>{$row['booking_id']}</td><td>{$row['client_name']}</td><td>{$row['room_name']}</td>
                            <td>{$row['number_of_days']}</td><td>{$row['price']}</td><td>{$row['status']}</td>
                        </tr>";
                }
            } else {
                echo "<tr><td colspan='6' class='text-center'>No bookings found</td></tr>";
            }
            echo '</tbody></table>';
            echo "<h4>Total Price: " . number_format($total_price, 2) . "</h4>";

        } elseif ($selected_report == 'products') {
            echo '<h3>Products Report</h3>';
            echo '<table class="table table-bordered" id="productReportTable"><thead class="table-dark"><tr>
                    <th>Product ID</th><th>Product Name</th><th>Price</th><th>Quantity</th>
                </tr></thead><tbody>';
            if ($result && $result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    echo "<tr>
                            <td>{$row['product_id']}</td><td>{$row['name']}</td>
                            <td>{$row['price']}</td><td>{$row['quantity']}</td>
                        </tr>";
                }
            } else {
                echo "<tr><td colspan='4' class='text-center'>No products found</td></tr>";
            }
            echo '</tbody></table>';
        }
        ?>
    </div>
</div>

<script>
document.getElementById("exportToPdf").addEventListener("click", function () {
    const { jsPDF } = window.jspdf;
    const doc = new jsPDF();
    const table = document.querySelector('table');
    const rows = table.rows;

    const headers = Array.from(rows[0].cells).map(cell => cell.textContent);
    const data = Array.from(rows).slice(1).map(row => Array.from(row.cells).map(cell => cell.textContent));

    doc.autoTable({ head: [headers], body: data });
    doc.save('report.pdf');
});
</script>

</body>
</html>
