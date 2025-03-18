<?php
// Start session
session_start();

// Check if user_id is set in the session
if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id']; // Get the logged-in user's ID from session
} else {
    $user_id = null; // If no user is logged in, set user_id as null
}

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "vehicle_care_system";

$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle form submission for creating an ad
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] == 'create_ad') {
    $title = $_POST['title'] ?? null;
    $description = $_POST['description'] ?? null;
    $category = $_POST['category'] ?? null;
    $price = $_POST['price'] ?? null;
    $phone_number = $_POST['phone_number'];
    $image_url = $_POST['image_url'] ?? null;

    // Validation
    if (!$title || !$description || !$category || !$phone_number || !$price) {
        die("Please fill out all required fields.");
    }

    // Insert ad into the database
    $sql = "INSERT INTO ads (user_id, title, description, image_url, category, price, phone_number) VALUES (?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);

    // Use the correct type string
    $stmt->bind_param("issssds", $user_id, $title, $description, $image_url, $category, $price, $phone_number);


    if ($stmt->execute()) {
        header("Location: " . $_SERVER['PHP_SELF']); // Redirect to avoid resubmitting the form
        exit();
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
}

// Handle ad deletion
if (isset($_GET['delete_id'])) {
    $delete_id = $_GET['delete_id'];

    // Check if the logged-in user is the owner of the ad
    $sql = "SELECT user_id FROM ads WHERE ad_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $delete_id);
    $stmt->execute();
    $stmt->store_result();
    $stmt->bind_result($ad_user_id);
    $stmt->fetch();

    // Only allow deletion if the logged-in user is the creator of the ad
    if ($ad_user_id !== $user_id) {
        die("You are not authorized to delete this ad.");
    }

    // Delete ad from the database
    $sql = "DELETE FROM ads WHERE ad_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $delete_id);

    if ($stmt->execute()) {
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    } else {
        echo "Error deleting ad: " . $stmt->error;
    }

    $stmt->close();
}
$statuses = ['Pending', 'Approved', 'Rejected'];
// Fetch all ads to display
$sql = "SELECT a.ad_id, a.title, a.description, a.image_url, a.category, a.price, a.created_at,a.phone_number, u.name, a.user_id as ad_user_id 
        FROM ads a 
        JOIN users u ON a.user_id = u.user_id 
        ORDER BY a.created_at DESC";
$result = $conn->query($sql);
// Define available statuses


?>



<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ads Page</title>
    <style>
        /* General Styles */
        body {
            margin: 0;
            padding: 0;
            font-family: Arial, sans-serif;
            background-color: #f9f9f9;
            color: #333;
        }

        /* Container */
        .container {
            width: 90%;
            height: 100%;
            max-width: 1000px;
            margin: 10px auto;
            padding: 10px 100px;
            background: #fff;
            border-radius: 8px;
            position: relative;
        }

        /* Heading */
        h1 {
            text-align: center;
            color: #444;
            font-size: 3em;
            margin-top: 15px;
            font-weight: 600;
        }

        /* Create Ad Button */
        .create-ad-btn {
            position: fixed;
            top: 15px;
            right: 10px;
            padding: 15px;
            width: 10%;
            border: 1px solid #ccc;
            border-radius: 60px;
            background-color: #4CAF50;
            color: white;
            font-weight: bold;
            cursor: pointer;
            text-align: center;
            transition: background-color 0.3s ease;
            z-index: 1000;
        }

        .create-ad-btn:hover {
            background-color: #45a049;
        }

        /* Modal Styles */
        .modal {
            display: none;
            position: fixed;
            z-index: 10;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
        }

        .modal-content {
            background-color: #fff;
            margin: 10% auto;
            padding: 20px;
            border: 1px solid #ccc;
            border-radius: 10px;
            width: 40%;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }

        .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
        }

        .close:hover {
            color: #000;
        }

        /* Form Styling */
        form {
            display: flex;
            flex-direction: column;
            gap: 15px;
            max-width: 400px;
            margin: 20px auto;
            padding: 20px;
            border: 1px solid #ccc;
            border-radius: 10px;
            background: #fff;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        input[type="text"],
        input[type="number"],
        input[type="phonenumber"],
        textarea {
            width: calc(100% - 20px);
            padding: 10px;
            margin: 0 auto;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 1em;
            background: #fdfdfd;
        }

        textarea {
            resize: none;
        }

        button[type="submit"] {
            background-color: #4CAF50;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 1em;
            font-weight: bold;
            transition: background-color 0.3s ease;
        }

        button[type="submit"]:hover {
            background-color: #45a049;
        }

        /* Ads Display Section */
        .ads-display {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 2fr));
            gap: 20px;
            width: 90%;
            max-width: 1200px;
            margin-top: 20px;
        }

        .ad-box {
            position: relative;
            /* Make the ad-box the reference for the delete button */
            background: linear-gradient(135deg, #ffffff, #f9f9f9);
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            padding-top: 15px;
            /* Add padding if needed to avoid overlap */
        }


        .ad-box:hover {
            transform: scale(1.03);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.2);
        }

        .ad-image {
            background: #e9ecef;
            padding: 15px;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .ad-image img {
            max-width: 100%;
            height: auto;
            border-radius: 8px;
        }

        .ad-content {
            padding: 20px;
            text-align: center;
        }

        .ad-box h3 {
            font-size: 1.4em;
            margin-bottom: 10px;
            color: #333;
            font-weight: bold;
        }

        .ad-box p {
            font-size: 0.95em;
            color: #666;
            line-height: 1.5;
            margin: 10px 0;
        }

        .ad-box .price {
            font-size: 1.2em;
            color: #28a745;
            font-weight: bold;
            margin: 15px 0;
        }

        .ad-box .category {
            font-size: 0.85em;
            color: #888;
            font-style: italic;
        }

        .delete-btn {
            position: fixed;
            /* Position relative to the ad-box */
            top: 25px;
            /* Distance from the top */
            right: 10px;
            /* Distance from the right */
            background-color: #f44336;
            color: white;
            padding: 5px 10px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 0.9em;
            transition: background-color 0.3s ease;
            z-index: 10;
            /* Ensure it stays above other elements */
        }

        .delete-btn:hover {
            background-color: #d32f2f;
        }

        .menu-bar {
            background-color: rgb(255, 255, 255);
            color: #333;
            padding: 30px 0;
            text-align: center;
            box-shadow: 0 4px 60px rgba(0, 0, 0, 0.15);
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

        .logo_img{
            
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
        <img class="logo_img" src="logovcs.png" alt="logo">
    </ul>
</div>



    <div class="container">
        <h1>Ads Section</h1>

        <button class="create-ad-btn" onclick="document.getElementById('adFormModal').style.display = 'block'">Post Your Ad</button>

        <!-- Modal for Creating Ad -->
        <div id="adFormModal" class="modal">
            <div class="modal-content">
                <span class="close" onclick="document.getElementById('adFormModal').style.display = 'none'">&times;</span>
                <form action="ads.php" method="POST" id="adForm">
                    <input type="text" name="title" placeholder="Ad Title" required>
                    <textarea name="description" placeholder="Description" rows="4" required></textarea>
                    <input type="text" name="category" placeholder="Category" required>
                    <input type="number" name="price" placeholder="Price" required>
                    <input type="text" name="phone_number" id="phone_number" placeholder="Enter your phone number" required>
                    <input type="text" name="image_url" placeholder="Image URL" required>
                    <input type="hidden" name="action" value="create_ad">
                    <button type="submit">Post Ad</button>
                </form>
            </div>
        </div>

        <!-- Display Ads -->
        <div class="ads-display">
            <?php
            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    echo '<div class="ad-box">';
                    echo '<div class="ad-image"><img src="' . $row['image_url'] . '" alt="' . $row['title'] . '"></div>';
                    echo '<div class="ad-content">';
                    echo '<h3>' . $row['title'] . '</h3>';
                    echo '<p>' . $row['description'] . '</p>';
                    echo '<div class="price">$' . $row['price'] . '</div>';
                    echo "<p>Phone Number: " . $row['phone_number'] . "</p>";
                    echo '<div class="category">Category: ' . $row['category'] . '</div>';
                    echo '<div class="date">Posted on: ' . $row['created_at'] . '</div>';

                    // Show delete button only if the user is the creator of the ad
                    if ($row['ad_user_id'] == $user_id) {
                        echo '<a href="javascript:void(0);" class="delete-btn" onclick="confirmDelete(' . $row['ad_id'] . ')">Delete</a>';
                    }
                    echo '</div>';
                    echo '</div>';
                }
            } else {
                echo "<p>No ads available.</p>";
            }
            ?>
        </div>
    </div>

    <script>
        // Close the modal if the user clicks outside of it
        window.onclick = function(event) {
            if (event.target === document.getElementById('adFormModal')) {
                document.getElementById('adFormModal').style.display = 'none';
            }
        }

        function confirmDelete(ad_id) {
            var confirmDelete = confirm("Are you sure you want to delete this ad?");
            if (confirmDelete) {
                window.location.href = "ads.php?delete_id=" + ad_id;
            }
        }
    </script>

</body>

</html>

<?php
$conn->close();
?>
