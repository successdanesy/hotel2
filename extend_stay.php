<?php
include('db_connect.php');

// Check if the room number is provided
if (isset($_POST['room_number'])) {
    $room_number = $_POST['room_number'];

    // Placeholder for extending stay logic
    // You can implement additional logic to update booking dates in a separate table.

    header('Location: room.php?message=Stay extended successfully for room ' . $room_number . '.');
    exit();
} else {
    header('Location: room.php?error=Room number is missing.');
    exit();
}
?>
