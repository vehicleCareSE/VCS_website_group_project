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
        // General Greetings and Assistance
        "hello" => "Hi there! How can I assist you today?",
        "how are you" => "I'm just a bot, but I'm here to help!",
        "what is your name" => "I'm your Vehicle Care Assistant Bot!",
        "bye" => "Goodbye! Have a great day!",
        "thank you" => "You're welcome! Let me know if you need more help.",
        "help" => "Sure! Let me know your issue or question about vehicles.",
    
        // Vehicle Maintenance Questions
        "what are common maintenance tips" => "Check oil levels, tire pressure, brake condition, and ensure regular servicing.",
        "how often should I change my car oil" => "It's recommended every 5,000–7,500 miles for modern engines.",
        "how to check tire pressure" => "Use a tire pressure gauge to ensure it matches the recommended PSI in your vehicle manual.",
        "why is my engine overheating" => "Check for low coolant levels, a faulty radiator, or a broken water pump.",
        "what is the purpose of coolant" => "Coolant prevents your engine from overheating or freezing and reduces corrosion.",
        "how to clean car headlights" => "Use a mixture of toothpaste and water or a headlight restoration kit.",
        "when should I replace my car battery" => "Replace it every 3–5 years or if it shows signs of failure like difficulty starting.",
        "how to check brake pads" => "Look for wear indicators on the pads or unusual squeaking or grinding noises.",
        "how to prevent rust on a car" => "Wash regularly, wax your car, and apply rust-proof coatings.",
        "what is a timing belt" => "It synchronizes the rotation of the engine's crankshaft and camshaft to ensure proper timing.",
    
        // Vehicle Issues and Solutions
        "why is my car making a squealing noise" => "It could be a worn-out belt or brake issue. Have it inspected soon.",
        "why does my car pull to one side" => "Check wheel alignment or tire pressure. Uneven wear could also be the cause.",
        "why is my car vibrating" => "Common reasons include unbalanced tires, engine misfires, or a damaged axle.",
        "why is there a burning smell in my car" => "This may be due to oil leakage, overheated brakes, or clutch issues.",
        "what causes a car to lose power" => "Faulty fuel injectors, a clogged air filter, or a bad spark plug are common causes.",
        "why won't my car start" => "The battery, alternator, starter motor, or fuel system might be at fault.",
        "what does the check engine light mean" => "It indicates a problem with the engine or emissions system. Have it scanned for codes.",
        "why is my car leaking fluid" => "It could be oil, coolant, brake fluid, or transmission fluid. Identify the color of the leak for diagnosis.",
        "what causes tire blowouts" => "Over-inflation, under-inflation, or hitting sharp objects like nails can cause blowouts.",
        "how to fix a flat tire" => "Use a spare tire or a repair kit temporarily, then have it repaired at a service center.",
    
        // Specific Vehicle Services
        "do you offer oil changes" => "Yes, we offer professional oil change services.",
        "do you provide vehicle cleaning" => "Yes, we provide both interior and exterior cleaning services.",
        "can I book a repair appointment online" => "Yes, you can book through our website or app.",
        "what is the cost of brake repair" => "It varies by vehicle. We provide free diagnostics before quoting a price.",
        "do you offer emergency roadside assistance" => "Yes, we have a 24/7 roadside assistance service.",
        "do you handle engine problems" => "Yes, our experts can diagnose and repair engine issues.",
        "do you offer tire alignment services" => "Yes, we provide tire alignment and balancing services.",
        "do you perform vehicle inspections" => "Yes, we perform comprehensive vehicle inspections.",
        "can you install accessories" => "Yes, we install accessories like cameras, sensors, and more.",
        "what are your service hours" => "We are open from 8 AM to 8 PM daily.",
    
        // Random Questions about Vehicle Care
        "what are some fuel-saving tips" => "Avoid rapid acceleration, maintain proper tire pressure, and reduce excess weight.",
        "why does my car have low mileage" => "Driving habits, poor maintenance, and low tire pressure can affect mileage.",
        "how to fix a car that won't start in the cold" => "Check the battery, use a block heater, and ensure you have winter-grade oil.",
        "why is my steering wheel shaking" => "Unbalanced tires or worn-out suspension components could be the issue.",
        "how to clean car seats" => "Use a vacuum and upholstery cleaner for fabric seats or leather cleaner for leather seats.",
        "what are common brake problems" => "Squealing, grinding, or reduced braking power often indicate worn-out pads or rotors.",
        "why is my fuel gauge not working" => "The issue could be with the fuel sending unit or the gauge itself.",
        "how to jump-start a car" => "Connect jumper cables to a working car battery and follow the correct sequence of connections.",
        "how to tell if my tires need replacement" => "Look for low tread depth (less than 2/32 inch) or uneven wear patterns.",
        "why is my car stalling" => "A dirty throttle body, bad fuel pump, or ignition issues are common causes.",
    
        // Vehicle-related Emergencies
        "what to do if my car overheats" => "Turn off the AC, turn on the heater, and stop the car safely to check coolant levels.",
        "what to do after a car accident" => "Ensure safety, contact authorities, exchange information, and inform your insurance provider.",
        "how to prevent engine failure" => "Perform regular maintenance, check oil levels, and replace air and fuel filters as needed.",
        "what to do if brakes fail" => "Shift to a lower gear, use the parking brake, and safely steer off the road.",
        "why is my car shaking at high speeds" => "This could indicate unbalanced tires or alignment issues.",
        "how to deal with a tire blowout" => "Hold the steering wheel firmly, slow down gradually, and pull over safely.",
        "what is hydroplaning" => "It's when tires lose traction on wet surfaces. Avoid sudden movements and drive slowly in rain.",
        "how to handle skidding" => "Steer into the skid and avoid braking suddenly to regain control.",
        "what to do if my key is stuck in the ignition" => "Ensure the car is in park and try turning the steering wheel gently to release it.",
        "what to do if my headlights go out" => "Use your parking lights or hazards to signal other drivers and pull over safely.",
    ];
    

    // Default response
    return $responses[$message] ?? "I'm sorry, I didn't understand that. Can you rephrase?";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chatbot</title>
    <link rel="stylesheet" href="styles.css">
    <style>
    /* General Styles */
    body {
        font-family: Arial, sans-serif;
        margin: 0;
        padding: 0;
        background-color: #f4f4f9;
        font-size: 14px; /* Base font size */
    }
    .img-background {
        background: url('background.jpg') no-repeat center center fixed;
        background-size: cover;
    }
    /* Menu Bar */
    .menu-bar {
        background-color:rgb(97, 214, 166);
        color: #fff;
        padding: 8px 0;
        text-align: center;
        font-size: 14px; /* Smaller menu font */
    }
    .menu-bar ul {
        list-style: none;
        padding: 0;
        margin: 0;
    }
    .menu-bar li {
        display: inline;
        margin: 0 10px;
    }
    .menu-bar a {
        color: #fff;
        text-decoration: none;
        font-weight: bold;
    }
    /* Chat Container */
    .chat-container {
    background-color: #fff;
    width: 500px;
    margin: 40px auto;
    border-radius: 8px;
    box-shadow: 0 0 8px rgba(0, 0, 0, 0.2);
    padding: 15px;
    font-size: 14px;
    height: 650px; /* Increased height */
    margin-left: auto; /* Align to the right */
    margin-right: 20px;  /* Align to the right */
}
    .custom-h2 {
        text-align: center;
        color: #3398db;
        font-size: 18px; /* Smaller header font */
        margin-bottom: 10px;
    }
    .chat-history {
        border: 1px solid #ddd;
        padding: 8px;
        height: 430px;
        overflow-y: scroll;
        background-color: #fafafa;
        border-radius: 8px;
        margin-bottom: 10px;
        font-size: 13px; /* Smaller font for chat messages */
    }
    .user-message {
        background-color:rgb(51, 219, 146);
        color: #fff;
        border-radius: 10px;
        padding: 6px;
        margin: 5px 0px;
        text-align: right;
        font-size: 13px;
    }
    .bot-response {
        background-color: #e0e0e0;
        color: #333;
        border-radius: 10px;
        padding: 6px;
        margin: 5px 0;
        text-align: left;
        font-size: 13px;
    }
    .chat-form input {
        width: 75%;
        padding: 8px;
        border: 1px solid #ccc;
        border-radius: 5px;
        font-size: 14px;
    }
    .chat-form button {
        background-color:rgb(51, 219, 146);
        color: #fff;
        border: none;
        padding: 8px 12px;
        border-radius: 5px;
        cursor: pointer;
        font-size: 14px;
    }
    .success-message, .error-message {
        text-align: center;
        font-size: 12px;
        color: green;
    }
    .error-message {
        color: red;
    }
    .img-background {
            background-image: url("Untitled-2.png");
            background-size: cover;
            /* Make the image cover the entire screen */
            background-position: center;
            /* Center the image */
            background-attachment: fixed;
        }
</style>
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

    <!-- Confirm Logout Script -->
    <script>
    // Scroll to the bottom of the chat history to show the latest message
    window.onload = function() {
        var chatHistory = document.querySelector('.chat-history');
        chatHistory.scrollTop = chatHistory.scrollHeight;
    };

    // Ensure it scrolls to the bottom when new messages are added
    document.querySelector('.chat-form').addEventListener('submit', function() {
        setTimeout(function() {
            var chatHistory = document.querySelector('.chat-history');
            chatHistory.scrollTop = chatHistory.scrollHeight;
        }, 100); // Slight delay to wait for the message to load
    });
</script>
</body>


</html>