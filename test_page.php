<?php
// test_page.php
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test Guest ID by Room Number</title>
    <script>
    function fetchGuestId() {
        const roomNumber = document.getElementById('room_number').value;

        // Check if room number is entered
        if (roomNumber === "") {
            alert("Please enter a room number");
            return;
        }

        // Perform fetch request to get the guest ID by room number
        fetch(`get_guest_id_by_room.php?room_number=${roomNumber}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    document.getElementById('guest_id').value = data.guest_id;
                    document.getElementById('status').innerHTML = 'Guest ID found: ' + data.guest_id;
                } else {
                    document.getElementById('status').innerHTML = 'Error: ' + data.error;
                }
            })
            .catch(error => {
                console.error('Error fetching guest ID:', error);
                document.getElementById('status').innerHTML = 'An error occurred.';
            });
    }
</script>
</head>
<body>
    <h1>Test Guest ID by Room Number</h1>

    <label for="room_number">Room Number:</label>
    <input type="text" id="room_number" name="room_number" placeholder="Enter Room Number" required>

    <button onclick="fetchGuestId()">Get Guest ID</button>

    <br><br>

    <label for="guest_id">Guest ID:</label>
    <input type="text" id="guest_id" name="guest_id" readonly>

    <p id="status"></p>
</body>
</html>