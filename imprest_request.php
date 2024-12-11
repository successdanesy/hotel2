<?php
session_start();
include('db_connect.php'); // Database connection

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        if ($_POST['action'] === 'add') {
            $itemName = $_POST['item_name'];
            $quantity = $_POST['quantity'];
            $status = 'Pending';

            // Insert request into imprest_requests table
            $query = "INSERT INTO imprest_requests (item_name, quantity, status) VALUES (?, ?, ?)";
            $stmt = $conn->prepare($query);
            $stmt->bind_param('sss', $itemName, $quantity, $status);

            if ($stmt->execute()) {
                echo json_encode(['success' => true, 'message' => 'Request added successfully!']);
            } else {
                echo json_encode(['success' => false, 'error' => 'Failed to add the request.']);
            }
            $stmt->close();
        } elseif ($_POST['action'] === 'complete') {
            $id = $_POST['id'];
            $price = $_POST['price'];

            // Update request status and price
            $query = "UPDATE imprest_requests SET status = 'Completed', price = ? WHERE id = ?";
            $stmt = $conn->prepare($query);
            $stmt->bind_param('di', $price, $id);

            if ($stmt->execute()) {
                echo json_encode(['success' => true, 'message' => 'Request completed successfully!']);
            } else {
                echo json_encode(['success' => false, 'error' => 'Failed to complete the request.']);
            }
            $stmt->close();
        }
    }
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Imprest Requests</title>
    <link rel="stylesheet" href="imprest_request.css">
</head>
<body>

<h2>Imprest Requests</h2>

<form id="imprestForm">
    <input type="hidden" name="action" value="add">
    <label for="item_name">Item Name:</label>
    <input type="text" id="item_name" name="item_name" required><br><br>

    <label for="quantity">Quantity:</label>
    <input type="text" id="quantity" name="quantity" required><br><br>

    <button type="submit">Submit Request</button>
</form>

<h3>Pending Requests</h3>
<table id="imprestTable">
    <thead>
        <tr>
            <th>ID</th>
            <th>Item Name</th>
            <th>Quantity</th>
            <th>Status</th>
            <th>Price</th>
            <th>Action</th>
        </tr>
    </thead>
    <tbody>
        <!-- Dynamic content will be loaded here -->
    </tbody>
</table>

<script src="imprest_request.js"></script>

</body>
</html>
