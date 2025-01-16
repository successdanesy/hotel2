<?php
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
include('db_connect.php'); // Database connection

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
    // Initialize missing keys to 0
    if (!isset($data['front_desk'])) {
        $data['front_desk'] = 0;
    }
    if (!isset($data['restaurant'])) {
        $data['restaurant'] = 0;
    }
    if (!isset($data['bar'])) {
        $data['bar'] = 0;
    }

    // Calculate the total sum
    $data['total_sum'] = $data['front_desk'] + $data['restaurant'] + $data['bar'];
}
unset($data); // Break the reference with the last element

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Income Spreadsheet</title>
    <link rel="stylesheet" href="income.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-adapter-date-fns"></script>
</head>
<body>
<div class="main-content">
    <header>
        <h1>Income Spreadsheet</h1>
        <a href="export_income.php?export=csv" class="button"><i class="fa-solid fa-table"></i></i>Export to CSV</a>
        <a href="manager.php" class="button new-guest">
        <i class="fa-solid fa-right-from-bracket"></i>Back
        </a>
    </header>

    <!-- Date Filter Form -->
    <form method="GET" action="income.php" class="filter-form">
        <label for="start_date">Start Date:</label>
        <input type="date" id="start_date" name="start_date" value="<?php echo $startDate; ?>" required>
        <label for="end_date">End Date:</label>
        <input type="date" id="end_date" name="end_date" value="<?php echo $endDate; ?>" required>
        <button type="submit" class="button"><i class="fa-solid fa-filter"></i>Filter</button>
    </form>

    <table>
        <thead>
            <tr>
                <th>DATE</th>
                <th>FRONT-DESK</th>
                <th>RESTAURANT</th>
                <th>BAR</th>
                <th>MINI MART</th>
                <th>HALL RENTALS</th>
                <th>LAUNDRY</th>
                <th>TOTAL SUM</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($combinedData as $row): ?>
                <tr>
                    <td><?= htmlspecialchars($row['date']) ?></td>
                    <td><?= number_format($row['front_desk'], 2) ?></td>
                    <td><?= number_format($row['restaurant'], 2) ?></td>
                    <td><?= number_format($row['bar'], 2) ?></td>
                    <td></td> <!-- Blank Mini Mart column -->
                    <td></td> <!-- Blank Hall Rentals column -->
                    <td></td> <!-- Blank Laundry column -->
                    <td><?= number_format($row['total_sum'], 2) ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <!-- Charts and Graphs -->
    <canvas id="incomeChart"></canvas>
    <script>
        const ctx = document.getElementById('incomeChart').getContext('2d');
        const data = {
            labels: <?= json_encode(array_column($combinedData, 'date')) ?>,
            datasets: [
                {
                    label: 'Front-Desk',
                    data: <?= json_encode(array_column($combinedData, 'front_desk')) ?>,
                    borderColor: 'red',
                    fill: false
                },
                {
                    label: 'Restaurant',
                    data: <?= json_encode(array_column($combinedData, 'restaurant')) ?>,
                    borderColor: 'green',
                    fill: false
                },
                {
                    label: 'Bar',
                    data: <?= json_encode(array_column($combinedData, 'bar')) ?>,
                    borderColor: 'blue',
                    fill: false
                },
                {
                    label: 'Total Sum',
                    data: <?= json_encode(array_column($combinedData, 'total_sum')) ?>,
                    borderColor: 'black',
                    fill: false
                }
            ]
        };
        const config = {
            type: 'line',
            data: data,
            options: {
                scales: {
                    x: {
                        type: 'time',
                        time: {
                            unit: 'day',
                            tooltipFormat: 'MMM d', // Format for tooltips
                        },
                        title: {
                            display: true,
                            text: 'Date'
                        }
                    },
                    y: {
                        title: {
                            display: true,
                            text: 'Amount (₦)'
                        }
                    }
                },
                plugins: {
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                let label = context.dataset.label || '';
                                if (label) {
                                    label += ': ';
                                }
                                label += new Intl.NumberFormat('en-NG', { style: 'currency', currency: 'NGN' }).format(context.parsed.y);
                                return label;
                            }
                        }
                    }
                }
            }
        };
        const incomeChart = new Chart(ctx, config);
    </script>
</div>
</body>
</html>
