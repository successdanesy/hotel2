<?php
session_start();
include('db_connect.php'); // Database connection

// Fetch categories and menu items
$queryCategories = "SELECT id, category_name FROM categories ORDER BY category_name";
$queryMenuItems = "SELECT id, name, category_id FROM menu_items"; // ❌ Removed 'price'

$categories = $conn->query($queryCategories)->fetch_all(MYSQLI_ASSOC);
$menuItems = $conn->query($queryMenuItems)->fetch_all(MYSQLI_ASSOC);

// Organize menu items by category
$menuItemsByCategory = [];
foreach ($menuItems as $item) {
    $menuItemsByCategory[$item['category_id']][] = [
        'id' => $item['id'],
        'name' => $item['name'],
        
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
        $menuQuery = "SELECT name, price FROM menu_items WHERE id = ?";
        $stmt = $conn->prepare($menuQuery);
        $stmt->bind_param("i", $menuItemId);
        $stmt->execute();
        $stmt->bind_result($itemName, $itemPrice);
        $stmt->fetch();
        $stmt->close();

        if ($roomNumber && $itemName && $itemPrice && $guestId) {
            $orderQuery = "INSERT INTO kitchen_orders 
                           (room_number, order_description, status, timestamp, total_amount, special_instructions, guest_id) 
                           VALUES (?, ?, 'Pending', NOW(), ?, ?, ?)";
            $stmt = $conn->prepare($orderQuery);
            $stmt->bind_param("ssdsd", $roomNumber, $itemName, $itemPrice, $specialInstructions, $guestId);
            
            if ($stmt->execute()) {
                header("Location: kitchen.php");
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
        $menuQuery = "SELECT name, price FROM menu_items WHERE id = ?";
        $stmt = $conn->prepare($menuQuery);
        $stmt->bind_param("i", $menuItemId);
        $stmt->execute();
        $stmt->bind_result($itemName, $itemPrice);
        $stmt->fetch();
        $stmt->close();

        if ($itemName && $itemPrice) {
            $orderQuery = "INSERT INTO kitchen_orders 
                           (order_description, status, timestamp, total_amount, special_instructions, guest_id) 
                           VALUES (?, 'Pending', NOW(), ?, ?, NULL)";
            $stmt = $conn->prepare($orderQuery);
            $stmt->bind_param("sds", $itemName, $itemPrice, $specialInstructions);
            
            if ($stmt->execute()) {
                header("Location: kitchen.php");
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

// Get the selected date from the form submission or default to today's date
$selected_date = isset($_GET['selected_date']) ? $_GET['selected_date'] : date('Y-m-d');

// Verify that the date is properly formatted
if (DateTime::createFromFormat('Y-m-d', $selected_date) === false) {
    $selected_date = date('Y-m-d'); // Fallback to today's date if the format is incorrect
}

// Fetch orders based on the selected date
function fetchOrders($conn, $selected_date) {
    $query = "SELECT id, room_number, order_description, quantity, total_amount, special_instructions, status, timestamp 
          FROM kitchen_orders 
          WHERE DATE(timestamp) = ? 
          ORDER BY timestamp DESC";

    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $selected_date);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_all(MYSQLI_ASSOC);
}

$orders = fetchOrders($conn, $selected_date);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Antilla Kitchen Staff</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="kitchen.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>
<div class="main-content">
        <header>
        <h1><i class="fas fa-utensils icon"></i>Kitchen Orders</h1>
            <p>Manage Kitchen Orders Below.</p>
            <a href="imprest_request.php" class="button new-guest">
            <i class="fas fa-hand-holding-usd icon"></i> Imprest Request
            </a>
            <a href="logout.php" class="button new-guest">
                <i class="fa-solid fa-arrow-right-from-bracket"></i> Logout
            </a>
        </header>
    
<form id="orderForm" method="POST" action="kitchen.php">
    <!-- Guest Information Section -->
        <div class="section">
            <h2><i class="fas fa-user icon"></i>Guest Information</h2>
                <div class="form-group">
                    <label for="guest_type">Guest Type:</label>
                    <select id="guest_type" name="guest_type" onchange="toggleGuestFields()">
                        <option value="guest">Guest</option>
                        <option value="non_guest">Non-Guest</option>
                    </select>
                </div>
                <div id="guest_fields" class="form-group">
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
                </div>
                <div id="guest-id-lookup" class="form-group">
                    <label for="guest_id">Guest ID:</label>
                    <input type="text" id="guest_id" readonly>
                    <p id="status"></p>
                    <br>
                    <button type="button" class="button" onclick="fetchGuestId()">Fetch Guest ID</button>
                </div>
            
        </div>

        <!-- Food Selection Section -->
        <div class="section">
            <h2><i class="fas fa-utensils icon"></i>Food Selection</h2>
            <div class="form-group">
                <label for="category">Category:</label>
                <select id="category" required>
                    <option value="">-- Select Category --</option>
                    <?php foreach ($categories as $category): ?>
                        <option value="<?= $category['id'] ?>"><?= htmlspecialchars($category['category_name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
            <label for="menu_item">Menu Item:</label>
            <select id="menu_item" name="menu_item">
    <option value="">-- Select Menu Item --</option>
    <?php foreach ($menuItems as $item): ?>
        <option value="<?= $item['id'] ?>">
    <?= htmlspecialchars($item['name']) ?>
</option>


    <?php endforeach; ?>
</select>

            </div>

            <div class="price-adjustment">
    <button type="button" class="adjust-price" data-amount="-500">-500</button>
    <button type="button" class="adjust-price" data-amount="-200">-200</button>
    <button type="button" class="adjust-price" data-amount="-100">-100</button>
    <button type="button" class="adjust-price" data-amount="-50">-50</button>

    <span id="current_price" data-menu-id="" data-original-price="0">₦0</span>

    <button type="button" class="adjust-price" data-amount="50">+50</button>
    <button type="button" class="adjust-price" data-amount="100">+100</button>
    <button type="button" class="adjust-price" data-amount="200">+200</button>
    <button type="button" class="adjust-price" data-amount="500">+500</button>
</div>


            <div class="form-group">
                <label for="special_instructions">Special Instructions:</label>
                <textarea id="special_instructions" name="special_instructions" placeholder="Add any instructions..."></textarea>
            </div>
            <button type="button" id="addToTray" class="button"><i class="fas fa-plus icon"></i>Add to Tray</button>
        </div>
    </form>

<!-- Order Tray Section -->
<div class="section">
            <h2><i class="fas fa-shopping-cart icon"></i>Order Tray</h2>
            <table id="orderTray">
    <thead>
        <tr>
            <th>Item</th>
            <th>Quantity</th>  <!-- ✅ Added Quantity Column -->
            <th>Price (₦)</th>
            <th>Instructions</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <!-- Tray items will be dynamically added here -->
    </tbody>
</table>

            <button id="submitOrders" type="button" class="button"><i class="fas fa-paper-plane icon"></i>Submit Orders To Front-Desk</button>
        </div>
<br>
<br>
<!-- Submitted Orders Section -->
<div class="section">
            <h2><i class="fas fa-list icon"></i>Submitted Orders</h2>
<!-- Date Filter Form -->
<form method="GET" action="kitchen.php" class="filter-form">
    <label for="selected_date">Select Date:</label>
    <input type="date" id="selected_date" name="selected_date" value="<?php echo $selected_date; ?>" required>
    <button type="submit" class="button"><i class="fa-solid fa-filter"></i>Filter By Date</button>
</form>
<table>
<thead>
    <tr>
        <th>ID</th>
        <th>Room</th>
        <th>Order</th>
        <th>Quantity</th>  <!-- ✅ Added Quantity Column -->
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
            <td><?= isset($order['quantity']) ? $order['quantity'] : 'N/A' ?></td>
  <!-- ✅ Show Quantity -->
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

</div>

<script>

document.getElementById('submitOrders').addEventListener('click', function(event) {
    event.preventDefault(); // Prevent the default form submission

    const form = document.getElementById('orderForm');
    const formData = new FormData(form);

    fetch('kitchen.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.text())
    .then(data => {
        // Reload the page after a successful order submission
        window.location.reload();
    })
    .catch(error => console.error('Error submitting order:', error));
});


    const menuItemsByCategory = <?= json_encode($menuItemsByCategory) ?>;

    function toggleGuestFields() {
    var guestType = document.getElementById("guest_type").value;
    var guestFields = document.getElementById("guest_fields"); // Room Number dropdown
    var guestIdLookup = document.getElementById("guest-id-lookup"); // Guest ID & Fetch button

    if (guestType === "guest") {
        guestFields.style.display = "block";  // Show Room Number dropdown
        guestIdLookup.style.display = "block"; // Show Guest ID & Fetch button
    } else {
        guestFields.style.display = "none";   // Hide Room Number dropdown
        guestIdLookup.style.display = "none"; // Hide Guest ID & Fetch button
    }
}

document.addEventListener("DOMContentLoaded", function () {
    toggleGuestFields(); // ✅ Ensures fields are set correctly on page load
    document.getElementById("guest_type").addEventListener("change", toggleGuestFields);
});



    document.getElementById('category').addEventListener('change', function() {
        var categoryId = this.value;
        var menuItemSelect = document.getElementById('menu_item');
        menuItemSelect.innerHTML = '<option value="">-- Select Menu Item --</option>';
        
        if (categoryId && menuItemsByCategory[categoryId]) {
            menuItemsByCategory[categoryId].forEach(function(item) {
                var option = document.createElement('option');
                option.value = item.id;
                option.textContent = item.name + ' - ₦' + item.price;
                menuItemSelect.appendChild(option);
            });
        }
    });

    function markAsComplete(orderId) {
        $.ajax({
            url: 'kitchen.php',
            type: 'POST',
            data: {
                mark_completed: true,
                order_id: orderId
            },
            success: function(response) {
                var result = JSON.parse(response);
                if (result.status === 'Completed') {
                    document.getElementById('status-' + orderId).textContent = 'Completed';
                    document.getElementById('mark-completed-btn-' + orderId).disabled = true;
                }
            }
        });
    }

    // Initial call to set guest fields visibility
    toggleGuestFields();
</script>

<script src="kitchen.js"></script>
</body>
</html>
