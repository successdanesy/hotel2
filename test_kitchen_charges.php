<?php
// Include the database connection
include('db_connect.php');

// Enable error reporting for debugging
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Test Input: Specify a guest_id for testing
$test_guest_id = 35; // Replace this with a valid guest_id from your database

// Define the query for kitchen charges
$query_kitchen = "SELECT COALESCE(SUM(total_amount), 0) AS kitchen_charges
                  FROM kitchen_orders
                  WHERE guest_id = ? 
                    AND status = 'completed'";
$stmt_kitchen = $conn->prepare($query_kitchen);
$stmt_kitchen->bind_param("i", $guest_id);

if ($stmt_kitchen->execute()) {
    $result_kitchen = $stmt_kitchen->get_result();
    $kitchen_data = $result_kitchen->fetch_assoc();
    $kitchen_charges = $kitchen_data['kitchen_charges'];
} else {
    die("Error fetching kitchen charges: " . $stmt_kitchen->error);
}


// Bind the test guest_id to the query
$stmt_kitchen->bind_param("i", $test_guest_id);

// Execute the query
if ($stmt_kitchen->execute()) {
    $result_kitchen = $stmt_kitchen->get_result();
    if ($result_kitchen) {
        $kitchen_data = $result_kitchen->fetch_assoc();
        $kitchen_charges = $kitchen_data['kitchen_charges'];

        // Display the result
        echo "<h1>Test Results for Guest ID: $test_guest_id</h1>";
        echo "<p><strong>Kitchen Charges:</strong> ₦" . number_format($kitchen_charges, 2) . "</p>";
    } else {
        echo "Error fetching result: " . $stmt_kitchen->error;
    }
} else {
    echo "Error executing query: " . $stmt_kitchen->error;
}

// Close the statement and connection
$stmt_kitchen->close();
$conn->close();
?>
