
<?php
// staff.php
include('sidebar.php');  // Include the sidebar at the top of your page
?>
<?php
if (!isset($_SESSION["user_id"])) {
    header("Location: ../login.php");
    exit();
}
?>
<?php
include 'db_connection.php';


// Add Booking
if (isset($_POST['add_booking'])) {
    $client_name = $_POST['client_name'];
    $client_phone = $_POST['client_phone'];
    $client_gender = $_POST['client_gender'];
    $building = $_POST['building'];
    $room_name = $_POST['room_name']; // Get the room name from the form
    $check_in_date = $_POST['check_in_date'];
    $number_of_days = $_POST['number_of_days'];
    $price = $_POST['price'];
    $expected_check_out = date('Y-m-d', strtotime($check_in_date . ' + ' . $number_of_days . ' days'));

    // Fetch the room ID from the database
    $room_query = "SELECT id FROM room WHERE room_name = ? AND building = ?";
    $stmt = $conn->prepare($room_query);
    $stmt->bind_param('ss', $room_name, $building);
    $stmt->execute();
    $room_result = $stmt->get_result();

    if ($room_result->num_rows > 0) {
        $room = $room_result->fetch_assoc();
        $room_id = $room['id']; // Get the room ID for the booking

        // Check if the room is available for the selected dates
        $check_booking_query = "
            SELECT * FROM bookings 
            WHERE room_id = ? 
            AND (? < expected_check_out 
            AND DATE_ADD(?, INTERVAL ? DAY) > check_in_date)
        ";
        $check_booking_stmt = $conn->prepare($check_booking_query);
        $check_booking_stmt->bind_param('isss', $room_id, $check_in_date, $check_in_date, $number_of_days);
        $check_booking_stmt->execute();
        $check_booking_result = $check_booking_stmt->get_result();

        if ($check_booking_result->num_rows > 0) {
            $_SESSION['error_message'] = "Room is already booked for the selected period.";
        } else {
            // Insert booking into the database
            $insert_query = "
                INSERT INTO bookings (client_name, client_phone, client_gender, building, room_id, room_name, check_in_date, number_of_days, price, expected_check_out) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ";
            $insert_stmt = $conn->prepare($insert_query);
            $insert_stmt->bind_param('sssssdsdss', $client_name, $client_phone, $client_gender, $building, $room_id, $room_name, $check_in_date, $number_of_days, $price, $expected_check_out);

            if ($insert_stmt->execute()) {
                $booking_id = $conn->insert_id;
                $_SESSION['success_message'] = "Booking added successfully!";
                header("Location: receipt.php?id=$booking_id");
                exit;
            } else {
                $_SESSION['error_message'] = "Error: " . $conn->error;
            }
        }
    } else {
        $_SESSION['error_message'] = "Selected room not found.";
    }
}



// Fetch all rooms
$rooms_result = $conn->query("SELECT * FROM room");

// Fetch all bookings
$bookings_result = $conn->query("SELECT * FROM bookings");

// Handle the AJAX request to fetch rooms by building
if (isset($_GET['fetch_rooms_by_building'])) {
    $building = $_GET['building'];

    // Fetch room names and their corresponding prices for the selected building
    $rooms_result = $conn->query("SELECT room_name, price FROM room WHERE building = '$building'");

    $rooms = [];
    while ($room = $rooms_result->fetch_assoc()) {
        $rooms[] = $room;  // Collect room data along with price
    }

    // Return rooms data as JSON
    echo json_encode($rooms);
    exit;
}

// Delete Booking
if (isset($_GET['delete_booking'])) {
    $booking_id = $_GET['delete_booking'];

    // Get room_id from booking before deleting to set it as available
    $getRoomQuery = $conn->query("SELECT room_id FROM bookings WHERE id = '$booking_id'");
    if ($getRoomQuery->num_rows > 0) {
        $room = $getRoomQuery->fetch_assoc();
        $room_id = $room['room_id'];

        // Delete booking
        if ($conn->query("DELETE FROM bookings WHERE id = '$booking_id'")) {
            // Set room status to available again
            $conn->query("UPDATE room SET status = 'available' WHERE id = '$room_id'");
            $_SESSION['success_message'] = "Booking deleted successfully.";
        } else {
            $_SESSION['error_message'] = "Failed to delete booking: " . $conn->error;
        }
    } else {
        $_SESSION['error_message'] = "Booking not found.";
    }

    header("Location: bookings.php");
    exit;
}

