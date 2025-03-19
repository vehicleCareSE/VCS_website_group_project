<?php
session_start();

// Database connection
try {
    $pdo = new PDO(
        "mysql:host=localhost;dbname=vehicle_care_system",
        "root",
        "",
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

$user_id = $_SESSION['user_id'] ?? 1;
$items = [];
$total_price = 0;

// Check if this is a single item checkout from spare parts detail page
if (isset($_POST['spare_id']) && isset($_POST['quantity'])) {
    // Fetch single item details
    try {
        $stmt = $pdo->prepare("SELECT spare_id, item_name, price FROM spare_parts WHERE spare_id = ?");
        $stmt->execute([$_POST['spare_id']]);
        $item = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($item) {
            $quantity = filter_var($_POST['quantity'], FILTER_VALIDATE_INT, ["options" => ["min_range" => 1, "max_range" => 99]]);
            if ($quantity === false) {
                die("Invalid quantity specified.");
            }
            
            $items[] = [
                'spare_id' => $item['spare_id'],
                'item_name' => $item['item_name'],
                'quantity' => $quantity,
                'price' => $item['price'],
                'total' => $quantity * $item['price']
            ];
            $total_price = $quantity * $item['price'];
        } else {
            die("Invalid item specified.");
        }
    } catch (PDOException $e) {
        die("Error fetching item details: " . $e->getMessage());
    }
} else {
    // Fetch cart items
    try {
        $stmt = $pdo->prepare("
            SELECT c.cart_id, s.spare_id, s.item_name, c.quantity, s.price 
            FROM cart c 
            JOIN spare_parts s ON c.spare_id = s.spare_id 
            WHERE c.user_id = ?
        ");
        $stmt->execute([$user_id]);
        
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $row['total'] = $row['quantity'] * $row['price'];
            $items[] = $row;
            $total_price += $row['total'];
        }
        
        if (empty($items)) {
            die("Your cart is empty. Add items before placing an order.");
        }
    } catch (PDOException $e) {
        die("Error fetching cart items: " . $e->getMessage());
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout</title>
    <style>
        
body {
            font-family: 'Arial', sans-serif;
            background-color: #f4f4f4;
        }

        .container {
            max-width: 1000px;
            margin: 0 auto;
            background-color: #fff;
            padding: 20px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        h1 {
            text-align: center;
            font-size: 24px;
            color: #333;
        }

        .cart-summary {
            margin-bottom: 20px;
        }

        .cart-summary table {
            width: 100%;
            border-collapse: collapse;
        }

        .cart-summary th,
        .cart-summary td {
            padding: 10px;
            text-align: center;
            border-bottom: 1px solid #ddd;
        }

        .total {
            text-align: left;
            font-size: 18px;
            font-weight: bold;
            padding: 10px;
            text-align: center;
            border-bottom: 1px solid #ddd;
        }

        .checkout-form {
            margin-top: 30px;
        }

        .checkout-form input[type="text"],
        .checkout-form input[type="email"],
        .checkout-form input[type="number"],
        .checkout-form select {
            width: 100%;
            padding: 8px;
            margin-bottom: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }

        .checkout-form button {
            width: 100%;
            padding: 12px;
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
        }

        .checkout-form button:hover {
            opacity: 0.8;
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
            <li><a href="logout.php" id="logout-button">Logout</a></li>
            <img class="logo_img" src="logovcs1.png" alt="logo">
        </ul>
    </div>

    <div class="container">
        <h1>Checkout</h1>

        <!-- Cart Summary -->
        <div class="cart-summary">
            <h3><?= count($items) > 1 ? 'Your Cart' : 'Order Summary' ?></h3>
            <table>
                <thead>
                    <tr>
                        <th>Item</th>
                        <th>Quantity</th>
                        <th>Price</th>
                        <th>Total</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($items as $item): ?>
                        <tr>
                            <td><?= htmlspecialchars($item['item_name']) ?></td>
                            <td><?= htmlspecialchars($item['quantity']) ?></td>
                            <td>$<?= number_format($item['price'], 2) ?></td>
                            <td>$<?= number_format($item['total'], 2) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <div class="total">
                Total: $<?= number_format($total_price, 2) ?>
            </div>
        </div>

        <!-- Checkout Form -->
        <form class="checkout-form" method="POST" action="place_order.php" onsubmit="return validateForm();">
            <!-- Hidden fields to pass order information -->
            <input type="hidden" name="order_type" value="<?= count($items) > 1 ? 'cart' : 'single' ?>">
            <?php if (count($items) === 1): ?>
                <input type="hidden" name="spare_id" value="<?= htmlspecialchars($items[0]['spare_id']) ?>">
                <input type="hidden" name="quantity" value="<?= htmlspecialchars($items[0]['quantity']) ?>">
            <?php endif; ?>
            <input type="hidden" name="total_price" value="<?= htmlspecialchars($total_price) ?>">

            <h3>Delivery Address</h3>
            <input type="text" name="address" id="address" placeholder="Enter your address" required>

            <h3>Payment Method</h3>
            <select name="payment_method" id="payment_method" required>
                <option value="">Select payment method</option>
                <option value="credit_card">Credit Card</option>
                <option value="paypal">PayPal</option>
                <option value="bank_transfer">Bank Transfer</option>
            </select>

            <h3>Email Address</h3>
            <input type="email" name="email" id="email" placeholder="Enter your email" required>

            <button type="submit">Pay Now</button>
        </form>
    </div>

    <script>
        function validateForm() {
            const address = document.getElementById('address').value.trim();
            const email = document.getElementById('email').value.trim();
            const paymentMethod = document.getElementById('payment_method').value;

            if (!address || !email || !paymentMethod) {
                alert('Please fill in all required fields.');
                return false;
            }

            if (!email.match(/^[^\s@]+@[^\s@]+\.[^\s@]+$/)) {
                alert('Please enter a valid email address.');
                return false;
            }

            return confirm('Are you sure you want to confirm the order?');
        }
    </script>
</body>
</html>

