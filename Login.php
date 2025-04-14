<?php
session_start();

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
include('db_connection.php'); // Ensure this file connects to your database

$message = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST["username"]);
    $password = trim($_POST["password"]);

    $query = $conn->prepare("SELECT * FROM staff WHERE username = ?");
    $query->bind_param("s", $username);
    $query->execute();
    $result = $query->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();

        if (password_verify($password, $user['password'])) {
            $_SESSION["user_id"] = $user["id"];
            $_SESSION["username"] = $user["username"];
            $_SESSION["position"] = $user["position"];
            $_SESSION['flash'] = ['message' => 'Login successful!', 'type' => 'success'];
            header("Location: index.php");
            exit();
        } else {
            $message = "Invalid password!";
        }
    } else {
        $message = "User not found!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login - Grand Hotel System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f1f4f8;
        }
        .login-card {
            margin-top: 60px;
            padding: 30px;
            background: white;
            border-radius: 12px;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
        }
        .card-header {
            background: #0d6efd;
            color: white;
            text-align: center;
            padding: 15px;
            border-top-left-radius: 12px;
            border-top-right-radius: 12px;
        }
        .form-label {
            font-weight: 500;
        }
        .forgot-link {
            text-align: center;
            margin-top: 10px;
        }
        .hotel-header {
            background: #343a40;
            color: white;
            padding: 1.2rem;
            text-align: center;
            border-bottom: 5px solid #ffc107;
        }
        .hotel-header h2 {
            margin: 0;
            font-weight: bold;
        }
        .hotel-header p {
            margin: 0;
            font-size: 0.95rem;
        }
    </style>
</head>
<body>

    <div class="hotel-header">
        <h2>Grand Hotel</h2>
        <p>📍 Mombasa Road, Nairobi | ☎️ +254 712 345 678 | ✉️ info@sunsetparadise.co.ke</p>
    </div>

    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-5">
                <div class="login-card">
                    <div class="card-header">
                        <h4>Staff Login</h4>
                    </div>
                    <div class="card-body">
                        <?php if ($message): ?>
                            <div class="alert alert-danger"><?= $message ?></div>
                        <?php endif; ?>
                        <form method="POST" action="">
                            <div class="mb-3">
                                <label class="form-label">Username</label>
                                <input type="text" name="username" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Password</label>
                                <input type="password" name="password" class="form-control" required>
                            </div>
                            <button class="btn btn-primary w-100">Login</button>
                        </form>
                        <div class="forgot-link">
                            <a href="forget_password.php" class="text-decoration-none">Forgot Password?</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
