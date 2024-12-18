<?php
session_start();

include('db_connect.php');

if (!isset($_SESSION['username'])) {
    $_SESSION['msg'] = "You must log in first";
    header('location: login.php');
}

if (isset($_GET['logout'])) {
    session_destroy();
    unset($_SESSION['username']);
    header("location: login.php");
}

require_once 'server.php'; // To include your database connection

// Fetching Kitchen Orders
$sql_kitchen = "SELECT * FROM kitchen_orders WHERE status = 'completed'"; // Only fetching completed orders
$result_kitchen = $conn->query($sql_kitchen);

// Fetching Bar Orders
$sql_bar = "SELECT * FROM bar_orders"; // Adjust this query based on your table and schema
$result_bar = $conn->query($sql_bar);


// Fetch orders marked as 'sent to front desk'
$query = "SELECT * FROM kitchen_orders WHERE status = 'sent to front desk'";
$result = $conn->query($query);

// Fetch orders marked as 'sent to front desk' bar
$query = "SELECT * FROM bar_orders WHERE status = 'sent to front desk'";
$result = $conn->query($query);



// Fetch all orders and display them as needed

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Antilla Hotel Manager</title>
    <link rel="stylesheet" href="manager_1.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>

<header>
    <h1>Manager Page</h1>
    <div class="welcome">
        <i class="fas fa-user"></i>
        <span>Welcome, Manager</span>
    </div>

    <a href="logout.php" class="button new-guest">
                <i class="fa-solid fa-arrow-right-from-bracket"></i> Logout
            </a>
</header>

        <!-- Dashboard Content -->
        <div class="dashboard">
            <div class="grid-container">
                <!-- Imprest Section -->
                <section class="imprest-section">
                    <h2>Imprest Management</h2>
                    <div class="column-status">
                        <div class="header">Check Imprest</div>
                        <button class="button view-tasks">
                            <i class="fas fa-tasks"></i> <a href="manager_imprest.php">Hotel Imprest Request</a>
                        </button>
                    </div>
                </section>

                <!-- Service Requests Section -->
                <section class="Spreadsheet-section">
                    <h2>Spreadsheet Export</h2>
                    <div class="column-status">
                        <div class="header">Export Spreadsheets</div>
                        <button class="button view-tasks">
                            <i class="fas fa-tasks"></i> <a href="manager_imprest.php">Click Here</a>
                        </button>
                    </div>
                </section>

                <!-- Kitchen Order Section -->
<section class="kitchen-order">
    <h2>Kitchen Order - Antilla Apartments & Suites</h2>
    <table id="kitchen-orders">
        <tr>
            <th>Room Number</th>
            <th>Order Description</th>
            <th>Total Amount (₦)</th>
            <th>Status</th>
            <th>Special Instructions</th>
        </tr>
        <?php
            if ($result_kitchen->num_rows > 0) {
                while ($order = $result_kitchen->fetch_assoc()) {
                    echo "<tr>";
                    echo "<td>" . htmlspecialchars($order['room_number']) . "</td>";
                    echo "<td>" . htmlspecialchars($order['order_description']) . "</td>";
                    echo "<td>" . number_format($order['total_amount'], 2) . "</td>";
                    echo "<td>" . htmlspecialchars($order['status']) . "</td>";
                    echo "<td>" . htmlspecialchars($order['special_instructions']) . "</td>";
                    echo "</tr>";
                }
            } else {
                echo "<tr><td colspan='5'>No kitchen orders available.</td></tr>";
            }
            ?>
    </table>
</section>


                <!-- Bar Order Section -->
                <section class="bar-order">
                    <h2>Bar Order - Antilla Apartments & Suites</h2>
                    <table id="bar-orders">
                        <tr>
                            <th><i class="fas fa-glass-cheers"></i> Room Orders</th>
                        </tr>
                        <!-- Data from database will be injected here -->
                        <?php
                        if ($result_bar->num_rows > 0) {
                            while ($order = $result_bar->fetch_assoc()) {
                                echo "<tr><td>Room " . $order['room_number'] . " - " . $order['order_description'] . " (Status: " . $order['status'] . ")</td></tr>";
                            }
                        } else {
                            echo "<tr><td>No bar orders available.</td></tr>";
                        }
                        ?>
                    </table>
                </section>
            </div>
        </div>
    </div>


    <script src="home.js"></script>

</body>
</html>
