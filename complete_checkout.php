<?php
session_start();
include('db_connect.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $guest_id = intval($_POST['guest_id']);
    $room_number = intval($_POST['room_number']);
    $total_charges = floatval($_POST['total_charges']);
    $total_room_charges = floatval($_POST['total_room_charges']);

    // Start a transaction
    $conn->begin_transaction();

    try {
        // Insert or update total_paid in the bookings table
        $update_booking_query = "UPDATE bookings SET total_paid = ? WHERE guest_id = ?";
        $stmt_update_booking = $conn->prepare($update_booking_query);
        $stmt_update_booking->bind_param("di", $total_charges, $guest_id);

        if (!$stmt_update_booking->execute()) {
            throw new Exception("Error updating booking: " . $stmt_update_booking->error);
        } else {
            error_log("Booking updated successfully.");
        }

        // Insert or update total_room_charges in the bookings table
        $update_booking_query = "UPDATE bookings SET total_room_charges = ? WHERE guest_id = ?";
        $stmt_update_booking = $conn->prepare($update_booking_query);
        $stmt_update_booking->bind_param("di", $total_room_charges, $guest_id);

        if (!$stmt_update_booking->execute()) {
            throw new Exception("Error updating booking: " . $stmt_update_booking->error);
        } else {
            error_log("Booking updated successfully.");
        }

        // Update the room status
        $update_room_query = "UPDATE rooms SET status = 'Available', guest_id = NULL, guest_name = NULL WHERE room_number = ?";
        $stmt_update_room = $conn->prepare($update_room_query);
        $stmt_update_room->bind_param("i", $room_number);

        if (!$stmt_update_room->execute()) {
            throw new Exception("Error updating room status: " . $stmt_update_room->error);
        } else {
            error_log("Room status updated successfully.");
        }

        // Commit transaction
        $conn->commit();

        // Redirect on success
        header('Location: room.php?message=Checkout completed successfully.');
        exit();
    } catch (Exception $e) {
        // Rollback transaction on error
        $conn->rollback();
        error_log($e->getMessage());
        die($e->getMessage());
    }
} else {
    header('Location: room.php?error=Invalid request.');
    exit();
}
?>
