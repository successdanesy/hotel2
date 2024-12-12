<?php
// Include database connection file
include 'db_connect.php';

header('Content-Type: application/json');

try {
    // Retrieve JSON data from the request
    $data = json_decode(file_get_contents('php://input'), true);

    // Validate the data
    if (empty($data) || !is_array($data)) {
        throw new Exception('Invalid request data.');
    }

    // Prepare SQL statement for inserting data
    $sql = "INSERT INTO imprest_requests (item_name, quantity, price) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);

    // Iterate through each item and insert it into the database
    foreach ($data as $item) {
        // Validate each item's fields
        if (empty($item['itemName']) || empty($item['quantity']) || !is_numeric($item['price'])) {
            throw new Exception('Invalid item data. All fields are required.');
        }

        // Execute the prepared statement
        $stmt->bind_param('ssd', $item['itemName'], $item['quantity'], $item['price']);
        if (!$stmt->execute()) {
            throw new Exception('Failed to insert item: ' . $stmt->error);
        }
    }

    // If everything is successful
    echo json_encode(['success' => true]);

} catch (Exception $e) {
    // Handle errors and send error message back to client
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}

// Close database connection
$conn->close();
?>
