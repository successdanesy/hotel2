// Function to fetch kitchen and bar orders
function fetchOrders(selectedDate) {
    // Use Fetch API to get data from get_orders.php with the selected date as a parameter
    fetch(`get_orders.php?selected_date=${selectedDate}`)
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        }) // Parse the response as JSON
        .then(data => {
            // Update the kitchen orders table
            const kitchenOrdersTable = document.getElementById('kitchen-orders');
            kitchenOrdersTable.innerHTML = '<tr><th>Room Number</th><th>Order Description</th><th>Total Amount (₦)</th><th>Status</th><th>Special Instructions</th></tr>'; // Clear previous data
            if (data.kitchen.length > 0) {
                data.kitchen.forEach(order => {
                    const row = kitchenOrdersTable.insertRow();
                    row.innerHTML = `<td>${order.room_number}</td><td>${order.order_description}</td><td>${parseFloat(order.total_amount).toFixed(2)}</td><td>${order.status}</td><td>${order.special_instructions}</td>`;
                });
            } else {
                const row = kitchenOrdersTable.insertRow();
                const cell = row.insertCell(0);
                cell.colSpan = 5;
                cell.textContent = 'No kitchen orders available.';
            }

            // Update the bar orders table
            const barOrdersTable = document.getElementById('bar-orders');
            barOrdersTable.innerHTML = '<tr><th>Room Number</th><th>Order Description</th><th>Total Amount (₦)</th><th>Status</th><th>Special Instructions</th></tr>'; // Clear previous data
            if (data.bar.length > 0) {
                data.bar.forEach(order => {
                    const row = barOrdersTable.insertRow();
                    row.innerHTML = `<td>${order.room_number}</td><td>${order.order_description}</td><td>${parseFloat(order.total_amount).toFixed(2)}</td><td>${order.status}</td><td>${order.special_instructions}</td>`;
                });
            } else {
                const row = barOrdersTable.insertRow();
                const cell = row.insertCell(0);
                cell.colSpan = 5;
                cell.textContent = 'No bar orders available.';
            }
        })
        .catch(error => {
            console.error('Error fetching orders:', error);
        });
}

// Initial setup
const defaultDate = new Date().toISOString().split('T')[0]; // Get today's date in YYYY-MM-DD format
let selectedDate = defaultDate; // Initialize with today's date

// Function to refresh orders at intervals
function refreshOrders() {
    fetchOrders(selectedDate);
}

// Call fetchOrders initially when the page loads
fetchOrders(selectedDate);

// Set an interval to refresh the orders every 10 seconds (10000 ms)
setInterval(refreshOrders, 10000);

// Update the fetchOrders call when the date is changed
document.getElementById('selected_date').addEventListener('change', function() {
    selectedDate = this.value; // Update the selectedDate variable
    fetchOrders(selectedDate);
});
