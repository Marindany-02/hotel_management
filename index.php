<?php
session_start();
if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}
?>
<?php
if (isset($_SESSION['flash'])):
    $type = $_SESSION['flash']['type'];
    $message = $_SESSION['flash']['message'];
?>
    <div id="flash-message" class="alert alert-<?= $type ?> alert-dismissible fade show mt-3 mx-3" role="alert">
        <?= $message ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>

    <script>
        // Auto-hide alert after 3 seconds
        setTimeout(() => {
            const flash = document.getElementById('flash-message');
            if (flash) {
                flash.classList.remove('show');
                flash.classList.add('fade');
            }
        }, 3000);
    </script>
<?php
unset($_SESSION['flash']);
endif;
?>

<?php
include 'db_connection.php';

// Room status data
$status_result = $conn->query("SELECT 
    SUM(CASE WHEN status = 'booked' THEN 1 ELSE 0 END) AS booked,
    SUM(CASE WHEN status = 'checked-in' THEN 1 ELSE 0 END) AS checked_in,
    SUM(CASE WHEN status = 'checked-out' THEN 1 ELSE 0 END) AS checked_out
    FROM bookings");
$status_data = $status_result->fetch_assoc();

// Booking income (today)
$booking_income_result = $conn->query("SELECT SUM(price) AS total_booking_income 
                                       FROM bookings 
                                       WHERE DATE(check_in_date) = CURDATE()");
$total_booking_income = $booking_income_result->fetch_assoc()['total_booking_income'] ?? 0;

// Order income (today)
$order_income_result = $conn->query("SELECT SUM(amount_paid) AS total_order_income 
                                     FROM orders 
                                     WHERE DATE(created_at) = CURDATE()");
$total_order_income = $order_income_result->fetch_assoc()['total_order_income'] ?? 0;

// Combined total revenue today
$total_revenue_today = $total_booking_income + $total_order_income;

// Total orders count (today)
$order_count_result = $conn->query("SELECT COUNT(*) AS total_orders_today 
                                    FROM orders 
                                    WHERE DATE(created_at) = CURDATE()");
$total_orders_today = $order_count_result->fetch_assoc()['total_orders_today'] ?? 0;

// Recent bookings
$recent_bookings_result = $conn->query("SELECT * FROM bookings ORDER BY check_in_date DESC LIMIT 3");
$recent_bookings = [];
while ($row = $recent_bookings_result->fetch_assoc()) {
    $recent_bookings[] = $row;
}

// Recent orders
$recent_orders_result = $conn->query("SELECT * FROM orders ORDER BY created_at DESC LIMIT 3");
$recent_orders = [];
while ($row = $recent_orders_result->fetch_assoc()) {
    $recent_orders[] = $row;
}

// Fetch low stock products (Qty < 10)
$low_stock_result = $conn->query("SELECT name, quantity FROM products WHERE quantity < 500");
$low_stock_data = [];
while ($row = $low_stock_result->fetch_assoc()) {
    $low_stock_data[] = $row;
}
// Monthly cumulative sales: orders + bookings
$monthly_orders_query = $conn->query("SELECT SUM(amount_paid) AS orders_sales 
                                      FROM orders 
                                      WHERE MONTH(created_at) = MONTH(CURDATE()) 
                                        AND YEAR(created_at) = YEAR(CURDATE())");

$monthly_bookings_query = $conn->query("SELECT SUM(price) AS bookings_income 
                                        FROM bookings 
                                        WHERE MONTH(check_in_date) = MONTH(CURDATE()) 
                                          AND YEAR(check_in_date) = YEAR(CURDATE())");

$orders_sales = $monthly_orders_query->fetch_assoc()['orders_sales'] ?? 0;
$bookings_income = $monthly_bookings_query->fetch_assoc()['bookings_income'] ?? 0;

$monthly_total_sales = $orders_sales + $bookings_income;

?>
<?php
// Monthly Revenue Overview (combined bookings + orders)
$monthly_revenue = array_fill(1, 12, 0); // Initialize Jan–Dec to 0

$monthly_revenue_query = $conn->query("
    SELECT 
        MONTH(check_in_date) AS month, 
        SUM(price) AS revenue 
    FROM bookings 
    WHERE YEAR(check_in_date) = YEAR(CURDATE()) 
    GROUP BY MONTH(check_in_date)
");
while ($row = $monthly_revenue_query->fetch_assoc()) {
    $monthly_revenue[(int)$row['month']] += (float)$row['revenue'];
}

$order_monthly_query = $conn->query("
    SELECT 
        MONTH(created_at) AS month, 
        SUM(amount_paid) AS revenue 
    FROM orders 
    WHERE YEAR(created_at) = YEAR(CURDATE()) 
    GROUP BY MONTH(created_at)
");
while ($row = $order_monthly_query->fetch_assoc()) {
    $monthly_revenue[(int)$row['month']] += (float)$row['revenue'];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hotel Management System</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="manifest" href="manifest.json">
    <!-- Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .content-wrapper {
            margin-left: 260px;
            padding: 20px;
        }
    </style>
</head>
<body>
    <div class="app-container">
        <!-- Sidebar Navigation -->
        <nav class="sidebar">
        <div class="sidebar-header">
            <h2>Grand Hotel</h2>
            <div class="connection-status online">
                <span class="status-text">Welcome!</span>
            </div>
        </div>
            <ul class="sidebar-menu">
                <li class="active" data-page="dashboard"><i class="fas fa-tachometer-alt"></i> Dashboard</li>
                <li><a href="products/pos.php"><i class="fas fa-cash-register"></i>Point of Sale</a></li>
                <li><i class="fas fa-bed"></i><a href="rooms/room_management.php">Room Management</a></li>
                <li><i class="fas fa-calendar-check"></i><a href="rooms/bookings.php">Bookings</a></li>
                <li><a href="staff.php"><i class="fas fa-user-tie"></i>Staff</a></li>
                <li><a href="reports.php"><i class="fas fa-chart-bar"></i> Reports</a></li>
            </ul>
            <div class="sidebar-footer">
            <div class="user-info">
    <div class="user-details">
        <p class="user-name">
            <?php echo isset($_SESSION['username']) ? htmlspecialchars($_SESSION['username']) : 'Guest'; ?>
        </p>
        <p class="position">
            <?php echo isset($_SESSION['position']) ? htmlspecialchars($_SESSION['position']) : 'Role'; ?>
        </p>
    </div>
</div>
<button> <a href="logout.php" class="btn btn-danger"><i class="fas fa-sign-out-alt"></i>Logout</a></button>
            </div>
        </nav>

        <!-- Main Content Area -->
        <main class="main-content">
            <!-- Dashboard Page -->
            <section id="dashboard" class="page active">
    <div class="page-header">
        <h1>Dashboard</h1>
        <p class="current-date"><?php echo date("F j, Y"); ?></p>
    </div>
    <div class="dashboard-stats">
    <div class="stat-card">
    <div class="stat-icon"><i class="fas fa-bed"></i></div>
    <div class="stat-info">
        <h3>Room Status</h3>
        
            <strong>Booked:</strong> <?php echo $status_data['booked']; ?> &nbsp; &nbsp;
            <strong>Checked In:</strong> <?php echo $status_data['checked_in']; ?> &nbsp; &nbsp;
            <strong>Checked Out:</strong> <?php echo $status_data['checked_out']; ?> &nbsp; &nbsp;
    </div>
</div>
                <div class="stat-card">
                <div class="stat-icon"><i class="fas fa-cash-register"></i></div>
                <div class="stat-info">
                    <h3 class="card-title text-muted">Today's Bookings Income</h3>
                    <h2 class="text-success">Ksh <?= number_format($total_booking_income, 2) ?></h2>
                    </div>
                </div>

        <div class="stat-card">
    <div class="stat-icon"><i class="fas fa-cash-register"></i></div>
    <div class="stat-info">
        <h3>Today's Sales From POS</h3>
        <p class="stat-value">Ksh <?php echo number_format($total_order_income, 2); ?></p>
        <p class="stat-detail">
            <strong>Transactions:</strong> <?php echo $total_orders_today; ?> 
        </p>
    </div>
</div>
<div class="stat-card">
    <div class="stat-icon"><i class="fas fa-chart-line"></i></div>
    <div class="stat-info">
        <h3>This Month's Cumulative Sales</h3>
        <p class="stat-value">Ksh <?php echo number_format($monthly_total_sales, 2); ?></p>
        <p class="stat-detail">
            POS: Ksh <?php echo number_format($orders_sales, 2); ?> |
            Bookings: Ksh <?php echo number_format($bookings_income, 2); ?>
        </p>
    </div>
</div>
    
    </div>
    <div class="dashboard-charts">
    <div class="chart-container">
        <h3>Revenue Overview</h3>
        <canvas id="revenueChart"></canvas>
    </div>
    <div class="chart-container">
        <h3>Low Stock Products</h3>
        <canvas id="lowStockChart"></canvas>
    </div>
</div>

    <div class="recent-activities">
                    <h3>Recent Activities</h3>
                    <div class="recent-bookings">
                        <h4>Recent Bookings</h4>
                        <ul class="activity-list">
                            <?php foreach ($recent_bookings as $booking) { ?>
                                <li class="activity-item">
                                    <strong><?php echo $booking['client_name']; ?></strong> booked a room (Room ID: <?php echo $booking['room_id']; ?>) on <?php echo $booking['check_in_date']; ?>
                                </li>
                            <?php } ?>
                        </ul>
                    </div>

                    <div class="recent-orders">
                        <h4>Recent Orders</h4>
                        <ul class="activity-list">
                            <?php foreach ($recent_orders as $order) { ?>
                                <li class="activity-item">
                                    Order #<?php echo $order['id']; ?> (Ksh <?php echo number_format($order['amount_paid'], 2); ?>) on <?php echo $order['created_at']; ?>
                                </li>
                            <?php } ?>
                        </ul>
                    </div>
</section>


    <!-- Scripts -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.7.0/chart.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
    <script src="jdb.js"></script>
    <script src="pos/pos.js"></script>
    <script src="reports.js"></script>
    <script src="js/offline.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
   <!-- Scripts -->
   <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.7.0/chart.min.js"></script>
   <script>
    const monthlyRevenueData = <?php echo json_encode(array_values($monthly_revenue)); ?>;
    const months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 
                    'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];

    const ctx = document.getElementById('revenueChart').getContext('2d');
    const revenueLineChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: months,
            datasets: [{
                label: 'Monthly Revenue (Ksh)',
                data: monthlyRevenueData,
                fill: true,
                backgroundColor: 'rgba(54, 162, 235, 0.1)',
                borderColor: 'rgba(54, 162, 235, 1)',
                tension: 0.3,
                pointBackgroundColor: 'rgba(54, 162, 235, 1)',
                pointRadius: 4,
                pointHoverRadius: 6
            }]
        },
        options: {
            responsive: true,
            plugins: {
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return 'Ksh ' + context.parsed.y.toLocaleString();
                        }
                    }
                },
                legend: {
                    display: true
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return 'Ksh ' + value.toLocaleString();
                        }
                    }
                }
            }
        }
    });
</script>


<script>
    // Low Stock Chart
    const lowStockProducts = <?php echo json_encode($low_stock_data); ?>;
    const productNames = lowStockProducts.map(product => product.name);
    const productQuantities = lowStockProducts.map(product => product.quantity);

    var ctxLowStock = document.getElementById('lowStockChart').getContext('2d');
    var lowStockChart = new Chart(ctxLowStock, {
        type: 'bar',
        data: {
            labels: productNames,
            datasets: [{
                label: 'Low Stock (Qty < 500)',
                data: productQuantities,
                backgroundColor: 'rgba(255, 99, 132, 0.5)',
                borderColor: 'rgba(255, 99, 132, 1)',
                borderWidth: 2
            }]
        },
        options: {
            responsive: true,
            plugins: {
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return context.parsed.y + ' items';
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    stepSize: 1
                }
            }
        }
    });
</script>



</body>
</html>