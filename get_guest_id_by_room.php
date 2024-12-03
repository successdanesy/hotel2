<?php
// get_guest_id_by_room.php

// Get room_number from the query string
if (isset($_GET['room_number'])) {
    $room_number = $_GET['room_number'];

    // Your database connection code
    include('db_connect.php'); // Replace with your actual database connection file

    // Prepare SQL query to fetch guest_id from rooms table
    // Assuming checkout_date can be NULL for active bookings (not checked out yet)
    $query = "SELECT guest_id FROM rooms WHERE room_number = ? AND status = 'Occupied' LIMIT 1";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $room_number);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // Fetch the guest_id if the room is occupied
        $row = $result->fetch_assoc();
        echo json_encode(["success" => true, "guest_id" => $row['guest_id']]);
    } else {
        // No guest found for that room
        echo json_encode(["success" => false, "error" => "No guest found for this room"]);
    }

    $stmt->close();
    $conn->close();
} else {
    echo json_encode(["success" => false, "error" => "Room number is required"]);
}
?>
