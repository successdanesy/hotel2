<?php
session_start();
include('db_connect.php'); // Database connection

header('Content-Type: application/json'); // Ensure the response is JSON

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $data = json_decode(file_get_contents('php://input'), true);
        
        // Log the raw received data for debugging
        error_log("Received Data: " . print_r($data, true));

        $guestType = $data['guestType'] ?? null;
        $roomNumber = $data['roomNumber'] ?? null;
        $orders = $data['orders'] ?? [];
        $specialInstructions = $data['specialInstructions'] ?? '';
        $guestId = $data['guestId'] ?? null;

        // Log detailed input data
        error_log("Guest Type: " . $guestType);
        error_log("Room Number: " . $roomNumber);
        error_log("Guest ID: " . $guestId);
        error_log("Orders: " . print_r($orders, true));
        error_log("Special Instructions: " . $specialInstructions);

        if ($guestType === 'guest' && $roomNumber && $guestId) {
            foreach ($orders as $order) {
                $menuItemId = $order['menuItemId'] ?? null;
                $menuItemText = $order['menuItemText'] ?? '';
                $price = $order['price'] ?? 0;

                if (!$menuItemId || !$menuItemText || $price <= 0) {
                    throw new Exception('Invalid order details.');
                }

                error_log("Inserting guest order: $menuItemText for room $roomNumber");

                $query = "INSERT INTO bar_orders (room_number, order_description, status, timestamp, total_amount, special_instructions, guest_id, guest_type) 
                          VALUES (?, ?, 'Pending', NOW(), ?, ?, ?, ?)";
                $stmt = $conn->prepare($query);
                $stmt->bind_param('ssdsds', $roomNumber, $menuItemText, $price, $specialInstructions, $guestId, $guestType);

                if (!$stmt->execute()) {
                    error_log('SQL Error (Guest): ' . $stmt->error); // Log SQL error for guest order
                    throw new Exception('Failed to add the guest order: ' . $stmt->error);
                }
                $stmt->close();
            }
        } elseif ($guestType === 'non_guest') {
            foreach ($orders as $order) {
                $menuItemId = $order['menuItemId'] ?? null;
                $menuItemText = $order['menuItemText'] ?? '';
                $price = $order['price'] ?? 0;

                if (!$menuItemId || !$menuItemText || $price <= 0) {
                    throw new Exception('Invalid order details.');
                }

                error_log("Inserting non-guest order: $menuItemText");

                $query = "INSERT INTO bar_orders (order_description, status, timestamp, total_amount, special_instructions, guest_type) 
                          VALUES (?, 'Pending', NOW(), ?, ?, ?)";
                $stmt = $conn->prepare($query);
                $stmt->bind_param('sds', $menuItemText, $price, $specialInstructions, $guestType);

                if (!$stmt->execute()) {
                    error_log('SQL Error (Non-Guest): ' . $stmt->error); // Log SQL error for non-guest order
                    throw new Exception('Failed to add the non-guest order: ' . $stmt->error);
                }
                $stmt->close();
            }
        } else {
            throw new Exception('Invalid input.');
        }

        echo json_encode(['success' => true, 'message' => 'Orders submitted successfully!']);
    }
} catch (Exception $e) {
    error_log('Exception: ' . $e->getMessage()); // Log the error message to the server log
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>
