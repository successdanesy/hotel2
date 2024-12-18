// Fetch and display rooms
document.addEventListener("DOMContentLoaded", () => {
    fetchRooms();

    // Check if the form exists before adding the event listener
    const form = document.getElementById("add-room-form");
    if (form) {
        form.addEventListener("submit", addRoom);
    }
});

// Fetch rooms from the server
function fetchRooms() {
    fetch("fetch_rooms.php")
        .then(response => response.json())
        .then(data => {
            const tableBody = document.getElementById("room-table-body");
            if (tableBody) {
                tableBody.innerHTML = "";

                data.forEach(room => {
                    const row = document.createElement("tr");
                    row.innerHTML = `
                        <td>${room.room_number}</td>
                        <td>${room.room_type}</td>
                        <td>${room.status}</td>
                        <td>${room.price}</td>
                        <td>
                            <button class="check-out" data-id="${room.id}">Check Out</button>
                        </td>
                    `;
                    tableBody.appendChild(row);

                    // Add event listener for dynamically created button
                    const checkoutButton = row.querySelector('.check-out');
                    if (checkoutButton) {
                        checkoutButton.addEventListener('click', handleCheckOut);
                    }
                });
            }
        })
        .catch(error => console.error("Error fetching rooms:", error));
}

// Handle check-out button click
function handleCheckOut(event) {
    const roomId = event.target.dataset.id;
    fetch(`checkout.php?room_id=${roomId}`, { method: 'POST' })
        .then(response => response.json())
        .then(data => {
            alert(data.message);
            if (data.status === "success") {
                fetchRooms(); // Refresh room list
            }
        })
        .catch(error => console.error("Error checking out room:", error));
}

// Add a new room
function addRoom(event) {
    event.preventDefault();

    const formData = new FormData(event.target);

    fetch("add_room.php", {
        method: "POST",
        body: formData,
    })
        .then(response => response.json())
        .then(data => {
            alert(data.message);
            if (data.status === "success") {
                fetchRooms(); // Refresh room list
            }
        })
        .catch(error => console.error("Error adding room:", error));
}
