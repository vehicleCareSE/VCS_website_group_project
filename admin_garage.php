<?php
// Database Connection
$conn = new mysqli('localhost', 'root', '', 'vehicle_care_system');
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch total services using prepared statement
$totalServicesQuery = "SELECT COUNT(*) as total_services FROM services";
$totalServicesResult = $conn->query($totalServicesQuery);
$totalServices = $totalServicesResult->fetch_assoc()['total_services'];

// Fetch services per month using prepared statement
$currentMonth = date('Y-m');
$servicesPerMonthQuery = "SELECT COUNT(*) as services_this_month FROM services WHERE DATE_FORMAT(service_date, '%Y-%m') = ?";
$stmt = $conn->prepare($servicesPerMonthQuery);
$stmt->bind_param('s', $currentMonth);
$stmt->execute();
$result = $stmt->get_result();
$servicesThisMonth = $result->fetch_assoc()['services_this_month'];
$stmt->close();

// Fetch total vehicles
$totalVehiclesQuery = "SELECT COUNT(*) as total_vehicles FROM vehicles";
$totalVehiclesResult = $conn->query($totalVehiclesQuery);
$totalVehicles = $totalVehiclesResult->fetch_assoc()['total_vehicles'];

// Fetch vehicles registered this month using prepared statement
$vehiclesRegisteredQuery = "SELECT COUNT(*) as vehicles_registered FROM vehicles WHERE DATE_FORMAT(added_on, '%Y-%m') = ?";
$stmt = $conn->prepare($vehiclesRegisteredQuery);
$stmt->bind_param('s', $currentMonth);
$stmt->execute();
$result = $stmt->get_result();
$vehiclesRegistered = $result->fetch_assoc()['vehicles_registered'];
$stmt->close();

// Handle Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Make sure registration number is provided and not empty
    if (empty($_POST['vehicle_registration_number'])) {
        echo "<script>alert('Please enter a valid vehicle registration number!');</script>";
    } else {
        // Get vehicle_id and vehicle_name from registration number
        $vehicleQuery = "SELECT vehicle_id, vehicle_name FROM vehicles WHERE registration_number = ?";
        $stmt = $conn->prepare($vehicleQuery);
        $stmt->bind_param('s', $_POST['vehicle_registration_number']);
        $stmt->execute();
        $result = $stmt->get_result();
        $vehicleData = $result->fetch_assoc();
        $stmt->close();
 
        if (!$vehicleData) {
            echo "<script>alert('Vehicle not found!');</script>";
        } else {
            $vehicle_id = $vehicleData['vehicle_id'];
            $vehicle_name = $vehicleData['vehicle_name']; 
           
            $sql = "INSERT INTO services (
                        driver_name, telephone_number, current_mileage, service_description,
                        defects_and_deficiencies, extra_accessories, service_check, tyre_change,
                        replacement_vehicle, client_signature, repairer_stamp, vehicle_id,
                        vehicle_name, vehicle_registration_number
                    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
           
            $stmt = $conn->prepare($sql);
            $service_check = isset($_POST['service_check']) ? 1 : 0;
            $tyre_change = isset($_POST['tyre_change']) ? 1 : 0;
            $replacement_vehicle = isset($_POST['replacement_vehicle']) ? 1 : 0;
           
            $stmt->bind_param(
                "ssisssiiississ",
                $_POST['driver_name'],
                $_POST['telephone_number'],
                $_POST['current_mileage'],
                $_POST['service_description'],
                $_POST['defects_and_deficiencies'],
                $_POST['extra_accessories'],
                $service_check,
                $tyre_change,
                $replacement_vehicle,
                $_POST['client_signature'],
                $_POST['repairer_stamp'],
                $vehicle_id,
                $vehicle_name,
                $_POST['vehicle_registration_number']
            );
 
            if ($stmt->execute()) {
                echo "<script>alert('Service added successfully!');</script>";
            } else {
                echo "<script>alert('Error: " . $stmt->error . "');</script>";
            }
 
            $stmt->close();
        }
    }
}

// Handle AJAX Requests
if (isset($_GET['action'])) {
    header('Content-Type: application/json');
    
    if ($_GET['action'] == 'total_services') {
        $query = "SELECT s.service_id, s.driver_name, s.telephone_number, s.service_date, 
                         v.registration_number
                  FROM services s
                  JOIN vehicles v ON s.vehicle_id = v.vehicle_id";
        
        if (!($result = $conn->query($query))) {
            echo json_encode(['error' => 'Query failed: ' . $conn->error]);
            exit;
        }
        
        $services = $result->fetch_all(MYSQLI_ASSOC);
        echo json_encode($services);
        exit;
    }

    if ($_GET['action'] == 'total_vehicles') {
        $query = "SELECT vehicle_id, vehicle_name, vehicle_type, registration_number, 
                         color, mileage, added_on
                  FROM vehicles";
                  
        if (!($result = $conn->query($query))) {
            echo json_encode(['error' => 'Query failed: ' . $conn->error]);
            exit;
        }
        
        $vehicles = $result->fetch_all(MYSQLI_ASSOC);
        echo json_encode($vehicles);
        exit;
    }
}

