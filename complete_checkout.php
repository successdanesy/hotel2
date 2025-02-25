<?php
session_start();
include('db_connect.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Retrieve form data
    $guest_id = intval($_POST['guest_id']);
    $room_number = intval($_POST['room_number']);
    $actual_checkout_date = $_POST['checkout_date']; // Get the actual checkout date from the form
    $room_price = floatval($_POST['room_price']);
    $discount = floatval($_POST['discount']);
    $discounted_price = floatval($_POST['discounted_price']);
    $checkin_date = $_POST['checkin_date'];

    // Fetch additional charges (kitchen and bar)
    $query_kitchen = "SELECT COALESCE(SUM(total_amount), 0) AS kitchen_charges FROM kitchen_orders WHERE guest_id = ? AND status = 'completed'";
    $stmt_kitchen = $conn->prepare($query_kitchen);
    $stmt_kitchen->bind_param("i", $guest_id);
    $stmt_kitchen->execute();
    $result_kitchen = $stmt_kitchen->get_result();
    $kitchen_charges = $result_kitchen->fetch_assoc()['kitchen_charges'] ?? 0.0;

    $query_bar = "SELECT COALESCE(SUM(total_amount), 0) AS bar_charges FROM bar_orders WHERE guest_id = ? AND status = 'completed'";
    $stmt_bar = $conn->prepare($query_bar);
    $stmt_bar->bind_param("i", $guest_id);
    $stmt_bar->execute();
    $result_bar = $stmt_bar->get_result();
    $bar_charges = $result_bar->fetch_assoc()['bar_charges'] ?? 0.0;

    $additional_charges = $kitchen_charges + $bar_charges;

    // Recalculate room charges based on the actual checkout date
    $checkin_date_obj = new DateTime($checkin_date);
    $checkout_date_obj = new DateTime($actual_checkout_date);
    $total_days = $checkin_date_obj->diff($checkout_date_obj)->days;

    $total_room_charges = $discounted_price * $total_days;
    $total_charges = $total_room_charges + $additional_charges;

    // Start a transaction
    $conn->begin_transaction();

    try {
        // Update the booking with the actual checkout date and recalculated charges
        $update_booking_query = "UPDATE bookings SET checkout_date = ?, total_paid = ?, total_room_charges = ? WHERE guest_id = ?";
        $stmt_update_booking = $conn->prepare($update_booking_query);
        $stmt_update_booking->bind_param("sddi", $actual_checkout_date, $total_charges, $total_room_charges, $guest_id);

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