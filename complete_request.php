<?php
require_once 'db_connect.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['complete']) && isset($_POST['price']) && isset($_POST['table'])) {
        $id = $_POST['complete'];
        $price = $_POST['price'];
        $table = $_POST['table']; // Determine which table to update

        // Remove commas from the price
        $price_clean = str_replace(',', '', $price);

        // Ensure the price is a numeric value
        if (is_numeric($price_clean)) {
            // Update status to Completed and insert price for the selected table
            $sql = "UPDATE $table SET status='Completed', price='$price_clean' WHERE id='$id'";
            $result = mysqli_query($conn, $sql);

            if ($result) {
                header('Location: manager_imprest.php?message=completed');
                exit();
            } else {
                echo "Error updating the record: " . mysqli_error($conn);
            }
        } else {
            echo "Invalid price input.";
        }
    }
}
?>
