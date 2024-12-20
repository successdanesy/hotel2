<?php
session_start();
include('db_connect.php');

// Check if guest_id is provided
if (!isset($_GET['guest_id'])) {
    die("Guest ID not provided.");
}

$guest_id = $conn->real_escape_string($_GET['guest_id']);

// Fetch guest details, room details, and charges
$query = "
    SELECT b.guest_name, b.guest_id, b.room_number, b.checkin_date, b.checkout_date, 
           r.room_type, r.weekday_price, r.weekend_price, b.discount, b.total_paid,
           COALESCE(
               (SELECT SUM(k.total_amount) FROM kitchen_orders k WHERE k.guest_id = b.guest_id AND k.status = 'completed'), 0
           ) AS kitchen_order_total,
           COALESCE(
               (SELECT SUM(bar.total_amount) FROM bar_orders bar WHERE bar.guest_id = b.guest_id AND bar.status = 'completed'), 0
           ) AS bar_order_total
    FROM bookings b
    LEFT JOIN rooms r ON b.room_number = r.room_number
    WHERE b.guest_id = '$guest_id'";

$result = $conn->query($query);

if (!$result || $result->num_rows === 0) {
    die("No guest found with the provided ID.");
}

$guest = $result->fetch_assoc();

// Extract details
$guest_name = $guest['guest_name'];
$room_number = $guest['room_number'];
$checkin_date = $guest['checkin_date'];
$checkout_date = $guest['checkout_date'];
$room_type = $guest['room_type'];
$weekday_price = $guest['weekday_price'];
$weekend_price = $guest['weekend_price'];
$discount = floatval($guest['discount']);
$kitchen_charges = floatval($guest['kitchen_order_total']);
$bar_charges = floatval($guest['bar_order_total']);

// Calculate additional charges
$additional_charges = $kitchen_charges + $bar_charges;

// Determine room price based on check-in day
$checkin_day = date('l', strtotime($checkin_date));
$is_weekend = in_array($checkin_day, ['Friday', 'Saturday', 'Sunday']);
$room_price = $is_weekend ? floatval($weekend_price) : floatval($weekday_price);

// Calculate discounted price
$discounted_price = max(0, $room_price - $discount);

// Calculate total nights and room charges
$checkin_date_obj = new DateTime($checkin_date);
$checkout_date_obj = new DateTime($checkout_date);
$total_days = $checkin_date_obj->diff($checkout_date_obj)->days;
$total_room_charges = $room_price * $total_days;
$total_discounted_room_charges = $discounted_price * $total_days;

// Calculate total paid (discounted room price + additional charges)
$total_paid = $total_discounted_room_charges + $additional_charges;
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
            <p><strong>Guest Name:</strong> <?php echo htmlspecialchars($guest_name); ?></p>
            <p><strong>Guest ID:</strong> <?php echo htmlspecialchars($guest_id); ?></p>
            <p><strong>Room Number:</strong> <?php echo htmlspecialchars($room_number); ?></p>
            <p><strong>Room Type:</strong> <?php echo htmlspecialchars($room_type); ?></p>
            <p><strong>Check-in Date:</strong> <?php echo htmlspecialchars($checkin_date); ?></p>
            <p><strong>Check-out Date:</strong> <?php echo htmlspecialchars($checkout_date); ?></p>
        </div>
        <div class="charges">
            <h3>Charges Summary</h3>
            <?php if ($discount > 0): ?>
                <p><strong>Discount Applied (₦<?php echo number_format($discount, 2); ?> per night)</strong></p>
                <p><strong>Discounted Room Price (<?php echo $total_days; ?> nights @ ₦<?php echo number_format($discounted_price, 2); ?> per night):</strong> ₦<?php echo number_format($total_discounted_room_charges, 2); ?></p>
            <?php else: ?>
                <p><strong>Room Charges (<?php echo $total_days; ?> nights @ ₦<?php echo number_format($room_price, 2); ?> per night):</strong> ₦<?php echo number_format($total_room_charges, 2); ?></p>
            <?php endif; ?>
            <p><strong>Kitchen Orders:</strong> ₦<?php echo number_format($kitchen_charges, 2); ?></p>
            <p><strong>Bar Orders:</strong> ₦<?php echo number_format($bar_charges, 2); ?></p>
            <p><strong>(Kitchen + Bar):</strong> ₦<?php echo number_format($additional_charges, 2); ?></p>
        </div>
        <hr class="divider">
        <p class="total"><strong>Total Paid:</strong> ₦<?php echo number_format($total_paid, 2); ?></p>
        <hr class="divider">

        <div class="footer">
            <p class="thank-you">Thank you for staying with us!</p>
            <p class="visit-again">We hope to see you again soon.</p>
            <button onclick="window.print();" class="button">Print Receipt</button>
        </div>
    </div>
</body>
</html>
