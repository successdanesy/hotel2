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
$query_room = "SELECT guest_id, guest_name, room_type, weekday_price, weekend_price FROM rooms WHERE room_number = ?";
$stmt_room = $conn->prepare($query_room);
$stmt_room->bind_param("i", $room_number);
$stmt_room->execute();
$result_room = $stmt_room->get_result();

if ($result_room->num_rows > 0) {
    $room = $result_room->fetch_assoc();
    $guest_id = $room['guest_id'];
    $guest_name = $room['guest_name'];
    $room_type = $room['room_type'];
    $weekday_price = $room['weekday_price'];
    $weekend_price = $room['weekend_price'];

    // Fetch additional charges (e.g., kitchen, bar charges) for this guest
    $query_kitchen = "SELECT COALESCE(SUM(total_amount), 0) AS kitchen_charges FROM kitchen_orders WHERE guest_id = ? AND status = 'completed'";
    $stmt_kitchen = $conn->prepare($query_kitchen);
    $stmt_kitchen->bind_param("i", $guest_id);
    $stmt_kitchen->execute();
    $result_kitchen = $stmt_kitchen->get_result();
    $kitchen_charges = $result_kitchen->fetch_assoc()['kitchen_charges'];

    $query_bar = "SELECT COALESCE(SUM(total_amount), 0) AS bar_charges FROM bar_orders WHERE guest_id = ? AND status = 'completed'";
    $stmt_bar = $conn->prepare($query_bar);
    $stmt_bar->bind_param("i", $guest_id);
    $stmt_bar->execute();
    $result_bar = $stmt_bar->get_result();
    $bar_charges = $result_bar->fetch_assoc()['bar_charges'];

    // Calculate total charges
    $current_day = date('l');
    $room_price = ($current_day == 'Friday' || $current_day == 'Saturday' || $current_day == 'Sunday') 
        ? $weekend_price 
        : $weekday_price;

    $total_charges = $room_price + $kitchen_charges + $bar_charges;

    // Update the guest's checkout date and total charges in the bookings table
    $update_booking_query = "UPDATE bookings SET checkout_date = ?, total_charges = ? WHERE guest_id = ?";
    $stmt_update_booking = $conn->prepare($update_booking_query);
    $stmt_update_booking->bind_param("sdi", $actual_checkout_date, $total_charges, $guest_id);

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
        die("Error updating checkout date or total charges: " . $stmt_update_booking->error);
    }
} else {
    die("Error: No current guest found for the room.");
}

exit();
?>