// Handle Vehicle Suggestions
if (isset($_GET['query'])) {
    header('Content-Type: application/json');
    $query = $_GET['query'];
    $stmt = $conn->prepare("SELECT registration_number, vehicle_name FROM vehicles WHERE registration_number LIKE ?");
    $searchTerm = "%" . $query . "%";
    $stmt->bind_param("s", $searchTerm);
    $stmt->execute();
    $result = $stmt->get_result();
    $suggestions = [];

    while ($row = $result->fetch_assoc()) {
        $suggestions[] = $row;
    }

    echo json_encode($suggestions);
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vehicle Service Form</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f4f9;
            color: #333;
        }

        button {
            padding: 10px 20px;
            background-color: #007BFF;
            color: #fff;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        button:hover {
            background-color: #0056b3;
        }

        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            justify-content: center;
            align-items: center;
            z-index: 1000;
        }

        .modal-content {
            background-color: #fff;
            padding: 20px;
            border-radius: 5px;
            width: 50%;
            max-height: 80%;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
            position: relative;
            overflow-y: auto;
        }

        .modal-content h2 {
            margin-top: 0;
        }

        .close {
            position: absolute;
            top: 10px;
            right: 15px;
            font-size: 20px;
            cursor: pointer;
        }

        label {
            display: block;
            margin-top: 10px;
        }

        input,
        textarea {
            width: 100%;
            padding: 10px;
            margin-top: 5px;
            margin-bottom: 15px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }

        #suggestions {
            border: 1px solid #ccc;
            max-height: 150px;
            overflow-y: auto;
            background-color: #fff;
            position: absolute;
            z-index: 1000;
        }

        #suggestions div {
            padding: 10px;
            cursor: pointer;
        }

        #suggestions div:hover {
            background-color: #007BFF;
            color: #fff;
        }

        #no-match {
            color: red;
            font-size: 12px;
            margin-top: -10px;
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

        #seller-btn {
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            position: fixed;
            top: 20px;
            right: 10px;
            z-index: 1000;
            cursor: pointer;
            transition: background-color 0.3s ease;
            display: block;
            background-color: #2e7dcc;
            color: white;
            font-size: 16px;
        }

        #seller-btn:hover {
            background-color: #c82333;
        }

        .service-button-container {
            display: flex;
            justify-content: center;
            margin-top: 40px;
        }

        .service-button {
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
            padding: 15px;
            background-color: rgb(255, 255, 255);
            color: black;
            border-radius: 10px;
            margin: 10px;
            width: 150px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            cursor: pointer;
            transition: background-color 0.3s ease, transform 0.3s ease;
        }

        .service-button:hover {
            background-color: rgba(121, 184, 251, 0.45);
            transform: translateY(-5px);
        }

        .service-button img {
            width: 50px;
            height: 50px;
            margin-bottom: 10px;
        }

        .background-card {
            display: flex;
            justify-content: center;
            /* Center dashboard horizontally */
            margin-top: 20px;
            /* Add a margin at the top */


        }

        .dashboard {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            /* 4 cards per row */
            gap: 70px;
            /* Reduced gap between cards */
        }

        .card {
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            padding: 20px;
            width: 200px;
            /* Fixed width */
            height: 200px;
            /* Fixed height */
            text-align: center;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
        }

        .card h3 {
            padding-top: 100px;
            /* Add padding to push it down slightly */
            margin: 0;
            /* Ensure there's no extra margin */
            font-size: 15px;
            color: #333;
        }

        .card p {
            font-size: 120px;
            color: #555;
            margin-top: -5px;
            /* Adjust this value to move it upward */
        }

        .filters {

            /* Fixed width */

            display: flex;
            gap: 10px;
            margin: 20px auto;
            /* Center filters horizontally */
            justify-content: center;
            /* Center filters horizontally */
        }

        .filters select,
        .filters input {
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }
    </style>
</head>

