<?php
// staff.php
include('sidebar.php');  // Include the sidebar at the top of your page
?>
<?php
include 'db_connection.php';

// Add category
if (isset($_POST['add_category'])) {
    $category_name = $_POST['category_name'];
    if ($conn->query("INSERT INTO room_category (category) VALUES ('$category_name')")) {
        $_SESSION['message'] = "Category added successfully!";
        $_SESSION['msg_type'] = "success";
    } else {
        $_SESSION['message'] = "Error adding category: " . $conn->error;
        $_SESSION['msg_type'] = "danger";
    }
}

// Add room with image upload and capacity
if (isset($_POST['add_room'])) {
    $building = $_POST['building'];
    $category_id = $_POST['category_id'];
    $capacity = $_POST['room_capacity'];
    $price = $_POST['price'];
    $image = $_FILES['image']['name'];
    $image_tmp = $_FILES['image']['tmp_name'];
    $image_path = "../uploads/rooms/" . $image;
    $room_name = $_POST['room_name'];
    $range_start = $_POST['range_start'];
    $range_end = $_POST['range_end'];


    // Upload image (if provided)
    if (!empty($image)) {
        move_uploaded_file($image_tmp, $image_path);
    } else {
        $image = "default.png"; // fallback
    }

    if (!empty($range_start) && !empty($range_end) && !empty($room_name)) {
        // Bulk insert with custom prefix (e.g. LTE)
        for ($i = $range_start; $i <= $range_end; $i++) {
            $auto_name = $room_name . " " . $i;
            $conn->query("INSERT INTO room (room_name, building, room_category_id, room_capacity, price, image) 
                          VALUES ('$auto_name', '$building', '$category_id', '$capacity', '$price', '$image')");
        }
        $_SESSION['message'] = "Bulk rooms added!";
        $_SESSION['msg_type'] = "success";
    }
     elseif (!empty($room_name)) {
        // Single insert
        $conn->query("INSERT INTO room (room_name, building, room_category_id, room_capacity, price, image) 
                      VALUES ('$room_name', '$building', '$category_id', '$capacity', '$price', '$image')");
        $_SESSION['message'] = "Room added!";
        $_SESSION['msg_type'] = "success";
    } else {
        $_SESSION['message'] = "Provide room name or range!";
        $_SESSION['msg_type'] = "danger";
    }
}



// Edit room
if (isset($_POST['edit_room'])) {
    $room_id = $_POST['room_id']; // Get the room ID from the form
    $room_name = $_POST['room_name']; // Get the updated room name from the form
    $category_id = $_POST['category_id']; // Get the updated category ID from the form
    $capacity = $_POST['capacity'];
    $price = $_POST['price']; // Get the updated room capacity from the form

    // Debugging: Check if the variables are set properly
    if (empty($room_id) || empty($room_name) || empty($category_id) || empty($capacity)) {
        $_SESSION['message'] = "All fields are required!";
        $_SESSION['msg_type'] = "danger";
    } else {
        // Update the room in the database
        $update_query = "UPDATE room SET room_name='$room_name', room_category_id='$category_id', room_capacity='$capacity',price='$price' WHERE id=$room_id";

        // Check if the update query is successful
        if ($conn->query($update_query)) {
            $_SESSION['message'] = "Room updated successfully!";
            $_SESSION['msg_type'] = "success";
        } else {
            // Error: If the query fails, show the error
            $_SESSION['message'] = "Error updating room: " . $conn->error;
            $_SESSION['msg_type'] = "danger";
        }
    }
}

// Display success or error message
if (isset($_SESSION['message'])) {
    echo '<div class="alert alert-' . $_SESSION['msg_type'] . ' alert-dismissible fade show" role="alert">
            ' . $_SESSION['message'] . '
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
          </div>';
    unset($_SESSION['message']);
    unset($_SESSION['msg_type']);
}

// Delete room
if (isset($_GET['delete_room'])) {
    $room_id = $_GET['delete_room'];
    if ($conn->query("DELETE FROM room WHERE id=$room_id")) {
        $_SESSION['message'] = "Room deleted successfully!";
        $_SESSION['msg_type'] = "success";
    } else {
        $_SESSION['message'] = "Error deleting room: " . $conn->error;
        $_SESSION['msg_type'] = "danger";
    }
}

// Delete category
if (isset($_GET['delete_category'])) {
    $category_id = $_GET['delete_category'];
    if ($conn->query("DELETE FROM room_category WHERE id=$category_id")) {
        $_SESSION['message'] = "Category deleted successfully!";
        $_SESSION['msg_type'] = "success";
    } else {
        $_SESSION['message'] = "Error deleting category: " . $conn->error;
        $_SESSION['msg_type'] = "danger";
    }
}

// Fetch categories
$categories_result = $conn->query("SELECT * FROM room_category");
if (!$categories_result) {
    die("Error fetching categories: " . $conn->error);
}

// Fetch rooms
$rooms_result = $conn->query("SELECT * FROM room");
if (!$rooms_result) {
    die("Error fetching rooms: " . $conn->error);
}

// Display success or error message
if (isset($_SESSION['message'])) {
    echo '<div class="alert alert-' . $_SESSION['msg_type'] . ' alert-dismissible fade show" role="alert">
            ' . $_SESSION['message'] . '
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
          </div>';
    unset($_SESSION['message']);
    unset($_SESSION['msg_type']);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Room Management</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <style>
        .card-deck {
            display: flex;
            justify-content: space-between;
        }
        .card {
            width: 48%;
        }
        .form-container {
            margin-top: 20px;
        }
        .form-container h5 {
            margin-bottom: 15px;
        }
        .room-image {
            width: 50px;  /* Image thumbnail size */
            height: 50px;
            object-fit: cover;
        }
        .list-group-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .icon-button {
            font-size: 1.5rem;
            margin: 0 5px;
        }
    </style>
</head>
<body>
    <div class="container mt-5">
        <h2>Room Management</h2>

        <!-- Cards for Categories and Rooms CRUD -->
        <div class="card-deck mb-5">
            <!-- Category Card -->
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Manage Categories</h5>
                    <form method="POST" class="mb-3">
                        <input type="text" name="category_name" class="form-control mb-2" placeholder="New Category Name" required>
                        <button type="submit" name="add_category" class="btn btn-primary" style="background-color: #28a745; color: white; padding: 10px 20px; border: none; border-radius: 5px; font-size: 16px;">Add Category</button>
                    </form>
                    <h6>Categories List</h6>
                    <ul class="list-group">
                        <?php while ($category = $categories_result->fetch_assoc()) { ?>
                            <li class="list-group-item d-flex justify-content-between">
                                <?php echo $category['category']; ?>
                                <a href="?delete_category=<?php echo $category['id']; ?>" class="btn btn-danger btn-sm icon-button"><i class="fas fa-trash"></i></a>
                            </li>
                        <?php } ?>
                    </ul>
                </div>
            </div>

            <!-- Room Card -->
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Manage Rooms</h5>
                    <div class="card">
    <div class="card-body">
        <h5 class="card-title">Add Room(s)</h5>
        <form method="POST" enctype="multipart/form-data">
            <input type="text" name="building" class="form-control mb-2" placeholder="Building Name" required>
            <input type="number" name="room_capacity" class="form-control mb-2" placeholder="Room Capacity" required>
            <input type="number" step="0.01" name="price" class="form-control mb-2" placeholder="Room Price" required>
            <select name="category_id" class="form-control mb-2" required>
                <option value="">Select Category</option>
                <?php 
                $categories_result->data_seek(0);
                while ($row = $categories_result->fetch_assoc()) { ?>
                    <option value="<?php echo $row['id']; ?>"><?php echo $row['category']; ?></option>
                <?php } ?>
            </select>
            <input type="file" name="image" class="form-control mb-2">
            
            <input type="text" name="room_name" class="form-control mb-2" placeholder="Room Name (e.g. R1)" >
            <div class="d-flex justify-content-between mb-2">
                <input type="number" name="range_start" class="form-control me-1" placeholder="Range Start (e.g. 1)">
                <input type="number" name="range_end" class="form-control ms-1" placeholder="Range End (e.g. 27)">
            </div>

            <button type="submit" name="add_room" class="btn btn-success">Add Room(s)</button>
        </form>
    </div>
</div>

                    <h6>Rooms List</h6>
                    <ul class="list-group">
                        <?php while ($room = $rooms_result->fetch_assoc()) { ?>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
    <img src="../uploads/rooms/<?php echo $room['image']; ?>" alt="Room Image" class="room-image">
    <?php echo $room['room_name']; ?> 
    (<?php echo $room['room_capacity']; ?>) - 
    Ksh <?php echo number_format($room['price'], 2); ?>
    <div>
    <a href="edit_room.php?id=<?php echo $room['id']; ?>" class="btn btn-primary"><i class="fa-solid fa-pen"></i></a>
        <a href="?delete_room=<?php echo $room['id']; ?>" class="btn btn-danger btn-sm icon-button"><i class="fas fa-trash"></i></a>
    </div>
</li>

                        <?php } ?>
                    </ul>
                </div>
            </div>
        </div>
    </div>
    <!-- Edit Room Modal -->
<!-- Edit Room Modal -->
<div class="modal fade" id="editRoomModal" tabindex="-1" aria-labelledby="editRoomModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="editRoomModalLabel">Edit Room</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <!-- Edit Room Form -->
        <form method="POST" action="room_management.php">
          <input type="hidden" name="room_id" id="edit_room_id">
          <div class="mb-3">
            <label for="edit_room_name" class="form-label">Room Name</label>
            <input type="text" class="form-control" id="edit_room_name" name="room_name" required>
          </div>
          <div class="mb-3">
            <label for="edit_category_id" class="form-label">Room Category</label>
            <select class="form-select" id="edit_category_id" name="category_id" required>
              <!-- Categories will be populated via PHP -->
              <?php
                while ($category = $categories_result->fetch_assoc()) {
                    echo "<option value='{$category['id']}'>{$category['category']}</option>";
                }
              ?>
            </select>
          </div>
          <div class="mb-3">
            <label for="edit_capacity" class="form-label">Capacity</label>
            <input type="number" class="form-control" id="edit_capacity" name="capacity" required>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            <button type="submit" name="edit_room" class="btn btn-primary">Update Room</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>


    <script>
    // Wait until the page is fully loaded
    window.onload = function() {
        // If the alert message exists, set a timeout to hide it after 3 seconds
        var message = document.getElementById('message');
        if (message) {
            setTimeout(function() {
                message.style.display = 'none';
            }, 3000); // 3000ms = 3 seconds
        }
    };
</script>
<script>
  // Event listener to populate the modal with room data when "Edit" button is clicked
  document.querySelectorAll('.edit-btn').forEach(button => {
    button.addEventListener('click', function() {
      // Get data attributes from the clicked button
      const roomId = this.getAttribute('data-id');
      const roomName = this.getAttribute('data-name');
      const categoryId = this.getAttribute('data-category');
      const capacity = this.getAttribute('data-capacity');
      
      // Populate modal fields with data
      document.getElementById('edit_room_id').value = roomId;
      document.getElementById('edit_room_name').value = roomName;
      document.getElementById('edit_category_id').value = categoryId;
      document.getElementById('edit_capacity').value = capacity;
    });
  });
</script>


</body>
</html>
