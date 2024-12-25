<?php
session_start();
include('db_connect.php'); // Database connection

header('Content-Type: text/csv');
header('Content-Disposition: attachment;filename=Antilla_Monthly_expense_data.csv');

// Date range filter
$startDate = isset($_GET['start_date']) ? $_GET['start_date'] : date('Y-m-01');
$endDate = isset($_GET['end_date']) ? $_GET['end_date'] : date('Y-m-t');

// Fetch data for Kitchen Requests
$queryKitchen = "SELECT DATE(timestamp) as date, item_name, quantity, price, 'Kitchen' as type FROM imprest_requests WHERE DATE(timestamp) BETWEEN ? AND ? ORDER BY DATE(timestamp)";
$stmt = $conn->prepare($queryKitchen);
$stmt->bind_param("ss", $startDate, $endDate);
$stmt->execute();
$kitchenData = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Fetch data for Bar Requests
$queryBar = "SELECT DATE(timestamp) as date, item_name, quantity, price, 'Bar' as type FROM imprest_requests_bar WHERE DATE(timestamp) BETWEEN ? AND ? ORDER BY DATE(timestamp)";
$stmt = $conn->prepare($queryBar);
$stmt->bind_param("ss", $startDate, $endDate);
$stmt->execute();
$barData = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Fetch data for Other Requests
$queryOther = "SELECT DATE(timestamp) as date, item_name, quantity, price, 'Other' as type FROM other_imprests WHERE DATE(timestamp) BETWEEN ? AND ? ORDER BY DATE(timestamp)";
$stmt = $conn->prepare($queryOther);
$stmt->bind_param("ss", $startDate, $endDate);
$stmt->execute();
$otherData = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$output = fopen('php://output', 'w');
fputcsv($output, ['DATE OF REQUEST', 'DEPARTMENT', 'ITEMS PURCHASED', 'QUANTITY', 'PRICE']);

foreach ($kitchenData as $row) {
    fputcsv($output, [$row['date'], 'Kitchen', $row['item_name'], $row['quantity'], number_format($row['price'], 2)]);
}

foreach ($barData as $row) {
    fputcsv($output, [$row['date'], 'Bar', $row['item_name'], $row['quantity'], number_format($row['price'], 2)]);
}

foreach ($otherData as $row) {
    fputcsv($output, [$row['date'], 'Other', $row['item_name'], $row['quantity'], number_format($row['price'], 2)]);
}

fclose($output);
exit();
?>
