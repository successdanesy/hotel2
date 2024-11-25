<?php
include('db_connect.php');

if (isset($_POST['room_number']) && isset($_POST['new_checkout_date'])) {
    $room_number = $_POST['room_number'];
    $new_checkout_date = $_POST['new_checkout_date'];

    // Update the `check_out_date` in the `bookings` table
    $query = "UPDATE bookings SET check_out_date = ? WHERE room_number = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("si", $new_checkout_date, $room_number);

    if ($stmt->execute()) {
        header('Location: room.php?message=Stay extended successfully.');
    } else {
        header('Location: room.php?error=Failed to extend stay.');
    }

    exit();
} else {
    header('Location: room.php?error=Required data is missing.');
    exit();
}
?>
