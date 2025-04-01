<?php
session_start();
include('db_connect.php'); // Include database connection

// Check if the user is logged in and has the correct role
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'kitchen') {
    header('Location: login.php');
    exit();
}

// Check if the form is submitted via AJAX
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['mark_completed'])) {
    $order_id = $_POST['order_id'];

    // Update the order status to 'Completed'
    $update_query = "UPDATE kitchen_orders SET status = 'Completed' WHERE id = ?";
    $stmt = $conn->prepare($update_query);
    $stmt->bind_param("i", $order_id);
    $stmt->execute();

     // Update the order status to 'Completed'
     $update_query = "UPDATE bar_orders SET status = 'Completed' WHERE id = ?";
     $stmt = $conn->prepare($update_query);
     $stmt->bind_param("i", $order_id);
     $stmt->execute();

    // Check if the update was successful
    if ($stmt->affected_rows > 0) {
        echo json_encode(['status' => 'Completed']);
    } else {
        echo json_encode(['status' => 'Error', 'message' => 'Failed to update order status']);
    }

    $stmt->close();
    exit();
}
?>
