<?php
session_start();
include('db_connect.php');

// Check if the user is logged in
if (!isset($_SESSION['username'])) {
    $_SESSION['msg'] = "You must log in first";
    header('location: login.php');
    exit();
}

// Fetch all guest details
$query = "SELECT * FROM bookings ORDER BY checkin_date DESC";
$result = $conn->query($query);
$guests = [];
while ($row = $result->fetch_assoc()) {
    $guests[] = $row; // Add each guest to the array
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Guest Management - Antilla Apartments & Suites</title>
    <link rel="stylesheet" href="guest.css">
</head>
<body>
    <div class="main-content">
        <header>
            <h1>Guest Management</h1>
            <p>Manage and view all current and past guest records.</p>
        </header>

        <!-- Guest Table -->
        <section class="guest-list">
            <h2>Guest List</h2>
            <table>
                <thead>
                    <tr>
                        <th>Guest Name</th>
                        <th>Room Number</th>
                        <th>Check-in Date</th>
                        <th>Check-out Date</th>
                        <th>Payment Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($guests as $guest): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($guest['guest_name']); ?></td>
                            <td><?php echo htmlspecialchars($guest['room_number']); ?></td>
                            <td><?php echo htmlspecialchars($guest['checkin_date']); ?></td>
                            <td><?php echo htmlspecialchars($guest['checkout_date']); ?></td>
                            <td><?php echo htmlspecialchars($guest['payment_status']); ?></td>
                            <td>
                                <a href="edit_guest.php?guest_id=<?php echo $guest['id']; ?>" class="button edit-btn">Edit</a>
                                <a href="view_guest.php?guest_id=<?php echo $guest['id']; ?>" class="button view-btn">View</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </section>
    </div>

    <!-- Footer -->
    <footer>
        <p>&copy; 2024 Antilla Apartments & Suites. All rights reserved.</p>
    </footer>
</body>
</html>
