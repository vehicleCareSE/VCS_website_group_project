<?php
// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "vehicle_care_system";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Initialize spare parts array
$spare_parts = [];
$search_query = isset($_GET['search']) ? $_GET['search'] : '';

if (!empty($search_query)) {
    // Search spare parts by item name or description
    $sql = "SELECT spare_id, item_name, description, price,image_path, stock 
            FROM spare_parts 
            WHERE item_name LIKE ? OR description LIKE ?";
    $stmt = $conn->prepare($sql);
    $search_term = "%" . $search_query . "%";
    $stmt->bind_param("ss", $search_term, $search_term);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    // Fetch all spare parts
    $sql = "SELECT spare_id, item_name, description, price, image_path,stock FROM spare_parts";
    $result = $conn->query($sql);
}

// Fetch results into an array
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $spare_parts[] = $row;
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Spare Parts</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="spareparts.css">
    
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

            <!-- Add View Cart and Orders buttons with icons -->
            <div class="buttons-container">
                <li>
                    <a href="cart.php" class="cart-button">
                        <i class="fas fa-shopping-cart"></i>
                        <span class="cart-count">0</span>
                        Cart
                    </a>
                </li>
                <li>
                    <a href="ordersview.php" class="orders-button">
                        <i class="fas fa-box"></i>
                        Orders
                    </a>
                </li>
            </div>
        </ul>
    </div>


    <!-- Full-width image after the menu bar -->
    <img class="bg_img" src="images\spareparts images\background img\sparepartt up_img.jpg" alt="Spare Parts Banner" class="hero-image">



    <!-- Spare Parts Section -->
    <div class="container">
        <!-- Search Bar -->
        <form method="GET" action="" style="margin-bottom: 20px;">
            <input type="text" name="search" placeholder="Search spare parts..." value="<?= htmlspecialchars($search_query) ?>" style="padding: 10px; width: 60%; border: 1px solid #ccc; border-radius: 60px;">
            <button type="submit" style="padding: 10px 20px; background-color:rgb(61, 62, 61); color: white; border: none; border-radius: 15px; cursor: pointer;  ">Search</button>
        </form>
        <h1 class="spare-part-heading">Spare Parts</h1>


        <div id="spare-parts-container">
            <?php if (empty($spare_parts)): ?>
                <p>No results found for "<?= htmlspecialchars($search_query) ?>". Please try another search term.</p>
            <?php else: ?>
                <?php foreach ($spare_parts as $part): ?>
                    <div class="spare-part">
                        <img src="<?= htmlspecialchars($part['image_path']) ?>" alt="<?= htmlspecialchars($part['item_name']) ?>" class="spare-part-image">
                        <h3>
                            <a href="spare_part_details.php?id=<?= htmlspecialchars($part['spare_id']) ?>">
                                <?= htmlspecialchars($part['item_name']) ?>
                            </a>
                        </h3>
                        <p><?= htmlspecialchars($part['description']) ?></p>
                        <p class="price">Price: $<?= htmlspecialchars($part['price']) ?></p>
                        <p>
                            <?php if ($part['stock'] > 0): ?>
                                <span class="status in-stock">In Stock</span>
                            <?php else: ?>
                                <span class="status out-of-stock">Out of Stock</span>
                            <?php endif; ?>
                        </p>
                        <button class="add-to-cart-btn" onclick="addToCart(<?= htmlspecialchars($part['spare_id']) ?>)">Add to Cart</button>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <!-- Footer -->
    <footer class="footer">
        <div class="footer-container">
            <!-- Left Section -->
            <div class="footer-section">
                <h3 class="footer-title">Vehicle Care System</h3>
                <p>
                    Dedicated to providing seamless services for vehicle enthusiasts.
                    Explore spare parts, garages, vehicle ads, live support, and more at your fingertips.
                </p>
            </div>

            <!-- Center Section -->
            <div class="footer-section">
                <h3 class="Welcome to VSC">Contact Us</h3>
                <p>Email: <a href="mailto:support@vehiclecare.com">support@vehiclecare.com</a></p>
                <p>Phone: 0555555554</p>
                <p>No 123 ,Bandarawela,Badulla,Srilanka</p>

            </div>

            <!-- Right Section -->
            <div class="footer-section">
                <h3 class="footer-title">Follow Us</h3>
                <div class="social-icons">
                    <a href="#"><img src="images/Social/fb.png" alt="Facebook"></a>
                    <a href="#"><img src="images/Social/x.png" alt="Twitter"></a>
                    <a href="#"><img src="images/Social/insta.png" alt="Instagram"></a>
                    <a href="#"><img src="images/Social/linked.png" alt="LinkedIn"></a>
                </div>
            </div>
        </div>

        <!-- Bottom Section -->
        <div class="footer-bottom">
            <p>&copy; 2024 Vehicle Care System. All rights reserved.</p>
        </div>
    </footer>

    <!-- JavaScript -->
    <script>
        function addToCart(spareId) {
            fetch('add_to_cart.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        spare_id: spareId
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Added to cart successfully!');
                    } else {
                        alert('Failed to add to cart: ' + data.message);
                    }
                })
                .catch(error => {
                    alert('An error occurred: ' + error.message);
                });
        }

        function confirmLogout(event) {
            return confirm('Are you sure you want to log out?');
        }


        // Example: Update cart count dynamically
        const cartCount = document.querySelector('.cart-count');
        cartCount.innerText = 3; // Replace 3 with the actual count fetched from your backend
    </script>
</body>

</html>
