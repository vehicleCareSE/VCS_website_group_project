<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$conn = new mysqli('localhost', 'root', '', 'vehicle_care_system');
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

// Handle form submission for adding a new product
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_product'])) {
    $name = $conn->real_escape_string($_POST['name']);
    $description = $conn->real_escape_string($_POST['description']);
    $price = $_POST['price'];
    $stock = $_POST['stock'];
    $image = '';

    // Handle file upload
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $targetDir = "uploads/";

        // Create the uploads directory if it doesn't exist
        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0777, true);
        }

        // Generate a unique file name to avoid overwriting
        $fileName = uniqid() . '_' . basename($_FILES["image"]["name"]);
        $targetFilePath = $targetDir . $fileName;

        // Allow only specific file types
        $allowedTypes = ['jpg', 'jpeg', 'png', 'gif'];
        $fileType = strtolower(pathinfo($targetFilePath, PATHINFO_EXTENSION));

        if (in_array($fileType, $allowedTypes)) {
            // Check file size (e.g., limit to 2MB)
            if ($_FILES["image"]["size"] <= 2 * 1024 * 1024) {
                if (move_uploaded_file($_FILES["image"]["tmp_name"], $targetFilePath)) {
                    $image = $targetFilePath; // Save the file path
                } else {
                    echo "Error uploading the file.";
                    exit();
                }
            } else {
                echo "File size exceeds 2MB limit.";
                exit();
            }
        } else {
            echo "Invalid file type. Only JPG, JPEG, PNG, and GIF files are allowed.";
            exit();
        }
    }

    // Insert product into the database
    $stmt = $conn->prepare("INSERT INTO products (name, description, price, stock, image) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param('ssdis', $name, $description, $price, $stock, $image);
    $stmt->execute();
    $stmt->close();

    // Refresh the page to reflect the new product
    header('Location: products.php');
    exit();
}

// Fetch products from the database
$result = $conn->query("SELECT * FROM products ORDER BY id DESC");
?>


<!DOCTYPE html>
<html>
<head>
    <title>Products</title>
    <link rel="stylesheet" href="products.css">
</head>
<body>
    <div class="products-page">
        <h2>Products</h2>
        <!-- Add Product Button -->
        <button id="add-product-btn">Add Product</button>

        <!-- Products Container -->
        <div class="products-container">
            <?php while ($row = $result->fetch_assoc()): ?>
                <div class="product-widget" onclick="showProductDetails(<?php echo htmlspecialchars(json_encode($row)); ?>)">
                    <img src="<?php echo htmlspecialchars($row['image']); ?>" alt="Product Image">
                    <div class="product-info">
                        <h3><?php echo htmlspecialchars($row['name']); ?></h3>
                        <p>$<?php echo number_format($row['price'], 2); ?></p>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    </div>

    <!-- Add Product Popup -->
    <div id="add-product-popup" class="popup">
        <div class="popup-content">
            <span class="close-popup" onclick="closePopup('add-product-popup')">&times;</span>
            <h3>Add New Product</h3>
            <form method="POST" enctype="multipart/form-data">
                <label for="name">Name:</label>
                <input type="text" id="name" name="name" required>
                <label for="description">Description:</label>
                <textarea id="description" name="description" required></textarea>
                <label for="price">Price:</label>
                <input type="number" id="price" name="price" step="0.01" required>
                <label for="stock">Stock:</label>
                <input type="number" id="stock" name="stock" required>
                <label for="image">Image:</label>
                <input type="file" id="image" name="image" accept="image/*">
                <button type="submit" name="add_product">Add Product</button>
            </form>
        </div>
    </div>

    <!-- Product Details Popup -->
    <div id="product-details-popup" class="popup">
        <div class="popup-content">
            <span class="close-popup" onclick="closePopup('product-details-popup')">&times;</span>
            <div class="product-details">
                <img id="details-image" src="" alt="Product Image">
                <h3 id="details-name"></h3>
                <p id="details-description"></p>
                <p><strong>Price:</strong> $<span id="details-price"></span></p>
                <p><strong>Stock:</strong> <span id="details-stock"></span></p>
            </div>
        </div>
    </div>

    <script src="products.js"></script>
</body>
</html>
