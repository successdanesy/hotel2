<?php
session_start();
include('db_connect.php');

header('Content-Type: application/json');

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

            // ✅ Prepare the query
            $query = "INSERT INTO kitchen_orders (room_number, order_description, quantity, status, timestamp, total_amount, special_instructions, guest_id, guest_type) 
                      VALUES (?, ?, ?, ?, NOW(), ?, ?, ?, ?)";
            $stmt = $conn->prepare($query);
            if (!$stmt) {
                throw new Exception('Query Preparation Failed: ' . $conn->error);
            }

            // ✅ Bind parameters correctly
            $stmt->bind_param('ssisdsds', $roomNumber, $menuItemText, $quantity, $status, $price, $specialInstructions, $guestId, $guestType);

            // ✅ Execute and check for errors
            if (!$stmt->execute()) {
                throw new Exception('SQL Execution Failed: ' . $stmt->error);
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
