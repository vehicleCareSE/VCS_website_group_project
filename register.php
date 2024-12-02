<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT);

    // Connect to the database
    $conn = new mysqli('localhost', 'root', '', 'vehicle_care_system');
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Check if the email already exists
    $stmt = $conn->prepare("SELECT email FROM users WHERE email = ?");
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $error = "Email already exists. Please use a different email.";
    } else {
        // Insert the new user if the email doesn't exist
        $stmt = $conn->prepare("INSERT INTO users (name, email, password) VALUES (?, ?, ?)");
        $stmt->bind_param('sss', $name, $email, $password);

        if ($stmt->execute()) {
            header('Location: login.php'); // Redirect to the login page
            exit();
        } else {
            $error = "Error: " . $stmt->error;
        }
    }

    $stmt->close();
    $conn->close();
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Register</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body class="img-background">
    <div class="main-border">
        <h2>Register</h2>

        <!-- Display Error Message -->
        <?php if (isset($error)) echo "<p style='color: red; font-size:20px'>$error</p>"; ?>

        <form method="POST">
            <input type="text" name="name" placeholder="Name" required id="text"><br>
            <input type="email" name="email" placeholder="Email" required id="text"><br>
            <input type="password" name="password" placeholder="Password" required id="text"><br>
            <button type="submit">Register</button>
        </form>

        <!-- login Button -->
        <div style="margin-top: 20px; text-align: center;">
            <a href="login.php" style="text-decoration: none; font-size: 20px; color: white; background-color: #007bff; padding: 10px 20px; border-radius: 20px;">Login</a>
        </div>
    </div>

</body>
</html>
