<?php
include('db_connection.php');

// Fetch booking details from the database based on the booking ID
if (isset($_GET['id'])) {
    $booking_id = $_GET['id'];

    // Query to fetch booking details
    $booking_query = "SELECT * FROM bookings WHERE id = ?";
    $stmt = $conn->prepare($booking_query);
    $stmt->bind_param('i', $booking_id);
    $stmt->execute();
    $booking_result = $stmt->get_result();

    if ($booking_result->num_rows > 0) {
        $booking = $booking_result->fetch_assoc();
    } else {
        echo "Booking not found.";
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking Receipt</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <style>
        .receipt-container {
            margin-top: 50px;
            padding: 30px;
            background-color: #f8f9fa;
            box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1);
        }
        .hotel-info {
            margin-bottom: 30px;
        }
        .hotel-info h3 {
            font-size: 1.5rem;
            color: #4a6fa5;
        }
    </style>
</head>
<body>

<div class="container receipt-container">
    <div class="hotel-info">
        <h3>Hotel XYZ</h3>
        <p>123 Main Street, City, Country</p>
        <p>Email: info@hotelxyz.com | Phone: +1234567890</p>
    </div>

    <h4>Booking Receipt</h4>
    <p><strong>Booking ID:</strong> #<?php echo $booking['id']; ?></p>
    <p><strong>Client Name:</strong> <?php echo $booking['client_name']; ?></p>
    <p><strong>Client Phone:</strong> <?php echo $booking['client_phone']; ?></p>
    <p><strong>Client Gender:</strong> <?php echo $booking['client_gender']; ?></p>
    <p><strong>Room Name:</strong> <?php echo $booking['room_name']; ?></p>
    <p><strong>Building:</strong> <?php echo $booking['building']; ?></p>
    <p><strong>Check-in Date:</strong> <?php echo $booking['check_in_date']; ?></p>
    <p><strong>Number of Days:</strong> <?php echo $booking['number_of_days']; ?></p>
    <p><strong>Price per Day:</strong> $<?php echo $booking['price']; ?></p>
    <p><strong>Total Price:</strong> $<?php echo $booking['price'] * $booking['number_of_days']; ?></p>
    <p><strong>Expected Check-out:</strong> <?php echo $booking['expected_check_out']; ?></p>
    
    <div class="d-flex justify-content-between">
        <button onclick="window.location.href='bookings.php'" class="btn btn-primary">Back to Bookings</button>
        <button onclick="window.print()" class="btn btn-secondary">Download Receipt</button>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
