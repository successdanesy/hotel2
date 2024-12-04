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

// Fetch current guest details from rooms table using guest_id
$query_room = "SELECT guest_id, guest_name FROM rooms WHERE room_number = ?";
$stmt_room = $conn->prepare($query_room);
$stmt_room->bind_param("i", $room_number);
$stmt_room->execute();
$result_room = $stmt_room->get_result();

if ($result_room->num_rows > 0) {
    $room = $result_room->fetch_assoc();
    $guest_id = $room['guest_id'];
    $guest_name = $room['guest_name'];

    // Update the guest's checkout date in the bookings table
    $update_booking_query = "UPDATE bookings SET checkout_date = ? WHERE guest_id = ?";
    $stmt_update_booking = $conn->prepare($update_booking_query);
    $stmt_update_booking->bind_param("si", $actual_checkout_date, $guest_id);

    if ($stmt_update_booking->execute()) {
        // Update the room status and clear guest details
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
    die("Error: No current guest found for the room.");
}

exit();
?>
