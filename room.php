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

        <!-- Add Room Form -->
        <section class="add-room">
            <h2>Add a New Room</h2>
            <form id="add-room-form">
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
                <tbody id="room-table-body">
                    <!-- Dynamic content will be loaded here -->
                </tbody>
            </table>
        </section>
    </div>
</body>
</html>
