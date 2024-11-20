// Fetch kitchen and bar orders dynamically
// Fetch data from get_orders.php
function fetchOrders() {
    fetch('get_orders.php')
    .then(response => response.json())
    .then(data => {
        // Bar orders section
        let barOrdersContainer = document.querySelector('.bar-order table');
        barOrdersContainer.innerHTML = '';
        data.bar_orders.forEach(order => {
            let row = `<tr>
                          <td>Room ${order.room_number} - ${order.order_description} (${order.status})</td>
                       </tr>`;
            barOrdersContainer.innerHTML += row;
        });

        // Kitchen orders section
        let kitchenOrdersContainer = document.querySelector('.kitchen-order table');
        kitchenOrdersContainer.innerHTML = '';
        data.kitchen_orders.forEach(order => {
            let row = `<tr>
                          <td>Room ${order.room_number} - ${order.order_description} (${order.status})</td>
                       </tr>`;
            kitchenOrdersContainer.innerHTML += row;
        });
    })
    .catch(error => {
        console.error('Error fetching orders:', error);
    });
}

// Fetch orders when the page loads
window.onload = fetchOrders;

// Optionally, you can refresh the orders every 10 seconds to simulate real-time updates
setInterval(fetchOrders, 10000); // Fetch orders every 10 seconds
