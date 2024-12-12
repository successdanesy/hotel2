<?php
session_start();
include('db_connect.php'); // Database connection

// Handle adding a new imprest request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_request'])) {
    $itemName = $_POST['item_name'] ?? '';
    $quantity = $_POST['quantity'] ?? '';
    $price = $_POST['price'] ?? null;

    if (!empty($itemName) && !empty($quantity)) {
        $query = "INSERT INTO imprest_requests_bar (item_name, quantity, price, status, timestamp)
                  VALUES (?, ?, ?, 'Pending', NOW())";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("ssd", $itemName, $quantity, $price);
        if ($stmt->execute()) {
            header("Location: imprest_request_bar.php");
            exit();
        } else {
            $error = "Error adding the imprest request: " . $stmt->error;
        }
        $stmt->close();
    } else {
        $error = "Item name and quantity are required.";
    }
}

// Fetch all imprest requests
function fetchRequests($conn) {
    $query = "SELECT id, item_name, quantity, price, status, timestamp FROM imprest_requests_bar ORDER BY timestamp DESC";
    return $conn->query($query)->fetch_all(MYSQLI_ASSOC);
}

$requests = fetchRequests($conn);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bar Imprest Requests</title>
    <link rel="stylesheet" href="imprest_request.css">
</head>
<body>
<div class="main-content">
    <header>
    <h1>Bar Imprest Requests</h1>
        <a href="bar.php" class="button">Back to Bar</a>

        <a href="logout.php" class="button">Logout</a>
    </header>

    <form method="POST" action="imprest_request_bar.php">
        <label for="item_name">Item Name:</label>
        <input type="text" name="item_name" id="item_name" required>

        <label for="quantity">Quantity:</label>
        <input type="text" name="quantity" id="quantity" required>

        <label for="price">Price (₦):</label>
        <input type="number" name="price" id="price" step="0.01">

        <button type="submit" name="submit_request">Submit Request</button>
    </form>

    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Item Name</th>
                <th>Quantity</th>
                <th>Price (₦)</th>
                <th>Status</th>
                <th>Timestamp</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($requests as $request): ?>
                <tr>
                    <td><?= $request['id'] ?></td>
                    <td><?= htmlspecialchars($request['item_name']) ?></td>
                    <td><?= htmlspecialchars($request['quantity']) ?></td>
                    <td><?= number_format($request['price'], 2) ?></td>
                    <td><?= htmlspecialchars($request['status']) ?></td>
                    <td><?= htmlspecialchars($request['timestamp']) ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
<script src="imprest_request_bar.js"></script>
</body>
</html>
