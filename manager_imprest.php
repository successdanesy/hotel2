<?php
require_once 'db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_other_request'])) {
    $itemName = $_POST['item_name'] ?? '';
    $quantity = $_POST['quantity'] ?? '';
    $price = $_POST['price'] ?? null;

    if (!empty($itemName) && !empty($quantity)) {
        $query = "INSERT INTO other_imprests (item_name, quantity, price, status, timestamp)
                  VALUES (?, ?, ?, 'Pending', NOW())";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("ssd", $itemName, $quantity, $price);
        if ($stmt->execute()) {
            header("Location: manager_imprest.php");
            exit();
        } else {
            $error = "Error adding the other imprest request: " . $stmt->error;
        }
        $stmt->close();
    } else {
        $error = "Item name and quantity are required.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manager Page - Imprest Requests</title>
    <link rel="stylesheet" href="manager_imprest.css">
</head>
<body>

<header>
    <h1>Manager Page</h1>
    <div class="welcome">
        <i class="fas fa-user"></i>
        <span>Welcome, Manager</span>
    </div>

    <a href="manager.php" class="button new-guest">
        <i class="fa-solid fa-arrow-right-from-bracket"></i>Home Page
    </a>
</header>

<main>
    <h2>Manage Imprest Requests</h2>

    <?php
    if (isset($_GET['message'])) {
        if ($_GET['message'] == 'deleted') {
            echo "<p class='success'>Request deleted successfully!</p>";
        }
    }
    ?>

    <!-- Date Filter -->
    <form method="POST" action="">
        <label for="date">Select Date:</label>
        <input type="date" id="date" name="date">
        <button type="submit">Filter</button>
    </form>

    <!-- Grid Container for Imprest Requests (Kitchen), (Bar), and Other Imprests -->
    <div class="grid-container">
        <!-- Kitchen Requests -->
        <section>
    <h3>Imprest Requests (Kitchen)</h3>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Request Description</th>
                <th>Price</th>
                <th>Mark as Complete</th>
                <th>Delete</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $date_filter = isset($_POST['date']) ? $_POST['date'] : date('Y-m-d');
            $sql = "SELECT * FROM imprest_requests WHERE status != 'Completed' AND DATE(timestamp) = '$date_filter'";
            $result = mysqli_query($conn, $sql);

            while ($row = mysqli_fetch_assoc($result)) {
                echo "<tr>";
                echo "<td>{$row['id']}</td>";
                echo "<td>{$row['item_name']}</td>";
                echo "<td>";
                echo "<form method='POST' action='complete_request.php'>";
                echo "<input type='hidden' name='table' value='imprest_requests'>"; // Specify table name
                echo "<input type='text' name='price' value='{$row['price']}' required>";
                echo "</td>";
                echo "<td>";
                echo "<button type='submit' name='complete' value='{$row['id']}'>Mark as Complete</button>";
                echo "</form>";
                echo "</td>";
                echo "<td>";
                echo "<form method='POST' action='delete_request.php'>";
                echo "<input type='hidden' name='id' value='{$row['id']}'>";
                echo "<button type='submit' name='delete'>Delete</button>";
                echo "</form>";
                echo "</td>";
                echo "</tr>";
            }
            ?>
        </tbody>
    </table>
</section>

<section>
    <h3>Imprest Requests (Bar)</h3>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Request Description</th>
                <th>Price</th>
                <th>Mark as Complete</th>
                <th>Delete</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $sql = "SELECT * FROM imprest_requests_bar WHERE status != 'Completed' AND DATE(timestamp) = '$date_filter'";
            $result = mysqli_query($conn, $sql);

            while ($row = mysqli_fetch_assoc($result)) {
                echo "<tr>";
                echo "<td>{$row['id']}</td>";
                echo "<td>{$row['item_name']}</td>";
                echo "<td>";
                echo "<form method='POST' action='complete_request.php'>";
                echo "<input type='hidden' name='table' value='imprest_requests_bar'>"; // Specify table name
                echo "<input type='text' name='price' value='{$row['price']}' required>";
                echo "</td>";
                echo "<td>";
                echo "<button type='submit' name='complete' value='{$row['id']}'>Mark as Complete</button>";
                echo "</form>";
                echo "</td>";
                echo "<td>";
                echo "<form method='POST' action='delete_request.php'>";
                echo "<input type='hidden' name='id' value='{$row['id']}'>";
                echo "<button type='submit' name='delete'>Delete</button>";
                echo "</form>";
                echo "</td>";
                echo "</tr>";
            }
            ?>
        </tbody>
    </table>
</section>

<section>
    <h3>Other Imprest Requests</h3>
    <form method="POST" action="manager_imprest.php">
        <label for="item_name">Item Name:</label>
        <input type="text" name="item_name" id="item_name" required>

        <label for="quantity">Quantity:</label>
        <input type="text" name="quantity" id="quantity" required>

        <label for="price">Price (₦):</label>
        <input type="number" name="price" id="price" step="0.01" required>

        <button type="submit" name="submit_other_request">Submit Request</button>
    </form>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Request Description</th>
                <th>Quantity</th>
                <th>Price</th>
                <th>Mark as Complete</th>
                <th>Delete</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $sql = "SELECT * FROM other_imprests WHERE status != 'Completed' AND DATE(timestamp) = '$date_filter'";
            $result = mysqli_query($conn, $sql);

            while ($row = mysqli_fetch_assoc($result)) {
                echo "<tr>";
                echo "<td>{$row['id']}</td>";
                echo "<td>{$row['item_name']}</td>";
                echo "<td>{$row['quantity']}</td>";
                echo "<td>";
                echo "<form method='POST' action='complete_request.php'>";
                echo "<input type='hidden' name='table' value='other_imprests'>"; // Specify table name
                echo "<input type='text' name='price' value='{$row['price']}' required>";
                echo "</td>";
                echo "<td>";
                echo "<button type='submit' name='complete' value='{$row['id']}'>Mark as Complete</button>";
                echo "</form>";
                echo "</td>";
                echo "<td>";
                echo "<form method='POST' action='delete_request.php'>";
                echo "<input type='hidden' name='id' value='{$row['id']}'>";
                echo "<button type='submit' name='delete'>Delete</button>";
                echo "</form>";
                echo "</td>";
                echo "</tr>";
            }
            ?>
        </tbody>
    </table>
</section>

    </div>

    <!-- Completed Imprest Requests Section -->
    <section class="completed-section">
        <h3>Completed Imprest Requests</h3>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Request Description</th>
                    <th>Price</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $sql = "SELECT * FROM imprest_requests WHERE status = 'Completed' AND DATE(timestamp) = '$date_filter'";
                $result = mysqli_query($conn, $sql);

                while ($row = mysqli_fetch_assoc($result)) {
                    echo "<tr>";
                    echo "<td>{$row['id']}</td>";
                    echo "<td>{$row['item_name']}</td>";
                    echo "<td>{$row['price']}</td>";
                    echo "</tr>";
                }

                $sql2 = "SELECT * FROM imprest_requests_bar WHERE status = 'Completed' AND DATE(timestamp) = '$date_filter'";
                $result2 = mysqli_query($conn, $sql2);

                while ($row2 = mysqli_fetch_assoc($result2)) {
                    echo "<tr>";
                    echo "<td>{$row2['id']}</td>";
                    echo "<td>{$row2['item_name']}</td>";
                    echo "<td>{$row2['price']}</td>";
                    echo "</tr>";
                }

                $sql3 = "SELECT * FROM other_imprests WHERE status = 'Completed' AND DATE(timestamp) = '$date_filter'";
                $result3 = mysqli_query($conn, $sql3);

                while ($row3 = mysqli_fetch_assoc($result3)) {
                    echo "<tr>";
                    echo "<td>{$row3['id']}</td>";
                    echo "<td>{$row3['item_name']}</td>";
                    echo "<td>{$row3['price']}</td>";
                    echo "</tr>";
                }
                ?>
            </tbody>
        </table>
        <!-- <form method="POST" action="export_csv.php">
            <button type="submit" name="export">Export Completed Requests as CSV</button>
        </form> -->
    </section>

</main>

<!-- Footer Section  -->
<footer>
    <p>&copy; 2024 Antilla Apartments & Suites. All rights reserved.</p>
</footer>

</body>
</html>
