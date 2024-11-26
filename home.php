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

// Logic to notify the front desk if there are new orders
if ($result->num_rows > 0) {
    // You can add any kind of notification system, for example, sound or alert
    echo "<script>
            var audio = new Audio('notification.mp3'); // Add your notification sound file
            audio.play();
            alert('New kitchen order received!');
          </script>";
}

// Fetch all orders and display them as needed

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Antilla Apartments & Suites - Hotel Management Dashboard</title>
    <link rel="stylesheet" href="home.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
    <!-- Sidebar -->
    <aside class="sidebar">
        <div class="icon"><i class="fas fa-hotel"></i></div>
        <div class="icon"><i class="fas fa-tasks"></i></div>
        <div class="icon"><i class="fas fa-calendar-alt"></i></div>
        <div class="icon"><i class="fas fa-cog"></i></div>
    </aside>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Header -->
        <header>
            <input type="text" placeholder="Search rooms, services, and guests" class="search-bar">
            <a href="room.php" class="button new-guest">
                <i class="fas fa-user-plus"></i> New Guest
            </a>
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
                        <p>These are the available rooms</p>
                        <button class="button view-tasks">
                            <i class="fas fa-tasks"></i> <a href="room.php">View all rooms</a>
                        </button>
                        <button class="button view-tasks">
                            <i class="fas fa-tasks"></i> <a href="guest_management.php">Guest Management</a>
                        </button>
                    </div>
                </section>

                <!-- Service Requests Section -->
                <section class="service-requests">
                    <h2>Upcoming Service Requests</h2>
                    <div class="request-item">
                        <span><i class="fas fa-wine-bottle"></i> Bar Request: Additional wine bottles needed at bar</span>
                        <span>07:30 AM</span>
                    </div>
                    <div class="request-item">
                        <span><i class="fas fa-exclamation-circle"></i> Kitchen Update: Out of grilled salmon until further notice</span>
                        <span>08:15 AM</span>
                    </div>
                    <div class="request-item">
                        <span><i class="fas fa-concierge-bell"></i> Front Desk: Confirmed late checkout for Room 205</span>
                        <span>09:00 AM</span>
                    </div>
                    <button class="button view-schedule">
                        <i class="fas fa-calendar"></i> <a href="service-requests.php">View Full Schedule</a>
                    </button>
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

    <!-- Notification Bar and Sound -->
    <div id="notification-bar" style="display: none; background-color: green; color: white; padding: 10px; text-align: center;">
        <strong>New Completed Order!</strong> A completed order has been received.
    </div>

    <audio id="notification-sound" src="notification.mp3" preload="auto"></audio>


    <script src="home.js"></script>

    <script>
    // Call the function to show notification when the page loads
<?php if ($result_kitchen->num_rows > 0): ?>
    showNotification(); // Trigger the notification if there are completed orders
<?php endif; ?>
</script>

</body>
</html>
