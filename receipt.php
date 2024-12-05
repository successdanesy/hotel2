<?php
session_start();
include('db_connect.php');

if (!isset($_GET['guest_id'])) {
    die("Guest ID not provided.");
}

$guest_id = $conn->real_escape_string($_GET['guest_id']);

// Fetch guest details
$query = "
    SELECT b.guest_name, b.guest_id, b.room_number, b.checkin_date, b.checkout_date, 
           r.weekday_price, r.weekend_price,
           (SELECT IFNULL(SUM(k.total_amount), 0) 
            FROM kitchen_orders k 
            WHERE k.room_number = b.room_number AND k.guest_id = b.guest_id) AS kitchen_order_total,
           (SELECT IFNULL(SUM(bar.total_amount), 0) 
            FROM bar_orders bar 
            WHERE bar.room_number = b.room_number AND bar.guest_id = b.guest_id) AS bar_order_total
    FROM bookings b
    LEFT JOIN rooms r ON b.room_number = r.room_number
    WHERE b.guest_id = '$guest_id'";

$result = $conn->query($query);

if (!$result || $result->num_rows === 0) {
    die("No guest found with the provided ID.");
}

$guest = $result->fetch_assoc();

// Calculate total charges
$checkin_day = date('w', strtotime($guest['checkin_date']));
$is_weekend = ($checkin_day == 5 || $checkin_day == 6 || $checkin_day == 0);
$room_price = $is_weekend ? $guest['weekend_price'] : $guest['weekday_price'];

$total_paid = $room_price + $guest['kitchen_order_total'] + $guest['bar_order_total'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Receipt - Antilla Apartments & Suites</title>
    <link rel="stylesheet" href="receipt.css">
</head>
<body>
    <!-- Page Header -->
    <div class="page-header">
        <h1>Antilla Apartments & Suites</h1>
        <a href="guest_management.php" class="back-button">Back to Guest Management</a>
    </div>

    <div class="receipt">
        <div class="header">
            <h1>Antilla Apartments & Suites</h1>
            <p>Your Home Away From Home</p>
        </div>

        <hr class="divider">

        <div class="guest-details">
            <h2>Guest Details</h2>
            <p><strong>Guest Name:</strong> <?php echo htmlspecialchars($guest['guest_name']); ?></p>
            <p><strong>Room Number:</strong> <?php echo htmlspecialchars($guest['room_number']); ?></p>
            <p><strong>Check-in Date:</strong> <?php echo htmlspecialchars($guest['checkin_date']); ?></p>
            <p><strong>Check-out Date:</strong> <?php echo htmlspecialchars($guest['checkout_date']); ?></p>
        </div>

        <!-- <hr class="divider"> -->

        <div class="charges">
            <h3>Charges Summary</h3>
            <p><strong>Room Price:</strong> ₦<?php echo number_format($room_price, 2); ?></p>
            <p><strong>Kitchen Orders:</strong> ₦<?php echo number_format($guest['kitchen_order_total'], 2); ?></p>
            <p><strong>Bar Orders:</strong> ₦<?php echo number_format($guest['bar_order_total'], 2); ?></p>
            <p class="total"><strong>Total Paid:</strong> ₦<?php echo number_format($total_paid, 2); ?></p>
        </div>

        <hr class="divider">

        <div class="footer">
            <p class="thank-you">Thank you for staying with us!</p>
            <p class="visit-again">We hope to see you again soon.</p>
            <button onclick="window.print();" class="button">Print Receipt</button>
        </div>
    </div>
</body>
</html>

