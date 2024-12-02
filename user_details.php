<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$conn = new mysqli('localhost', 'root', '', 'vehicle_care_system');
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

// Handle form submission to update user details
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $phone = $_POST['phone'];
    $city = $_POST['city'];
    $dob = $_POST['dob'];

    // Ensure all required fields are present
    if (empty($name) || empty($phone) || empty($city) || empty($dob)) {
        $error = "All fields except Email and Role are required.";
    } else {
        // Update query excluding email and role
        $stmt = $conn->prepare("UPDATE users SET name = ?, phone = ?, city = ?, dob = ? WHERE user_id = ?");
        $stmt->bind_param('ssssi', $name, $phone, $city, $dob, $_SESSION['user_id']);

        if ($stmt->execute()) {
            $success = "Details updated successfully.";
            $_SESSION['name'] = $name; // Update session variable
        } else {
            $error = "Error updating details: " . $stmt->error;
        }
        $stmt->close();
    }
}


// Fetch user details
$stmt = $conn->prepare("SELECT name, email, phone, city, dob, role FROM users WHERE user_id = ?");
$stmt->bind_param('i', $_SESSION['user_id']);
$stmt->execute();
$stmt->bind_result($name, $email, $phone, $city, $dob, $role);
$stmt->fetch();
$stmt->close();
$conn->close();

// Handle null values
$name = $name ?? "";
$email = $email ?? "";
$phone = $phone ?? "";
$city = $city ?? "";
$dob = $dob ?? "";
$role = $role ?? "";
?>

<!DOCTYPE html>
<html>
<head>
    <title>User Details</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body class="img-background">
    <!-- Menu Bar -->
    <div class="menu-bar">
        <ul>
            <li><a href="home.php">Home</a></li>
            <li><a href="products.php">Products</a></li>
            <li><a href="user_details.php">Profile</a></li>
            <li><a href="support.php">Support</a></li>
            <li><a href="logout.php" id="logout-button" onclick="return confirmLogout(event);">Logout</a></li>
        </ul>
    </div>

    <!-- Profile Details -->
    <div class="profile-details">
        <h2 class="custom-h2">Your Details</h2>

        <!-- Error or Success Messages -->
        <?php if (isset($success)) echo "<p class='success-message'>$success</p>"; ?>
        <?php if (isset($error)) echo "<p class='error-message'>$error</p>"; ?>

        <form method="POST" id="edit-form">
            <p>
                <label>Name:</label>
                <input type="text" name="name" value="<?php echo htmlspecialchars($name); ?>" disabled>
            </p>
            <p>
                <label>Email:</label>
                <input type="email" name="email" value="<?php echo htmlspecialchars($email); ?>" readonly>
            </p>
            <p>
                <label>Phone:</label>
                <input type="text" name="phone" value="<?php echo htmlspecialchars($phone); ?>" disabled>
            </p>
            <p>
                <label>City:</label>
                <input type="text" name="city" value="<?php echo htmlspecialchars($city); ?>" disabled>
            </p>
            <p>
                <label>Date of Birth:</label>
                <input type="date" name="dob" value="<?php echo htmlspecialchars($dob); ?>" disabled>
            </p>
            <p>
                <label>Role:</label>
                <input type="text" name="role" value="<?php echo htmlspecialchars($role); ?>" readonly>
            </p>
            <button type="button" id="edit-button">Edit</button>
            <button type="submit" id="save-button" style="display: none;">Save</button>
        </form>
    </div>

    <script>
    // Enable editing when "Edit" button is clicked
        document.getElementById('edit-button').addEventListener('click', function () {
            document.querySelectorAll('#edit-form input').forEach(input => {
                if (input.name !== 'email' && input.name !== 'role') {
                    input.disabled = false; // Enable all except Email and Role
                }
            });
            document.getElementById('edit-button').style.display = 'none'; // Hide Edit button
            document.getElementById('save-button').style.display = 'inline-block'; // Show Save button
        });
    </script>

</body>
</html>
