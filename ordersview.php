<?php
// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "vehicle_care_system";

$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Start session to retrieve logged-in user's ID
session_start();
$user_id = $_SESSION['user_id'] ?? null; // Replace this with your actual login authentication logic

if (!$user_id) {
    die("Unauthorized access. Please log in."); // Prevent unauthorized access if user_id is not set
}

// Fetch orders for the logged-in user
$sql = "SELECT o.order_id, o.status, oi.item_name, oi.spare_id, oi.quantity, oi.price, oi.total_price 
        FROM orders o
        JOIN order_items oi ON o.order_id = oi.order_id
        WHERE o.user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

$orders = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $orders[] = $row;
    }
}
$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Orders</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f4f9;
        }

        .container {
            max-width: 1200px;
            margin: auto;
            padding: 20px;
        }

        .order-item {
            border: 1px solid #ddd;
            border-radius: 5px;
            margin: 10px 0;
            padding: 10px;
            background: #fff;
        }

        .order-item h3 {
            font-size: 1.2em;
            color: #333;
        }

        .order-item p {
            color: #666;
            margin: 5px 0;
        }

        .menu-bar {
            background-color: #fff;
            color: #333;
            padding: 30px 0;
            text-align: center;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            position: sticky;
            top: 0;
            z-index: 1000;
        }

        .menu-bar ul {
            list-style: none;
            padding: 0;
            margin: 0;
            display: flex;
            justify-content: center;
        }

        .menu-bar li {
            display: inline;
            margin: 0 20px;
        }

        .menu-bar a {
            color: #333;
            text-decoration: none;
            font-weight: bold;
            font-size: 18px;
            padding: 8px 12px;
            border-radius: 5px;
            transition: all 0.3s ease;
        }

        .menu-bar a:hover {
            background-color: rgb(62, 62, 62);
            color: #fff;
            text-shadow: 0 1px 4px rgba(0, 0, 0, 0.2);
        }

        .logo_img {
            position: fixed;
            top: -60px;
            right: 1400px;
            padding: 15px;
            width: 10%;

            color: white;
            font-weight: bold;
            cursor: pointer;
            text-align: center;
            transition: background-color 0.3s ease;
            z-index: 1000;
        }

        .orders-container {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            margin-top: 20px;
        }

        .order-block {
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 15px;
            background-color: #fff;
            width: calc(50% - 10px);
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .order-block h3 {
            margin-top: 0;
            font-size: 1.4em;
            color: #004080;
        }

        .order-items {
            list-style-type: none;
            padding: 0;
            margin-top: 10px;
        }

        .order-items li {
            margin-bottom: 10px;
            color: #555;
            font-size: 0.9em;
        }
    </style>
</head>

<body>

    <!-- Menu Bar -->
    <div class="menu-bar">
        <ul>
            <li><a href="home.php">Home</a></li>
            <li><a href="spareparts.php">Products</a></li>
            <li><a href="user_details.php">Profile</a></li>
            <li><a href="livesupport.php">Support</a></li>
            <li><a href="logout.php" id="logout-button" onclick="return confirmLogout(event);">Logout</a></li>
            <img class="logo_img" src="logovcs.png" alt="logo">
        </ul>
    </div>



    <!-- Orders Section -->
    <div class="container">
        <h1>Your Orders</h1>
        <?php if (!empty($orders)): ?>
            <?php $currentOrderId = null; ?>
            <div class="orders-container">
                <?php foreach ($orders as $order): ?>
                    <?php if ($currentOrderId !== $order['order_id']): ?>
                        <?php if ($currentOrderId !== null): ?>
                            </ul>
            </div>
        <?php endif; ?>
        <div class="order-block">
            <h3>Order ID: <?= htmlspecialchars($order['order_id']) ?></h3>
            <p>Status: <?= htmlspecialchars($order['status']) ?></p>
            <ul class="order-items">
                <li>
                    <strong>Item:</strong> <?= htmlspecialchars($order['item_name']) ?> |
                    <strong>Spare ID:</strong> <?= htmlspecialchars($order['spare_id']) ?> |
                    <strong>Quantity:</strong> <?= htmlspecialchars($order['quantity']) ?> |
                    <strong>Price:</strong> $<?= htmlspecialchars($order['price']) ?> |
                    <strong>Total Price:</strong> $<?= htmlspecialchars($order['total_price']) ?>
                </li>
            <?php else: ?>
                <li>
                    <strong>Item:</strong> <?= htmlspecialchars($order['item_name']) ?> |
                    <strong>Spare ID:</strong> <?= htmlspecialchars($order['spare_id']) ?> |
                    <strong>Quantity:</strong> <?= htmlspecialchars($order['quantity']) ?> |
                    <strong>Price:</strong> $<?= htmlspecialchars($order['price']) ?> |
                    <strong>Total Price:</strong> $<?= htmlspecialchars($order['total_price']) ?>
                </li>
            <?php endif; ?>
            <?php $currentOrderId = $order['order_id']; ?>
        <?php endforeach; ?>
            </ul>
        </div>
    </div>
<?php else: ?>
    <p>No orders found for your account.</p>
<?php endif; ?>
</div>


<script>
    // Confirm logout
    function confirmLogout(event) {
        if (!confirm("Are you sure you want to log out?")) {
            event.preventDefault();
        }
    }
</script>

</body>

</html>