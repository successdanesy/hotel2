<?php
session_start();
include('db_connect.php');

// Check if room number and actual checkout date are provided
if (!isset($_POST['room_number']) || !isset($_POST['actual_checkout_date'])) {
    header('Location: room.php?error=Room number and actual checkout date are required for checkout.');
    exit();
}

$room_number = $_POST['room_number'];
$actual_checkout_date = $_POST['actual_checkout_date'];

// Update the bookings table with the actual checkout date
$query_booking = "UPDATE bookings SET checkout_date = ? WHERE room_number = ?";
$stmt_booking = $conn->prepare($query_booking);
$stmt_booking->bind_param("si", $actual_checkout_date, $room_number);
$stmt_booking->execute();

// Check if the booking update was successful
if ($stmt_booking->affected_rows > 0) {
    // Update the room status to 'Available'
    $query_room = "UPDATE rooms SET status = 'Available' WHERE room_number = ?";
    $stmt_room = $conn->prepare($query_room);
    $stmt_room->bind_param("i", $room_number);
    $stmt_room->execute();

    // Redirect to the room management page with a success message
    if ($stmt_room->affected_rows > 0) {
        header('Location: room.php?message=Checkout completed successfully.');
    } else {
        header('Location: room.php?error=Failed to update room status.');
    }
} else {
    header('Location: room.php?error=Failed to update checkout date in bookings.');
}

exit();
