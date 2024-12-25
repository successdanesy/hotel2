<?php
session_start();
include('db_connect.php'); // Database connection

header('Content-Type: text/csv');
header('Content-Disposition: attachment;filename=Antilla_Monthly_income_data.csv');

// Date range filter
$startDate = isset($_GET['start_date']) ? $_GET['start_date'] : date('Y-m-01');
$endDate = isset($_GET['end_date']) ? $_GET['end_date'] : date('Y-m-t');

// Fetch data for Front Desk with COALESCE
$queryFrontDesk = "SELECT DATE(checkin_date) as date, SUM(COALESCE(total_room_charges, 0)) as total FROM bookings WHERE DATE(checkin_date) BETWEEN ? AND ? GROUP BY DATE(checkin_date)";
$stmt = $conn->prepare($queryFrontDesk);
$stmt->bind_param("ss", $startDate, $endDate);
$stmt->execute();
$frontDeskData = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Fetch data for Restaurant
$queryRestaurant = "SELECT DATE(timestamp) as date, SUM(total_amount) as total FROM kitchen_orders WHERE DATE(timestamp) BETWEEN ? AND ? GROUP BY DATE(timestamp)";
$stmt = $conn->prepare($queryRestaurant);
$stmt->bind_param("ss", $startDate, $endDate);
$stmt->execute();
$restaurantData = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Fetch data for Bar
$queryBar = "SELECT DATE(timestamp) as date, SUM(total_amount) as total FROM bar_orders WHERE DATE(timestamp) BETWEEN ? AND ? GROUP BY DATE(timestamp)";
$stmt = $conn->prepare($queryBar);
$stmt->bind_param("ss", $startDate, $endDate);
$stmt->execute();
$barData = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Combine data
$combinedData = [];
foreach ($frontDeskData as $fd) {
    $date = $fd['date'];
    $combinedData[$date]['date'] = $date;
    $combinedData[$date]['front_desk'] = $fd['total'];
    $combinedData[$date]['restaurant'] = 0;
    $combinedData[$date]['bar'] = 0;
    $combinedData[$date]['mini_mart'] = 0; // Blank column
    $combinedData[$date]['hall_rentals'] = 0; // Blank column
    $combinedData[$date]['laundry'] = 0; // Blank column
}
foreach ($restaurantData as $res) {
    $date = $res['date'];
    if (!isset($combinedData[$date])) {
        $combinedData[$date]['date'] = $date;
        $combinedData[$date]['front_desk'] = 0;
        $combinedData[$date]['mini_mart'] = 0;
        $combinedData[$date]['hall_rentals'] = 0;
        $combinedData[$date]['laundry'] = 0;
    }
    $combinedData[$date]['restaurant'] = $res['total'];
}
foreach ($barData as $bar) {
    $date = $bar['date'];
    if (!isset($combinedData[$date])) {
        $combinedData[$date]['date'] = $date;
        $combinedData[$date]['front_desk'] = 0;
        $combinedData[$date]['restaurant'] = 0;
        $combinedData[$date]['mini_mart'] = 0;
        $combinedData[$date]['hall_rentals'] = 0;
        $combinedData[$date]['laundry'] = 0;
    }
    $combinedData[$date]['bar'] = $bar['total'];
}

// Calculate TOTAL SUM for each date
foreach ($combinedData as &$data) {
    $data['total_sum'] = $data['front_desk'] + $data['restaurant'] + $data['bar'];
}

$output = fopen('php://output', 'w');
fputcsv($output, ['DATE', 'FRONT-DESK', 'RESTAURANT', 'BAR', 'MINI MART', 'HALL RENTALS', 'LAUNDRY', 'TOTAL SUM']);

foreach ($combinedData as $row) {
    fputcsv($output, [
        $row['date'],
        number_format($row['front_desk'], 2),
        number_format($row['restaurant'], 2),
        number_format($row['bar'], 2),
        '', // Blank Mini Mart column
        '', // Blank Hall Rentals column
        '', // Blank Laundry column
        number_format($row['total_sum'], 2)
    ]);
}

fclose($output);
exit();
?>
