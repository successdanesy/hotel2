<?php
include('db_connect.php');
// Correctly including db_connect.php

if ($conn) {
    echo "Database connected successfully!";
} else {
    echo "Failed to connect to database.";
}



// Fetch kitchen orders from the database
$sql = "SELECT * FROM kitchen_orders";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    $orders = array();
    while($row = $result->fetch_assoc()) {
        $orders[] = $row;
    }
    echo json_encode($orders); // Output the orders as a JSON response
} else {
    echo json_encode([]);
}

$conn->close();
?>
