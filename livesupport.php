<?php
// Start session and authenticate user
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: livesupport.php');
    exit();
}

// Database connection
$conn = new mysqli('localhost', 'root', '', 'vehicle_care_system');
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Initialize variables
$error = "";
$success = "";
$bot_name = "VehicleCareBot";
$user_messages = [];
$bot_responses = [];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_message = $_POST['message'];

    // Ensure the user entered a message
    if (empty($user_message)) {
        $error = "Please enter a message.";
    } else {
        // Save user message to the database
        $stmt = $conn->prepare("INSERT INTO chat (user_id, user_message, bot_response) VALUES (?, ?, ?)");
        $bot_response = generateBotResponse($user_message); // Generate bot response
        $stmt->bind_param('iss', $_SESSION['user_id'], $user_message, $bot_response);

        if ($stmt->execute()) {
            $success = "Message sent successfully.";
            $user_messages[] = $user_message;
            $bot_responses[] = $bot_response;
        } else {
            $error = "Error saving message: " . $stmt->error;
        }
        $stmt->close();
    }
}

// Fetch chat history
$stmt = $conn->prepare("SELECT user_message, bot_response FROM chat WHERE user_id = ? ORDER BY created_at ASC");
$stmt->bind_param('i', $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    $user_messages[] = $row['user_message'];
    $bot_responses[] = $row['bot_response'];
}
$stmt->close();
$conn->close();

// Function to generate bot responses
function generateBotResponse($message) {
    $message = strtolower(trim($message));

    // Simple predefined responses
    $responses = [
        "hello" => "Hi there! How can I assist you today?",
        "how are you" => "I'm just a bot, but I'm here to help!",
        "services" => "We provide vehicle repair, maintenance, and cleaning services.",
        "bye" => "Goodbye! Have a great day!",
    ];

    // Default response
    return $responses[$message] ?? "I'm sorry, I didn't understand that. Can you rephrase?";
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Chatbot</title>
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

    <!-- Chatbot Interface -->
    <div class="chat-container">
        <h2 class="custom-h2">Chat with <?php echo $bot_name; ?></h2>

        <!-- Error or Success Messages -->
        <?php if (!empty($success)) echo "<p class='success-message'>$success</p>"; ?>
        <?php if (!empty($error)) echo "<p class='error-message'>$error</p>"; ?>

        <!-- Chat History -->
        <div class="chat-history">
            <?php
            foreach ($user_messages as $index => $user_message) {
                echo "<div class='user-message'><strong>You:</strong> " . htmlspecialchars($user_message) . "</div>";
                echo "<div class='bot-response'><strong>$bot_name:</strong> " . htmlspecialchars($bot_responses[$index]) . "</div>";
            }
            ?>
        </div>

        <!-- Chat Input -->
        <form method="POST" class="chat-form">
            <input type="text" name="message" placeholder="Type your message..." required>
            <button type="submit">Send</button>
        </form>
    </div>
</body>
</html>
