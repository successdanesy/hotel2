<?php
session_start();
include('db_connect.php'); // Database connection

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $roomNumber = $data['roomNumber'];
    $orders = $data['orders'];
    $specialInstructions = $data['specialInstructions'];

    // Fetch the current guest_id for the room
    $query_guest = "SELECT guest_id FROM rooms WHERE room_number = ?";
    $stmt_guest = $conn->prepare($query_guest);
    $stmt_guest->bind_param('i', $roomNumber);
    $stmt_guest->execute();
    $result_guest = $stmt_guest->get_result();
    
    if ($result_guest->num_rows > 0) {
        $guest = $result_guest->fetch_assoc();
        $guestId = $guest['guest_id'];  // Get the current guest_id
    } else {
        echo json_encode(['success' => false, 'error' => 'No guest currently assigned to this room.']);
        exit();
    }

    // Process each order in the tray
    foreach ($orders as $order) {
        $menuItemId = $order['menuItemId'];
        $menuItemText = $order['menuItemText'];
        $price = $order['price'];

        // Insert into bar_orders
        $query = "INSERT INTO bar_orders (room_number, order_description, status, timestamp, total_amount, special_instructions, guest_id) 
                  VALUES (?, ?, 'Pending', NOW(), ?, ?, ?)";
        $stmt = $conn->prepare($query);
        $stmt->bind_param('issds', $roomNumber, $menuItemText, $price, $specialInstructions, $guestId);

        if (!$stmt->execute()) {
            echo json_encode(['success' => false, 'error' => 'Failed to add the order.']);
            exit();
        }
        $stmt->close();
    }

    echo json_encode(['success' => true, 'message' => 'Orders submitted successfully!']);
}
?>
