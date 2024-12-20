<?php
session_start();
include('db_connect.php'); // Database connection

// Fetch categories and menu items
$queryCategories = "SELECT id, category_name FROM categories_bar ORDER BY category_name";
$queryMenuItems = "SELECT id, name, price, category_id FROM menu_items_bar";

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
    $guestType = $_POST['guest_type'] ?? 'guest';
    $roomNumberId = $_POST['room_number'] ?? null;
    $menuItemId = $_POST['menu_item'] ?? null;
    $specialInstructions = $_POST['special_instructions'] ?? '';

    if ($guestType === 'guest' && $roomNumberId && $menuItemId) {
        // Get room number and guest ID
        $roomQuery = "SELECT room_number, guest_id FROM rooms WHERE id = ?";
        $stmt = $conn->prepare($roomQuery);
        $stmt->bind_param("i", $roomNumberId);
        $stmt->execute();
        $stmt->bind_result($roomNumber, $guestId);
        $stmt->fetch();
        $stmt->close();

        // Get menu item details
        $menuQuery = "SELECT name, price FROM menu_items_bar WHERE id = ?";
        $stmt = $conn->prepare($menuQuery);
        $stmt->bind_param("i", $menuItemId);
        $stmt->execute();
        $stmt->bind_result($itemName, $itemPrice);
        $stmt->fetch();
        $stmt->close();

        if ($roomNumber && $itemName && $itemPrice && $guestId) {
            $orderQuery = "INSERT INTO bar_orders 
                           (room_number, order_description, status, timestamp, total_amount, special_instructions, guest_id, guest_type) 
                           VALUES (?, ?, 'Pending', NOW(), ?, ?, ?, ?)";
            $stmt = $conn->prepare($orderQuery);
            $stmt->bind_param("ssdsd", $roomNumber, $itemName, $itemPrice, $specialInstructions, $guestId, $guestType);

            if ($stmt->execute()) {
                header("Location: bar.php");
                exit();
            } else {
                $error = "Error adding the order: " . $stmt->error;
            }
            $stmt->close();
        } else {
            $error = "Invalid room, menu item, or missing guest information.";
        }
    } elseif ($guestType === 'non_guest' && $menuItemId) {
        // Handle non-guest order
        $menuQuery = "SELECT name, price FROM menu_items_bar WHERE id = ?";
        $stmt = $conn->prepare($menuQuery);
        $stmt->bind_param("i", $menuItemId);
        $stmt->execute();
        $stmt->bind_result($itemName, $itemPrice);
        $stmt->fetch();
        $stmt->close();

        if ($itemName && $itemPrice) {
            $orderQuery = "INSERT INTO bar_orders 
                           (order_description, status, timestamp, total_amount, special_instructions, guest_type) 
                           VALUES (?, 'Pending', NOW(), ?, ?, ?)";
            $stmt = $conn->prepare($orderQuery);
            $stmt->bind_param("sds", $itemName, $itemPrice, $specialInstructions, $guestType);

            if ($stmt->execute()) {
                header("Location: bar.php");
                exit();
            } else {
                $error = "Error adding the order: " . $stmt->error;
            }
            $stmt->close();
        } else {
            $error = "Invalid menu item.";
        }
    } else {
        $error = "Invalid input.";
    }
}

// Handle marking an order as completed
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['mark_completed'])) {
    $orderId = $_POST['order_id'] ?? null;
    if ($orderId) {
        $updateQuery = "UPDATE bar_orders SET status = 'Completed' WHERE id = ?";
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
              FROM bar_orders ORDER BY timestamp DESC";
    return $conn->query($query)->fetch_all(MYSQLI_ASSOC);
}

$orders = fetchOrders($conn);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Antilla Bar Page</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="bar.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>
<div class="main-content">
        <header>
        <h1>Bar Orders</h1>
            <p>Manage Bar Orders Below.</p>

            <a href="imprest_request_bar.php" class="button new-guest">
                <i class="fa-solid fa-arrow-right-from-bracket"></i> Bar imprest
            </a>
            <a href="logout.php" class="button new-guest">
                <i class="fa-solid fa-arrow-right-from-bracket"></i> Logout
            </a>
        </header>

<form id="orderForm" method="POST" action="bar.php">
    <!-- Guest Type Selection -->
    <div>
        <label for="guest_type">Guest Type:</label>
        <select id="guest_type" name="guest_type" onchange="toggleGuestFields()">
            <option value="guest">Guest</option>
            <option value="non_guest">Non-Guest</option>
        </select>
    </div>

    <!-- Room Selection -->
    <div id="guest_fields">
        <label for="room_number">Room Number:</label>
        <select name="room_number" id="room_number">
            <option value="">-- Select Room --</option>
            <?php
            $roomsQuery = "SELECT id, room_number FROM rooms WHERE status = 'Occupied'";
            $roomsResult = $conn->query($roomsQuery);
            while ($room = $roomsResult->fetch_assoc()) {
                echo "<option value='{$room['room_number']}'>{$room['room_number']}</option>";
            }
            ?>
        </select>
        <div id="guest-id-lookup">
            <label for="guest_id">Guest ID:</label>
            <input type="text" id="guest_id" readonly>
            <button type="button" onclick="fetchGuestId()">Fetch Guest ID</button>
            <p id="status"></p>
        </div>
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
        <textarea id="special_instructions" name="special_instructions" placeholder="Add any instructions..."></textarea>
    </div>
    <button type="button" id="addToTray">Add to Tray</button>
</form>

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

        function toggleGuestFields() {
            var guestType = document.getElementById("guest_type").value;
            var guestFields = document.getElementById("guest_fields");
            guestFields.style.display = (guestType === "guest") ? "block" : "none";
        }
    </script>
    
    <script src="bar.js"></script>
</body>
</html>

