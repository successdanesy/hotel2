<?php
include('db_connect.php');

$data = json_decode(file_get_contents('php://input'), true);
$roomNumberId = $data['roomNumber'];
$orders = $data['orders'];

if (!$roomNumberId || empty($orders)) {
    echo json_encode(['success' => false, 'error' => 'Invalid input']);
    exit;
}

// Fetch room number
$roomQuery = "SELECT room_number FROM rooms WHERE id = ?";
$stmt = $conn->prepare($roomQuery);
$stmt->bind_param("i", $roomNumberId);
$stmt->execute();
$stmt->bind_result($roomNumber);
$stmt->fetch();
$stmt->close();

if (!$roomNumber) {
    echo json_encode(['success' => false, 'error' => 'Invalid room']);
    exit;
}

// Batch insert orders
$query = "INSERT INTO kitchen_orders (room_number, order_description, status, timestamp, total_amount, special_instructions) 
          VALUES (?, ?, 'Pending', NOW(), ?, ?)";
$stmt = $conn->prepare($query);

foreach ($orders as $order) {
    $stmt->bind_param("ssds", $roomNumber, $order['menuItemText'], $order['price'], $order['specialInstructions']);
    $stmt->execute();
}

$stmt->close();
echo json_encode(['success' => true]);
?>
