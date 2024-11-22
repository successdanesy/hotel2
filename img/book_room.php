<?php
include('db_connect.php');

// Check if necessary data is provided
if (isset($_POST['room_number'], $_POST['guest_name'], $_POST['checkin_date'], $_POST['checkout_date'])) {
    $room_number = $_POST['room_number'];
    $guest_name = $_POST['guest_name'];
    $checkin_date = $_POST['checkin_date'];
    $checkout_date = $_POST['checkout_date'];

    // Fetch room price based on room number and day of the week
    $sql = "SELECT * FROM rooms WHERE room_number = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $room_number);
    $stmt->execute();
    $result = $stmt->get_result();
    $room = $result->fetch_assoc();

    if ($room) {
        // Calculate the price based on the check-in and check-out dates
        $price_per_night = 0;

        // Calculate the days between checkin and checkout
        $checkin = new DateTime($checkin_date);
        $checkout = new DateTime($checkout_date);
        $interval = $checkin->diff($checkout);
        $days = $interval->days;

        // Determine if the stay includes a weekend (Friday-Sunday)
        $is_weekend = false;
        for ($i = 0; $i < $days; $i++) {
            $current_day = $checkin->format('l');
            if ($current_day == 'Friday' || $current_day == 'Saturday' || $current_day == 'Sunday') {
                $is_weekend = true;
                break;
            }
            $checkin->modify('+1 day');
        }

        // Price determination based on weekdays or weekend
        if ($is_weekend) {
            $price_per_night = $room['weekend_price'];
        } else {
            $price_per_night = $room['weekday_price'];
        }

        // Calculate total price
        $total_price = $price_per_night * $days;

        // Insert booking into the database
        $sql = "INSERT INTO bookings (room_number, guest_name, checkin_date, checkout_date, price, status) VALUES (?, ?, ?, ?, ?, 'booked')";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssssd", $room_number, $guest_name, $checkin_date, $checkout_date, $total_price);
        $stmt->execute();

        // Update room status to 'occupied'
        $sql = "UPDATE rooms SET status = 'occupied' WHERE room_number = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $room_number);
        $stmt->execute();

        header('Location: room.php?message=Booking successful for room ' . $room_number . '.');
        exit();
    } else {
        header('Location: room.php?error=Room not found.');
        exit();
    }
} else {
    header('Location: room.php?error=Missing data.');
    exit();
}
?>
