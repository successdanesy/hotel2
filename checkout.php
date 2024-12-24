<?php
session_start();
include('db_connect.php');

// Enable error reporting for debugging during development
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Check if room number is provided
if (!isset($_POST['room_number'])) {
    header('Location: room.php?error=Room number is required for checkout.');
    exit();
}

$room_number = intval($_POST['room_number']);

// Fetch guest and room details
$query_room = "SELECT guest_id, guest_name, room_type, weekday_price, weekend_price FROM rooms WHERE room_number = ?";
$stmt_room = $conn->prepare($query_room);
$stmt_room->bind_param("i", $room_number);
$stmt_room->execute();
$result_room = $stmt_room->get_result();
$room_data = $result_room->fetch_assoc();

if (!$room_data) {
    header('Location: room.php?error=No room found for the selected room number.');
    exit();
}

// Extract room and guest details
$guest_id = $room_data['guest_id'];
$guest_name = htmlspecialchars($room_data['guest_name']);

// Fetch booking details
$query_booking = "SELECT checkin_date, checkout_date, discount FROM bookings WHERE guest_id = ?";
$stmt_booking = $conn->prepare($query_booking);
$stmt_booking->bind_param("i", $guest_id);
$stmt_booking->execute();
$result_booking = $stmt_booking->get_result();

if ($result_booking->num_rows === 0) {
    header('Location: room.php?error=No active booking found for this guest.');
    exit();
}

$booking_data = $result_booking->fetch_assoc();
$checkin_date = $booking_data['checkin_date'];
$checkout_date = $booking_data['checkout_date'];
$discount = floatval($booking_data['discount'] ?? 0);

// Fetch kitchen charges
$query_kitchen = "SELECT COALESCE(SUM(total_amount), 0) AS kitchen_charges FROM kitchen_orders WHERE guest_id = ? AND status = 'completed'";
$stmt_kitchen = $conn->prepare($query_kitchen);
$stmt_kitchen->bind_param("i", $guest_id);
$stmt_kitchen->execute();
$result_kitchen = $stmt_kitchen->get_result();
$kitchen_charges = $result_kitchen->fetch_assoc()['kitchen_charges'] ?? 0.0;

// Fetch bar charges
$query_bar = "SELECT COALESCE(SUM(total_amount), 0) AS bar_charges FROM bar_orders WHERE guest_id = ? AND status = 'completed'";
$stmt_bar = $conn->prepare($query_bar);
$stmt_bar->bind_param("i", $guest_id);
$stmt_bar->execute();
$result_bar = $stmt_bar->get_result();
$bar_charges = $result_bar->fetch_assoc()['bar_charges'] ?? 0.0;

// Calculate additional charges
$queries = [
    'kitchen_charges' => "SELECT COALESCE(SUM(total_amount), 0) AS total FROM kitchen_orders WHERE guest_id = ? AND status = 'completed'",
    'bar_charges' => "SELECT COALESCE(SUM(total_amount), 0) AS total FROM bar_orders WHERE guest_id = ? AND status = 'completed'",
];
$additional_charges = 0;

foreach ($queries as $key => $query) {
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $guest_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $charges = $result->fetch_assoc();
    $additional_charges += floatval($charges['total'] ?? 0);
}

// Determine room pricing
$checkin_day = date('l', strtotime($checkin_date));
$is_weekend = in_array($checkin_day, ['Friday', 'Saturday', 'Sunday']);
$room_price = $is_weekend ? floatval($room_data['weekend_price']) : floatval($room_data['weekday_price']);
$discounted_price = max(0, $room_price - $discount);

// Calculate total room charges
$checkin_date_obj = new DateTime($checkin_date);
$checkout_date_obj = new DateTime($checkout_date);
$total_days = $checkin_date_obj->diff($checkout_date_obj)->days;

$total_room_charges = $discounted_price * $total_days;
$total_charges = $total_room_charges + $additional_charges;

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Guest Checkout</title>
    <link rel="stylesheet" href="room.css">
</head>
<body>
    <div class="main-content">
        <header>
            <h1>Guest Checkout</h1>
            <p>Review the details and complete the checkout process.</p>
        </header>

        <section class="checkin-form">
        <form action="complete_checkout.php" method="POST">
    <label for="guest_name">Guest Name:</label>
    <input type="text" id="guest_name" name="guest_name" value="<?php echo htmlspecialchars($guest_name); ?>" readonly>

    <label for="room_number">Room Number:</label>
    <input type="text" id="room_number" name="room_number" value="<?php echo htmlspecialchars($room_number); ?>" readonly>

    <label for="checkin_date">Check-in Date:</label>
    <input type="text" id="checkin_date" name="checkin_date" value="<?php echo htmlspecialchars($checkin_date); ?>" readonly>

    <label for="checkout_date">Check-out Date:</label>
    <input type="text" id="checkout_date" name="checkout_date" value="<?php echo htmlspecialchars($checkout_date); ?>">

    <label for="room_price">Room Price (Per Night):</label>
    <input type="text" id="room_price" name="room_price" value="₦<?php echo number_format($room_price, 2); ?>" readonly>

    <label for="discount">Discount Applied (Per Night):</label>
