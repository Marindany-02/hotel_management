<?php
session_start();

// DB connection
$conn = new mysqli("localhost", "root", "", "hotel_management");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$showResetForm = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['verify'])) {
    $username = $_POST['username'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];

    $stmt = $conn->prepare("SELECT * FROM staff WHERE name=? AND email=? AND phone=?");
    $stmt->bind_param("sss", $username, $email, $phone);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $_SESSION['reset_user'] = $username;
        $showResetForm = true;
        $message = ["type" => "success", "text" => "User verified. Enter new password below."];
    } else {
        $message = ["type" => "danger", "text" => "User not found. Please check your details."];
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reset'])) {
    $newPassword = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $username = $_SESSION['reset_user'];

    $stmt = $conn->prepare("UPDATE staff SET password=? WHERE name=?");
    $stmt->bind_param("ss", $newPassword, $username);

    if ($stmt->execute()) {
        $message = ["type" => "success", "text" => "Password successfully updated!"];
        unset($_SESSION['reset_user']);
        $showResetForm = false;
    } else {
        $message = ["type" => "danger", "text" => "Error updating password. Try again."];
        $showResetForm = true;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Forgot Password | Grand Hotel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: #f8f9fa;
        }
        .hotel-header {
            background: #343a40;
            color: white;
            padding: 1.5rem;
            text-align: center;
            border-bottom: 5px solid #ffc107;
        }
        .hotel-header h2 {
            margin: 0;
        }
        .hotel-header p {
            margin: 0;
            font-size: 0.95rem;
        }
        .form-container {
            max-width: 500px;
            margin: 40px auto;
            padding: 30px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 0 12px rgba(0, 0, 0, 0.08);
        }
        .btn-primary, .btn-success {
            font-weight: bold;
        }
        .form-heading {
            font-size: 1.3rem;
            font-weight: 600;
            margin-bottom: 20px;
        }
        .footer-link {
            text-align: center;
            margin-top: 20px;
        }
    </style>
</head>
<body>

    <div class="hotel-header">
        <h2>Grand Hotel</h2>
        <p>📍 Mombasa Road, Nairobi | ☎️ +254 712 345 678 | ✉️ info@sunsetparadise.co.ke</p>
    </div>

    <div class="container">
        <div class="form-container">
            <?php if (isset($message)): ?>
                <div class="alert alert-<?= $message['type'] ?> alert-dismissible fade show" role="alert">
                    <?= $message['text'] ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <!-- Verification Form -->
            <form method="POST">
                <div class="form-heading">Forgot Password</div>

                <div class="mb-3">
                    <label class="form-label">Name (Full Name)</label>
                    <input type="text" name="username" class="form-control" value="<?= $_POST['username'] ?? '' ?>" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Email Address</label>
                    <input type="email" name="email" class="form-control" value="<?= $_POST['email'] ?? '' ?>" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Phone Number</label>
                    <input type="text" name="phone" class="form-control" value="<?= $_POST['phone'] ?? '' ?>" required>
                </div>
                <button type="submit" name="verify" class="btn btn-primary w-100">Verify Account</button>
            </form>

            <!-- Password Reset Form -->
            <form method="POST" id="resetForm" class="mt-4" <?= $showResetForm ? '' : 'style="display:none;"' ?>>
                <div class="form-heading">Set New Password</div>
                <div class="mb-3">
                    <label class="form-label">New Password</label>
                    <input type="password" name="password" class="form-control" required>
                </div>
                <button type="submit" name="reset" class="btn btn-success w-100">Reset Password</button>
            </form>

            <div class="footer-link">
                <a href="login.php" class="text-decoration-none">← Back to Login</a>
            </div>
        </div>
    </div>

    <script>
        <?php if ($showResetForm): ?>
            document.getElementById("resetForm").style.display = "block";
        <?php endif; ?>
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
