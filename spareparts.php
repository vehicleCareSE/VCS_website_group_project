<?php
// spareparts.php

// Database connection
$host = "localhost";
$username = "root";
$password = ""; 
$dbname = "vehicle_care_system";

$conn = new mysqli($host, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch spare parts from the database
$sql = "SELECT * FROM spare_parts";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Spare Parts</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body class="img-background2">
    <!-- Menu Bar -->
    <div class="menu-bar1">
        <ul>
            <li><a href="home.php">Home</a></li>
            <li><a href="products.php">Products</a></li>
            <li><a href="user_details.php">Profile</a></li>
            <li><a href="support.php">Support</a></li>
            <li><a href="logout.php" id="logout-button" onclick="return confirmLogout(event);">Logout</a></li>
        </ul>
    </div>

    <div class="content1">
        <h1>Welcome, BUY Spare Parts !</h1>
        <p>Explore the categories below</p>

        <!-- Category Cards -->
        <div class="cards-container">
            <!-- Card 1 -->
            <div id="cards-container" class="cards-container">
            <!-- Card 1 -->
            <div class="card" data-id="1" onclick="navigateToPage('');">
                <img src="" alt="Category 1">
                <div class="card-content">
                    <h3>Spare Parts</h3>
                </div>
                <div class="favorite-icon" onclick="toggleFavorite(event, this);"></div>
            </div>

            <!-- Card 2 -->
            <div class="card" data-id="2" onclick="navigateToPage('');">
                <img src="" alt="Category 2">
                <div class="card-content">
                    <h3>Spare Parts 2</h3>
                </div>
                <div class="favorite-icon" onclick="toggleFavorite(event, this);"></div>
            </div>

            <!-- Card 3 -->
            <div class="card" data-id="3" onclick="navigateToPage('');">
                <img src="" alt="Category 3">
                <div class="card-content">
                    <h3>Spare Parts 3</h3>
                </div>
                <div class="favorite-icon" onclick="toggleFavorite(event, this);"></div>
            </div>

            <!-- Card 4 -->
            <div class="card" data-id="4" onclick="navigateToPage('');">
                <img src="" alt="Category 4">
                <div class="card-content">
                    <h3>Spare Parts 4</h3>
                </div>
                <div class="favorite-icon" onclick="toggleFavorite(event, this);"></div>
            </div>

            <!-- Card 5 -->
            <div class="card" data-id="5" onclick="navigateToPage('');">
                <img src="" alt="Category 5">
                <div class="card-content">
                    <h3>Spare Parts 5</h3>
                </div>
                <div class="favorite-icon" onclick="toggleFavorite(event, this);"></div>
            </div>

            <!-- Card 6 -->
            <div class="card" data-id="6" onclick="navigateToPage('');">
                <img src="" alt="Category 6">
                <div class="card-content">
                    <h3>Spare Parts 6</h3>
                </div>
                <div class="favorite-icon" onclick="toggleFavorite(event, this);"></div>
            </div>
        </div>
    </div>

    <?php if ($result && $result->num_rows > 0): ?>
        <table border="1">
            <thead>
                <tr>
                    <th>Part ID</th>
                    <th>Part Name</th>
                    <th>Description</th>
                    <th>Price (LKR)</th>
                    <th>Quantity</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['id']); ?></td>
                        <td><?php echo htmlspecialchars($row['name']); ?></td>
                        <td><?php echo htmlspecialchars($row['description']); ?></td>
                        <td><?php echo htmlspecialchars($row['price']); ?></td>
                        <td><?php echo htmlspecialchars($row['quantity']); ?></td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p>No spare parts available at the moment.</p>
    <?php endif; ?>
    <div class="addparts">
    <h2>Add New Spare Part</h2>
    <form action="add_sparepart.php" method="post">
        <label for="name">Part Name:</label>
        <input type="text" id="name" name="name" required><br><br>

        <label for="description">Description:</label>
        <textarea id="description" name="description" required></textarea><br><br>

        <label for="price">Price (LKR):</label>
        <input type="number" id="price" name="price" step="0.01" required><br><br>

        <label for="quantity">Quantity:</label>
        <input type="number" id="quantity" name="quantity" required><br><br>

        <button type="submit">Add Spare Part</button>
    </form>
    </dev>
    <!-- Footer -->
    <footer class="footer">
        <div class="footer-container">
            <!-- About Section -->
        <div class="footer-section about">
            <h3>About Us</h3>
            <p>
                Vehicle Care System is dedicated to providing seamless services for vehicle enthusiasts. 
                Explore spare parts, garages, vehicle ads, live support, and more at your fingertips.
            </p>
        </div>

        <!-- Quick Links Section -->
        <div class="footer-section links">
            <h3>Quick Links</h3>
            <ul>
                <li><a href="home.php">Home</a></li>
                <li><a href="products.php">Products</a></li>
                <li><a href="user_details.php">Profile</a></li>
                <li><a href="support.php">Support</a></li>
                <li><a href="q_a.php">Q&A Forum</a></li>
            </ul>
        </div>
        
            <!-- Social Media Section -->
            <div class="footer-section social">
                <h3>Follow Us</h3>
                <div class="social-icons">
                    <a href="#"><img src="images\Social\fb.png" alt="Facebook" style="width: 40px; height: 40px;"></a>
                    <a href="#"><img src="images\Social\x.png" alt="Twitter" style="width: 40px; height: 40px;"></a>
                    <a href="#"><img src="images\Social\insta.png" alt="Instagram" style="width: 40px; height: 40px;"></a>
                    <a href="#"><img src="images\Social\linked.png" alt="LinkedIn" style="width: 40px; height: 40px;"></a>
                </div>
            </div>
        </div>

        <!-- Copyright -->
        <div class="footer-bottom">
            &copy; 2024 Vehicle Care System. All rights reserved.
        </div>
    </footer>

    <script src="script.js"></script>
</body>
</html>
<?php
$conn->close();
?>
<!--VCS-->

