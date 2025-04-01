<?php
session_start();

include('db_connect.php');

// Check if the user is logged in
if (!isset($_SESSION['username'])) {
    $_SESSION['msg'] = "You must log in first";
    header('location: login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get room details from the form
    $room_number = htmlspecialchars($_POST['room_number']);
    $room_type = htmlspecialchars($_POST['room_type']);
    $weekday_price = htmlspecialchars($_POST['weekday_price']);
    $weekend_price = htmlspecialchars($_POST['weekend_price']);

    // Insert the new room into the database
    $query = "INSERT INTO rooms (room_number, room_type, status, weekday_price, weekend_price) VALUES (?, ?, 'Available', ?, ?)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ssdd", $room_number, $room_type, $weekday_price, $weekend_price);

    if ($stmt->execute()) {
        // Redirect to room.php with a success message
        header("Location: room.php?message=Room successfully added!");
        exit();
    } else {
        // Redirect to add_room.php with an error message
        $error = "Error adding room: " . $conn->error;
        header("Location: add_room.php?error=" . urlencode($error));
        exit();
    }
}

// Show messages
if (isset($_GET['message'])) {
    echo "<div class='alert success'>" . htmlspecialchars($_GET['message']) . "</div>";
}

if (isset($_GET['error'])) {
    echo "<div class='alert error'>" . htmlspecialchars($_GET['error']) . "</div>";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Room - Antilla Apartments & Suites</title>
    <link rel="stylesheet" href="room.css">
</head>
<body>
    <div class="main-content">
        <header>
            <h1>Add a New Room</h1>
            <p>Fill out the form below to add a new room to the system.</p>
        </header>

        <!-- Add Room Form -->
        <section class="add-room">
            <form id="add-room-form" method="POST" action="">
                <label for="room_number">Room Number:</label>
                <input type="text" id="room_number" name="room_number" required>

                <label for="room_type">Room Type:</label>
                <select id="room_type" name="room_type" required>
                    <option value="Standard" <?php echo (isset($room_type) && $room_type == 'Standard') ? 'selected' : ''; ?>>Standard</option>
                    <option value="Executive" <?php echo (isset($room_type) && $room_type == 'Executive') ? 'selected' : ''; ?>>Executive</option>
                    <option value="Luxury" <?php echo (isset($room_type) && $room_type == 'Luxury') ? 'selected' : ''; ?>>Luxury</option>
                </select>

                <label for="weekday_price">Weekday Price (per night):</label>
                <input type="number" id="weekday_price" name="weekday_price" step="0.01" required>

                <label for="weekend_price">Weekend Price (per night):</label>
                <input type="number" id="weekend_price" name="weekend_price" step="0.01" required>

                <button type="submit">Add Room</button>
            </form>
            
            <section class="back-to-room-management">
                <form action="room.php" method="GET">
                    <button type="submit" class="button back-button">Back to Room Management</button>
                </form>
            </section>

        </section>
    </div>
</body>
</html>
