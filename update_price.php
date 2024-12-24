<?php
require_once 'db_connect.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['complete']) && isset($_POST['price'])) {
        $id = $_POST['complete'];
        $price = $_POST['price']; // Direct input as string

        // Remove commas from the price
        $price_clean = str_replace(',', '', $price);

        // Ensure the price is a numeric value
        if (is_numeric($price_clean)) {
            // Update status to Completed and insert price for each table
            $sql1 = "UPDATE imprest_requests SET status='Completed', price='$price_clean' WHERE id='$id'";
            $result1 = mysqli_query($conn, $sql1);

            $sql2 = "UPDATE imprest_requests_bar SET status='Completed', price='$price_clean' WHERE id='$id'";
            $result2 = mysqli_query($conn, $sql2);

            $sql3 = "UPDATE other_imprests SET status='Completed', price='$price_clean' WHERE id='$id'";
            $result3 = mysqli_query($conn, $sql3);

            if ($result1 || $result2 || $result3) {
                header('Location: manager_imprest.php');
                exit();
            } else {
                echo "Error updating the records.";
            }
        } else {
            echo "Invalid price input.";
        }
    }
}
?>
