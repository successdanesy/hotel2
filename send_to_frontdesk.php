<?php
include('db_connect.php'); // Include database connection

// Decode JSON payload from the frontend
$data = json_decode(file_get_contents('php://input'), true);

$roomNumber = $data['roomNumber']; // Room number
$orders = $data['orders'];        // Orders array
$totalAmount = $data['totalAmount']; // Total amount
$specialInstructions = $data['specialInstructions']; // Special instructions

// Default status for new orders
$status = 'Pending';

// Current timestamp
$timestamp = date('Y-m-d H:i:s');

// Insert order into the kitchen_orders table
$query = "INSERT INTO kitchen_orders (room_number, order_description, quantity, status, total_amount, special_instructions, timestamp) 
          VALUES (?, ?, ?, ?, ?, ?, ?)";
$stmt = $conn->prepare($query);

// Bind parameters: room_number (string), orders (JSON), status (string), total_amount (float), special_instructions (string), timestamp (datetime)
foreach ($orders as $order) {
    $orderDescription = $order['menuItemText'];
    $quantity = $order['quantity']; // ✅ Get quantity from order
    $price = $order['price']; // ✅ Ensure we store the price correctly

    // ✅ Prepare statement for each order
    $stmt = $conn->prepare("INSERT INTO kitchen_orders (room_number, order_description, quantity, status, total_amount, special_instructions, timestamp) 
                            VALUES (?, ?, ?, ?, ?, ?, ?)");

    if (!$stmt) {
        die(json_encode(['success' => false, 'error' => 'Query Preparation Failed: ' . $conn->error]));
    }

    // ✅ Bind parameters properly
    $stmt->bind_param("ssisdss", $roomNumber, $orderDescription, $quantity, $status, $price, $specialInstructions, $timestamp);

    if (!$stmt->execute()) {
        die(json_encode(['success' => false, 'error' => 'SQL Execution Failed: ' . $stmt->error]));
    }

    $stmt->close();
}
$stmt->execute();

if ($stmt->affected_rows > 0) {
    echo json_encode(['success' => true, 'message' => 'Order successfully sent to the kitchen.']);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to send order.']);
}

// Clean up resources
$stmt->close();
$conn->close();
?>
