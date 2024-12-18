<?php
session_start();
include('db_connect.php');


// connect to the database
$db = mysqli_connect('localhost', 'root', '', 'project');

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Variables for testing
$guest_id = 91;  // Assuming guest ID is 1 for testing
$actual_checkout_date = date("Y-m-d H:i:s");  // Current date and time
$final_total_after_discount = 450.00;  // Example final total
$discount = 50.00;  // Example discount amount

// Update booking query
$update_booking_query = "UPDATE bookings SET checkout_date = ?, total_charges = ?, discount = ? WHERE guest_id = ?";
$stmt_update_booking = $conn->prepare($update_booking_query);

// Binding parameters
$stmt_update_booking->bind_param("sdii", $actual_checkout_date, $final_total_after_discount, $discount, $guest_id);

// Executing query
if ($stmt_update_booking->execute()) {
    echo "Booking updated successfully!";
} else {
    echo "Error updating booking: " . $stmt_update_booking->error;
}

// Closing connection
$stmt_update_booking->close();
$conn->close();
?>

?>
