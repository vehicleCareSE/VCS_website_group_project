<?php
session_start();
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "vehicle_care_system";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if user is logged in
$user_id = $_SESSION['user_id'] ?? null;
if (!$user_id) {
    die("Unauthorized access. Please log in.");
}

// Handle Add Vehicle Request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_vehicle'])) {
    $vehicle_name = $_POST['vehicle_name'] ?? null;
    $vehicle_type = $_POST['vehicle_type'] ?? null;
    $model_year = $_POST['model_year'] ?? null;
    $registration_number = $_POST['registration_number'] ?? null;
    $color = $_POST['color'] ?? null;
    $mileage = $_POST['mileage'] ?? null;

    $sql = "INSERT INTO vehicles (user_id, vehicle_name, vehicle_type, model_year, registration_number, color, mileage, added_on) 
            VALUES (?, ?, ?, ?, ?, ?, ?, NOW())";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ississs", $user_id, $vehicle_name, $vehicle_type, $model_year, $registration_number, $color, $mileage);

    if ($stmt->execute()) {
        echo "<script>alert('Vehicle added successfully!');</script>";
    } else {
        echo "<script>alert('Error adding vehicle.');</script>";
    }
}

// Handle Delete Vehicle Request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_vehicle'])) {
    $registration_number = $_POST['registration_number'];

    $sql = "DELETE FROM vehicles WHERE registration_number = ? AND user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si", $registration_number, $user_id);

    if ($stmt->execute()) {
        echo "<script>alert('Vehicle deleted successfully!');</script>";
    } else {
        echo "<script>alert('Error deleting vehicle.');</script>";
    }
}

// Handle Update Vehicle Request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_vehicle'])) {
    $old_registration = $_POST['old_registration'];
    $vehicle_name = $_POST['vehicle_name'];
    $vehicle_type = $_POST['vehicle_type'];
    $model_year = $_POST['model_year'];
    $registration_number = $_POST['registration_number'];
    $color = $_POST['color'];
    $mileage = $_POST['mileage'];

    $sql = "UPDATE vehicles SET 
            vehicle_name = ?, 
            vehicle_type = ?, 
            model_year = ?, 
            registration_number = ?,
            color = ?, 
            mileage = ?
            WHERE registration_number = ? AND user_id = ?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param(
        "ssissssi",
        $vehicle_name,
        $vehicle_type,
        $model_year,
        $registration_number,
        $color,
        $mileage,
        $old_registration,
        $user_id
    );

    if ($stmt->execute()) {
        echo "<script>alert('Vehicle updated successfully!');</script>";
    } else {
        echo "<script>alert('Error updating vehicle.');</script>";
    }
}

// Fetch User's Vehicles
$sql = "SELECT * FROM vehicles WHERE user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$vehicles = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Garage</title>
    <link rel="stylesheet" href="garage01.css">

</head>

