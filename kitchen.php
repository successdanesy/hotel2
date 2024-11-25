<?php
session_start();
include('db_connect.php');

// Check if user is logged in
if (!isset($_SESSION['username'])) {
    header('Location: login.php');
    exit();
}

// Fetch dishes from the database
$dishes_query = "SELECT * FROM dishes"; // Replace 'dishes' with your table name
$dishes_result = $conn->query($dishes_query);

$dishes = [];
if ($dishes_result->num_rows > 0) {
    while ($row = $dishes_result->fetch_assoc()) {
        $dishes[] = $row;
    }
}

// Fetch kitchen updates
$updates_query = "SELECT * FROM kitchen_updates ORDER BY created_at DESC"; // Replace 'kitchen_updates' with your table name
$updates_result = $conn->query($updates_query);

$updates = [];
if ($updates_result->num_rows > 0) {
    while ($row = $updates_result->fetch_assoc()) {
        $updates[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kitchen Order - Antilla Apartments & Suites</title>
    <link rel="stylesheet" href="kitchen.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>

<!-- Sidebar -->
<aside class="sidebar">
    <div class="icon"><i class="fas fa-hotel"></i></div>
    <div class="icon"><i class="fas fa-tasks"></i></div>
    <div class="icon"><i class="fas fa-calendar-alt"></i></div>
    <div class="icon"><i class="fas fa-cog"></i></div>
</aside>

<!-- Main Content -->
<div class="main-content">
    <header>
        <input type="text" placeholder="Search dishes..." class="search-bar">
        <div class="welcome"><i class="fas fa-user-circle"></i> Welcome</div>
    </header>

    <h2>Kitchen Order - Select and Send Orders to Kitchen</h2>

    <!-- Filters Section -->
    <div class="filters">
        <button onclick="filterCategory('Appetizers')">Appetizers</button>
        <button onclick="filterCategory('Main Courses')">Main Courses</button>
        <button onclick="filterCategory('Desserts')">Desserts</button>
    </div>

    <div class="order-page">
        <!-- Dish Selection Section -->
        <div class="dishes">
            <?php foreach ($dishes as $dish): ?>
                <div class="dish-card" data-category="<?= htmlspecialchars($dish['category']); ?>">
                    <img src="images/<?= htmlspecialchars($dish['image']); ?>" alt="<?= htmlspecialchars($dish['name']); ?>">
                    <div class="dish-info">
                        <h3><?= htmlspecialchars($dish['name']); ?></h3>
                        <p>₦<?= number_format($dish['price'], 2); ?></p>
                        <button onclick="addToOrder('<?= htmlspecialchars($dish['name']); ?>', <?= $dish['price']; ?>)">+ Add</button>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Order Summary Sidebar -->
        <aside class="order-summary">
            <h3>Order Summary</h3>
            <label for="roomNumber">Select Room Number:</label>
            <select id="roomNumber">
                <option value="101">101</option>
                <option value="102">102</option>
                <option value="103">103</option>
            </select>
            <ul id="orderList"></ul>
            <p>Total: ₦<span id="totalAmount">0.00</span></p>
            <label for="specialInstructions">Special Instructions:</label>
            <textarea id="specialInstructions" placeholder="Add any specific instructions..."></textarea>
            <button id="clearAllOrdersButton">Clear All Orders</button>
            <button class="send-to-frontdesk" onclick="confirmOrder()">Send to Front Desk</button>
        </aside>
    </div>

    <!-- Updates Section -->
    <section id="kitchen-updates">
        <h2>Kitchen Updates</h2>
        <form id="update-form" action="send_update.php" method="POST">
            <input type="text" id="update-input" name="update_message" placeholder="Enter update (e.g., Out of salmon)">
            <button type="submit" class="button send-update"><i class="fas fa-share-square"></i> Send Update</button>
        </form>
        <ul>
            <?php foreach ($updates as $update): ?>
                <li><?= htmlspecialchars($update['message']); ?> - <em><?= htmlspecialchars($update['created_at']); ?></em></li>
            <?php endforeach; ?>
        </ul>
    </section>
</div>

<script src="kitchen.js"></script>
</body>
</html>
