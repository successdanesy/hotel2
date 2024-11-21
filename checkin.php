<?php
include('db_connect.php');

// Check if the room number is provided
if (isset($_POST['room_number'])) {
    $room_number = $_POST['room_number'];

    // Update the room status to 'taken'
    $query = "UPDATE rooms SET status = 'taken' WHERE room_number = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $room_number);

    if ($stmt->execute()) {
        header('Location: room.php?message=Room checked in successfully.');
    } else {
        header('Location: room.php?error=Failed to check in the room.');
    }
    exit();
} else {
    header('Location: room.php?error=Room number is missing.');
    exit();
}
?>
