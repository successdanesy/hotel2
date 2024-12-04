<?php
session_start();
include('db_connect.php');

// Enable error reporting for debugging
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Redirect if no room number is provided
if (!isset($_POST['room_number'])) {
    header('Location: room.php?error=Room number is required for checkout.');
    exit();
}

$room_number = $_POST['room_number'];

// Fetch guest details from the rooms table
$query_room = "SELECT guest_id, guest_name, room_type, weekday_price, weekend_price FROM rooms WHERE room_number = ?";
$stmt_room = $conn->prepare($query_room);
$stmt_room->bind_param("i", $room_number);
$stmt_room->execute();
$result_room = $stmt_room->get_result();
$room_data = $result_room->fetch_assoc();

// Handle missing room data
if (!$room_data) {
    header('Location: room.php?error=No room found for the selected room number.');
    exit();
}

// Extract guest details
$guest_id = $room_data['guest_id'];
$guest_name = $room_data['guest_name'];

// Fetch booking details for debugging or any additional logic
$query_booking = "SELECT checkin_date, checkout_date FROM bookings WHERE guest_id = ?";
$stmt_booking = $conn->prepare($query_booking);
$stmt_booking->bind_param("i", $guest_id);
$stmt_booking->execute();
$result_booking = $stmt_booking->get_result();

if ($result_booking->num_rows > 0) {
    $booking_data = $result_booking->fetch_assoc();
    $checkin_date = $booking_data['checkin_date'];
    $checkout_date = $booking_data['checkout_date'];
} else {
    die("Error: No booking details found for Guest ID: $guest_id.");
}

// Query for kitchen charges based on guest_id
$query_kitchen = "SELECT COALESCE(SUM(total_amount), 0) AS kitchen_charges
                  FROM kitchen_orders
                  WHERE guest_id = ? 
                  AND status = 'completed'";
$stmt_kitchen = $conn->prepare($query_kitchen);
$stmt_kitchen->bind_param("i", $guest_id);
$stmt_kitchen->execute();
$result_kitchen = $stmt_kitchen->get_result();
$kitchen_data = $result_kitchen->fetch_assoc();
$kitchen_charges = $kitchen_data['kitchen_charges'] ?? 0;

// Query for bar charges based on guest_id
$query_bar = "SELECT COALESCE(SUM(total_amount), 0) AS bar_charges
              FROM bar_orders
              WHERE guest_id = ? 
              AND status = 'completed'";
$stmt_bar = $conn->prepare($query_bar);
$stmt_bar->bind_param("i", $guest_id);
$stmt_bar->execute();
$result_bar = $stmt_bar->get_result();
$bar_data = $result_bar->fetch_assoc();
$bar_charges = $bar_data['bar_charges'] ?? 0;

// Calculate total additional charges and room charges
$additional_charges = $kitchen_charges + $bar_charges;
$current_day = date('l');
$room_price = ($current_day == 'Friday' || $current_day == 'Saturday' || $current_day == 'Sunday') 
    ? $room_data['weekend_price'] 
    : $room_data['weekday_price'];
$total_charges = $room_price + $additional_charges;

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
        <h1>Antilla Apartment & Suites Receipt</h1>
        <p><strong>Guest Name:</strong> <?php echo htmlspecialchars($guest_name); ?></p>
        <p><strong>Guest ID:</strong> <?php echo htmlspecialchars($guest_id); ?></p>
        <p><strong>Room Number:</strong> <?php echo htmlspecialchars($room_number); ?></p>
        <p><strong>Room Type:</strong> <?php echo htmlspecialchars($room_data['room_type']); ?></p>
        <p><strong>Check-in Date:</strong> <?php echo htmlspecialchars($checkin_date); ?></p>
        <p><strong>Check-out Date:</strong> <?php echo htmlspecialchars($checkout_date); ?></p>
        <p><strong>Room Charges:</strong> ₦<?php echo number_format($room_price, 2); ?></p>
        <p><strong>Kitchen Charges:</strong> ₦<?php echo number_format($kitchen_charges, 2); ?></p>
        <p><strong>Bar Charges:</strong> ₦<?php echo number_format($bar_charges, 2); ?></p>
        <p><strong>Additional Charges (Bar/Kitchen):</strong> ₦<?php echo number_format($additional_charges, 2); ?></p>
        <hr>
        <p><strong>Total Charges:</strong> ₦<?php echo number_format($total_charges, 2); ?></p>

        <form action="complete_checkout.php" method="POST">
            <label for="actual_checkout_date">Actual Checkout Date:</label>
            <input type="date" id="actual_checkout_date" name="actual_checkout_date" value="<?php echo date('Y-m-d'); ?>" required>
            <input type="hidden" name="room_number" value="<?php echo $room_number; ?>">
            <button type="submit" class="button">Complete Checkout</button>
        </form>
        <h1>Thank You For Your Patronage</h1>
    </div>
</body>
</html>
