<?php
session_start();
include('db_connect.php');

// Enable error reporting for debugging
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Check if the user is logged in
if (!isset($_SESSION['username'])) {
    $_SESSION['msg'] = "You must log in first";
    header('location: login.php');
    exit();
}

// Test database connection
if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

// Handle search and payment status filters
$search = isset($_GET['search']) ? '%' . $conn->real_escape_string($_GET['search']) . '%' : '%';
$payment_status = isset($_GET['payment_status']) && $_GET['payment_status'] ? $conn->real_escape_string($_GET['payment_status']) : '%';

// Simplified SQL query with subqueries to avoid GROUP BY issues
$query = "
    SELECT b.booking_id, b.guest_name, b.guest_id, b.room_number, b.checkin_date, b.checkout_date, b.payment_status,
           r.weekday_price, r.weekend_price,
           (SELECT IFNULL(SUM(k.total_amount), 0) FROM kitchen_orders k WHERE k.room_number = b.room_number) AS kitchen_order_total,
           (SELECT IFNULL(SUM(bar.total_amount), 0) FROM bar_orders bar WHERE bar.room_number = b.room_number) AS bar_order_total,
           CASE 
               WHEN CURRENT_DATE BETWEEN b.checkin_date AND b.checkout_date THEN 'Checked In'
               ELSE 'Checked Out'
           END AS status
    FROM bookings b
    LEFT JOIN rooms r ON b.room_number = r.room_number
    WHERE (b.guest_name LIKE ? OR b.room_number LIKE ?)
      AND b.payment_status LIKE ?
    ORDER BY b.checkin_date DESC";


$stmt = $conn->prepare($query);
if (!$stmt) {
    die("SQL Error: " . $conn->error);
}
$stmt->bind_param("sss", $search, $search, $payment_status);
$stmt->execute();
$result = $stmt->get_result();
if (!$result) {
    die("Execution Error: " . $stmt->error);
}

// Fetch guest data
$guests = [];
while ($row = $result->fetch_assoc()) {
    $guests[] = $row;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Guest Management - Antilla Apartments & Suites</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="guest.css">
</head>
<body>
    <div class="main-content">
        <header>
            <h1>Guest Management</h1>
            <p>Manage and view all current and past guest records.</p>
            <a href="export_guests.php" class="button new-guest">
                <i class="fa-solid fa-arrow-right-from-bracket"></i>Export to Excel
            </a>
            <a href="home.php" class="button new-guest">
                <i class="fa-solid fa-arrow-right-from-bracket"></i>Home
            </a>
        </header>

        <form method="GET" action="guest_management.php" class="search-form">
            <input type="text" name="search" placeholder="Search by name or room number" 
                   value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
            <select name="payment_status">
                <option value="">All Payment Status</option>
                <option value="paid" <?php echo (isset($_GET['payment_status']) && $_GET['payment_status'] == 'paid') ? 'selected' : ''; ?>>Paid</option>
                <option value="unpaid" <?php echo (isset($_GET['payment_status']) && $_GET['payment_status'] == 'unpaid') ? 'selected' : ''; ?>>Unpaid</option>
            </select>
            <button type="submit" class="button">Search</button>
        </form>

        <!-- Guest Table -->
        <!-- Guest Table -->
<section class="guest-list">
    <h2>Guest List</h2>
    <table>
        <thead>
            <tr>
                <th>Guest ID</th> <!-- New Column for Guest ID -->
                <th>Guest Name</th>
                <th>Room Number</th>
                <th>Room Price</th> <!-- Room Price Column -->
                <th>Check-in Date</th>
                <th>Check-out Date</th>
                <th>Payment Status</th>
                <th>Kitchen Order</th>
                <th>Bar Order</th>
                <th>Total Paid</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($guests as $guest): ?>
                <tr>
                <td><?php echo htmlspecialchars($guest['guest_name'] ?? 'Guest Name Not Available'); ?></td>
                <td><?php echo htmlspecialchars($guest['guest_id'] ?? 'ID Not Available'); ?></td>

                    <td><?php echo htmlspecialchars($guest['room_number']); ?></td>
                    <td>₦<?php 
                        // Determine if the date range is during the weekend (Friday-Sunday)
                        $checkin_day = date('w', strtotime($guest['checkin_date'])); // Get the day of the week (0 = Sunday, 1 = Monday, ..., 6 = Saturday)
                        $checkout_day = date('w', strtotime($guest['checkout_date']));

                        // Check if either check-in or check-out is on a weekend day (Friday-Sunday)
                        $is_weekend = ($checkin_day == 5 || $checkin_day == 6 || $checkout_day == 5 || $checkout_day == 6 || $checkout_day == 0); // Friday-Sunday are weekends

                        // Calculate the room price based on weekend or weekday
                        $room_price = 0;
                        if ($is_weekend) {
                            // It's a weekend
                            $room_price = $guest['weekend_price'];
                        } else {
                            // It's a weekday
                            $room_price = $guest['weekday_price'];
                        }

                        echo number_format($room_price, 2); 
                    ?></td> <!-- Display Room Price -->
                    <td><?php echo htmlspecialchars($guest['checkin_date']); ?></td>
                    <td><?php echo htmlspecialchars($guest['checkout_date']); ?></td>
                    <td><?php echo htmlspecialchars($guest['payment_status']); ?></td>
                    <td>₦<?php echo number_format($guest['kitchen_order_total'], 2); ?></td>
                    <td>₦<?php echo number_format($guest['bar_order_total'], 2); ?></td>
                    <td>₦<?php echo number_format(
                        $guest['kitchen_order_total'] + $guest['bar_order_total'] + $room_price, 2); 
                    ?></td> <!-- Total Paid -->
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</section>

    </div>

    <!-- Footer -->
    <footer>
        <p>&copy; 2024 Antilla Apartments & Suites. All rights reserved.</p>
    </footer>
</body>
</html>
