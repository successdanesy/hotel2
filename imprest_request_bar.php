<?php
session_start();
include('db_connect.php'); // Database connection

// Handle adding a new imprest request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_request'])) {
    $itemName = $_POST['item_name'] ?? '';
    $quantity = $_POST['quantity'] ?? '';
    $price = $_POST['price'] ?? null;

    if (!empty($itemName) && !empty($quantity)) {
        $query = "INSERT INTO imprest_requests_bar (item_name, quantity, price, status, timestamp)
                  VALUES (?, ?, ?, 'Pending', NOW())";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("ssd", $itemName, $quantity, $price);
        if ($stmt->execute()) {
            header("Location: imprest_request_bar.php");
            exit();
        } else {
            $error = "Error adding the imprest request: " . $stmt->error;
        }
        $stmt->close();
    } else {
        $error = "Item name and quantity are required.";
    }
}

// Get the selected date from the form submission or default to today's date
$selected_date = isset($_GET['selected_date']) ? $_GET['selected_date'] : date('Y-m-d');

// Verify that the date is properly formatted
if (DateTime::createFromFormat('Y-m-d', $selected_date) === false) {
    $selected_date = date('Y-m-d'); // Fallback to today's date if the format is incorrect
}

// Fetch imprest requests based on the selected date
function fetchRequests($conn, $selected_date) {
    $query = "SELECT id, item_name, quantity, price, status, timestamp FROM imprest_requests_bar 
              WHERE DATE(timestamp) = ? ORDER BY timestamp DESC";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $selected_date);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_all(MYSQLI_ASSOC);
}

$requests = fetchRequests($conn, $selected_date);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bar Imprest Requests</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="imprest_request.css">
</head>
<body>
<div class="main-content">
    <header>
    <h1>Bar Imprest Requests</h1>
        <a href="bar.php" class="button"><i class="fa-solid fa-right-from-bracket"></i>Back to Bar</a>
        <a href="logout.php" class="button"><i class="fa-solid fa-right-from-bracket"></i>Logout</a>
    </header>

    <form method="POST" action="imprest_request_bar.php">
        <label for="item_name">Item Name:</label>
        <input type="text" name="item_name" id="item_name" required>

        <label for="quantity">Quantity:</label>
        <input type="text" name="quantity" id="quantity" required>

        <!-- <label for="price">Price (₦):</label>
        <input type="number" name="price" id="price" step="0.01"> -->

        <button type="submit" name="submit_request">Submit Request To Manager</button>
    </form>

<!-- Date Filter Form -->
<form method="GET" action="imprest_request_bar.php" class="filter-form">
        <label for="selected_date">Select Date:</label>
        <input type="date" id="selected_date" name="selected_date" value="<?php echo $selected_date; ?>" required>
        <button type="submit" class="button"><i class="fa-solid fa-filter"></i>Filter by Date</button>
    </form>

    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Item Name</th>
                <th>Quantity</th>
                <th>Price (₦)</th>
                <th>Status</th>
                <th>Timestamp</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($requests as $request): ?>
                <tr>
                    <td><?= $request['id'] ?></td>
                    <td><?= htmlspecialchars($request['item_name']) ?></td>
                    <td><?= htmlspecialchars($request['quantity']) ?></td>
                    <td><?= number_format($request['price'], 2) ?></td>
                    <td><?= htmlspecialchars($request['status']) ?></td>
                    <td><?= htmlspecialchars($request['timestamp']) ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
<script src="imprest_request_bar.js"></script>
</body>
</html>
