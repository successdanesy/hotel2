<?php
include('db_connect.php');

// Fetch filtered data if search/filter parameters are present
$search = isset($_GET['search']) ? '%' . $conn->real_escape_string($_GET['search']) . '%' : '%';
$payment_status = isset($_GET['payment_status']) && $_GET['payment_status'] ? $conn->real_escape_string($_GET['payment_status']) : '%';

$query = "SELECT * FROM bookings 
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
echo "Guest Name\tRoom Number\tCheck-in Date\tCheck-out Date\tPayment Status\n";
while ($row = $result->fetch_assoc()) {
    echo "{$row['guest_name']}\t{$row['room_number']}\t{$row['checkin_date']}\t{$row['checkout_date']}\t{$row['payment_status']}\n";
}
?>
