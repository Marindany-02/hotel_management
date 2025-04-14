<?php
session_start();
// now you can access $_SESSION variables
?>

<!-- sidebar.php -->
<head>
    <!-- Link to Sidebar Specific CSS -->
    <link rel="stylesheet" href="css/sidebar.css">
    <style>
        /* Sidebar Active Link Styling */
        .sidebar-menu li a.active {
            background-color: #3a5a8c; /* Change this to your preferred color */
            color: #fff;
            font-weight: bold;
        }

        .sidebar-menu li a.active i {
            color: #fff;
        }
    </style>
</head>

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
            <li><a href="index.php"><i class="fas fa-tachometer-alt"></i>Dashboard</a></li>
            <li><a href="products/pos.php"><i class="fas fa-cash-register"></i>Point Of Sale</a></li>
            <li data-page="rooms"><a href="rooms/room_management.php"><i class="fas fa-bed"></i>Room Management</a></li>
            <li><a href="rooms/bookings.php"><i class="fas fa-calendar-check"></i>Bookings</a></li>
            <li data-page="staff"><a href="staff.php"><i class="fas fa-user-tie"></i>Staff</a></li>
            <li data-page="rooms"><a href="reports.php"><i class="fas fa-chart-bar"></i>Reports</a></li>
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
</div>

<!-- JavaScript to make active state work -->
<script>
    // Sidebar Active Link Toggle
    document.querySelectorAll('.sidebar-menu li a').forEach(item => {
        item.addEventListener('click', function() {
            // Remove active class from all links
            document.querySelectorAll('.sidebar-menu li a').forEach(link => {
                link.classList.remove('active');
            });

            // Add active class to the clicked link
            this.classList.add('active');
        });
    });
    // Automatically set the active class based on the current page
document.querySelectorAll('.sidebar-menu li a').forEach(item => {
    if (item.href === window.location.href) {
        item.classList.add('active');
    }
});

</script>
