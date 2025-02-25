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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script>
        // Function to recalculate pricing and update the DOM
        function updatePricing() {
            // Get the checkout date from the input field
            const checkoutDateInput = document.getElementById('checkout_date');
            const actualCheckoutDate = new Date(checkoutDateInput.value);

            // Get the check-in date from the hidden input field
            const checkinDate = new Date('<?php echo $checkin_date; ?>');

            // Calculate the total days
            const timeDiff = actualCheckoutDate - checkinDate;
            const totalDays = Math.ceil(timeDiff / (1000 * 60 * 60 * 24)); // Convert milliseconds to days

            // Ensure totalDays is not negative
            if (totalDays < 0) {
                alert("Checkout date cannot be before check-in date.");
                return;
            }

            // Get the discounted price per night
            const discountedPrice = parseFloat('<?php echo $discounted_price; ?>');

            // Calculate total room charges
            const totalRoomCharges = discountedPrice * totalDays;

            // Get additional charges (kitchen and bar)
            const kitchenCharges = parseFloat('<?php echo $kitchen_charges; ?>');
            const barCharges = parseFloat('<?php echo $bar_charges; ?>');
            const additionalCharges = kitchenCharges + barCharges;

            // Calculate total charges
            const totalCharges = totalRoomCharges + additionalCharges;

            // Update the DOM with the new values
            document.getElementById('total_days').textContent = totalDays;
            document.getElementById('total_room_charges').value = `₦${totalRoomCharges.toFixed(2)}`;
            document.getElementById('total_charges').value = `₦${totalCharges.toFixed(2)}`;
        }

        // Attach the event listener to the checkout date input
        document.addEventListener('DOMContentLoaded', function () {
            const checkoutDateInput = document.getElementById('checkout_date');
            checkoutDateInput.addEventListener('change', updatePricing);
        });
    </script>
</head>
<body>
    <div class="main-content">
        <header>
            <h1><i class="fas fa-user-check icon"></i>Guest Checkout</h1>
            <p>Review the details and complete the checkout process.</p>
            <a href="room.php" class="button new-guest">
                <i class="fa-solid fa-arrow-right-from-bracket"></i>Back to Room Management
            </a>
        </header>

        <section class="checkin-form">
            <form action="complete_checkout.php" method="POST">
                <!-- Guest and Room Details -->
                <div class="section">
                    <h2><i class="fas fa-user icon"></i>Guest Information</h2>
                    <div class="form-group">
                <label for="guest_name">Guest Name:</label>
                <input type="text" id="guest_name" name="guest_name" value="<?php echo htmlspecialchars($guest_name); ?>" readonly>
                </div>
                </div>

                <!-- Room Details Section -->
                <div class="section">
                    <h2><i class="fas fa-bed icon"></i>Room Information</h2>
                    <div class="form-group">
                <label for="room_number">Room Number:</label>
                <input type="text" id="room_number" name="room_number" value="<?php echo htmlspecialchars($room_number); ?>" readonly>
                </div>

                <div class="form-group">
                <label for="checkin_date">Check-in Date:</label>
                <input type="text" id="checkin_date" name="checkin_date" value="<?php echo htmlspecialchars($checkin_date); ?>" readonly>
                </div>

                <!-- Make Checkout Date Editable -->
                <div class="form-group">
                <label for="checkout_date">Check-out Date:</label>
                <input type="date" id="checkout_date" name="checkout_date" value="<?php echo htmlspecialchars($checkout_date); ?>" required>
                </div>

                <!-- Pricing Details -->
                <div class="section">
                <h2><i class="fa-solid fa-money-bill"></i>Room Pricing Details</h2>
                <div class="form-group">
                <label for="room_price">Room Price (Per Night):</label>
                <input type="text" id="room_price" name="room_price" value="₦<?php echo number_format($room_price, 2); ?>" readonly>
                </div>
                <div class="form-group
                <label for="discount">Discount Applied (Per Night):</label>
                <input type="text" id="discount" name="discount" value="₦<?php echo htmlspecialchars(number_format(floatval($discount), 2)); ?>" readonly>
                </div>
                <div class="form-group">
                <label for="discounted_price">Room Price After Discount (Per Night):</label>
                <input type="text" id="discounted_price" name="discounted_price" value="₦<?php echo number_format($discounted_price, 2); ?>" readonly>
                </div>
                <label for="total_room_charges">Room Charges (<span id="total_days"><?php echo $total_days; ?></span> nights):</label>
                <input type="text" id="total_room_charges" name="total_room_charges" value="₦<?php echo number_format($total_room_charges, 2); ?>" readonly>


                <!-- Kitchen/Bar Details -->
                <div class="section">
                <h2><i class="fa-solid fa-utensils"></i>Kitchen/Bar Pricing Details</h2>
                <label for="kitchen_charges">Kitchen Charges:</label>
                <input type="text" id="kitchen_charges" name="kitchen_charges" value="₦<?php echo number_format($kitchen_charges, 2); ?>" readonly>

                <label for="bar_charges">Bar Charges:</label>
                <input type="text" id="bar_charges" name="bar_charges" value="₦<?php echo number_format($bar_charges, 2); ?>" readonly>

                <label for="additional_charges">Total (Bar+Kitchen):</label>
                <input type="text" id="additional_charges" name="additional_charges" value="₦<?php echo number_format($additional_charges, 2); ?>" readonly>


                <!-- Grand Total Details -->
                <div class="section">
                <h2><i class="fa-solid fa-money-bill"></i>Grand Total</h2>
                <label for="total_charges">Total Charges:</label>
                <input type="text" id="total_charges" name="total_charges" value="₦<?php echo number_format($total_charges, 2); ?>" readonly>

                <!-- Hidden Fields for Backend Processing -->
                <input type="hidden" id="guest_id" name="guest_id" value="<?php echo $guest_id; ?>">
                <input type="hidden" id="room_number" name="room_number" value="<?php echo $room_number; ?>">
                <input type="hidden" id="checkin_date" name="checkin_date" value="<?php echo $checkin_date; ?>">
                <input type="hidden" id="room_price" name="room_price" value="<?php echo $room_price; ?>">
                <input type="hidden" id="discount" name="discount" value="<?php echo $discount; ?>">
                <input type="hidden" id="discounted_price" name="discounted_price" value="<?php echo $discounted_price; ?>">

                <!-- Submit Button -->
                <button type="submit" class="button">Complete Checkout</button>
            </form>
        </section>
    </div>
</body>
</html>