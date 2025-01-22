<?php
include('db_connect.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $guest_name = htmlspecialchars($_POST['guest_name']);
    $room_number = htmlspecialchars($_POST['room_number']);
    $price = floatval(str_replace('₦', '', $_POST['price'])); // Remove currency symbol and convert to float
    $payment_status = htmlspecialchars($_POST['payment_status']);
    $payment_method = htmlspecialchars($_POST['payment_method']);
    $checkin_date = htmlspecialchars($_POST['checkin_date']);
    $checkout_date = htmlspecialchars($_POST['checkout_date']);

    // Handle the discount
    $discount = 0; // Default discount is 0
    if (isset($_POST['discount']) && trim($_POST['discount']) !== "" && ctype_digit($_POST['discount'])) {
        $discount = intval($_POST['discount']);
    }

    // Calculate the discounted price
    $discounted_price = max(0, $price - $discount);

    // Calculate the total room charges
    $checkin_date_obj = new DateTime($checkin_date);
    $checkout_date_obj = new DateTime($checkout_date);
    $total_days = $checkin_date_obj->diff($checkout_date_obj)->days;
    $total_room_charges = $discounted_price * $total_days;

    // Start a transaction
    $conn->begin_transaction();

    try {
        // Insert the guest name into the guests table
        $insert_guest_query = "INSERT INTO guests (guest_name) VALUES (?)";
        $stmt1 = $conn->prepare($insert_guest_query);
        $stmt1->bind_param("s", $guest_name);
        $stmt1->execute();

        // Get the guest_id of the newly inserted guest
        $guest_id = $stmt1->insert_id;

        // Insert booking details into the bookings table, including total_room_charges
        $insert_booking_query = "INSERT INTO bookings (guest_name, room_number, price, payment_status, payment_method, checkin_date, checkout_date, guest_id, discount, total_room_charges) 
                                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt2 = $conn->prepare($insert_booking_query);
        $stmt2->bind_param("ssdssssisd", $guest_name, $room_number, $discounted_price, $payment_status, $payment_method, $checkin_date, $checkout_date, $guest_id, $discount, $total_room_charges);
        $stmt2->execute();

        // Store the guest_id in the session for future use (e.g., kitchen orders, bar orders)
        $_SESSION['guest_id'] = $guest_id;

        // Update the room status to 'Occupied', assign guest_id and guest_name to the room
        $update_room_query = "UPDATE rooms SET status = 'Occupied', guest_id = ?, guest_name = ? WHERE room_number = ?";
        $stmt3 = $conn->prepare($update_room_query);
        $stmt3->bind_param("isi", $guest_id, $guest_name, $room_number);
        $stmt3->execute();

        // Commit transaction
        $conn->commit();

        // Redirect to the room management page or another page as needed
        header('Location: room.php?message=Guest checked in successfully.');
        exit();
    } catch (Exception $e) {
        $conn->rollback();
        header('Location: room.php?error=Failed to complete check-in.');
        exit();
    }
} else {
    header('Location: room.php?error=Invalid request.');
    exit();
}
?>