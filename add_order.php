<?php
include 'db_connection.php'; // Include DB connection

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $room_number = $_POST['room_number'];
    $menu_item = $_POST['menu_item'];
    $special_instructions = $_POST['special_instructions'];

    // Fetch the name and price of the selected menu item
    $query = "SELECT name, price FROM menu_items WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $menu_item);
    $stmt->execute();
    $stmt->bind_result($name, $price);
    $stmt->fetch();
    $stmt->close();

    // Calculate total amount (since it's one item, total is the price)
    $total_amount = $price;

    // Insert order into the kitchen_orders table
    $insert_query = "INSERT INTO kitchen_orders (room_number, order_description, status, timestamp, total_amount, special_instructions) 
                     VALUES (?, ?, 'pending', NOW(), ?, ?)";
    $insert_stmt = $conn->prepare($insert_query);
    $order_description = "Room " . $room_number . " - " . $name; // Use name only
    $insert_stmt->bind_param("ssds", $room_number, $order_description, $total_amount, $special_instructions);
    $insert_stmt->execute();

    // Return a success response (optional)
    echo json_encode(['success' => true]);
}
?>
