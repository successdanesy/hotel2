<?php
session_start();
include('db_connect.php'); // Database connection

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $roomNumber = $data['roomNumber'];
    $guestId = $data['guestId'];
    $orders = $data['orders'];
    $specialInstructions = $data['specialInstructions'];

    // Process each order in the tray
    foreach ($orders as $order) {
        $menuItemId = $order['menuItemId'];
        $menuItemText = $order['menuItemText'];
        $price = $order['price'];

        // Insert into kitchen_orders
        $query = "INSERT INTO kitchen_orders (room_number, order_description, status, timestamp, total_amount, special_instructions, guest_id) 
        VALUES (?, ?, 'Pending', NOW(), ?, ?, ?)";
$stmt = $conn->prepare($query);
$stmt->bind_param('ssdsd', $roomNumber, $menuItemText, $price, $specialInstructions, $guestId);




        if (!$stmt->execute()) {
            echo json_encode(['success' => false, 'error' => 'Failed to add the order.']);
            exit();
        }
        $stmt->close();
    }

    echo json_encode(['success' => true, 'message' => 'Orders submitted successfully!']);
}
?>