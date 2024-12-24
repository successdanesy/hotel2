<?php
include 'db_connect.php';

header('Content-Type: application/json');

try {
    $data = json_decode(file_get_contents('php://input'), true);

    if (empty($data) || !is_array($data)) {
        throw new Exception('Invalid request data.');
    }

    $sql = "INSERT INTO other_imprests (item_name, quantity, price) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);

    foreach ($data as $item) {
        if (empty($item['itemName']) || empty($item['quantity']) || !is_numeric($item['price'])) {
            throw new Exception('Invalid item data. All fields are required.');
        }

        $stmt->bind_param('ssd', $item['itemName'], $item['quantity'], $item['price']);
        if (!$stmt->execute()) {
            throw new Exception('Failed to insert item: ' . $stmt->error);
        }
    }

    echo json_encode(['success' => true]);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}

$conn->close();
?>
