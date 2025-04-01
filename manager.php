<?php
session_start();

include('db_connect.php');

if (!isset($_SESSION['username'])) {
    $_SESSION['msg'] = "You must log in first";
    header('location: login.php');
    exit();
}

if (isset($_GET['logout'])) {
    session_destroy();
    unset($_SESSION['username']);
    header("location: login.php");
    exit();
}

// require_once 'server.php'; // To include your database connection

// Get the selected date from the form submission or default to today's date
$selected_date = isset($_GET['selected_date']) ? $_GET['selected_date'] : date('Y-m-d');

// Verify that the date is properly formatted
if (DateTime::createFromFormat('Y-m-d', $selected_date) === false) {
    $selected_date = date('Y-m-d'); // Fallback to today's date if the format is incorrect
}

// Fetching Kitchen Orders based on the selected date
$sql_kitchen = "SELECT * FROM kitchen_orders WHERE DATE(timestamp) = ?";
$stmt_kitchen = $conn->prepare($sql_kitchen);
$stmt_kitchen->bind_param("s", $selected_date);
$stmt_kitchen->execute();
$result_kitchen = $stmt_kitchen->get_result();

// Fetching Bar Orders based on the selected date
$sql_bar = "SELECT * FROM bar_orders WHERE DATE(timestamp) = ?";
$stmt_bar = $conn->prepare($sql_bar);
$stmt_bar->bind_param("s", $selected_date);
$stmt_bar->execute();
$result_bar = $stmt_bar->get_result();

// Debugging: Verify the selected date and result sets
error_log("Selected Date: " . $selected_date);
error_log("Kitchen Orders Count: " . $result_kitchen->num_rows);
error_log("Bar Orders Count: " . $result_bar->num_rows);

$stmt_kitchen->close();
$stmt_bar->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Antilla Hotel Manager</title>
    <link rel="stylesheet" href="home.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>

<!-- Main Content -->
<div class="main-content">
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

<!-- Date Filter Form -->
<form method="GET" action="manager.php" class="filter-form">
    <label for="selected_date">Select Date:</label>
    <input type="date" id="selected_date" name="selected_date" value="<?php echo $selected_date; ?>" required>
    <button type="submit" class="button"><i class="fa-solid fa-filter"></i>Filter</button>
</form>

<!-- Dashboard Content -->
<div class="dashboard">
    <div class="grid-container">
        <!-- Imprest Section -->
        <section class="room-management">
            <h2>Imprest Management</h2>
            <div class="room-status">
                <div class="rooms-header">Check Imprest</div>
                <button class="button view-tasks">
                    <i class="fas fa-tasks"></i> <a href="manager_imprest.php">Hotel Imprest Request</a>
                </button>
            </div>
        </section>

        <!-- Service Requests Section -->
        <section class="service-requests">
            <h2>Spreadsheet Export</h2>
            <div class="room-status">
                <div class="rooms-header">Income Spreadsheets</div>
                <button class="button view-tasks">
                <i class="fa-solid fa-table"></i></i> <a href="income.php">Click Here</a>
                </button>
                <div class="rooms-header">Expense Spreadsheets</div>
                <button class="button view-tasks">
                <i class="fa-solid fa-table"></i> <a href="expense.php">Click Here</a>
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
                        echo "<tr><td colspan='5'>No kitchen orders available for the selected date.</td></tr>";
                    }
                ?>
            </table>
        </section>

        <!-- Bar Order Section -->
        <section class="bar-order">
            <h2>Bar Order - Antilla Apartments & Suites</h2>
            <table id="bar-orders">
                <tr>
                    <th>Room Number</th>
                    <th>Order Description</th>
                    <th>Total Amount (₦)</th>
                    <th>Status</th>
                    <th>Special Instructions</th>
                </tr>
                <?php
                    if ($result_bar->num_rows > 0) {
                        while ($order = $result_bar->fetch_assoc()) {
                            echo "<tr>";
                            echo "<td>" . htmlspecialchars($order['room_number']) . "</td>";
                            echo "<td>" . htmlspecialchars($order['order_description']) . "</td>";
                            echo "<td>" . number_format($order['total_amount'], 2) . "</td>";
                            echo "<td>" . htmlspecialchars($order['status']) . "</td>";
                            echo "<td>" . htmlspecialchars($order['special_instructions']) . "</td>";
                            echo "</tr>";
                        }
                    } else {
                        echo "<tr><td colspan='5'>No bar orders available for the selected date.</td></tr>";
                    }
                ?>
            </table>
        </section>
    </div>
</div>
</div>

<script src="home.js"></script>

<!-- Footer Section 
<footer>
    <p>&copy; 2024 Antilla Apartments & Suites. All rights reserved.</p>
</footer> -->

</body>
</html>
