// Function to fetch kitchen and bar orders
function fetchOrders() {
    // Use Fetch API to get data from get_orders.php
    fetch('get_orders.php')
        .then(response => response.json()) // Parse the response as JSON
        .then(data => {
            // Update the kitchen orders table
            const kitchenOrdersTable = document.getElementById('kitchen-orders');
            kitchenOrdersTable.innerHTML = '<tr><th><i class="fas fa-utensils"></i> Room Orders</th></tr>'; // Clear previous data
            if (data.kitchen.length > 0) {
                data.kitchen.forEach(order => {
                    const row = kitchenOrdersTable.insertRow();
                    const cell = row.insertCell(0);
                    cell.textContent = `Room ${order.room_number} - ${order.order_description} (Status: ${order.status})`;
                });
            } else {
                const row = kitchenOrdersTable.insertRow();
                const cell = row.insertCell(0);
                cell.textContent = 'No kitchen orders available.';
            }

            // Update the bar orders table
            const barOrdersTable = document.getElementById('bar-orders');
            barOrdersTable.innerHTML = '<tr><th><i class="fas fa-glass-cheers"></i> Room Orders</th></tr>'; // Clear previous data
            if (data.bar.length > 0) {
                data.bar.forEach(order => {
                    const row = barOrdersTable.insertRow();
                    const cell = row.insertCell(0);
                    cell.textContent = `Room ${order.room_number} - ${order.order_description} (Status: ${order.status})`;
                });
            } else {
                const row = barOrdersTable.insertRow();
                const cell = row.insertCell(0);
                cell.textContent = 'No bar orders available.';
            }
        })
        .catch(error => {
            console.error('Error fetching orders:', error);
        });



}

// Call fetchOrders initially when the page loads
fetchOrders();

// Set an interval to refresh the orders every 10 seconds (10000 ms)
setInterval(fetchOrders, 10000);

