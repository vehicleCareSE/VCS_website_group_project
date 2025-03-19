<?php
session_start();

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "vehicle_care_system";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle order confirmation or decline
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $order_id = $_POST['order_id'];
    $action = $_POST['action']; // 'confirm' or 'decline'

    if ($action === 'confirm') {
        $status = 'Confirmed';
        $sql_update = "UPDATE orders SET status = ? WHERE order_id = ?";
        $stmt = $conn->prepare($sql_update);
        $stmt->bind_param("si", $status, $order_id);
        $stmt->execute();
        $stmt->close();
    } elseif ($action === 'decline') {
        $status = 'Canceled';
        $sql_update = "UPDATE orders SET status = ? WHERE order_id = ?";
        $stmt = $conn->prepare($sql_update);
        $stmt->bind_param("si", $status, $order_id);
        $stmt->execute();
        $stmt->close();

        // Update stock and sales in spare_parts table
        $sql_items = "SELECT spare_id, quantity FROM order_items WHERE order_id = ?";
        $stmt = $conn->prepare($sql_items);
        $stmt->bind_param("i", $order_id);
        $stmt->execute();
        $result_items = $stmt->get_result();
        $stmt->close();

        while ($item = $result_items->fetch_assoc()) {
            $spare_id = $item['spare_id'];
            $quantity = $item['quantity'];

            // Update stock and sales
            $sql_update_spare = "UPDATE spare_parts SET stock = stock + ?, sales = sales - ? WHERE spare_id = ?";
            $stmt = $conn->prepare($sql_update_spare);
            $stmt->bind_param("iii", $quantity, $quantity, $spare_id);
            $stmt->execute();
            $stmt->close();
        }
    }
}

// Fetch all pending orders
$sql = "SELECT * FROM orders WHERE status = 'Pending'";
$result = $conn->query($sql);

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Orders</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f9f9f9;
            margin: 0;
            
        }

        .container {
            max-width: 1200px;
            margin: auto;
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }

        h1 {
            color: #333;
            font-size: 26px;
            margin-bottom: 20px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        th, td {
            padding: 12px;
            border: 1px solid #ddd;
            text-align: center;
        }

        th {
            background-color: #f4f4f4;
            font-weight: bold;
        }

        .btn {
            display: inline-block;
            padding: 8px 12px;
            color: #fff;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
        }

        .btn-confirm {
            background-color: #4CAF50;
        }

        .btn-decline {
            background-color: #f44336;
        }

        .btn:hover {
            opacity: 0.8;
        }

        .menu-bar {
    background-color: #004080;
    color: #fff;
    color: #333;
    padding: 30px 0;
    text-align: center;
    box-shadow: 0 4px 60px rgba(0, 0, 0, 0.51);
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
    color: #ffffff;
    text-decoration: none;
    font-weight: bold;
    font-size: 18px;
    padding: 5px 12px;
    border-radius: 5px;
    transition: all 0.3s ease;
}

.menu-bar a:hover {
    background-color: rgb(0, 0, 0);
    color: #fff;
    text-shadow: 0 1px 4px rgba(0, 0, 0, 0.2);
}
#seller-btn{
  
   
    padding: 10px 20px; /* Optional: For better spacing */
    border: none; /* Optional: Remove border for a clean look */
    border-radius: 5px; /* Optional: Rounded corners */
    position: fixed; /* Sticks the button in a fixed position */
    top: 20px; /* Distance from the top of the screen */
    right: 10px; /* Distance from the right edge of the screen */
    z-index: 1000; /* Ensures it stays on top of other elements */
    cursor: pointer; /* Changes cursor to pointer on hover */
    transition: background-color 0.3s ease; /* Optional: Smooth color transition */
    display: block;
    background-color: #2e7dcc;
    color: white;
    font-size: 16px;
}

#seller-btn:hover {
    background-color: #c82333;
    /* Slightly darker red on hover */
}
    </style>
</head>
<body>

<div class="menu-bar">
        <ul>
            <li><a href="admin_Dashboard.php">Dashboard</a></li>
            <li><a href="admin_order.php">Orders</a></li>
            <li><a href="user_details.php">Profile</a></li>
            <li><a href="livesupport.php">Support</a></li>
            <li><a href="logout.php" id="logout-button" onclick="return confirmLogout(event);">Logout</a></li>
            <li><a href="home.php" id="seller-btn">Switch to Buying</a></li>
        </ul>
    </div>

<div class="container">
    <h1>Admin Orders</h1>
    <?php if ($result->num_rows > 0): ?>
        <table>
            <thead>
                <tr>
                    <th>Order ID</th>
                    <th>Total Price</th>
                    <th>Address</th>
                    <th>Payment Method</th>
                    <th>Email</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($order = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?= $order['order_id'] ?></td>
                        <td>$<?= $order['total_price'] ?></td>
                        <td><?= $order['address'] ?></td>
                        <td><?= $order['payment_method'] ?></td>
                        <td><?= $order['email'] ?></td>
                        <td>
                            <form method="POST" style="display: inline;">
                                <input type="hidden" name="order_id" value="<?= $order['order_id'] ?>">
                                <input type="hidden" name="action" value="confirm">
                                <button type="submit" class="btn btn-confirm">Confirm</button>
                            </form>
                            <form method="POST" style="display: inline;">
                                <input type="hidden" name="order_id" value="<?= $order['order_id'] ?>">
                                <input type="hidden" name="action" value="decline">
                                <button type="submit" class="btn btn-decline">Decline</button>
                            </form>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p>No pending orders found.</p>
    <?php endif; ?>
</div>

</body>
</html>
