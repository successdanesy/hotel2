<?php
require_once 'db_connect.php'; // Include database connection
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manager Page - Imprest Requests</title>
    <link rel="stylesheet" href="imprest_request.css">
</head>
<body>

<header>
    <h1>Manager Page</h1>
    <div class="welcome">
        <i class="fas fa-user"></i>
        <span>Welcome, Manager</span>
    </div>
</header>

<main>
    <h2>Manage Imprest Requests</h2>

    <!-- Table for imprest_requests -->
    <section>
        <h3>Imprest Requests (Kitchen)</h3>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Request Description</th>
                    <th>Amount</th>
                    <th>Mark as Complete</th>
                </tr>
            </thead>
            <tbody>
                <?php
                // Fetch imprest_requests
                $sql = "SELECT * FROM imprest_requests WHERE status != 'Completed'";
                $result = mysqli_query($conn, $sql);

                while ($row = mysqli_fetch_assoc($result)) {
                    echo "<tr>";
                    echo "<td>{$row['id']}</td>";
                    echo "<td>{$row['item_name']}</td>";
                    echo "<td>";
                    echo "<form method='POST' action='update_price.php'>";
                    echo "<input type='hidden' name='id' value='{$row['id']}'>";
                    echo "<input type='text' name='price' value='{$row['amount']}' required>";
                    echo "</td>";
                    echo "<td>";
                    echo "<button type='submit' name='complete' value='{$row['id']}'>Mark as Complete</button>";
                    echo "</form>";
                    echo "</td>";
                    echo "</tr>";
                }
                ?>
            </tbody>
        </table>
    </section>

    <!-- Table for imprest_requests_bar -->
    <section>
        <h3>Imprest Requests (Bar)</h3>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Request Description</th>
                    <th>Amount</th>
                    <th>Mark as Complete</th>
                </tr>
            </thead>
            <tbody>
                <?php
                // Fetch imprest_requests_bar
                $sql = "SELECT * FROM imprest_requests_bar WHERE status != 'Completed'";
                $result = mysqli_query($conn, $sql);

                while ($row = mysqli_fetch_assoc($result)) {
                    echo "<tr>";
                    echo "<td>{$row['id']}</td>";
                    echo "<td>{$row['item_name']}</td>";
                    echo "<td>";
                    echo "<form method='POST' action='update_price.php'>";
                    echo "<input type='hidden' name='id' value='{$row['id']}'>";
                    echo "<input type='text' name='price' value='{$row['amount']}' required>";
                    echo "</td>";
                    echo "<td>";
                    echo "<button type='submit' name='complete' value='{$row['id']}'>Mark as Complete</button>";
                    echo "</form>";
                    echo "</td>";
                    echo "</tr>";
                }
                ?>
            </tbody>
        </table>
    </section>

</main>

</body>
</html>
