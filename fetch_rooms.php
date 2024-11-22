<?php
include('db_connect.php');

// Get the current day of the week
$current_day = date('l'); // 'l' returns the full textual representation of the day (e.g., Monday, Tuesday, etc.)

// Function to determine if today is a weekend day (Friday, Saturday, or Sunday)
function isWeekend($day) {
    return in_array($day, ['Friday', 'Saturday', 'Sunday']);
}

// Function to get the room price based on the current day
function getRoomPrice($room_type) {
    global $current_day;
    // Define the prices for the different room types
    $prices = [
        'Standard' => ['weekday' => 45000, 'weekend' => 37500],
        'Executive' => ['weekday' => 55000, 'weekend' => 47500],
        'Luxury' => ['weekday' => 75000, 'weekend' => 67500]
    ];

    // Check if it's a weekend or a weekday
    if (isWeekend($current_day)) {
        return $prices[$room_type]['weekend'];
    } else {
        return $prices[$room_type]['weekday'];
    }
}

$sql = "SELECT * FROM rooms";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    $rooms = [];
    while ($row = $result->fetch_assoc()) {
        // Get the price for the room based on its type
        $room_type = $row['type']; // Assuming 'type' field in the database stores room type
        $room_price = getRoomPrice($room_type);
        
        // Add the price to the room data
        $row['price'] = $room_price;

        // Add the room to the array
        $rooms[] = $row;
    }
    echo json_encode($rooms);
} else {
    echo json_encode([]);
}
?>
