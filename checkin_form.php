<?php
session_start();
include('db_connect.php');

// Check if the user is logged in
if (!isset($_SESSION['username'])) {
    $_SESSION['msg'] = "You must log in first";
    header('location: login.php');
    exit();
}

// Get the room number from the URL or redirect back if missing
if (!isset($_GET['room_number'])) {
    header('Location: room.php?error=Room number is missing.');
    exit();
}

$room_number = htmlspecialchars($_GET['room_number']);

// Fetch the room details
$query = "SELECT room_type, weekday_price, weekend_price FROM rooms WHERE room_number = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $room_number);
$stmt->execute();
$result = $stmt->get_result();
$room = $result->fetch_assoc();

if (!$room) {
    header('Location: room.php?error=Invalid room number.');
    exit();
}

// Determine price based on the current day
$weekday_price = $room['weekday_price'];
$weekend_price = $room['weekend_price'];
$price = (date('N') >= 5) ? $weekend_price : $weekday_price;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Check-in Form</title>
    <link rel="stylesheet" href="room.css">
</head>
<body>
    <div class="main-content">
        <header>
            <h1>Check-in Form</h1>
            <p>Fill in the guest details to complete the check-in process.</p>
        </header>

        <section class="checkin-form">
            <form method="POST" action="checkin.php">
                <label for="guest_name">Guest Name:</label>
                <input type="text" id="guest_name" name="guest_name" placeholder="Enter Guest Name" required>

                <label for="room_number">Room Number:</label>
                <input type="text" id="room_number" name="room_number" value="<?php echo $room_number; ?>" readonly>

                <label for="checkin_date">Check-in Date:</label>
                <input type="date" id="checkin_date" name="checkin_date" required onchange="updatePrice()">

                <label for="checkout_date">Check-out Date:</label>
                <input type="date" id="checkout_date" name="checkout_date" required>

                <label for="price">Price (per night):</label>
                <input type="text" id="price" name="price" placeholder="Set Check-in Date First" readonly>

                <input type="hidden" id="weekday_price" value="<?php echo $weekday_price; ?>">
                <input type="hidden" id="weekend_price" value="<?php echo $weekend_price; ?>">

                <label for="discount">Discount Amount (₦):</label>
                <input type="text" id="discount" name="discount" placeholder="Enter discount" oninput="validateDiscountInput(event)">

                <label for="payment_status">Payment Status:</label>
                <select id="payment_status" name="payment_status" required>
                    <option value="Pay Now">Pay Now</option>
                    <option value="Pay at Checkout">Pay at Checkout</option>
                </select>

                <label for="payment_method">Payment Method:</label>
                <select id="payment_method" name="payment_method" required>
                    <option value="Cash">Cash</option>
                    <option value="POS">POS</option>
                    <!-- <option value="Transfer">Transfer</option> -->
                </select>

                <button type="submit" class="button checkin-btn">Complete Check-in</button>
            </form>
        </section>

        <section class="back-to-room-management">
            <form action="room.php" method="GET">
                <button type="submit" class="button back-button">Back to Room Management</button>
            </form>
        </section>
    </div>

    <script>
    // Function to validate discount input
    function validateDiscountInput(event) {
        const input = event.target;
        const value = input.value;

        // Only allow numbers and check if it's not empty
        if (!/^\d*$/.test(value)) {
            input.value = ''; // Clear the input
            alert('Please enter a valid number for discount.');
        }
    }

    // Function to update price based on check-in date 
    function updatePrice() {
    const checkinDate = document.getElementById('checkin_date').value;
    const weekdayPrice = parseFloat(document.getElementById('weekday_price').value);
    const weekendPrice = parseFloat(document.getElementById('weekend_price').value);
    const priceField = document.getElementById('price');

    if (checkinDate) {
        const date = new Date(checkinDate);
        const dayOfWeek = date.getUTCDay(); // Get the day of the week (0 = Sunday, 6 = Saturday)

        // Check if the day is Friday (5) or Saturday (6) or Sunday (0)
        const price = (dayOfWeek === 0 || dayOfWeek === 5 || dayOfWeek === 6) ? weekendPrice : weekdayPrice;
        priceField.value = '₦' + price.toFixed(2); // Update the price field
    }
}
 
            // Initial call to update the price field based on current date updatePrice();
</script>

</body>
</html>
