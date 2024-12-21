<?php
include('db_connect.php'); // Include database connection

// Get the selected date from the request, default to today's date
$selected_date = isset($_GET['selected_date']) ? $_GET['selected_date'] : date('Y-m-d');

// Fetching Kitchen Orders based on the selected date
$sql_kitchen = "SELECT * FROM kitchen_orders WHERE DATE(timestamp) = ?";
$stmt_kitchen = $conn->prepare($sql_kitchen);
$stmt_kitchen->bind_param("s", $selected_date);
$stmt_kitchen->execute();
$result_kitchen = $stmt_kitchen->get_result();

// Fetching Bar Orders based on the selected date
$sql_bar = "SELECT * FROM bar_orders WHERE DATE(timestamp) = ?";
$stmt_bar = $conn->prepare($sql_bar);
$stmt_bar->bind_param("s", $selected_date);
$stmt_bar->execute();
$result_bar = $stmt_bar->get_result();

// Prepare the response
$response = [
    'kitchen' => [],
    'bar' => []
];

while ($order = $result_kitchen->fetch_assoc()) {
    $response['kitchen'][] = $order;
}

while ($order = $result_bar->fetch_assoc()) {
    $response['bar'][] = $order;
}

// Debugging: Log the response before encoding it to JSON
error_log("Response Data: " . print_r($response, true));

// Return the JSON response
header('Content-Type: application/json');
echo json_encode($response);

$stmt_kitchen->close();
$stmt_bar->close();
$conn->close();
?>