<body>
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
    <h2>Garage</h2>

    <div class="cards-container">
        <div class="card" data-id="1" onclick="showPopup();">
            <img src="images\categories\c10.png" alt="Category 1">
            <div class="card-content">
                <h3>Add Vehicle</h3>
            </div>
            <div class="favorite-icon" onclick="toggleFavorite(event, this);"></div>
        </div>

        <div class="card" data-id="2" onclick="toggleVehicleDetails();">
            <img src="images\categories\c10.png" alt="Category 2">
            <div class="card-content">
                <h3>My Vehicle</h3>
            </div>
            <div class="favorite-icon" onclick="toggleFavorite(event, this);"></div>
        </div>
    </div>

    <div id="vehicleDetails" class="vehicle-details">
        <?php if ($vehicles->num_rows > 0): ?>
            <table>
                <tr>
                    <th>Vehicle Name</th>
                    <th>Type</th>
                    <th>Model Year</th>
                    <th>Registration Number</th>
                    <th>Color</th>
                    <th>Mileage</th>
                    <th>Added On</th>
                    <th>Actions</th>
                </tr>
                <?php while ($row = $vehicles->fetch_assoc()): ?>
                    <tr>
                        <td><?= htmlspecialchars($row['vehicle_name']) ?></td>
                        <td><?= htmlspecialchars($row['vehicle_type']) ?></td>
                        <td><?= htmlspecialchars($row['model_year']) ?></td>
                        <td><?= htmlspecialchars($row['registration_number']) ?></td>
                        <td><?= htmlspecialchars($row['color'] ?? '') ?></td>
                        <td><?= htmlspecialchars($row['mileage'] ?? '') ?></td>
                        <td><?= htmlspecialchars($row['added_on']) ?></td>
                        <td class="action-buttons">
                            <div class="button-group">
                                <form method="GET" action="vehicle_services.php" style="display: inline-block;">
                                    <input type="hidden" name="registration_number" value="<?= htmlspecialchars($row['registration_number']) ?>">
                                    <button type="submit" class="view-similar-btn">View Similar</button>
                                </form>
                                <button onclick='showEditPopup(<?= json_encode($row) ?>)' class="edit-btn">Edit</button>
                                <form method="POST" action="" style="display: inline-block;" onsubmit="return confirmDelete()">
                                    <input type="hidden" name="registration_number" value="<?= htmlspecialchars($row['registration_number']) ?>">
                                    <button type="submit" name="delete_vehicle" class="delete-btn">Delete</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </table>
        <?php else: ?>
            <p>No vehicles added yet. Click "Add Vehicle" to add your first vehicle.</p>
        <?php endif; ?>
    </div>

    <!-- Add Vehicle Popup -->
    <div id="overlay" class="overlay" onclick="hidePopup()"></div>
    <div id="popup" class="popup">
        <div class="popup-header">
            Add a New Vehicle
            <button class="close-btn" onclick="hidePopup()">Close</button>
        </div>
        <form method="POST" action="garages.php">
            <input type="text" name="vehicle_name" placeholder="Vehicle Name" required>
            <select name="vehicle_type" required>
                <option value="" disabled selected>Select Type</option>
                <option value="Sedan">Sedan</option>
                <option value="SUV">SUV</option>
                <option value="Truck">Truck</option>
                <option value="Motorbike">Motorbike</option>
            </select>
            <input type="number" name="model_year" placeholder="Model Year" required>
            <input type="text" name="registration_number" placeholder="Registration Number" required>
            <input type="text" name="color" placeholder="Color">
            <input type="number" name="mileage" placeholder="Mileage (km)" step="0.1">
            <button type="submit" name="add_vehicle" class="add-vehicle-btn">Add Vehicle</button>
        </form>
    </div>

    <!-- Edit Vehicle Popup -->
    <div id="editPopup" class="popup">
        <div class="popup-header">
            Edit Vehicle
            <button class="close-btn" onclick="hideEditPopup()">Close</button>
        </div>
        <form method="POST" action="">
            <input type="hidden" name="old_registration" id="old_registration">
            <input type="text" name="vehicle_name" id="edit_vehicle_name" placeholder="Vehicle Name" required>
            <select name="vehicle_type" id="edit_vehicle_type" required>
                <option value="Sedan">Sedan</option>
                <option value="SUV">SUV</option>
                <option value="Truck">Truck</option>
                <option value="Motorbike">Motorbike</option>
            </select>
            <input type="number" name="model_year" id="edit_model_year" placeholder="Model Year" required>
            <input type="text" name="registration_number" id="edit_registration_number" placeholder="Registration Number" required>
            <input type="text" name="color" id="edit_color" placeholder="Color">
            <input type="number" name="mileage" id="edit_mileage" placeholder="Mileage (km)" step="0.1">
            <button type="submit" name="update_vehicle" class="update-vehicle-btn">Update Vehicle</button>
        </form>
    </div>

    <script>
        function showPopup() {
            document.getElementById('popup').style.display = 'block';
            document.getElementById('overlay').style.display = 'block';
        }

        function hidePopup() {
            document.getElementById('popup').style.display = 'none';
            document.getElementById('overlay').style.display = 'none';
        }

        function showEditPopup(vehicleData) {
            document.getElementById('old_registration').value = vehicleData.registration_number;
            document.getElementById('edit_vehicle_name').value = vehicleData.vehicle_name;
            document.getElementById('edit_vehicle_type').value = vehicleData.vehicle_type;
            document.getElementById('edit_model_year').value = vehicleData.model_year;
            document.getElementById('edit_registration_number').value = vehicleData.registration_number;
            document.getElementById('edit_color').value = vehicleData.color;
            document.getElementById('edit_mileage').value = vehicleData.mileage;

            document.getElementById('editPopup').style.display = 'block';
            document.getElementById('overlay').style.display = 'block';
        }

        function hideEditPopup() {
            document.getElementById('editPopup').style.display = 'none';
            document.getElementById('overlay').style.display = 'none';
        }

        function toggleVehicleDetails() {
            const details = document.getElementById('vehicleDetails');
            details.style.display = details.style.display === 'none' ? 'block' : 'none';
        }

        function confirmDelete() {
            return confirm("Are you sure you want to delete this vehicle?");
        }

        // Card ordering functionality
        let originalOrder = [];

        document.addEventListener('DOMContentLoaded', () => {
            const cards = document.querySelectorAll('.card');
            originalOrder = Array.from(cards);
        });

        function toggleFavorite(event, icon) {
            event.stopPropagation();
            const card = icon.closest('.card');
            const container = document.getElementById('cards-container');

            icon.classList.toggle('active');

            if (icon.classList.contains('active')) {
                container.prepend(card);
            } else {
                restoreOriginalPosition(container, card);
            }
        }

        function restoreOriginalPosition(container, card) {
            const currentOrder = Array.from(container.children);
            const originalIndex = originalOrder.indexOf(card);

            if (originalIndex !== -1 && currentOrder[originalIndex] !== card) {
                container.removeChild(card);
                if (originalIndex >= container.children.length) {
                    container.appendChild(card);
                } else {
                    container.insertBefore(card, container.children[originalIndex]);
                }
            }
        }

        // Optional: Add form validation
        function validateVehicleForm(formId) {
            const form = document.getElementById(formId);
            const year = parseInt(form.querySelector('[name="model_year"]').value);
            const currentYear = new Date().getFullYear();

            if (year < 1900 || year > currentYear + 1) {
                alert('Please enter a valid model year between 1900 and ' + (currentYear + 1));
                return false;
            }

            const regNumber = form.querySelector('[name="registration_number"]').value;
            // Add your registration number format validation here
            // Example: if (!regNumber.match(/^[A-Z]{2}-\d{4}$/)) {
            //     alert('Please enter a valid registration number format (e.g., KA-1234)');
            //     return false;
            // }

            return true;
        }

        // Optional: Add search functionality
        function searchVehicles() {
            const searchInput = document.getElementById('vehicleSearch').value.toLowerCase();
            const rows = document.querySelectorAll('#vehicleDetails table tr:not(:first-child)');

            rows.forEach(row => {
                const text = row.textContent.toLowerCase();
                row.style.display = text.includes(searchInput) ? '' : 'none';
            });
        }

        // Optional: Sort table columns
        function sortTable(columnIndex) {
            const table = document.querySelector('#vehicleDetails table');
            const rows = Array.from(table.querySelectorAll('tr:not(:first-child)'));
            const header = table.querySelector('tr:first-child');

            rows.sort((a, b) => {
                const aValue = a.cells[columnIndex].textContent;
                const bValue = b.cells[columnIndex].textContent;
                return aValue.localeCompare(bValue);
            });

            // Remove existing rows
            rows.forEach(row => row.remove());

            // Add sorted rows
            rows.forEach(row => table.appendChild(row));
        }

        // Optional: Export table to CSV
        function exportToCSV() {
            const table = document.querySelector('#vehicleDetails table');
            const rows = table.querySelectorAll('tr');
            let csv = [];

            rows.forEach(row => {
                const rowData = [];
                row.querySelectorAll('td, th').forEach(cell => {
                    rowData.push(cell.textContent);
                });
                csv.push(rowData.join(','));
            });

            const csvContent = "data:text/csv;charset=utf-8," + csv.join('\n');
            const encodedUri = encodeURI(csvContent);
            const link = document.createElement("a");
            link.setAttribute("href", encodedUri);
            link.setAttribute("download", "vehicles.csv");
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        }
    </script>

</body>

</html>

<?php
$conn->close();
?>
