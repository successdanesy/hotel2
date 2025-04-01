// Function to fetch kitchen and bar orders
function fetchOrders(selectedDate) {
    fetch(`get_orders.php?selected_date=${selectedDate}`)
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(data => {
            // ✅ Update the Kitchen Orders Table
            const kitchenOrdersTable = document.getElementById('kitchen-orders');
            kitchenOrdersTable.innerHTML = `
                <tr>
                    <th>Room Number</th>
                    <th>Order Description</th>
                    <th>Quantity</th> <!-- ✅ Quantity Column -->
                    <th>Total Amount (₦)</th>
                    <th>Status</th>
                    <th>Special Instructions</th>
                </tr>
            `; // Clear previous data

            if (data.kitchen.length > 0) {
                data.kitchen.forEach(order => {
                    const row = kitchenOrdersTable.insertRow();
                    row.innerHTML = `
                        <td>${order.room_number}</td>
                        <td>${order.order_description}</td>
                        <td>${order.quantity || 'N/A'}</td> <!-- ✅ Show Quantity -->
                        <td>${parseFloat(order.total_amount).toFixed(2)}</td>
                        <td>${order.status}</td>
                        <td>${order.special_instructions}</td>
                    `;
                });
            } else {
                const row = kitchenOrdersTable.insertRow();
                const cell = row.insertCell(0);
                cell.colSpan = 6;
                cell.textContent = 'No kitchen orders available.';
            }

            // ✅ Update the Bar Orders Table
            const barOrdersTable = document.getElementById('bar-orders');
            barOrdersTable.innerHTML = `
                <tr>
                    <th>Room Number</th>
                    <th>Order Description</th>
                    <th>Quantity</th> <!-- ✅ Added Quantity Column -->
                    <th>Total Amount (₦)</th>
                    <th>Status</th>
                    <th>Special Instructions</th>
                </tr>
            `; // Clear previous data

            if (data.bar.length > 0) {
                data.bar.forEach(order => {
                    const row = barOrdersTable.insertRow();
                    row.innerHTML = `
    <td>${order.room_number}</td>
    <td>${order.order_description}</td>
    <td>${order.quantity || 'N/A'}</td> <!-- ✅ Quantity Fix -->
    <td>${parseFloat(order.total_amount).toFixed(2)}</td>
    <td>${order.status}</td>
    <td>${order.special_instructions}</td>
`;

                });
            } else {
                const row = barOrdersTable.insertRow();
                const cell = row.insertCell(0);
                cell.colSpan = 6;
                cell.textContent = 'No bar orders available.';
            }
        })
        .catch(error => {
            console.error('Error fetching orders:', error);
        });
}

// ✅ Initialize Orders with Today's Date
const defaultDate = new Date().toISOString().split('T')[0]; // Get today's date in YYYY-MM-DD format
let selectedDate = defaultDate; 

// ✅ Refresh Orders Periodically
function refreshOrders() {
    fetchOrders(selectedDate);
}

// ✅ Load Orders When Page Loads
fetchOrders(selectedDate);

// ✅ Auto Refresh Orders Every 10 Seconds
setInterval(refreshOrders, 10000);

// ✅ Fetch Orders When Date is Changed
document.getElementById('selected_date').addEventListener('change', function() {
    selectedDate = this.value;
    fetchOrders(selectedDate);
});
