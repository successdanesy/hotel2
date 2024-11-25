<?php
session_start();
include('db_connect.php'); // Database connection

// Handle form submission for adding a new order
if (isset($_POST['submit_order'])) {
    $room_number = $_POST['room_number'];
    $order_description = $_POST['order_description'];

    // Insert new order into kitchen_orders table with 'pending' status
    $query = "INSERT INTO kitchen_orders (room_number, order_description, status) VALUES (?, ?, 'pending')";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ss", $room_number, $order_description);
    $stmt->execute();
    $stmt->close();

    // Redirect after form submission to avoid re-posting on refresh
    header('Location: kitchen.php');
    exit();
}

// Handle marking order as completed
if (isset($_POST['mark_completed'])) {
    $order_id = $_POST['order_id'];

    // Update the order status to "sent to front desk"
    $query = "UPDATE kitchen_orders SET status = 'sent to front desk' WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $order_id);
    $stmt->execute();
    $stmt->close();

    // Return success response
    echo json_encode(['status' => 'sent to front desk']);
    exit();
}



// Fetch all orders from the kitchen_orders table
function fetchOrders() {
    global $conn;
    $query = "SELECT * FROM kitchen_orders ORDER BY timestamp DESC";
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
    <style>
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
        }
        th {
            background-color: #f2f2f2;
        }
        .button {
            padding: 8px 12px;
            background-color: #4CAF50;
            color: white;
            border: none;
            cursor: pointer;
        }
        .button:hover {
            background-color: #45a049;
        }
        .sent-to-front-desk {
            color: green;
        }
        .completed {
            color: green;
            font-weight: bold;
        }

    </style>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script>
        // Handle order form submission via AJAX
        $(document).on('submit', '#order-form', function(e) {
            e.preventDefault(); // Prevent the default form submission

            var room_number = $('#room_number').val();
            var order_description = $('#order_description').val();

            $.ajax({
                url: 'kitchen.php',
                type: 'POST',
                data: {
                    submit_order: true,
                    room_number: room_number,
                    order_description: order_description
                },
                success: function(response) {
                    // Clear the form fields
                    $('#room_number').val('');
                    $('#order_description').val('');

                    // Update the orders table dynamically
                    fetchOrders();
                }
            });
        });

        // Handle marking an order as completed via AJAX
        function markAsComplete(orderId) {
            $.ajax({
                url: 'kitchen.php',
                type: 'POST',
                data: {
                    mark_completed: true,
                    order_id: orderId
                },
                success: function(response) {
                    // Parse the JSON response from the server
                    var data = JSON.parse(response);

                    if (data.status === 'sent to front desk') {
                        // Update the order status in the table dynamically
                        $('#order-status-' + orderId).text('Sent to Front Desk');
                        $('#order-status-' + orderId).addClass('sent-to-front-desk');  // You can also add a custom CSS class if needed
                    }
                }
            });
        }

        // Fetch and update the orders table dynamically
        function fetchOrders() {
            $.ajax({
                url: 'fetch_orders.php', // This script returns the table rows
                success: function(response) {
                    $('#orders-table tbody').html(response);
                }
            });
        }

        // Fetch orders when the page loads
        $(document).ready(function() {
            fetchOrders();
        });
    </script>
</head>
<body>
    <h1>Kitchen Orders</h1>

    <!-- Form for adding a new order -->
    <form id="order-form">
        <div>
            <label for="room_number">Room Number:</label>
            <input type="text" name="room_number" id="room_number" required>
        </div>
        <div>
            <label for="order_description">Order Description:</label>
            <textarea name="order_description" id="order_description" required></textarea>
        </div>
        <button type="submit" class="button">Add Order</button>
    </form>

    <hr>

    <!-- Orders Table -->
    <table id="orders-table">
        <thead>
            <tr>
                <th>Order ID</th>
                <th>Room Number</th>
                <th>Order Description</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <!-- Orders will be dynamically loaded here -->
        </tbody>
    </table>
</body>

<script>// Handle marking an order as completed via AJAX
// Handle marking an order as completed via AJAX
function markAsComplete(orderId) {
    $.ajax({
        url: 'mark_order.php',  // Use the mark_order.php for updating the status
        type: 'POST',
        data: {
            mark_completed: true,
            order_id: orderId
        },
        success: function(response) {
            // Parse the JSON response from the server
            var data = JSON.parse(response);

            if (data.status === 'Completed') {
                // Update the order status in the table dynamically
                $('#order-status-' + orderId).text('Completed');
                $('#order-status-' + orderId).addClass('completed');  // Optional: add a custom class for styling
            } else {
                alert('Failed to update order status: ' + data.message);  // Handle any errors
            }
        },
        error: function() {
            alert('There was an error processing your request.');
        }
    });
}

</script>
</html>
