<?php
include('db_connect.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $room_number = $_POST['room_number'];
    $room_type = $_POST['room_type'];
    $price = $_POST['price'];

    // Check if the room already exists
    $check_sql = "SELECT * FROM rooms WHERE room_number = ?";
    $stmt = $conn->prepare($check_sql);
    $stmt->bind_param("s", $room_number);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        echo json_encode(["status" => "error", "message" => "Room already exists"]);
    } else {
        // Insert the new room
        $sql = "INSERT INTO rooms (room_number, room_type, price) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssd", $room_number, $room_type, $price);

        if ($stmt->execute()) {
            echo json_encode(["status" => "success", "message" => "Room added successfully"]);
        } else {
            echo json_encode(["status" => "error", "message" => "Error adding room"]);
        }
    }
}
?>
