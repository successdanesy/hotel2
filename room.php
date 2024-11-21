<?php
session_start();

include('db_connect.php');

// Check if the user is logged in
if (!isset($_SESSION['username'])) {
    $_SESSION['msg'] = "You must log in first";
    header('location: login.php');
    exit();
}

if (isset($_GET['logout'])) {
    session_destroy();
    unset($_SESSION['username']);
    header("location: login.php");
    exit();
}

// Showing messages
if (isset($_GET['message'])) {
    echo "<div class='alert success'>" . htmlspecialchars($_GET['message']) . "</div>";
}

if (isset($_GET['error'])) {
    echo "<div class='alert error'>" . htmlspecialchars($_GET['error']) . "</div>";
}

// Fetch the selected room type filter from the URL or default to 'All'
$room_type_filter = isset($_GET['room_type']) ? $_GET['room_type'] : 'All';

// Build the query based on the room type filter
$query = "SELECT room_number, status, room_type, price FROM rooms";
if ($room_type_filter != 'All') {
    $query .= " WHERE room_type = '$room_type_filter'";
}

$result = $conn->query($query);

$rooms = [];
while ($row = $result->fetch_assoc()) {
    $rooms[] = $row; // Push each row into the rooms array
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Room Management - Antilla Apartments & Suites</title>
    <link rel="stylesheet" href="room.css">
    <script src="room.js" defer></script>
</head>
<body>
    <div class="main-content">
        <header>
            <h1>Room Management</h1>
            <p>Manage rooms and their statuses below.</p>
        </header>

        <!-- Room Filter Form -->
        <section class="room-filter">
            <h2>Filter Rooms</h2>
            <form action="room.php" method="GET">
                <label for="room_type">Room Type:</label>
                <select id="room_type" name="room_type" onchange="this.form.submit()">
                    <option value="All" <?php echo $room_type_filter == 'All' ? 'selected' : ''; ?>>All</option>
                    <option value="Single" <?php echo $room_type_filter == 'Single' ? 'selected' : ''; ?>>Single</option>
                    <option value="Double" <?php echo $room_type_filter == 'Double' ? 'selected' : ''; ?>>Double</option>
                    <option value="Suite" <?php echo $room_type_filter == 'Suite' ? 'selected' : ''; ?>>Suite</option>
                    <option value="Family" <?php echo $room_type_filter == 'Family' ? 'selected' : ''; ?>>Family</option>
                </select>
            </form>
        </section>

        <!-- Add Room Form -->
        <section class="add-room">
            <h2>Add a New Room</h2>
            <form id="add-room-form" method="POST" action="add_room.php">
                <label for="room_number">Room Number:</label>
                <input type="text" id="room_number" name="room_number" required>

                <label for="room_type">Room Type:</label>
                <select id="room_type" name="room_type" required>
                    <option value="Single">Single</option>
                    <option value="Double">Double</option>
                    <option value="Suite">Suite</option>
                    <option value="Family">Family</option>
                </select>

                <label for="price">Price (per night):</label>
                <input type="number" id="price" name="price" step="0.01" required>

                <button type="submit">Add Room</button>
            </form>
        </section>

        <!-- Room Table -->
        <section class="room-list">
            <h2>Room List</h2>
            <table>
                <thead>
                    <tr>
                        <th>Room Number</th>
                        <th>Room Type</th>
                        <th>Status</th>
                        <th>Price</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- Dynamically load rooms from the database -->
                    <?php foreach ($rooms as $room): ?>
                        <tr>
                            <td><?php echo $room['room_number']; ?></td>
                            <td><?php echo $room['room_type']; ?></td>
                            <td class="<?php echo strtolower(str_replace(' ', '-', $room['status'])); ?>">
                                <?php echo ucfirst($room['status']); ?>
                            </td>
                            <td><?php echo "$" . number_format($room['price'], 2); ?></td>
                            <td>
                                <?php if ($room['status'] == 'Available'): ?>
                                    <form action="checkin.php" method="POST">
                                        <input type="hidden" name="room_number" value="<?php echo $room['room_number']; ?>">
                                        <button type="submit" class="button checkin-btn">Check-in</button>
                                    </form>
                                <?php elseif ($room['status'] == 'Occupied'): ?>
                                    <form action="checkout.php" method="POST" class="checkout-form">
                                        <input type="hidden" name="room_number" value="<?php echo $room['room_number']; ?>">
                                        <button type="submit" class="button checkout-btn">Check-out</button>
                                    </form>
                                    <form action="extend_stay.php" method="POST" class="extend-form">
                                        <input type="hidden" name="room_number" value="<?php echo $room['room_number']; ?>">
                                        <button type="submit" class="button extend-btn">Extend Stay</button>
                                    </form>
                                <?php else: ?>
                                    <span class="status under-maintenance">Under Maintenance</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </section>
    </div>
</body>
</html>
