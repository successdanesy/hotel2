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

// Fetching Imprest Requests
$sql_imprest = "SELECT * FROM imprest_requests WHERE status != 'pending'";
$result_imprest = $conn->query($sql_imprest);

// Fetch all orders and display them as needed

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Antilla Front Desk</title>
    <link rel="stylesheet" href="home.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Header -->
        <header>
    <h1>Front-Desk Page</h1>
    <div class="welcome">
        <i class="fas fa-user"></i>
        <span>Welcome, Front-Desk</span>
    </div>

    <a href="logout.php" class="button new-guest">
                <i class="fa-solid fa-arrow-right-from-bracket"></i> Logout
            </a>
            <div class="welcome"><i class="fas fa-user-circle"></i> Welcome <?php echo $_SESSION['username']; ?></div>
</header>

        <!-- Dashboard Content -->
        <div class="dashboard">
            <div class="grid-container">
                <!-- Room Management Section -->
                <section class="room-management">
                    <h2>Room Management</h2>
                    <div class="room-status">
                        <div class="rooms-header">Check Rooms</div>
                        <button class="button view-tasks">
                            <i class="fas fa-tasks"></i> <a href="room.php">View all rooms</a>
                        </button>
                        <button class="button view-tasks">
                            <i class="fas fa-tasks"></i> <a href="guest_management.php">Guest Management</a>
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
