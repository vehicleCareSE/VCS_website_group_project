<?php
// Connect to the database
$conn = new mysqli('localhost', 'root', '', 'vehicle_care_system');
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle DELETE request
if (isset($_GET['delete_id'])) {
    $delete_id = $_GET['delete_id'];
    $stmt = $conn->prepare("DELETE FROM spare_parts WHERE spare_id = ?");
    $stmt->bind_param('i', $delete_id);
    $stmt->execute();
    $stmt->close();
    header('Location: admin_Dashboard.php'); // Refresh the page
    exit();
}

// Handle ADD PRODUCT request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_product'])) {
    $item_name = $_POST['item_name'];
    $description = $_POST['description'];
    $price = $_POST['price'];
    $image_path = $_POST['image_path'];
    $stock = $_POST['stock'];

    $stmt = $conn->prepare("INSERT INTO spare_parts (item_name, description, price, image_path, stock) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param('ssdsi', $item_name, $description, $price, $image_path, $stock);
    $stmt->execute();
    $stmt->close();
    header('Location: admin_Dashboard.php'); // Refresh the page
    exit();
}

// Handle EDIT PRODUCT request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_product'])) {
    $spare_id = $_POST['spare_id'];
    $item_name = $_POST['item_name'];
    $image_path = $_POST['image_path'];
    $stock = $_POST['stock'];

    $stmt = $conn->prepare("UPDATE spare_parts SET item_name = ?, image_path = ?, stock = ? WHERE spare_id = ?");
    $stmt->bind_param('ssii', $item_name, $image_path, $stock, $spare_id);
    $stmt->execute();
    $stmt->close();
    header('Location: admin_Dashboard.php'); // Refresh the page
    exit();
}

// Fetch all spare parts
$result = $conn->query("SELECT spare_id, item_name, stock, image_path, sales FROM spare_parts ORDER BY spare_id ASC");
?>

<!DOCTYPE html>
<html>

<head>
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="admin_dashboard.css">
</head>

<body class="dashboard-body">
    <div class="menu-bar">
        <ul>
            <li><a href="">Dashboard</a></li>
            <li><a href="admin_garage.php">Garage</a></li>
            <li><a href="admin_order.php">Orders</a></li>
            <li><a href="user_details.php">Profile</a></li>
            <li><a href="livesupport.php">Support</a></li>
            <li><a href="logout.php" id="logout-button" onclick="return confirmLogout(event);">Logout</a></li>
            <li><a href="home.php" id="seller-btn">Switch to Buying</a></li>
        </ul>
    </div>
    <div class="dashboard-container">
        <h1>Admin Dashboard</h1>

        <!-- Spare Parts Table -->
        <div class="table-container">
        <button class="add-product-button" onclick="openAddModal()">Add Product</button>
            <h2>All Spare Parts</h2>
            <table>
                <thead>
                    <tr>
                        <th>Image</th>
                        <th>Name</th>
                        <th>Stock</th>
                        <th>Sales</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><img src="<?= $row['image_path']; ?>" alt="<?= $row['item_name']; ?>" class="product-image"></td>
                            <td><?= $row['item_name']; ?></td>
                            <td><?= $row['stock']; ?></td>
                            <td><?= htmlspecialchars($row['sales']); ?></td>
                            <td>
                                <button class="delete-button" onclick="confirmDelete(<?= $row['spare_id']; ?>)">Delete</button>
                                <button class="edit-button" onclick="openEditModal(<?= htmlspecialchars(json_encode($row)); ?>)">Edit</button>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>

        <!-- Add Product Button -->
       

        <!-- Add Modal -->
        <div id="addProductModal" class="modal">
            <div class="modal-content">
                <span class="close" onclick="closeAddModal()">&times;</span>
                <h2>Add New Spare Part</h2>
                <form method="POST">
                    <input type="text" name="item_name" placeholder="Item Name" required>
                    <textarea name="description" placeholder="Description" required></textarea>
                    <input type="number" name="price" placeholder="Price" step="0.01" required>
                    <input type="text" name="image_path" placeholder="Image Path" required>
                    <input type="number" name="stock" placeholder="Stock" required>
                    <input type="hidden" name="add_product" value="1">
                    <button type="submit">Add Product</button>
                </form>
            </div>
        </div>

        <!-- Edit Modal -->
        <div id="editProductModal" class="modal">
            <div class="modal-content">
                <span class="close" onclick="closeEditModal()">&times;</span>
                <h2>Edit Spare Part</h2>
                <form method="POST">
                    <input type="hidden" name="spare_id" id="edit-spare-id">
                    <input type="text" name="item_name" id="edit-item-name" placeholder="Item Name" required>
                    <input type="text" name="image_path" id="edit-image-path" placeholder="Image Path" required>
                    <input type="number" name="stock" id="edit-stock" placeholder="Stock" required>
                    <input type="hidden" name="edit_product" value="1">
                    <button type="submit">Update Product</button>
                </form>
            </div>
        </div>

        <script>
            function openAddModal() {
                document.getElementById('addProductModal').style.display = 'block';
            }

            function closeAddModal() {
                document.getElementById('addProductModal').style.display = 'none';
            }

            function openEditModal(row) {
                document.getElementById('edit-spare-id').value = row.spare_id;
                document.getElementById('edit-item-name').value = row.item_name;
                document.getElementById('edit-image-path').value = row.image_path;
                document.getElementById('edit-stock').value = row.stock;
                document.getElementById('editProductModal').style.display = 'block';
            }

            function closeEditModal() {
                document.getElementById('editProductModal').style.display = 'none';
            }

            function confirmDelete(id) {
                if (confirm("Are you sure you want to delete this item?")) {
                    window.location.href = `admin_Dashboard.php?delete_id=${id}`;
                }
            }
        </script>
    </div>
</body>

</html>
