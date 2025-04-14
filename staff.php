<?php
// staff.php
include('sidebar.php');  // Include the sidebar at the top of your page
?>
<?php

// Database Connection
$servername = "localhost";
$username = "root";
$password = "";
$database = "hotel_management";

$conn = new mysqli($servername, $username, $password, $database);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle Staff Addition
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add'])) {
    $name = $_POST['name'];
    $username = $_POST['username'];
    $position = $_POST['position'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    $stmt = $conn->prepare("INSERT INTO staff (name,username, position, email, phone, password) VALUES (?,?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssss", $name,$username, $position, $email, $phone, $password);
    
    if ($stmt->execute()) {
        $_SESSION['message'] = ["type" => "success", "text" => "Staff added successfully!"];
    } else {
        $_SESSION['message'] = ["type" => "error", "text" => "Failed to add staff."];
    }

    // Debugging: Check if message is set correctly
    // var_dump($_SESSION['message']); // Uncomment this to see the message content

    // Redirect to refresh page
    header("Location: staff.php");
    exit();
}

// Handle Staff Update
if (isset($_POST['update'])) {
    $id = $_POST['id'];
    $name = $_POST['name'];
    $username = $_POST['username'];
    $position = $_POST['position'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    $stmt = $conn->prepare("UPDATE staff SET name=?,username=?, position=?, email=?, phone=?, password=? WHERE id=?");
    $stmt->bind_param("sssssi", $name,$username, $position, $email, $phone, $password, $id);
    if ($stmt->execute()) {
        $_SESSION['message'] = ["type" => "success", "text" => "Staff updated successfully!"];
    } else {
        $_SESSION['message'] = ["type" => "error", "text" => "Failed to update staff."];
    }

    // Debugging: Check if message is set correctly
    // var_dump($_SESSION['message']); // Uncomment this to see the message content

    header("Location: staff.php");
    exit();
}

// Handle Staff Deletion
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    if ($conn->query("DELETE FROM staff WHERE id=$id")) {
        $_SESSION['message'] = ["type" => "success", "text" => "Staff deleted successfully!"];
    } else {
        $_SESSION['message'] = ["type" => "error", "text" => "Failed to delete staff."];
    }

    // Debugging: Check if message is set correctly
    // var_dump($_SESSION['message']); // Uncomment this to see the message content

    header("Location: staff.php");
    exit();
}

// Fetch Staff List
$result = $conn->query("SELECT * FROM staff");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Staff Management</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
</head>
<body class="container mt-5">
    <h2 class="mb-4 text-center">Staff Management</h2>

    <!-- Display Success or Error Message -->
    <?php
    if (isset($_SESSION['message'])) {
        $message = $_SESSION['message'];
        $alertClass = $message['type'] == 'success' ? 'alert-success' : 'alert-danger';
        echo "<div class='alert $alertClass alert-dismissible fade show' role='alert'>
                {$message['text']}
                <button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button>
              </div>";
        unset($_SESSION['message']); // Clear message after displaying
    }
    ?>

    <!-- Add Staff Button -->
    <button class="btn btn-success mb-3" data-bs-toggle="modal" data-bs-target="#addStaffModal">Add Staff</button>

    <h3 class="mt-4">Staff List</h3>
    <table class="table table-bordered">
        <thead class="table-dark">
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Username</th>
                <th>Position</th>
                <th>Email</th>
                <th>Phone</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($staff = $result->fetch_assoc()) { ?>
            <tr>
                <td><?= $staff['id'] ?></td>
                <td><?= htmlspecialchars($staff['name']) ?></td>
                <td><?= htmlspecialchars($staff['username']) ?></td>
                <td><?= htmlspecialchars($staff['position']) ?></td>
                <td><?= htmlspecialchars($staff['email']) ?></td>
                <td><?= htmlspecialchars($staff['phone']) ?></td>
                <td>
                    <!-- Edit Button (Update Modal) -->
                    <button class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#updateStaffModal" data-id="<?= $staff['id'] ?>" data-name="<?= htmlspecialchars($staff['name']) ?>" data-position="<?= htmlspecialchars($staff['position']) ?>" data-email="<?= htmlspecialchars($staff['email']) ?>" data-phone="<?= htmlspecialchars($staff['phone']) ?>"><i class="fas fa-edit"></i></button>
                    
                    <!-- Delete Button -->
                    <a href="staff.php?delete=<?= $staff['id'] ?>" class="btn btn-danger btn-sm"><i class="fas fa-trash"></i></a>
                </td>
            </tr>
            <?php } ?>
        </tbody>
    </table>

    <!-- Add Staff Modal -->
    <div class="modal fade" id="addStaffModal" tabindex="-1" aria-labelledby="addStaffModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addStaffModalLabel">Add New Staff</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form action="" method="POST">
                        <div class="mb-3">
                            <label class="form-label">Name</label>
                            <input type="text" class="form-control" name="name" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Username</label>
                            <input type="text" class="form-control" name="username" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Position</label>
                            <input type="text" class="form-control" name="position" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" class="form-control" name="email" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Phone</label>
                            <input type="text" class="form-control" name="phone" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Password</label>
                            <input type="password" class="form-control" name="password" required>
                        </div>
                        <button type="submit" name="add" class="btn btn-primary w-100">Add Staff</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Update Staff Modal -->
    <div class="modal fade" id="updateStaffModal" tabindex="-1" aria-labelledby="updateStaffModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="updateStaffModalLabel">Update Staff</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form action="" method="POST">
                        <input type="hidden" name="id" id="updateId">
                        <div class="mb-3">
                            <label class="form-label">Name</label>
                            <input type="text" class="form-control" name="name" id="updateName" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Username</label>
                            <input type="text" class="form-control" name="username" id="updateUserName" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Position</label>
                            <input type="text" class="form-control" name="position" id="updatePosition" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" class="form-control" name="email" id="updateEmail" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Phone</label>
                            <input type="text" class="form-control" name="phone" id="updatePhone" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Password</label>
                            <input type="password" class="form-control" name="password" id="updatePassword" required>
                        </div>
                        <button type="submit" name="update" class="btn btn-primary w-100">Update Staff</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
    // Auto-hide notification after 3 seconds
    setTimeout(function() {
        var notification = document.getElementById('notification');
        if (notification) {
            notification.classList.add('fade');
            notification.classList.remove('show');
        }
    }, 3000);

    // Fill the update modal with staff data
    document.querySelectorAll('[data-bs-target="#updateStaffModal"]').forEach(button => {
        button.addEventListener('click', () => {
            document.getElementById('updateId').value = button.getAttribute('data-id');
            document.getElementById('updateName').value = button.getAttribute('data-name');
            document.getElementById('updateUserName').value = button.getAttribute('data-username');
            document.getElementById('updatePosition').value = button.getAttribute('data-position');
            document.getElementById('updateEmail').value = button.getAttribute('data-email');
            document.getElementById('updatePhone').value = button.getAttribute('data-phone');
        });
    });
</script>

</body>
</html>
