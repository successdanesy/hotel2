<?php
session_start();
include('db_connect.php');

// Check if the room number is provided
if (!isset($_POST['room_number'])) {
    header('Location: room.php?error=Room number is required for checkout.');
    exit();
}

$room_number = $_POST['room_number'];

// Update the room status to 'Available'
$query = "UPDATE rooms SET status = 'Available' WHERE room_number = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $room_number);
$stmt->execute();

// Check if the update was successful
if ($stmt->affected_rows > 0) {
    // Redirect back to the room management page with a success message
    header('Location: room.php?message=Checkout completed successfully.');
} else {
    // If something went wrong, redirect with an error message
    header('Location: room.php?error=Failed to complete checkout.');
}

exit();
?>
