<?php
include('db_connect.php');
// Correctly including db_connect.php

// Fetching Kitchen Orders
$sql_kitchen = "SELECT * FROM kitchen_orders";
$result_kitchen = $conn->query($sql_kitchen);

// Fetching Bar Orders
$sql_bar = "SELECT * FROM bar_orders";
$result_bar = $conn->query($sql_bar);

// Creating an array to hold the orders
$orders = [
    'kitchen' => [],
    'bar' => []
];

// Kitchen orders
if ($result_kitchen->num_rows > 0) {
    while ($order = $result_kitchen->fetch_assoc()) {
        $orders['kitchen'][] = $order;
    }
}

// Bar orders
if ($result_bar->num_rows > 0) {
    while ($order = $result_bar->fetch_assoc()) {
        $orders['bar'][] = $order;
    }
}

// Returning the data as JSON
echo json_encode($orders);
?>