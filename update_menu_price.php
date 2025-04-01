<?php
include('db_connect.php');
header('Content-Type: application/json');

$data = json_decode(file_get_contents("php://input"), true);
$menu_item_id = $data['menu_item_id'] ?? null;
$price_change = $data['price_change'] ?? null;

if (!$menu_item_id || !is_numeric($price_change)) {
    echo json_encode(["success" => false, "error" => "Invalid request."]);
    exit;
}

// Get current price
$query = "SELECT price FROM menu_items WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $menu_item_id);
$stmt->execute();
$stmt->bind_result($current_price);
$stmt->fetch();
$stmt->close();

// Calculate new price
$new_price = max(0, $current_price + $price_change);

// Update price in database
$update_query = "UPDATE menu_items SET price = ? WHERE id = ?";
$stmt = $conn->prepare($update_query);
$stmt->bind_param("di", $new_price, $menu_item_id);
$success = $stmt->execute();
$stmt->close();

if ($success) {
    echo json_encode(["success" => true, "new_price" => $new_price]);
} else {
    echo json_encode(["success" => false, "error" => "Database update failed."]);
}
?>
