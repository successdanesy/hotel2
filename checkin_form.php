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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="main-content">
    <header>
            <h1><i class="fas fa-user-check icon"></i>Check-in Form</h1>
            <p>Fill in the guest details to complete the check-in process.</p>
            <a href="room.php" class="button new-guest">
                <i class="fas fa-arrow-left icon"></i>Go Back
            </a>
        </header>

        <section class="checkin-form">
            <form method="POST" action="checkin.php">
                <!-- Guest Details Section -->
                <div class="section">
                    <h2><i class="fas fa-user icon"></i>Guest Information</h2>
                    <div class="form-group">
                        <label for="guest_name">Guest Name:</label>
                        <input type="text" id="guest_name" name="guest_name" placeholder="Enter Guest Name" required>
                    </div>
                </div>

                 <!-- Room Details Section -->
                 <div class="section">
                    <h2><i class="fas fa-bed icon"></i>Room Information</h2>
                    <div class="form-group">
                        <label for="room_number">Room Number:</label>
                        <input type="text" id="room_number" name="room_number" value="<?php echo $room_number; ?>" readonly>
                    </div>
                    <div class="form-group">
                        <label for="checkin_date">Check-in Date:</label>
                        <input type="date" id="checkin_date" name="checkin_date" required onchange="updatePrice()">
                    </div>
                    <div class="form-group">
                        <label for="checkout_date">Check-out Date:</label>
                        <input type="date" id="checkout_date" name="checkout_date" required onchange="updatePrice()">
                    </div>
                    <div class="form-group">
                        <label for="price">Price (per night):</label>
                        <input type="text" id="price" name="price" placeholder="Set Check-in Date First" readonly>
                    </div>
                    <div class="form-group">
                        <label for="discount">Discount Amount (₦):</label>
                        <input type="text" id="discount" name="discount" placeholder="Enter discount" oninput="validateDiscountInput(event)">
                    </div>
                </div>

                <input type="hidden" id="weekday_price" value="<?php echo $weekday_price; ?>">
                <input type="hidden" id="weekend_price" value="<?php echo $weekend_price; ?>">


                <!-- Payment Details Section -->
                <div class="section">
                    <h2><i class="fas fa-money-bill-wave icon"></i>Payment Information</h2>
                    <div class="form-group">
                        <label for="payment_status">Payment Status:</label>
                        <select id="payment_status" name="payment_status" required>
                            <option value="Pay Now">Pay Now</option>
                            <option value="Pay at Checkout">Pay at Checkout</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="payment_method">Payment Method:</label>
                        <select id="payment_method" name="payment_method" required>
                            <option value="Cash">Cash</option>
                            <option value="POS">POS</option>
                        </select>
                    </div>
                </div>

                <!-- Submit Button -->
                <div class="form-group">
                    <button type="submit" class="button checkin-btn"><i class="fas fa-check icon"></i>Complete Check-in</button>
                </div>
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
