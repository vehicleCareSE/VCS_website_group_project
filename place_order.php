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

$user_id = $_SESSION['user_id'] ?? null;
if (!$user_id) {
    die("Unauthorized access. Please log in.");
}

// Validate POST data
$address = $_POST['address'] ?? null;
$payment_method = $_POST['payment_method'] ?? null;
$email = $_POST['email'] ?? null;
$order_type = $_POST['order_type'] ?? null;

if (!$address || !$payment_method || !$email || !$order_type) {
    die("Please fill out all required fields.");
}

$items = [];
$total_price = 0;

// Begin transaction
$conn->begin_transaction();

try {
    if ($order_type === 'single') {
        // Handle single item order
        $spare_id = $_POST['spare_id'] ?? null;
        $quantity = $_POST['quantity'] ?? null;
        
        if (!$spare_id || !$quantity) {
            throw new Exception("Missing item details for single order");
        }

        // Fetch item details and check stock
        $sql = "SELECT item_name, price, stock FROM spare_parts WHERE spare_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $spare_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            throw new Exception("Item not found");
        }

        $item = $result->fetch_assoc();
        if ($item['stock'] < $quantity) {
            throw new Exception("Insufficient stock for item: " . $item['item_name']);
        }

        $total_price = $quantity * $item['price'];
        $items[] = [
            'spare_id' => $spare_id,
            'item_name' => $item['item_name'],
            'quantity' => $quantity,
            'price' => $item['price']
        ];

    } else if ($order_type === 'cart') {
        // Handle cart order
        $sql = "SELECT c.cart_id, c.spare_id, c.quantity, s.item_name, s.price, s.stock 
                FROM cart c 
                JOIN spare_parts s ON c.spare_id = s.spare_id 
                WHERE c.user_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 0) {
            throw new Exception("Your cart is empty. Add items before placing an order.");
        }

        while ($row = $result->fetch_assoc()) {
            if ($row['stock'] < $row['quantity']) {
                throw new Exception("Insufficient stock for item: " . $row['item_name']);
            }
            $total_price += $row['quantity'] * $row['price'];
            $items[] = $row;
        }
    } else {
        throw new Exception("Invalid order type");
    }

    if ($total_price === 0) {
        throw new Exception("Total price cannot be zero. Check your order.");
    }

    // Create order record
    $order_date = date("Y-m-d H:i:s");
    $status = "Pending";
    
    $order_sql = "INSERT INTO orders (user_id, address, payment_method, email, total_price, order_date, status) 
                  VALUES (?, ?, ?, ?, ?, ?, ?)";
    $stmt_order = $conn->prepare($order_sql);
    $stmt_order->bind_param("isssdss", $user_id, $address, $payment_method, $email, $total_price, $order_date, $status);

    if (!$stmt_order->execute()) {
        throw new Exception("Failed to create order: " . $stmt_order->error);
    }

    $order_id = $conn->insert_id;

    // Process order items
    $item_sql = "INSERT INTO order_items (order_id, item_name, spare_id, quantity, price, total_price, user_id) 
                 VALUES (?, ?, ?, ?, ?, ?, ?)";
    $update_stock_sql = "UPDATE spare_parts SET stock = stock - ?, sales = sales + ? WHERE spare_id = ?";

    $stmt_items = $conn->prepare($item_sql);
    $stmt_update_stock = $conn->prepare($update_stock_sql);

    foreach ($items as $item) {
        $item_total_price = $item['quantity'] * $item['price'];

        // Insert order item
        $stmt_items->bind_param(
            "isiiidi",
            $order_id,
            $item['item_name'],
            $item['spare_id'],
            $item['quantity'],
            $item['price'],
            $item_total_price,
            $user_id
        );

        if (!$stmt_items->execute()) {
            throw new Exception("Failed to insert order item: " . $stmt_items->error);
        }

        // Update stock and sales
        $stmt_update_stock->bind_param("iii", $item['quantity'], $item['quantity'], $item['spare_id']);
        if (!$stmt_update_stock->execute()) {
            throw new Exception("Failed to update stock and sales: " . $stmt_update_stock->error);
        }
    }

    // Clear cart if this was a cart order
    if ($order_type === 'cart') {
        $clear_cart_sql = "DELETE FROM cart WHERE user_id = ?";
        $stmt_clear_cart = $conn->prepare($clear_cart_sql);
        $stmt_clear_cart->bind_param("i", $user_id);

        if (!$stmt_clear_cart->execute()) {
            throw new Exception("Failed to clear cart: " . $stmt_clear_cart->error);
        }
    }

    // Commit transaction
    $conn->commit();

    // Redirect to confirmation page
    header("Location: order_confirmation.php?order_id=$order_id");
    exit();

} catch (Exception $e) {
    $conn->rollback();
    die("Error placing order: " . $e->getMessage());
} finally {
    // Close all prepared statements
    if (isset($stmt)) $stmt->close();
    if (isset($stmt_order)) $stmt_order->close();
    if (isset($stmt_items)) $stmt_items->close();
    if (isset($stmt_update_stock)) $stmt_update_stock->close();
    if (isset($stmt_clear_cart)) $stmt_clear_cart->close();
    $conn->close();
}
?>