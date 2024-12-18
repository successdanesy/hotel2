<?php
session_start();
include('db_connect.php');

// Enable error reporting for debugging
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Check if room number is provided
if (!isset($_POST['room_number'])) {
    die("Error: Room number is required for checkout.");
}

$room_number = $_POST['room_number'];
$checkout_date = isset($_POST['checkout_date']) ? $_POST['checkout_date'] : date('Y-m-d'); // Allow manual input or default to current date
$discount = isset($_POST['discount']) ? floatval($_POST['discount']) : 0; // Convert discount to float

// Fetch guest and room details
$query_room = "SELECT guest_id, guest_name, room_type, weekday_price, weekend_price FROM rooms WHERE room_number = ?";
$stmt_room = $conn->prepare($query_room);
$stmt_room->bind_param("i", $room_number);
$stmt_room->execute();
$result_room = $stmt_room->get_result();

if ($result_room->num_rows > 0) {
    $room = $result_room->fetch_assoc();
    $guest_id = $room['guest_id'];

    // Fetch booking details
    $query_booking = "SELECT checkin_date FROM bookings WHERE guest_id = ?";
    $stmt_booking = $conn->prepare($query_booking);
    $stmt_booking->bind_param("i", $guest_id);
    $stmt_booking->execute();
    $result_booking = $stmt_booking->get_result();

    if ($result_booking->num_rows > 0) {
        $booking = $result_booking->fetch_assoc();
        $checkin_date = $booking['checkin_date'];

        // Determine the room price based on the check-in day
        $checkin_day = date('l', strtotime($checkin_date));
        $is_weekend = in_array($checkin_day, ['Friday', 'Saturday', 'Sunday']);
        $room_price = $is_weekend ? $room['weekend_price'] : $room['weekday_price'];

        // Adjust nightly rate based on discount applied at check-in
        $adjusted_room_price = max(0, $room_price - $discount);

        // Fetch kitchen and bar charges
        $query_kitchen = "SELECT COALESCE(SUM(total_amount), 0) AS kitchen_charges FROM kitchen_orders WHERE guest_id = ? AND status = 'completed'";
        $stmt_kitchen = $conn->prepare($query_kitchen);
        $stmt_kitchen->bind_param("i", $guest_id);
        $stmt_kitchen->execute();
        $kitchen_charges = $stmt_kitchen->get_result()->fetch_assoc()['kitchen_charges'] ?? 0;

        $query_bar = "SELECT COALESCE(SUM(total_amount), 0) AS bar_charges FROM bar_orders WHERE guest_id = ? AND status = 'completed'";
        $stmt_bar = $conn->prepare($query_bar);
        $stmt_bar->bind_param("i", $guest_id);
        $stmt_bar->execute();
        $bar_charges = $stmt_bar->get_result()->fetch_assoc()['bar_charges'] ?? 0;

        // Calculate total charges
        $additional_charges = $kitchen_charges + $bar_charges;
        $total_room_charges = $adjusted_room_price * $total_days;
        $total_charges = $total_room_charges + $additional_charges;

        // Final total after discount (no additional subtraction)
        $final_total_after_discount = $total_charges;

        // Update booking
        $update_booking_query = "UPDATE bookings SET total_charges = ? WHERE guest_id = ?";
        $stmt_update_booking = $conn->prepare($update_booking_query);
        $stmt_update_booking->bind_param("di", $final_total_after_discount, $guest_id);

        if ($stmt_update_booking->execute()) {
            // Update room status
            $update_room_query = "UPDATE rooms SET status = 'Available', guest_id = NULL, guest_name = NULL WHERE room_number = ?";
            $stmt_update_room = $conn->prepare($update_room_query);
            $stmt_update_room->bind_param("i", $room_number);

            if ($stmt_update_room->execute()) {
                // Redirect on success
                header('Location: room.php?message=Checkout completed successfully.');
                exit();
            } else {
                die("Error updating room status: " . $stmt_update_room->error);
            }
        } else {
            die("Error updating booking: " . $stmt_update_booking->error);
        }
    } else {
        die("Error: No booking found for the guest.");
    }
} else {
    die("Error: No room data found for the provided room number.");
}

exit();
?>
