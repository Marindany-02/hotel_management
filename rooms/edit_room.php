<?php
session_start();
include 'db_connection.php';

// Fetch room details for editing
if (isset($_GET['id'])) {
    $room_id = $_GET['id'];
    $result = $conn->query("SELECT * FROM room WHERE id = $room_id");
    $room = $result->fetch_assoc();
}
?>

<!-- Edit Room Form -->
<div class="container d-flex justify-content-center align-items-center min-vh-100">
    <div class="card shadow-lg border-0 rounded-lg" style="max-width: 600px; width: 100%;">
        <div class="card-header bg-gradient text-white text-center py-4">
            <h3 class="card-title">Edit Room</h3>
        </div>
        <div class="card-body p-5">
            <form action="room_management.php" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="room_id" value="<?php echo $room['id']; ?>" />

                <!-- Room Name -->
                <div class="form-group mb-4">
                    <label for="room_name" class="form-label">Room Name:</label>
                    <input type="text" class="form-control form-control-lg input-shadow" name="room_name" id="room_name" value="<?php echo $room['room_name']; ?>" required />
                </div>

                <!-- Category -->
                <div class="form-group mb-4">
                    <label for="category_id" class="form-label">Category:</label>
                    <select class="form-select form-select-lg input-shadow" name="category_id" id="category_id" required>
                        <?php
                        $categories_result = $conn->query("SELECT * FROM room_category");
                        while ($category = $categories_result->fetch_assoc()) {
                            echo "<option value='{$category['id']}' " . ($category['id'] == $room['room_category_id'] ? 'selected' : '') . ">{$category['category']}</option>";
                        }
                        ?>
                    </select>
                </div>

                <!-- Capacity -->
                <div class="form-group mb-4">
                    <label for="capacity" class="form-label">Capacity:</label>
                    <input type="number" class="form-control form-control-lg input-shadow" name="capacity" id="capacity" value="<?php echo $room['room_capacity']; ?>" required />
                </div>

                <!-- Price -->
                <div class="form-group mb-4">
                    <label for="price" class="form-label">Price:</label>
                    <input type="number" class="form-control form-control-lg input-shadow" name="price" id="price" value="<?php echo $room['price']; ?>" required />
                </div>

                <!-- Room Image -->
                <div class="form-group mb-4">
                    <label for="image" class="form-label">Room Image:</label>
                    <input type="file" class="form-control form-control-lg input-shadow" name="image" id="image" />
                    <div class="mt-2">
                        <img src="../uploads/rooms/<?php echo $room['image']; ?>" alt="Room Image" class="img-fluid rounded shadow-sm" style="max-width: 100px;" />
                    </div>
                </div>

                <!-- Submit Button -->
                <button type="submit" name="edit_room" class="btn btn-success w-100 py-3 mt-3">Update Room</button>
            </form>
        </div>
    </div>
</div>

<!-- Custom Styling -->
<style>
    .container {
        max-width: 100%;
        padding: 20px;
    }

    .card {
        border-radius: 0.75rem;
        box-shadow: 0 6px 15px rgba(0, 0, 0, 0.1);
    }

    .card-header {
        background: linear-gradient(90deg, #1e9a45, #28b463);
        border-top-left-radius: 0.75rem;
        border-top-right-radius: 0.75rem;
    }

    .card-title {
        font-size: 1.8rem;
        font-weight: 600;
    }

    .form-label {
        font-weight: 600;
        font-size: 1.1rem;
    }

    .form-group {
        display: flex;
        flex-direction: column;
        margin-bottom: 1.5rem;
    }

    .form-control, .form-select {
        border-radius: 0.5rem;
        font-size: 1.1rem;
        padding: 15px 20px;
        box-sizing: border-box;
        transition: all 0.3s ease;
        width: 100%;
    }

    .form-control-lg {
        padding: 15px 20px;
    }

    .input-shadow {
        border: 1px solid #ccc;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    }

    .input-shadow:focus {
        border-color: #28a745;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        outline: none;
    }

    .btn-success {
        font-size: 1.2rem;
        font-weight: 500;
        padding: 15px;
        border-radius: 0.5rem;
        background-color: #28a745;
    }

    .img-fluid {
        max-width: 100px;
        border-radius: 0.5rem;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
    }
    
    /* Centering the form */
    .d-flex {
        display: flex;
        justify-content: center;
        align-items: center;
        min-height: 100vh;
    }

    .min-vh-100 {
        min-height: 100vh;
    }

</style>
