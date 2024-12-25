<?php
session_start();
include('db_connect.php'); // Database connection

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

// Combine data
$combinedData = array_merge($kitchenData, $barData, $otherData);

// Group data by date
$groupedData = [];
foreach ($combinedData as $row) {
    $date = $row['date'];
    if (!isset($groupedData[$date])) {
        $groupedData[$date] = [];
    }
    $groupedData[$date][] = $row;
}

// Calculate Grand Total
$grandTotal = 0;

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Expense Spreadsheet</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="income.css">
</head>
<body>
<div class="main-content">
    <header>
        <h1>Expense Spreadsheet</h1>
        <a href="export_expense.php?export=csv" class="button"><i class="fa-solid fa-table"></i></i>Export to CSV</a>
        <a href="manager.php" class="button new-guest">
            <i class="fa-solid fa-arrow-right-from-bracket"></i>Back
        </a>
    </header>

    <!-- Date Filter Form -->
    <form method="GET" action="expense.php" class="filter-form">
        <label for="start_date">Start Date:</label>
        <input type="date" id="start_date" name="start_date" value="<?php echo $startDate; ?>" required>
        <label for="end_date">End Date:</label>
        <input type="date" id="end_date" name="end_date" value="<?php echo $endDate; ?>" required>
        <button type="submit" class="button"><i class="fa-solid fa-filter"></i>Filter</button>
    </form>

    <table>
        <thead>
            <tr>
                <th>DATE OF REQUEST</th>
                <th>DEPARTMENT</th>
                <th>ITEMS PURCHASED</th>
                <th>QUANTITY</th>
                <th>PRICE (₦)</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($groupedData as $date => $rows): ?>
                <?php $isFirstRow = true; ?>
                <?php foreach ($rows as $row): ?>
                    <tr>
                        <td><?= $isFirstRow ? htmlspecialchars($row['date']) : '' ?></td>
                        <td><?= htmlspecialchars($row['type']) ?></td>
                        <td><?= htmlspecialchars($row['item_name']) ?></td>
                        <td><?= htmlspecialchars($row['quantity']) ?></td>
                        <td><?= number_format($row['price'], 2) ?></td>
                    </tr>
                    <?php
                    $grandTotal += $row['price'];
                    $isFirstRow = false;
                    ?>
                <?php endforeach; ?>
            <?php endforeach; ?>
        </tbody>
        <tfoot>
            <tr>
                <th colspan="4" style="text-align:right;">Grand Total</th>
                <th><?= number_format($grandTotal, 2) ?></th>
            </tr>
        </tfoot>
    </table>
</div>
</body>
</html>
