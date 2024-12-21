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
$query = "SELECT room_number, status, room_type, weekday_price, weekend_price FROM rooms";
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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <script src="room.js" defer></script>
</head>
<body>
    <div class="main-content">
        <header>
            <h1>Room Management</h1>
            <p>Manage rooms and their statuses below.</p>
            <a href="home.php" class="button new-guest">
                <i class="fa-solid fa-arrow-right-from-bracket"></i>Home
            </a>
        </header>

        <!-- Room Filter Form -->
<section class="room-filter">
    <h2>Filter Rooms</h2>
    <form action="room.php" method="GET">
        <label for="room_type">Room Type:</label>
        <select id="room_type" name="room_type" onchange="this.form.submit()">
            <option value="All" <?php echo $room_type_filter == 'All' ? 'selected' : ''; ?>>All</option>
            <option value="Standard" <?php echo $room_type_filter == 'Standard' ? 'selected' : ''; ?>>Standard</option>
            <option value="Executive" <?php echo $room_type_filter == 'Executive' ? 'selected' : ''; ?>>Executive</option>
            <option value="Luxury" <?php echo $room_type_filter == 'Luxury' ? 'selected' : ''; ?>>Luxury</option>
        </select>
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
                            <td>
                                <?php 
                                    // Calculate the price based on weekday or weekend
                                    $current_day = date('l');
                                    if ($current_day == 'Friday' || $current_day == 'Saturday' || $current_day == 'Sunday') {
                                        echo "₦" . number_format($room['weekend_price'], 2) . " (Weekend)";
                                    } else {
                                        echo "₦" . number_format($room['weekday_price'], 2) . " (Weekday)";
                                    }
                                ?>
                            </td>
                            <td>
    <?php if ($room['status'] == 'Available'): ?>
        <!-- Redirect to checkin_form.php -->
        <form action="checkin_form.php" method="GET">
            <input type="hidden" name="room_number" value="<?php echo $room['room_number']; ?>">
            <button type="submit" class="button book-btn">Book Now</button>
        </form>
    <?php elseif ($room['status'] == 'Occupied'): ?>
        <!-- Check-out Button -->
        <form action="checkout.php" method="POST">
    <input type="hidden" name="room_number" value="<?php echo $room['room_number']; ?>">
    <button type="submit" class="button checkout-btn">Check-out</button>
</form>

    <?php else: ?>
        <!-- Under Maintenance Status -->
        <span class="status under-maintenance">Under Maintenance</span>
    <?php endif; ?>
</td>

                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </section>
    </div>
    
      <!-- Link to Create Room Page -->
      <!-- <section class="add-room-link">
            <h2>Add a New Room</h2>
            <form action="create_room.php" method="GET">
                <button type="submit" class="button">Go to Add Room Page</button>
            </form>
        </section> -->
</body>

<!-- Footer Section -->
<footer>
    <p>&copy; 2024 Antilla Apartments & Suites. All rights reserved.</p>
</footer>

</html>
