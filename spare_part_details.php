<?php
session_start();

// Database connection using PDO with error handling
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

// Validate and sanitize input
$spare_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if ($spare_id === false || $spare_id === null) {
    die("Invalid spare part ID.");
}

// Use prepared statement to prevent SQL injection
try {
    $stmt = $pdo->prepare("SELECT * FROM spare_parts WHERE spare_id = ?");
    $stmt->execute([$spare_id]);
    
    if ($stmt->rowCount() === 0) {
        die("Spare part not found.");
    }
    
    $spare_part = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Query failed: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($spare_part['item_name']) ?></title>
    <style>
    
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f4f9;
        }

        .container {
            max-width: 800px;
            margin: 40px auto;
            padding: 20px;
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }

        .spare-part-details {
            text-align: center;
        }

        .spare-part-details h1 {
            color: #333;
        }

        .spare-part-details p {
            color: #666;
            font-size: 18px;
        }

        .price {
            font-size: 20px;
            color: #e63946;
            font-weight: bold;
        }

        .quantity-controls {
            display: flex;
            justify-content: center;
            align-items: center;
            margin-top: 10px;
        }

        .quantity-controls button {
            padding: 5px 10px;
            font-size: 18px;
            margin: 0 10px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        .quantity-controls button:hover {
            background-color: #0056b3;
        }

        .quantity-controls input {
            width: 50px;
            text-align: center;
            font-size: 18px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }

        .total-price {
            font-size: 18px;
            margin-top: 10px;
            color: #333;
        }

        .order-button {
            margin-top: 20px;
            padding: 10px 20px;
            background-color: #28a745;
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 18px;
            cursor: pointer;
        }

        .order-button:hover {
            background-color: #218838;
        }

        .menu-bar {
            background-color: rgb(255, 255, 255);
            color: #333;
            padding: 20px 0;
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

        .spare-part-image {
            max-width: 100%;
            height: auto;
            width: 300px;
            border-radius: 10px;
            display: block;
            margin: 0 auto 20px;
        }
    </style>
    
</head>
<body>
    <div class="menu-bar">
        <ul>
            <li><a href="home.php">Home</a></li>
            <li><a href="spareparts.php">Products</a></li>
            <li><a href="user_details.php">Profile</a></li>
            <li><a href="livesupport.php">Support</a></li>
            <li><a href="logout.php" id="logout-button">Logout</a></li>
        </ul>
    </div>

    <div class="container">
        <div class="spare-part-details">
            <img 
                src="<?= htmlspecialchars($spare_part['image_path']) ?>" 
                alt="<?= htmlspecialchars($spare_part['item_name']) ?>" 
                class="spare-part-image"
                onerror="this.src='placeholder.jpg';"
            >
            <h1><?= htmlspecialchars($spare_part['item_name']) ?></h1>
            <p><?= htmlspecialchars($spare_part['description']) ?></p>
            <p class="price" id="price">Price: $<?= number_format($spare_part['price'], 2) ?></p>

            <div class="quantity-controls">
                <button id="decrease-btn" type="button">-</button>
                <input type="number" id="quantity" value="1" min="1" max="99" readonly>
                <button id="increase-btn" type="button">+</button>
            </div>

            <p class="total-price" id="total-price">Total: $<?= number_format($spare_part['price'], 2) ?></p>

            <button class="order-button" id="order-now-btn" type="button">Order Now</button>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const price = parseFloat(<?= json_encode($spare_part['price']) ?>);
            const maxQuantity = 99;
            let quantity = 1;

            function updateQuantity(change) {
                const newQuantity = quantity + change;
                if (newQuantity >= 1 && newQuantity <= maxQuantity) {
                    quantity = newQuantity;
                    document.getElementById('quantity').value = quantity;
                    updateTotal();
                }
            }

            function updateTotal() {
                const total = (price * quantity).toFixed(2);
                document.getElementById('total-price').innerText = `Total: $${total}`;
            }

            document.getElementById('increase-btn').addEventListener('click', () => updateQuantity(1));
            document.getElementById('decrease-btn').addEventListener('click', () => updateQuantity(-1));

            document.getElementById('order-now-btn').addEventListener('click', function() {
                const total = (price * quantity).toFixed(2);
                const userConfirmed = confirm(`Confirm your order of ${quantity} items for a total of $${total}?`);
                
                if (userConfirmed) {
                    // Use form submission for better security
                    const form = document.createElement('form');
                    form.method = 'POST';
                    form.action = 'checkout.php';

                    const fields = {
                        'spare_id': <?= $spare_id ?>,
                        'quantity': quantity,
                        'total': total
                    };

                    for (const [name, value] of Object.entries(fields)) {
                        const input = document.createElement('input');
                        input.type = 'hidden';
                        input.name = name;
                        input.value = value;
                        form.appendChild(input);
                    }

                    document.body.appendChild(form);
                    form.submit();
                }
            });
        });
    </script>
</body>
</html>



