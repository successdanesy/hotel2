<?php
session_start();
include('db_connect.php'); // Database connection

header('Content-Type: application/json'); // Ensure the response is JSON

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $data = json_decode(file_get_contents('php://input'), true);
        $guestType = $data['guestType'];
        $roomNumber = $data['roomNumber'] ?? null;
        $orders = $data['orders'];
        $specialInstructions = $data['specialInstructions'];
        $guestId = $data['guestId'] ?? null;

        if (!is_array($orders) || empty($orders)) {
            throw new Exception('No orders received.');
        }

        foreach ($orders as $order) {
    $menuItemId = $order['menuItemId'];
    $menuItemText = $order['menuItemText'];
    $quantity = $order['quantity'];  // ✅ Get quantity from the order
    $price = $order['price'];
    $status = 'Pending';  // ✅ Define status

    // ✅ Insert into bar_orders
    $query = "INSERT INTO bar_orders (room_number, order_description, quantity, status, timestamp, total_amount, special_instructions, guest_id, guest_type) 
              VALUES (?, ?, ?, ?, NOW(), ?, ?, ?, ?)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('ssisdsds', $roomNumber, $menuItemText, $quantity, $status, $price, $specialInstructions, $guestId, $guestType);

    if (!$stmt->execute()) {
        throw new Exception('SQL Execution Failed (Bar Orders): ' . $stmt->error);
    }
    $stmt->close();
}

echo json_encode(['success' => true, 'message' => 'Orders submitted successfully!']);
    }
} catch (Exception $e) {
    error_log($e->getMessage());
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}

?>
