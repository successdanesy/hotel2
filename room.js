// Fetch and display rooms
document.addEventListener("DOMContentLoaded", () => {
    fetchRooms();

    const form = document.getElementById("add-room-form");
    form.addEventListener("submit", addRoom);
});

// Fetch rooms from the server
function fetchRooms() {
    fetch("fetch_rooms.php")
        .then(response => response.json())
        .then(data => {
            const tableBody = document.getElementById("room-table-body");
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
                        <button class="extend-stay" data-id="${room.id}">Extend Stay</button>
                    </td>
                `;
                tableBody.appendChild(row);
            });
        })
        .catch(error => console.error("Error fetching rooms:", error));
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
