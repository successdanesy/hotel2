<?php
session_start();
include('db_connect.php'); // Database connection


// Fetch categories and menu items
$queryCategories = "SELECT id, category_name FROM categories ORDER BY category_name";
$queryMenuItems = "SELECT id, name, price, category_id FROM menu_items";

$categories = $conn->query($queryCategories)->fetch_all(MYSQLI_ASSOC);
$menuItems = $conn->query($queryMenuItems)->fetch_all(MYSQLI_ASSOC);

// Organize menu items by category
$menuItemsByCategory = [];
foreach ($menuItems as $item) {
    $menuItemsByCategory[$item['category_id']][] = [
        'id' => $item['id'],
        'name' => $item['name'],
        'price' => $item['price']
    ];
}

// Handle adding a new order
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_order'])) {
    $roomNumberId = $_POST['room_number'] ?? null;
    $menuItemId = $_POST['menu_item'] ?? null;
    $specialInstructions = $_POST['special_instructions'] ?? '';

    if ($roomNumberId && $menuItemId) {
        // Get room number
        $roomQuery = "SELECT room_number FROM rooms WHERE id = ?";
        $stmt = $conn->prepare($roomQuery);
        $stmt->bind_param("i", $roomNumberId);
        $stmt->execute();
        $stmt->bind_result($roomNumber);
        $stmt->fetch();
        $stmt->close();

        // Get menu item details
        $menuQuery = "SELECT name, price FROM menu_items WHERE id = ?";
        $stmt = $conn->prepare($menuQuery);
        $stmt->bind_param("i", $menuItemId);
        $stmt->execute();
        $stmt->bind_result($itemName, $itemPrice);
        $stmt->fetch();
        $stmt->close();

        if ($roomNumber && $itemName && $itemPrice) {
            $orderQuery = "INSERT INTO kitchen_orders (room_number, order_description, status, timestamp, total_amount, special_instructions) 
                           VALUES (?, ?, 'Pending', NOW(), ?, ?)";
            $stmt = $conn->prepare($orderQuery);
            $stmt->bind_param("ssds", $roomNumber, $itemName, $itemPrice, $specialInstructions);

            if ($stmt->execute()) {
                header("Location: kitchen.php");
                exit();
            } else {
                $error = "Error adding the order: " . $stmt->error;
            }
            $stmt->close();
        } else {
            $error = "Invalid room or menu item.";
        }
    } else {
        $error = "Please fill in all required fields.";
    }
}

// Handle marking an order as completed
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['mark_completed'])) {
    $orderId = $_POST['order_id'] ?? null;
    if ($orderId) {
        $updateQuery = "UPDATE kitchen_orders SET status = 'Completed' WHERE id = ?";
        $stmt = $conn->prepare($updateQuery);
        $stmt->bind_param("i", $orderId);
        if ($stmt->execute()) {
            echo json_encode(['status' => 'Completed']);
            exit();
        } else {
            echo json_encode(['error' => 'Failed to update order status']);
            exit();
        }
    }
}


// Fetch all orders
function fetchOrders($conn) {
    $query = "SELECT id, room_number, order_description, total_amount, special_instructions, status, timestamp 
              FROM kitchen_orders ORDER BY timestamp DESC";
    return $conn->query($query)->fetch_all(MYSQLI_ASSOC);
}

$orders = fetchOrders($conn);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kitchen Orders</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="kitchen.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>
<div class="main-content">
        <header>
        <h1>Kitchen Orders</h1>
            <p>Manage Kitchen Orders Below.</p>
            <a href="logout.php" class="button new-guest">
                <i class="fa-solid fa-arrow-right-from-bracket"></i> Logout
            </a>
        </header>
    
    
        <form id="orderForm">
    <!-- Room Selection -->
    <div>
        <label for="room_number">Room Number:</label>
        <select name="room_number" id="room_number" required>
            <option value="">-- Select Room --</option>
            <?php
            $roomsQuery = "SELECT id, room_number FROM rooms WHERE status = 'Occupied'";
            $roomsResult = $conn->query($roomsQuery);
            while ($room = $roomsResult->fetch_assoc()) {
                echo "<option value='{$room['room_number']}'>{$room['room_number']}</option>";
            }
            ?>
        </select>
        </div>
        <div id="guest-id-lookup">
    <label for="guest_id">Guest ID:</label>
    <input type="text" id="guest_id" readonly>
    <button type="button" onclick="fetchGuestId()">Fetch Guest ID</button>
    <p id="status"></p>
</div>


    <!-- Menu Selection -->
    <div>
        <label for="category">Category:</label>
        <select id="category" required>
            <option value="">-- Select Category --</option>
            <?php foreach ($categories as $category): ?>
                <option value="<?= $category['id'] ?>"><?= htmlspecialchars($category['category_name']) ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div>
        <label for="menu_item">Menu Item:</label>
        <select id="menu_item" required>
            <option value="">-- Select Menu Item --</option>
        </select>
    </div>

    <div>
        <label for="special_instructions">Special Instructions:</label>
        <textarea id="special_instructions" placeholder="Add any instructions..."></textarea>
    </div>
    <button type="button" id="addToTray">Add to Tray</button>
</form>

<!-- Order Tray -->
<h3>Order Tray</h3>
<table id="orderTray">
    <thead>
        <tr>
            <th>Item</th>
            <th>Price (₦)</th>
            <th>Instructions</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <!-- Tray items will be dynamically added here -->
    </tbody>
</table>
<button id="submitOrders" type="button">Submit Orders</button>


    <!-- Orders Table -->
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Room</th>
                <th>Order</th>
                <th>Price (₦)</th>
                <th>Instructions</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($orders as $order): ?>
                <tr>
                    <td><?= $order['id'] ?></td>
                    <td><?= htmlspecialchars($order['room_number']) ?></td>
                    <td><?= htmlspecialchars($order['order_description']) ?></td>
                    <td><?= number_format($order['total_amount'], 2) ?></td>
                    <td><?= htmlspecialchars($order['special_instructions']) ?></td>
                    <td id="status-<?= $order['id'] ?>"><?= htmlspecialchars($order['status']) ?></td>
                    <td>
                        <?php if ($order['status'] !== 'Completed'): ?>
                            <button onclick="markAsComplete(<?= $order['id'] ?>)" id="mark-completed-btn-<?= $order['id'] ?>">Mark Completed</button>
                        <?php else: ?>
                            <button disabled>Completed</button>
                        <?php endif; ?>

                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    
    <script>
    const menuItemsByCategory = <?= json_encode($menuItemsByCategory) ?>;
</script>

<script src="kitchen.js"></script>
</body>
</html>
