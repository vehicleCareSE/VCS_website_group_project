<?php
session_start();
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'login') {
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Connect to MySQL database
    $conn = new mysqli('localhost', 'root', '', 'vehicle_care_system');
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Prepare the query to fetch the user's data
    $stmt = $conn->prepare("SELECT user_id, name, password, role FROM users WHERE email = ?");
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $stmt->store_result(); // Store result to count rows

    // Check if user exists
    if ($stmt->num_rows > 0) {
        $stmt->bind_result($user_id, $name, $hashed_password, $role);
        $stmt->fetch();

        // Verify the password
        if (password_verify($password, $hashed_password)) {
            // Set session variables
            $_SESSION['user_id'] = $user_id;
            $_SESSION['name'] = $name;
            $_SESSION['role'] = $role;

            // Redirect to home page
            header('Location: home.php');
            exit();
        } else {
            // Password mismatch
            $error = "Invalid login credentials!";
        }
    } else {
        // User not found
        $error = "Invalid login credentials!";
    }

    $stmt->close();
    $conn->close();
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'forgot_password') {
    $email = $_POST['email'];

    // Connect to MySQL database
    $conn = new mysqli('localhost', 'root', '', 'vehicle_care_system');
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Check if email exists
    $stmt = $conn->prepare("SELECT email FROM users WHERE email = ?");
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        // Simulate sending a password reset link (replace this with actual email logic)
        $success = "A password reset link has been sent to your email.";
    } else {
        $error = "Email not found!";
    }

    $stmt->close();
    $conn->close();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Login</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body class="img-background">
    <div class="main-border">
        <h2>Login</h2>
        <?php if (isset($error)) echo "<p style='color: red; font-size:20px; text-align:center;'>$error</p>"; ?>
        <?php if (isset($success)) echo "<p style='color: green; font-size:20px; text-align:center;'>$success</p>"; ?>

        <!-- Login Form -->
        <form method="POST">
            <input type="hidden" name="action" value="login">
            <input type="email" name="email" placeholder="Email" required id="text"><br>
            <input type="password" name="password" placeholder="Password" required id="text"><br>
            <button type="submit">Login</button>
        </form>

        <!-- Forgot Password Form -->
        <form method="POST" style="margin-top: 20px;">
            <input type="hidden" name="action" value="forgot_password">
            <input type="email" name="email" placeholder="Enter your email to reset password" required id="text"><br>
            <button type="submit" style="background-color: #f39c12;">Forgot Password</button>
        </form>

        <!-- Register Button -->
        <div style="margin-top: 20px; text-align: center;">
            <a href="register.php" style="text-decoration: none; font-size: 20px; color: white; background-color: #007bff; padding: 10px 20px; border-radius: 20px;">Register</a>
        </div>
    </div>
</body>
</html>

