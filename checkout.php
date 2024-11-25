<?php
session_start();
include('db_connect.php');

// Redirect if no room number is provided
if (!isset($_POST['room_number'])) {
    header('Location: room.php?error=Room number is required for checkout.');
    exit();
}

$room_number = $_POST['room_number'];

// Fetch guest and booking details
$query = "SELECT 
            b.guest_name,
            b.checkin_date,
            b.checkout_date,
            b.payment_status,
            b.payment_method,
            b.total_charges,
            r.room_type,
            r.weekday_price,
            r.weekend_price
          FROM bookings b
          INNER JOIN rooms r ON b.room_number = r.room_number
          WHERE b.room_number = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $room_number);
$stmt->execute();
$result = $stmt->get_result();
$booking = $result->fetch_assoc();

// Handle missing booking
if (!$booking) {
    header('Location: room.php?error=No booking found for the selected room.');
    exit();
}

// Placeholder for additional charges (kitchen and bar)
$additional_charges = 0; // We'll calculate this when integrating kitchen/bar pages

// Calculate the total charges
$current_day = date('l');
$room_price = ($current_day == 'Friday' || $current_day == 'Saturday' || $current_day == 'Sunday') 
    ? $booking['weekend_price'] 
    : $booking['weekday_price'];

$total_charges = $room_price + $additional_charges;

// Display receipt
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - Receipt</title>
    <link rel="stylesheet" href="checkout.css">
</head>
<body>
    <div class="receipt-container">
        <h1>Receipt</h1>
        <p><strong>Guest Name:</strong> <?php echo htmlspecialchars($booking['guest_name']); ?></p>
        <p><strong>Room Number:</strong> <?php echo $room_number; ?></p>
        <p><strong>Room Type:</strong> <?php echo htmlspecialchars($booking['room_type']); ?></p>
        <p><strong>Check-in Date:</strong> <?php echo htmlspecialchars($booking['checkin_date']); ?></p>
        <p><strong>Check-out Date:</strong> <?php echo htmlspecialchars($booking['checkout_date']); ?></p>
        <p><strong>Room Charges:</strong> ₦<?php echo number_format($room_price, 2); ?></p>
        <p><strong>Additional Charges (Bar/Kitchen):</strong> ₦<?php echo number_format($additional_charges, 2); ?></p>
        <hr>
        <p><strong>Total Charges:</strong> ₦<?php echo number_format($total_charges, 2); ?></p>
        <p><strong>Payment Status:</strong> <?php echo htmlspecialchars($booking['payment_status']); ?></p>
        <p><strong>Payment Method:</strong> <?php echo htmlspecialchars($booking['payment_method']); ?></p>
        <form action="complete_checkout.php" method="POST">
            <input type="hidden" name="room_number" value="<?php echo $room_number; ?>">
            <button type="submit" class="button">Complete Checkout</button>
        </form>
    </div>
</body>
</html>