<input type="text" id="discount" name="discount" value="₦<?php echo htmlspecialchars(number_format(floatval($discount), 2)); ?>" readonly>

    <label for="discounted_price">Room Price After Discount (Per Night):</label>
    <input type="text" id="discounted_price" name="discounted_price" value="₦<?php echo number_format($discounted_price, 2); ?>" readonly>

    <label for="total_room_charges">Room Charges (<?php echo $total_days; ?> nights):</label>
    <input type="text" id="total_room_charges" name="total_room_charges" value="₦<?php echo number_format($total_room_charges, 2); ?>" readonly>

    <label for="kitchen_charges">Kitchen Charges:</label>
    <input type="text" id="kitchen_charges" name="kitchen_charges" value="₦<?php echo number_format($kitchen_charges, 2); ?>" readonly>

    <label for="bar_charges">Bar Charges:</label>
    <input type="text" id="bar_charges" name="bar_charges" value="₦<?php echo number_format($bar_charges, 2); ?>" readonly>

    <label for="additional_charges">Additional Charges (Bar/Kitchen):</label>
    <input type="text" id="additional_charges" name="additional_charges" value="₦<?php echo number_format($additional_charges, 2); ?>" readonly>

    <label for="total_charges">Total Charges:</label>
    <input type="text" id="total_charges" name="total_charges" value="₦<?php echo number_format($total_charges, 2); ?>" readonly>
    
    <input type="hidden" id="total_paid" name="total_paid" value="<?php echo $total_charges; ?>" readonly>
    <input type="hidden" id="total_room_charges" name="total_room_charges" value="<?php echo $total_room_charges; ?>" readonly>


    <hr>
</form>

<form action="complete_checkout.php" method="POST"> <input type="hidden" id="guest_id" name="guest_id" value="<?php echo $guest_id; ?>"> 
<input type="hidden" id="room_number" name="room_number" value="<?php echo $room_number; ?>"> 
<input type="hidden" id="total_charges" name="total_charges" value="<?php echo $total_charges; ?>">
<input type="hidden" id="total_room_charges" name="total_room_charges" value="<?php echo $total_room_charges; ?>">

<button type="submit" class="button">Complete Checkout</button>
</form>
        </section>

        <section class="back-to-room-management">
            <form action="room.php" method="GET">
                <button type="submit" class="button back-button">Back to Room Management</button>
            </form>
        </section>
    </div> </body>
</html>


    <!-- <script>
    document.addEventListener('DOMContentLoaded', function() {
        const actualCheckoutDateInput = document.getElementById('actual_checkout_date');
        const checkinDate = "<?php echo $checkin_date; ?>"; // PHP variable passed to JS
        const roomPrice = <?php echo $discounted_price; ?>; // PHP variable passed to JS
        const discount = <?php echo $discount; ?>; // PHP variable passed to JS
        const additionalCharges = <?php echo $additional_charges; ?>; // PHP variable passed to JS
        const roomChargesInput = document.getElementById('total_room_charges');
        const totalChargesInput = document.getElementById('total_charges');

        // Function to calculate the new room charges
        function updateCharges() {
            const checkoutDate = actualCheckoutDateInput.value;
            if (checkoutDate) {
                // Calculate the difference in days
                const checkinDateObj = new Date(checkinDate);
                const checkoutDateObj = new Date(checkoutDate);
                const timeDiff = checkoutDateObj - checkinDateObj;
                const totalNights = Math.ceil(timeDiff / (1000 * 3600 * 24)); // Convert ms to days

                // Calculate total room charges based on the new checkout date
                const totalRoomCharges = (roomPrice - discount) * totalNights;

                // Update the room charges field
                roomChargesInput.value = '₦' + totalRoomCharges.toFixed(2);

                // Recalculate total charges (room + additional charges)
                const totalCharges = totalRoomCharges + additionalCharges;
                totalChargesInput.value = '₦' + totalCharges.toFixed(2);
            }
        }

        // Listen for changes in the checkout date field
        actualCheckoutDateInput.addEventListener('change', updateCharges);

        // Call the update function on page load in case the field already has a value
        updateCharges();
    });
</script> -->
