<?php
session_start();

// Enable error reporting (for debugging purposes, remove in production)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "vehicle_care_system";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed.']);
    exit;
}

// Read and decode JSON input
$input = json_decode(file_get_contents('php://input'), true);

if (!isset($input['spare_id'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid input.']);
    exit;
}

$spare_id = intval($input['spare_id']);
$user_id = $_SESSION['user_id'] ?? 1; // Replace with actual user ID logic

// Check if the item is already in the cart
$sql_check = "SELECT cart_id FROM cart WHERE user_id = ? AND spare_id = ?";
$stmt_check = $conn->prepare($sql_check);
$stmt_check->bind_param("ii", $user_id, $spare_id);
$stmt_check->execute();
$result_check = $stmt_check->get_result();

if ($result_check->num_rows > 0) {
    echo json_encode(['success' => false, 'message' => 'Item already in cart.']);
    exit;
}

// Add the item to the cart
$sql_insert = "INSERT INTO cart (user_id, spare_id, quantity) VALUES (?, ?, ?)";
$stmt_insert = $conn->prepare($sql_insert);
$quantity = 1; // Default quantity for a new item
$stmt_insert->bind_param("iii", $user_id, $spare_id, $quantity);

if ($stmt_insert->execute()) {
    echo json_encode(['success' => true, 'message' => 'Item added to cart.']);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to add to cart.']);
}

$conn->close();
?>
