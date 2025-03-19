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

$user_id = $_SESSION['user_id'] ?? 1;

$sql = "SELECT c.cart_id, s.item_name, c.quantity, s.price 
        FROM cart c 
        JOIN spare_parts s ON c.spare_id = s.spare_id 
        WHERE c.user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

$cart_items = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $cart_items[] = $row;
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
    <title>Your Cart</title>

    <style>
        body {
            font-family: 'Arial', sans-serif;
            background-color: #f1f1f1;
            margin: 0;
            padding: 0;
        }

        .container {
            max-width: 1200px;
            margin: 30px auto;
            padding: 20px;
            background-color: #fff;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        h1 {
            text-align: center;
            color: #333;
            margin-bottom: 20px;
            font-size: 20px;
            /* Reduced font size */
        }

        .cart-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }

        .cart-table th,
        .cart-table td {
            padding: 8px;
            text-align: center;
            border-bottom: 1px solid #ddd;
            font-size: 14px;
            /* Reduced font size */
        }

        .cart-table th {
            background-color: #f4f4f4;

        }

        .cart-table td {
            background-color: #fff;
        }

        .quantity-select {
            width: 60px;
            padding: 5px;
            border: 1px solid #ddd;
            font-size: 12px;
            /* Reduced font size */
        }

        .add-to-cart-button,
        .remove-button,
        .checkout-button {
            padding: 6px 12px;
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 12px;
            /* Reduced font size */

        }

        .remove-button {
            background-color: #e74c3c;
        }

        .add-to-cart-button:hover,
        .remove-button:hover,
        .checkout-button:hover {
            opacity: 0.8;
        }

        .total-section {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 20px;
        }

        .total-section strong {
            font-size: 14px;
            /* Reduced font size */
            color: #333;

        }

        .checkout-section {
            text-align: center;
        }

        .checkout-button {
            font-size: 16px;
            /* Reduced font size */
            padding: 10px 25px;
            background-color: #2196F3;
            border-radius: 5px;
            color: white;
            font-weight: bold;

        }

        .checkout-button:hover {
            background-color: #1976D2;
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
            <img class="logo_img" src="logovcs1.png" alt="logo">
        </ul>
    </div>



    <div class="container">
        <h1>Your Cart</h1>

        <table class="cart-table">
            <thead>
                <tr>
                    <th>Item</th>
                    <th>Quantity</th>
                    <th>Price</th>
                    <th>Total</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $total_price = 0;
                foreach ($cart_items as $item):
                    $item_total = $item['quantity'] * $item['price'];
                    $total_price += $item_total;
                ?>
                    <tr>
                        <td><?= htmlspecialchars($item['item_name']) ?></td>
                        <td>
                            <input type="number" class="quantity-select" value="<?= $item['quantity'] ?>" min="1" id="quantity_<?= $item['cart_id'] ?>" />
                            <button class="add-to-cart-button" onclick="updateCart(<?= $item['cart_id'] ?>)">Update</button>
                        </td>
                        <td>$<?= htmlspecialchars($item['price']) ?></td>
                        <td>$<?= htmlspecialchars($item_total) ?></td>
                        <td>
                            <button class="remove-button" onclick="removeFromCart(<?= $item['cart_id'] ?>)">Remove</button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <div class="total-section">
            <strong>Total: $<?= htmlspecialchars($total_price) ?></strong>
        </div>

        <div class="checkout-section">
            <button class="checkout-button" onclick="checkout()"> Checkout</button>
        </div>
    </div>

    <script>
        function updateCart(itemId) {
            const quantity = document.getElementById('quantity_' + itemId).value;

            const xhr = new XMLHttpRequest();
            xhr.open('POST', 'update_cart_quantity.php', true);
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
            xhr.onload = function() {
                if (xhr.status === 200) {
                    const response = JSON.parse(xhr.responseText);
                    if (response.success) {
                        alert('Cart updated successfully');
                        location.reload();
                    } else {
                        alert('Failed to update quantity');
                    }
                }
            };
            xhr.send('item_id=' + itemId + '&quantity=' + quantity);
        }

        function removeFromCart(itemId) {
            if (confirm('Are you sure you want to remove this item from your cart?')) {
                const xhr = new XMLHttpRequest();
                xhr.open('POST', 'remove_from_cart.php', true);
                xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
                xhr.onload = function() {
                    if (xhr.status === 200) {
                        alert('Item removed successfully');
                        location.reload();
                    } else {
                        alert('Failed to remove item');
                    }
                };
                xhr.send('item_id=' + itemId);
            }
        }

        function checkout() {
            window.location.href = 'checkout.php'; // Redirect to checkout page
        }
    </script>

</body>

</html>