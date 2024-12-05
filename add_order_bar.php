<?php
include 'db_connect.php'; // Include DB connection

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $room_number = $_POST['room_number'];
    $menu_item = $_POST['menu_item'];
    $special_instructions = $_POST['special_instructions'];

    // Fetch the name and price of the selected menu item
    $query = "SELECT name, price FROM menu_items_bar WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $menu_item);
    $stmt->execute();
    $stmt->bind_result($name, $price);
    $stmt->fetch();
    $stmt->close();

    // Calculate total amount (since it's one item, total is the price)
    $total_amount = $price;

    // Fetch the current guest_id for the given room number
    $guest_query = "SELECT guest_id FROM rooms WHERE room_number = ?";
    $guest_stmt = $conn->prepare($guest_query);
    $guest_stmt->bind_param("i", $room_number);
    $guest_stmt->execute();
    $guest_result = $guest_stmt->get_result();
    $guest = $guest_result->fetch_assoc();

    if (!$guest) {
        echo json_encode(['success' => false, 'message' => 'No guest currently assigned to the specified room.']);
        exit();
    }

    $guest_id = $guest['guest_id']; // Get the current guest_id

    // Insert order into the bar_orders table with guest_id
    $insert_query = "INSERT INTO bar_orders (room_number, order_description, status, timestamp, total_amount, special_instructions, guest_id) 
    VALUES (?, ?, 'Pending', NOW(), ?, ?, ?)";
    $insert_stmt = $conn->prepare($insert_query);
    $order_description = "Room " . $room_number . " - " . $name; // Use name only
    $insert_stmt->bind_param("issds", $room_number, $order_description, $total_amount, $special_instructions, $guest_id);
    $insert_stmt->execute();

    // Return a success response
    echo json_encode(['success' => true]);

}
?>
