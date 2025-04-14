<?php
session_start();
include 'db_connection.php';

// Fetch the booking details
if (isset($_GET['id'])) {
    $booking_id = $_GET['id'];

    // Fetch the booking details from the database
    $booking_query = "SELECT * FROM bookings WHERE id = ?";
    $stmt = $conn->prepare($booking_query);
    $stmt->bind_param('i', $booking_id);
    $stmt->execute();
    $booking_result = $stmt->get_result();

    if ($booking_result->num_rows > 0) {
        $booking = $booking_result->fetch_assoc();
    } else {
        $_SESSION['error_message'] = "Booking not found.";
        header('Location: bookings.php');
        exit();
    }
} else {
    $_SESSION['error_message'] = "Invalid booking ID.";
    header('Location: bookings.php');
    exit();
}

// Handle the edit booking form submission
if (isset($_POST['edit_booking'])) {
    $client_name = $_POST['client_name'];
    $client_phone = $_POST['client_phone'];
    $client_gender = $_POST['client_gender'];
    $building = $_POST['building'];
    $room_name = $_POST['room_name'];
    $check_in_date = $_POST['check_in_date'];
    $number_of_days = $_POST['number_of_days'];
    $price = $_POST['price'];
    $expected_check_out = date('Y-m-d', strtotime($check_in_date . ' + ' . $number_of_days . ' days'));

    // Update the booking details in the database
    $update_query = "
        UPDATE bookings
        SET client_name = ?, client_phone = ?, client_gender = ?, building = ?, room_name = ?, check_in_date = ?, number_of_days = ?, price = ?, expected_check_out = ?
        WHERE id = ?
    ";

    $stmt = $conn->prepare($update_query);
    $stmt->bind_param('ssssssdsdi', $client_name, $client_phone, $client_gender, $building, $room_name, $check_in_date, $number_of_days, $price, $expected_check_out, $booking_id);

    if ($stmt->execute()) {
        $_SESSION['success_message'] = "Booking updated successfully!";
        header('Location: bookings.php');
        exit();
    } else {
        $_SESSION['error_message'] = "Error: " . $stmt->error;
    }
}

// Fetch all buildings and rooms for selection
$buildings_result = $conn->query("SELECT DISTINCT building FROM room");
$rooms_result = $conn->query("SELECT room_name, building FROM room");

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Booking</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f7f7f7;
            font-family: Arial, sans-serif;
        }

        .container {
            max-width: 800px;
            background-color: #fff;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .form-control, .btn {
            border-radius: 5px;
        }

        .btn-back {
            margin-top: 20px;
        }

        .alert {
            margin-top: 20px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .heading {
            text-align: center;
            margin-bottom: 30px;
        }

        .form-control:focus {
            box-shadow: none;
            border-color: #007bff;
        }
    </style>
</head>
<body>
    <div class="container mt-5">
        <h2 class="heading">Edit Booking</h2>

        <!-- Success/Error Messages -->
        <?php
        if (isset($_SESSION['success_message'])) {
            echo "<div class='alert alert-success'>" . $_SESSION['success_message'] . "</div>";
            unset($_SESSION['success_message']);
        }
        if (isset($_SESSION['error_message'])) {
            echo "<div class='alert alert-danger'>" . $_SESSION['error_message'] . "</div>";
            unset($_SESSION['error_message']);
        }
        ?>

        <!-- Edit Booking Form -->
        <form method="POST">
            <div class="form-group">
                <input type="text" name="client_name" class="form-control" placeholder="Client's Name" value="<?php echo $booking['client_name']; ?>" required>
            </div>
            <div class="form-group">
                <input type="text" name="client_phone" class="form-control" placeholder="Client's Phone" value="<?php echo $booking['client_phone']; ?>" required>
            </div>
            <div class="form-group">
                <select name="client_gender" class="form-control" required>
                    <option value="Male" <?php if ($booking['client_gender'] == 'Male') echo 'selected'; ?>>Male</option>
                    <option value="Female" <?php if ($booking['client_gender'] == 'Female') echo 'selected'; ?>>Female</option>
                    <option value="Other" <?php if ($booking['client_gender'] == 'Other') echo 'selected'; ?>>Other</option>
                </select>
            </div>
            <div class="form-group">
                <select name="building" class="form-control" id="building" required>
                    <option value="">Select Building</option>
                    <?php
                    while ($building = $buildings_result->fetch_assoc()) { ?>
                        <option value="<?php echo $building['building']; ?>" <?php if ($building['building'] == $booking['building']) echo 'selected'; ?>>
                            <?php echo $building['building']; ?>
                        </option>
                    <?php } ?>
                </select>
            </div>
            <div class="form-group">
                <select name="room_name" class="form-control" id="room_name" required>
                    <option value="">Select Room</option>
                    <?php
                    while ($room = $rooms_result->fetch_assoc()) {
                        if ($room['building'] == $booking['building']) {
                            ?>
                            <option value="<?php echo $room['room_name']; ?>" <?php if ($room['room_name'] == $booking['room_name']) echo 'selected'; ?>>
                                <?php echo $room['room_name']; ?>
                            </option>
                        <?php }
                    } ?>
                </select>
            </div>
            <div class="form-group">
                <input type="date" name="check_in_date" class="form-control" value="<?php echo $booking['check_in_date']; ?>" required>
            </div>
            <div class="form-group">
                <input type="number" name="number_of_days" class="form-control" placeholder="Number of Days" value="<?php echo $booking['number_of_days']; ?>" required>
            </div>
            <div class="form-group">
                <input type="number" name="price" class="form-control" placeholder="Price" value="<?php echo $booking['price']; ?>" required>
            </div>

            <button type="submit" name="edit_booking" class="btn btn-primary btn-block">Update Booking</button>
        </form>

        <!-- Back to Bookings Button -->
        <a href="bookings.php" class="btn btn-secondary btn-block btn-back">Back to Bookings</a>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        // AJAX to update room names when the building is changed
        document.getElementById('building').addEventListener('change', function () {
            let building = this.value;

            fetch('?fetch_rooms_by_building=true&building=' + building)
                .then(response => response.json())
                .then(rooms => {
                    let roomSelect = document.getElementById('room_name');
                    roomSelect.innerHTML = '<option value="">Select Room</option>';  // Clear existing options
                    rooms.forEach(room => {
                        roomSelect.innerHTML += `<option value="${room.room_name}">${room.room_name} - Price: ${room.price}</option>`;
                    });
                });
        });
    </script>
</body>
</html>
