<?php
session_start();
include('db_connect.php'); // Database connection

// Handle form submission for adding a new order
if (isset($_POST['submit_order'])) {
    $room_number_id = $_POST['room_number'] ?? '';  // The room ID is selected from the dropdown
    $menu_item = $_POST['menu_item'] ?? '';  
    $special_instructions = $_POST['special_instructions'] ?? '';

    // Validate required fields
    if (!empty($room_number_id) && !empty($menu_item)) {
        // Fetch the room number from the rooms table based on the selected room ID
        $query = "SELECT room_number FROM rooms WHERE id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $room_number_id); // Get the room number based on the ID
        $stmt->execute();
        $stmt->bind_result($room_number);
        $stmt->fetch();
        $stmt->close();

        // Fetch the name and price of the selected menu item
        $query = "SELECT name, price FROM menu_items WHERE id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $menu_item);
        $stmt->execute();
        $stmt->bind_result($name, $price);
        $stmt->fetch();
        $stmt->close();

        // If the room number and item were found, proceed with inserting the order
        if ($room_number && $name && $price) {
            // Set order description to the name of the menu item only
            $order_description = $name;  // Only menu item name (e.g., "Pasta")

            // Insert the order into kitchen_orders table with the correct room number (NOT room ID)
            $insert_query = "INSERT INTO kitchen_orders (room_number, order_description, status, timestamp, total_amount, special_instructions) 
                             VALUES (?, ?, 'pending', NOW(), ?, ?)";
            $insert_stmt = $conn->prepare($insert_query);
            $insert_stmt->bind_param("ssds", $room_number, $order_description, $price, $special_instructions);
            
            // Execute the insert
            if ($insert_stmt->execute()) {
                // Redirect to avoid form resubmission on refresh
                header('Location: kitchen.php');
                exit();
            } else {
                echo "Error inserting order: " . $insert_stmt->error;
            }

            $insert_stmt->close();
        } else {
            echo "Room or menu item not found.";
        }
    } else {
        echo "Please fill out all required fields.";
    }
}


// Handle marking order as completed
if (isset($_POST['mark_completed'])) {
    $order_id = $_POST['order_id'];

    // Update the order status to "Completed"
    $query = "UPDATE kitchen_orders SET status = 'Completed' WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $order_id);
    $stmt->execute();
    $stmt->close();

    // Return success response
    echo json_encode(['status' => 'Completed']);  // Make sure to send a 'Completed' status
    exit();
}

// Fetch all orders from the kitchen_orders table
function fetchOrders() {
    global $conn;
    $query = "SELECT id, room_number, order_description, total_amount, special_instructions, status, timestamp FROM kitchen_orders ORDER BY timestamp DESC";
    $result = $conn->query($query);
    return $result;
}

$orders = fetchOrders();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kitchen Orders</title>
    <link rel="stylesheet" href="kitchen.css">

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="kitchen.js"></script>
</head>
<body>
    <h1>Kitchen Orders</h1>

    <!-- Form for adding a new order -->
    <form id="add-order-form" method="POST">
        <div>
            <label for="room_number">Room Number:</label>
            <select name="room_number" id="room_number" required>
                <?php
                // Fetch only occupied rooms from the rooms table
                $query = "SELECT id, room_number FROM rooms WHERE status = 'Occupied'";
                $result = $conn->query($query);

                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        echo "<option value='" . $row['id'] . "'>" . $row['room_number'] . "</option>";
                    }
                } else {
                    echo "<option value=''>No occupied rooms available</option>";
                }
                ?>
            </select>
        </div>
        <div>
            <label for="menu_item">Select Menu Item:</label>
            <select name="menu_item" id="menu_item" required>
                <?php
                // Fetch menu items from the database (only name and price)
                $query = "SELECT id, name, price FROM menu_items";
                $result = $conn->query($query);
                while ($row = $result->fetch_assoc()) {
                    echo "<option value='" . $row['id'] . "'>" . $row['name'] . " (₦" . number_format($row['price'], 2) . ")</option>";
                }
                ?>
            </select>
        </div>
        <div>
            <label for="special_instructions">Special Instructions:</label>
            <textarea name="special_instructions" id="special_instructions" placeholder="Add any specific instructions..."></textarea>
        </div>
        <button type="submit" name="submit_order" class="button">Add Order</button>
    </form>

    <hr>

    <!-- Orders Table -->
    <table id="orders-table">
        <thead>
            <tr>
                <th>Order ID</th>
                <th>Room Number</th>
                <th>Order Description</th>
                <th>Total Amount (₦)</th>
                <th>Special Instructions</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
        <?php while ($row = $orders->fetch_assoc()): ?>
        <tr>
            <td><?= $row['id'] ?></td>
            <td><?= $row['room_number'] ?></td>
            <td><?= $row['order_description'] ?></td>
            <td><?= number_format($row['total_amount'], 2) ?></td>
            <td><?= htmlspecialchars($row['special_instructions']) ?></td>
            <td id="order-status-<?= $row['id'] ?>"><?= $row['status'] ?></td>
            <td>
                <form method="POST" style="display:inline-block;">
                    <input type="hidden" name="order_id" value="<?= $row['id'] ?>">
                    <button type="submit" name="mark_completed" class="button">Mark as Completed</button>
                </form>
            </td>
        </tr>
        <?php endwhile; ?>
        </tbody>
    </table>

</body>
</html>