<body>

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
    <h1 style="text-align:center;">Sales Dashboard</h1>
    <div class="filters">
        <input type="text" value="This Month" readonly>
        <select>
            <option>All Services</option>
        </select>
        <select>
            <option>All Posts</option>
        </select>
    </div>
    <div class="background-card">
        <div class="dashboard">
            <div class="card">
                <h3><button id="btnTotalServices">Total Services</button></h3>
                <p><?= $totalServices; ?></p>
            </div>
            <div class="card">
                <h3>Services This Month</h3>
                <p><?= $servicesThisMonth; ?></p>
            </div>
            <div  class="card">
                <h3><button id="btnTotalVehicles">Total Vehicles</button></h3>
                <p><?= $totalVehicles; ?></p>
            </div>
            <div class="card">
                <h3>Vehicles Registered This Month</h3>
                <p><?= $vehiclesRegistered; ?></p>
            </div>
        </div>
    </div>

    <div class="service-button-container">
        <div class="service-button" id="addServiceBtn">
            <img src="images\categories\admingarge_service.png" alt="Add Service">
            <span>Add Service</span>
        </div>
        <div class="service-button" id="addServiceBtn">
            <img src="images\categories\admingarge_service.png" alt="Add Service">
            <span>Add Service</span>
        </div>
    </div>

    <div id="serviceFormModal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <h2>Vehicle Service Form</h2>
            <form method="POST">
                <label for="driver_name">Driver’s Name:</label>
                <input type="text" id="driver_name" name="driver_name" required>

                <label for="telephone_number">Telephone Number:</label>
                <input type="text" id="telephone_number" name="telephone_number" required>

                <label for="vehicle_registration_number">Vehicle Registration Number:</label>
                <input type="text" id="vehicle_registration_number" name="vehicle_registration_number" autocomplete="off" required>
                <div id="suggestions"></div>
                <p id="no-match" style="display:none;">No matching registration number found!</p>

                <label for="vehicle_name">Vehicle Name:</label>
                <input type="text" id="vehicle_name" name="vehicle_name" readonly required>

                <label for="current_mileage">Current Mileage:</label>
                <input type="number" id="current_mileage" name="current_mileage" required>

                <label for="service_description">Service Description:</label>
                <textarea id="service_description" name="service_description" required></textarea>

                <label for="defects_and_deficiencies">Defects and Deficiencies:</label>
                <textarea id="defects_and_deficiencies" name="defects_and_deficiencies"></textarea>

                <label for="extra_accessories">Extra Accessories:</label>
                <textarea id="extra_accessories" name="extra_accessories"></textarea>

                <label>Service Options:</label>
                <input type="checkbox" id="service_check" name="service_check">
                <label for="service_check">Service Check</label><br>
                <input type="checkbox" id="tyre_change" name="tyre_change">
                <label for="tyre_change">Tyre Change</label><br>
                <input type="checkbox" id="replacement_vehicle" name="replacement_vehicle">
                <label for="replacement_vehicle">Replacement Vehicle</label><br>

                <label for="client_signature">Client Signature:</label>
                <input type="text" id="client_signature" name="client_signature" required>

                <label for="repairer_stamp">Repairer Stamp:</label>
                <input type="text" id="repairer_stamp" name="repairer_stamp" required>

                <button type="submit">Submit</button>
            </form>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const modal = document.getElementById('serviceFormModal');
            const openBtn = document.getElementById('addServiceBtn');
            const closeBtn = document.querySelector('.close');
            const registrationInput = document.getElementById('vehicle_registration_number');
            const suggestionsBox = document.getElementById('suggestions');
            const noMatch = document.getElementById('no-match');
            const vehicleNameInput = document.getElementById('vehicle_name');

            // Open Modal
            openBtn.addEventListener('click', () => {
                modal.style.display = 'flex';
            });

            // Close Modal
            closeBtn.addEventListener('click', () => {
                modal.style.display = 'none';
            });

            // Close Modal on Outside Click
            window.addEventListener('click', (e) => {
                if (e.target === modal) {
                    modal.style.display = 'none';
                }
            });

            // Fetch Suggestions
            registrationInput.addEventListener('input', () => {
                const query = registrationInput.value;

                if (query.length > 1) {
                    fetch(`?query=${query}`)
                        .then(response => response.json())
                        .then(data => {
                            suggestionsBox.innerHTML = '';
                            noMatch.style.display = 'none';

                            if (data.length > 0) {
                                data.forEach(vehicle => {
                                    const div = document.createElement('div');
                                    div.textContent = vehicle.registration_number;
                                    div.setAttribute('data-name', vehicle.vehicle_name);
                                    div.addEventListener('click', () => {
                                        registrationInput.value = vehicle.registration_number;
                                        vehicleNameInput.value = vehicle.vehicle_name;
                                        suggestionsBox.innerHTML = '';
                                    });
                                    suggestionsBox.appendChild(div);
                                });
                            } else {
                                noMatch.style.display = 'block';
                            }
                        });
                } else {
                    suggestionsBox.innerHTML = '';
                    noMatch.style.display = 'none';
                }
            });
        });


        document.getElementById('btnTotalServices').addEventListener('click', () => {
    fetch('admin_garage.php?action=total_services')
        .then(response => response.json())
        .then(data => {
            console.log(data);
        });
});

// Fetch and display total vehicles
document.getElementById('btnTotalVehicles').addEventListener('click', () => {
    fetch('admin_garage.php?action=total_vehicles')
        .then(response => response.json())
        .then(data => {
            console.log(data);
        });
});
    </script>
</body>

</html>