// Check-in Logic
if (isset($_GET['checkin_booking'])) {
    $booking_id = $_GET['checkin_booking'];

    // Update the booking status to 'Checked In'
    $sql = "UPDATE bookings SET status='checked-in' WHERE id=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $booking_id);

    if ($stmt->execute()) {
        echo "<script>alert('Booking successfully checked in!');</script>";
    } else {
        echo "<script>alert('Error: " . $stmt->error . "');</script>";
    }

    // Redirect back to avoid re-submitting the form on refresh
    header("Location: bookings.php");
    exit();
}

// Checkout Logic
if (isset($_GET['checkout_booking'])) {
    $booking_id = $_GET['checkout_booking'];

    // Update the booking status to 'Checked Out'
    $sql = "UPDATE bookings SET status='checked-out' WHERE id=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $booking_id);

    if ($stmt->execute()) {
        echo "<script>alert('Booking successfully checked out!');</script>";
    } else {
        echo "<script>alert('Error: " . $stmt->error . "');</script>";
    }

    // Redirect back to avoid re-submitting the form on refresh
    header("Location: bookings.php");
    exit();
}

// Edit Logic (optional)
if (isset($_POST['edit_booking'])) {
    $booking_id = $_POST['booking_id'];
    $client_name = $_POST['client_name'];
    $client_phone = $_POST['client_phone'];
    // Add any other fields you want to edit...

    // Update the booking with new values
    $sql = "UPDATE bookings SET client_name=?, client_phone=? WHERE id=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('ssi', $client_name, $client_phone, $booking_id);

    if ($stmt->execute()) {
        echo "<script>alert('Booking updated successfully!');</script>";
    } else {
        echo "<script>alert('Error: " . $stmt->error . "');</script>";
    }

    // Redirect back to the bookings page
    header("Location: bookings.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking Management</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        .alert {
            margin-top: 20px;
        }
        .modal-content {
            background-color: #f8f9fa;
        }
        .table th, .table td {
            text-align: center;
        }
        .modal-header {
            background-color: #4a6fa5;
            color: white;
        }
        .modal-footer {
            background-color: #f1f1f1;
        }
        .content-wrapper {
            margin-left: 380px;
            padding: 20px;
        }
        .card {
            margin-bottom: 20px;
            box-shadow: 0px 4px 6px rgba(0, 0, 0, 0.1);
        }
        .card-header {
            background-color: #4a6fa5;
            font-size: 1.2rem;
        }
        .card-body {
            background-color: #f8f9fa;
        }
        .modal-dialog {
            max-width: 800px;
        }
        .form-control {
            font-size: 14px;
        }
    </style>
</head>
<body>
    <!-- Main Content Wrapper -->
    <div class="content-wrapper">
        <h2>Booking Management</h2>

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

        <!-- Button to Open Modal -->
        <button class="btn btn-primary mb-4" data-bs-toggle="modal" data-bs-target="#addBookingModal">
            Add Booking
        </button>

        <!-- Add New Booking Modal -->
        <div class="modal fade" id="addBookingModal" tabindex="-1" aria-labelledby="addBookingModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="addBookingModalLabel">Add New Booking</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form method="POST">
                            <div class="row">
                                <div class="col-md-6">
                                    <input type="text" name="client_name" class="form-control mb-2" placeholder="Client's Name" required>
                                </div>
                                <div class="col-md-6">
                                    <input type="text" name="client_phone" class="form-control mb-2" placeholder="Client's Phone" required>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <select name="client_gender" class="form-control mb-2" required>
                                        <option value="">Select Gender</option>
                                        <option value="Male">Male</option>
                                        <option value="Female">Female</option>
                                        <option value="Other">Other</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <select name="building" class="form-control mb-2" id="building" required>
                                        <option value="">Select Building</option>
                                        <?php
                                        // Fetch distinct building names from the room table
                                        $buildings_result = $conn->query("SELECT DISTINCT building FROM room");
                                        while ($building = $buildings_result->fetch_assoc()) { ?>
                                            <option value="<?php echo $building['building']; ?>"><?php echo $building['building']; ?></option>
                                        <?php } ?>
                                    </select>
                                </div>
                                <div class="col-md-6">
    <select name="room_name" class="form-control mb-2" id="room_name" required>
        <option value="">Select Room</option>
        <?php
        // Fetch all rooms and their corresponding buildings and prices
        $rooms_result = $conn->query("SELECT room_name, building, price FROM room");
        while ($room = $rooms_result->fetch_assoc()) { ?>
            <option value="<?php echo $room['room_name']; ?>" 
                    data-building="<?php echo $room['building']; ?>" 
                    data-price="<?php echo $room['price']; ?>">
                <?php echo $room['room_name']; ?>
            </option>
        <?php } ?>
    </select>
</div>

                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <input type="date" name="check_in_date" class="form-control mb-2" required>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <input type="number" name="number_of_days" class="form-control mb-2" placeholder="Number of Days" required>
                                </div>
                                <div class="col-md-6">
                                    <input type="number" name="price" class="form-control mb-2" placeholder="Price" id="price" required readonly>
                                </div>
                            </div>

                            <button type="submit" name="add_booking" class="btn btn-success mt-3">Add Booking</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Search Bar for Filtering Bookings -->
        <form method="GET" class="mb-4">
            <div class="input-group">
                <input type="text" name="search" class="form-control" placeholder="Search by client name..." value="<?php echo isset($_GET['search']) ? $_GET['search'] : ''; ?>">
                <button type="submit" class="btn btn-primary">Search</button>
            </div>
        </form>

        <!-- Filter by Status -->
        <form method="GET" class="mb-4">
            <select name="status_filter" class="form-control">
                <option value="">All Statuses</option>
                <option value="checked-in">Checked-in</option>
                <option value="checked-out">Checked-out</option>
                <option value="canceled">Canceled</option>
                <option value="booked">Booked</option>
            </select>
            <button type="submit" class="btn btn-primary">Filter</button>
        </form>

        <!-- Bookings List -->
        <h5>Existing Bookings</h5>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Client Name</th>
                    <th>Phone</th>
                    <th>Gender</th>
                    <th>Building</th>
                    <th>Room</th>
                    <th>Check-in Date</th>
                    <th>No. of Days</th>
                    <th>Price</th>
                    <th>Expected Check-out</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php
                // Filter by client name or status if search or status_filter is set
                $search_query = isset($_GET['search']) ? $_GET['search'] : '';
                $status_filter = isset($_GET['status_filter']) ? $_GET['status_filter'] : '';

                $sql = "SELECT * FROM bookings WHERE client_name LIKE ?";

                // Add status filter if needed
                if ($status_filter) {
                    $sql .= " AND status = ?";
                }
                
                $stmt = $conn->prepare($sql);

                // Bind parameters
                if ($status_filter) {
                    $search_query_param = "%" . $search_query . "%";
                    $stmt->bind_param('ss', $search_query_param, $status_filter);
                } else {
                    $search_query_param = "%" . $search_query . "%";
                    $stmt->bind_param('s', $search_query_param);
                }

                $stmt->execute();
                $result = $stmt->get_result();

                while ($booking = $result->fetch_assoc()) { ?>
                    <tr>
                        <td><?php echo $booking['id']; ?></td>
                        <td><?php echo $booking['client_name']; ?></td>
                        <td><?php echo $booking['client_phone']; ?></td>
                        <td><?php echo $booking['client_gender']; ?></td>
                        <td><?php echo $booking['building']; ?></td>
                        <td><?php echo $booking['room_name']; ?></td>
                        <td><?php echo $booking['check_in_date']; ?></td>
                        <td><?php echo $booking['number_of_days']; ?></td>
                        <td><?php echo $booking['price']; ?></td>
                        <td><?php echo $booking['expected_check_out']; ?></td>
                        <td><?php echo $booking['status']; ?></td>
                        <td>
                            <a href="bookings.php?checkin_booking=<?php echo $booking['id']; ?>" class="btn btn-success btn-sm">Check-in</a>
                            <a href="bookings.php?checkout_booking=<?php echo $booking['id']; ?>" class="btn btn-danger btn-sm">Check-out</a>
                            <a href="bookings.php?delete_booking=<?php echo $booking['id']; ?>" class="btn btn-warning btn-sm">Delete</a>
                            <a href="edit_booking.php?id=<?php echo $booking['id']; ?>" class="btn btn-warning">
                        <i class="fas fa-edit"></i> Edit
                    </a>
                        </td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    document.addEventListener("DOMContentLoaded", function() {
        const roomSelect = document.getElementById('room_name');
        const priceInput = document.querySelector('[name="price"]');
        const numberOfDaysInput = document.querySelector('[name="number_of_days"]');
        const buildingSelect = document.getElementById('building');

        // Function to update the price based on room selection and number of days
        function updatePrice() {
            const selectedOption = roomSelect.options[roomSelect.selectedIndex];
            const pricePerNight = selectedOption ? selectedOption.getAttribute('data-price') : 0;
            const numberOfDays = numberOfDaysInput.value;

            if (pricePerNight && numberOfDays) {
                const totalPrice = pricePerNight * numberOfDays;
                priceInput.value = totalPrice;
            } else {
                priceInput.value = ''; // Clear price if room is not selected or days are missing
            }
        }

        // Event listener for room selection change
        roomSelect.addEventListener('change', function() {
            updatePrice();
        });

        // Event listener for number of days input change
        numberOfDaysInput.addEventListener('input', function() {
            updatePrice();
        });

        // Event listener for building selection change
        buildingSelect.addEventListener('change', function() {
            var building = this.value;
            fetchRoomsByBuilding(building);
        });

        // Fetch rooms by building using AJAX
        function fetchRoomsByBuilding(building) {
            fetch(`?fetch_rooms_by_building=true&building=${building}`)
                .then(response => response.json())
                .then(data => {
                    roomSelect.innerHTML = '<option value="">Select Room</option>'; // Clear current options

                    if (data.length === 0) {
                        const noRoomsOption = document.createElement('option');
                        noRoomsOption.textContent = "No rooms available for this building.";
                        noRoomsOption.disabled = true;
                        roomSelect.appendChild(noRoomsOption);
                        return;
                    }

                    data.forEach(room => {
                        var option = document.createElement('option');
                        option.value = room.room_id; // Store room_id in value
                        option.setAttribute('data-price', room.price); // Store price in a data attribute
                        option.textContent = `${room.room_name} (ID: ${room.room_id})`; // Display both room name and ID
                        roomSelect.appendChild(option);
                    });
                })
                .catch(error => {
                    console.error("Error fetching rooms:", error);
                });
        }
    });
</script>

    <script>
        // Update price when room is selected
        document.getElementById('building').addEventListener('change', function() {
            var building = this.value;
            fetchRoomsByBuilding(building);
        });

        document.getElementById('room_name').addEventListener('change', function() {
            var selectedRoom = this.options[this.selectedIndex];
            var price = selectedRoom.getAttribute('data-price');
            document.getElementById('price').value = price;
        });

        function fetchRoomsByBuilding(building) {
            fetch(`?fetch_rooms_by_building=true&building=${building}`)
                .then(response => response.json())
                .then(data => {
                    var roomSelect = document.getElementById('room_name');
                    roomSelect.innerHTML = '<option value="">Select Room</option>';
                    data.forEach(room => {
                        var option = document.createElement('option');
                        option.value = room.room_name;
                        option.setAttribute('data-price', room.price);
                        option.textContent = room.room_name;
                        roomSelect.appendChild(option);
                    });
                });
        }
        // Wait for the page to fully load
document.addEventListener("DOMContentLoaded", function() {
    // Get the room_name select element
    const roomSelect = document.getElementById('room_name');
    
    // Add an event listener to the select element
    roomSelect.addEventListener('change', function() {
        // Get the selected option
        const selectedOption = roomSelect.options[roomSelect.selectedIndex];

        // Get the price from the selected option's data-price attribute
        const price = selectedOption.getAttribute('data-price');
        
        // Autofill the price field
        const priceInput = document.querySelector('[name="price"]');
        if (priceInput) {
            priceInput.value = price * document.querySelector('[name="number_of_days"]').value;
        }
    });

    // Listen for changes in the number of days field to update price accordingly
    const daysInput = document.querySelector('[name="number_of_days"]');
    if (daysInput) {
        daysInput.addEventListener('input', function() {
            const selectedOption = roomSelect.options[roomSelect.selectedIndex];
            const price = selectedOption.getAttribute('data-price');
            const priceInput = document.querySelector('[name="price"]');
            if (priceInput && price) {
                priceInput.value = price * daysInput.value;
            }
        });
    }
});

    </script>
    
</body>
</html>
