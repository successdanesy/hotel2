<?php
include('db_connect.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $guest_name = htmlspecialchars($_POST['guest_name']);
    $room_number = htmlspecialchars($_POST['room_number']);
    $price = htmlspecialchars($_POST['price']);
    $payment_status = htmlspecialchars($_POST['payment_status']);
    $payment_method = htmlspecialchars($_POST['payment_method']);
    $checkin_date = htmlspecialchars($_POST['checkin_date']);
    $checkout_date = htmlspecialchars($_POST['checkout_date']);

    // Start a transaction
    $conn->begin_transaction();

    try {
        // Update room status to 'Occupied'
        $update_room_query = "UPDATE rooms SET status = 'Occupied' WHERE room_number = ?";
        $stmt1 = $conn->prepare($update_room_query);
        $stmt1->bind_param("s", $room_number);
        $stmt1->execute();

        // Insert booking details into the bookings table
        $insert_booking_query = "INSERT INTO bookings (guest_name, room_number, price, payment_status, payment_method, checkin_date, checkout_date) VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt2 = $conn->prepare($insert_booking_query);
        $stmt2->bind_param("ssdssss", $guest_name, $room_number, $price, $payment_status, $payment_method, $checkin_date, $checkout_date);
        $stmt2->execute();

        // Commit transaction
        $conn->commit();

        header('Location: room.php?message=Guest checked in successfully.');
        exit();
    } catch (Exception $e) {
        $conn->rollback();
        header('Location: room.php?error=Failed to complete check-in.');
        exit();
    }
} else {
    header('Location: room.php?error=Invalid request.');
    exit();
}
?>
