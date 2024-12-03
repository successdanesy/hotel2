<?php
session_start();
include('db_connect.php');

// Enable error reporting for debugging
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Check if room number and actual checkout date are provided
if (!isset($_POST['room_number']) || !isset($_POST['actual_checkout_date'])) {
    die("Error: Room number and actual checkout date are required for checkout.");
}

$room_number = $_POST['room_number'];
$actual_checkout_date = $_POST['actual_checkout_date'];

// Validate `actual_checkout_date`
if (empty($actual_checkout_date)) {
    die("Error: Actual checkout date cannot be empty.");
}

// Fetch the booking details for validation
$query_booking = "SELECT booking_id, guest_id, checkin_date FROM bookings WHERE room_number = ?";
$stmt_booking = $conn->prepare($query_booking);
$stmt_booking->bind_param("i", $room_number);
$stmt_booking->execute();
$result_booking = $stmt_booking->get_result();

if ($result_booking->num_rows > 0) {
    $booking = $result_booking->fetch_assoc();
    $booking_id = $booking['booking_id'];
    $guest_id = $booking['guest_id'];
    $checkin_date = $booking['checkin_date'];

    // Ensure `actual_checkout_date` is not earlier than `checkin_date`
    if ($actual_checkout_date < $checkin_date) {
        die("Error: Actual checkout date cannot be earlier than the check-in date.");
    }

    // Update the bookings table with the actual checkout date
    $update_booking_query = "UPDATE bookings SET checkout_date = ? WHERE booking_id = ?";
    $stmt_update_booking = $conn->prepare($update_booking_query);
    $stmt_update_booking->bind_param("si", $actual_checkout_date, $booking_id);

    if ($stmt_update_booking->execute()) {
        // Even if no rows are affected, proceed with updating the room
        // Update the room status to 'Available' and reset guest_id and guest_name
        $update_room_query = "UPDATE rooms SET status = 'Available', guest_id = NULL, guest_name = NULL WHERE room_number = ?";
        $stmt_update_room = $conn->prepare($update_room_query);
        $stmt_update_room->bind_param("i", $room_number);

        if ($stmt_update_room->execute()) {
            // Redirect on successful checkout
            header('Location: room.php?message=Checkout completed successfully.');
        } else {
            die("Error updating room status: " . $stmt_update_room->error);
        }
    } else {
        die("Error updating checkout date: " . $stmt_update_booking->error);
    }
} else {
    die("Error: No booking found for the given room number.");
}

exit();
?>
