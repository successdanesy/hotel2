<?php
require_once 'db_connect.php'; // Include database connection
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manager Page - Imprest Requests</title>
    <link rel="stylesheet" href="manager.css">
</head>
<body>

<header>
    <h1>Manager Page</h1>
    <div class="welcome">
        <i class="fas fa-user"></i>
        <span>Welcome, Manager</span>
    </div>

    <a href="logout.php" class="button new-guest">
                <i class="fa-solid fa-arrow-right-from-bracket"></i> Logout
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

    <!-- Grid Container for Imprest Requests (Kitchen) and (Bar) -->
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
                        echo "<form method='POST' action='update_price.php'>";
                        echo "<input type='hidden' name='id' value='{$row['id']}'>";
                        echo "<input type='text' name='price' value='{$row['price']}' required>";
                        echo "</td>";
                        echo "<td>";
                        echo "<form method='POST' action='complete_request.php'>";
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

        <!-- Bar Requests -->
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
                        echo "<form method='POST' action='update_price.php'>";
                        echo "<input type='hidden' name='id' value='{$row['id']}'>";
                        echo "<input type='text' name='price' value='{$row['price']}' required>";
                        echo "</td>";
                        echo "<td>";
                        echo "<form method='POST' action='complete_request.php'>";
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
                ?>
            </tbody>
        </table>
        <form method="POST" action="export_csv.php">
            <button type="submit" name="export">Export Completed Requests as CSV</button>
        </form>
    </section>

</main>

</body>
</html>
