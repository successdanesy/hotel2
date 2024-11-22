<?php
include('db_connect.php');

// Check if the room number and the extension period are provided
if (isset($_POST['room_number']) && isset($_POST['extension_days'])) {
    $room_number = $_POST['room_number'];
    $extension_days = $_POST['extension_days'];

    // Get the current day of the week
    $current_day = date('l'); // 'l' returns the full textual representation of the day (e.g., Monday, Tuesday, etc.)

    // Function to determine if today is a weekend day (Friday, Saturday, or Sunday)
    function isWeekend($day) {
        return in_array($day, ['Friday', 'Saturday', 'Sunday']);
    }

    // Get the room's type and pricing details
    $sql = "SELECT room_type, weekday_price, weekend_price FROM rooms WHERE room_number = '$room_number'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $room = $result->fetch_assoc();
        $room_type = $room['room_type'];
        $weekday_price = $room['weekday_price'];
        $weekend_price = $room['weekend_price'];

        // Determine the price based on the current day (weekend or weekday)
        if (isWeekend($current_day)) {
            $price_per_day = $weekend_price;
        } else {
            $price_per_day = $weekday_price;
        }

        // Calculate the price for the extended stay
        $extended_price = $price_per_day * $extension_days;

        // Update the room booking with the new extended stay details
        // Assuming you have a `bookings` table or similar to update the booking details
        // For example, update the checkout_date and price in the bookings table.
        $update_sql = "UPDATE bookings SET checkout_date = DATE_ADD(checkout_date, INTERVAL $extension_days DAY), price = $extended_price WHERE room_number = '$room_number' AND status = 'booked'";

        if ($conn->query($update_sql) === TRUE) {
            header('Location: room.php?message=Stay extended successfully for room ' . $room_number . '. New price: ₦' . number_format($extended_price) . '.');
            exit();
        } else {
            header('Location: room.php?error=Failed to extend stay.');
            exit();
        }
    } else {
        header('Location: room.php?error=Room not found.');
        exit();
    }
} else {
    header('Location: room.php?error=Room number or extension days missing.');
    exit();
}
?>
