<?php
include('db_connect.php');

// Fetch filtered data if search/filter parameters are present
$search = isset($_GET['search']) ? '%' . $conn->real_escape_string($_GET['search']) . '%' : '%';
$payment_status = isset($_GET['payment_status']) && $_GET['payment_status'] ? $conn->real_escape_string($_GET['payment_status']) : '%';

// Updated query to select the necessary columns, including the new ones
$query = "SELECT guest_id, guest_name, room_number, price, payment_status, payment_method, checkin_date, checkout_date, total_charges 
          FROM bookings 
          WHERE (guest_name LIKE ? OR room_number LIKE ?)
          AND payment_status LIKE ?
          ORDER BY checkin_date DESC";

$stmt = $conn->prepare($query);
$stmt->bind_param("sss", $search, $search, $payment_status);
$stmt->execute();
$result = $stmt->get_result();

// Set headers for download
header("Content-Type: application/xls");
header("Content-Disposition: attachment; filename=guests.xls");
header("Pragma: no-cache");
header("Expires: 0");

// Output data
echo "Guest ID\tGuest Name\tRoom Number\tPrice\tPayment Status\tPayment Method\tCheck-in Date\tCheck-out Date\tTotal Charges\n";
while ($row = $result->fetch_assoc()) {
    echo "{$row['guest_id']}\t{$row['guest_name']}\t{$row['room_number']}\t{$row['price']}\t{$row['payment_status']}\t{$row['payment_method']}\t{$row['checkin_date']}\t{$row['checkout_date']}\t{$row['total_charges']}\n";
}
?>
