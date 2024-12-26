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

        if ($guestType === 'guest' && $roomNumber && $guestId) {
            // Process each order in the tray for a guest
            foreach ($orders as $order) {
                $menuItemId = $order['menuItemId'];
                $menuItemText = $order['menuItemText'];
                $price = $order['price'];

                // Insert into bar_orders for a guest
                $query = "INSERT INTO bar_orders (room_number, order_description, status, timestamp, total_amount, special_instructions, guest_id, guest_type) 
                          VALUES (?, ?, 'Pending', NOW(), ?, ?, ?, ?)";
                $stmt = $conn->prepare($query);
                $stmt->bind_param('ssdsds', $roomNumber, $menuItemText, $price, $specialInstructions, $guestId, $guestType);

                if (!$stmt->execute()) {
                    throw new Exception('Failed to add the order: ' . $stmt->error);
                }
                $stmt->close();
            }
        } elseif ($guestType === 'non_guest') {
            // Process each order in the tray for a non-guest
            foreach ($orders as $order) {
                $menuItemId = $order['menuItemId'];
                $menuItemText = $order['menuItemText'];
                $price = $order['price'];

                // Insert into bar_orders without room number and guest ID
                $query = "INSERT INTO bar_orders (order_description, status, timestamp, total_amount, special_instructions, guest_type) 
                          VALUES (?, 'Pending', NOW(), ?, ?, ?)";
                $stmt = $conn->prepare($query);
                $stmt->bind_param('sdss', $menuItemText, $price, $specialInstructions, $guestType);

                if (!$stmt->execute()) {
                    throw new Exception('Failed to add the order: ' . $stmt->error);
                }
                $stmt->close();
            }
        } else {
            throw new Exception('Invalid input.');
        }

        echo json_encode(['success' => true, 'message' => 'Bar orders submitted successfully!']);
    } else {
        throw new Exception('Invalid request method.');
    }
} catch (Exception $e) {
    error_log($e->getMessage()); // Log the error message to the server log
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>
