document.getElementById('category').addEventListener('change', function () {
    const categoryId = this.value;
    const menuSelect = document.getElementById('menu_item');
    menuSelect.innerHTML = '<option value="">-- Select Menu Item --</option>';

    if (menuItemsByCategory[categoryId]) {
        menuItemsByCategory[categoryId].forEach(item => {
            const option = document.createElement('option');
            option.value = item.id;
            option.textContent = `${item.name} (₦${parseFloat(item.price).toFixed(2)})`;
            menuSelect.appendChild(option);
        });
    }
});

const orderTray = [];
const orderTrayTable = document.getElementById('orderTray').querySelector('tbody');

// Add item to the tray
document.getElementById('addToTray').addEventListener('click', () => {
    const roomNumber = document.getElementById('room_number').value;
    const menuItemId = document.getElementById('menu_item').value;
    const menuItemText = document.getElementById('menu_item').selectedOptions[0]?.textContent || '';
    const specialInstructions = document.getElementById('special_instructions').value;

    if (!roomNumber || !menuItemId) {
        alert('Please select a room and menu item.');
        return;
    }

    const price = parseFloat(menuItemText.match(/\(₦([\d.]+)\)/)?.[1] || 0);
    orderTray.push({ menuItemId, menuItemText, price, specialInstructions });

    // Update table
    const row = document.createElement('tr');
    row.innerHTML = `
        <td>${menuItemText}</td>
        <td>₦${price.toFixed(2)}</td>
        <td>${specialInstructions}</td>
        <td><button class="remove-item">Remove</button></td>
    `;
    orderTrayTable.appendChild(row);

    // Remove item event
    row.querySelector('.remove-item').addEventListener('click', () => {
        const index = Array.from(orderTrayTable.children).indexOf(row);
        orderTray.splice(index, 1); // Remove from array
        row.remove(); // Remove row
    });
});

// Submit orders
document.getElementById('submitOrders').addEventListener('click', () => {
    if (!orderTray.length) {
        alert('The order tray is empty.');
        return;
    }

    const roomNumber = document.getElementById('room_number').value;
    fetch('submit_orders.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ roomNumber, orders: orderTray }),
    })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Orders submitted successfully!');
                orderTray.length = 0; // Clear the tray
                orderTrayTable.innerHTML = ''; // Clear the table
            } else {
                alert('Failed to submit orders.');
            }
        })
        .catch(error => console.error('Error:', error));
});



// Handle order form submission via AJAX
$(document).on('submit', '#order-form', function(e) {
    e.preventDefault(); // Prevent default form submission

    // Gather all form data
    var room_number = $('#room_number').val();
    var order_description = $('#order_description').val();
    var total_amount = $('#total_amount').val();
    var special_instructions = $('#special_instructions').val();

    // Submit data via AJAX
    $.ajax({
        url: 'kitchen.php',
        type: 'POST',
        data: {
            submit_order: true,
            room_number: room_number,
            order_description: order_description,
            total_amount: total_amount,
            special_instructions: special_instructions
        },
        success: function(response) {
            // Clear the form fields
            $('#order-form')[0].reset();

            // Refresh the orders table
            fetchOrders();
        },
        error: function() {
            alert('Failed to submit the order. Please try again.');
        }
    });
});


function markAsComplete(orderId) {
    fetch('kitchen.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `mark_completed=1&order_id=${orderId}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'Completed') {
            // Update button and status text
            const button = document.getElementById(`mark-completed-btn-${orderId}`);
            button.disabled = true; // Disable the button
            button.textContent = 'Completed'; // Change button text to "Completed"

            // Optionally update status on the page, if you have a specific element for order status
            const statusElement = document.getElementById(`status-${orderId}`);
            if (statusElement) {
                statusElement.textContent = 'Completed';
            }
        } else {
            alert('Failed to mark as completed.');
        }
    })
    .catch(error => {
        console.error('Error updating order:', error);
        alert('Failed to update order status. Please try again.');
    });
}




// Fetch and update the orders table dynamically
function fetchOrders() {
    $.ajax({
        url: 'fetch_orders.php', // This script returns the table rows
        success: function(response) {
            $('#orders-table tbody').html(response);
        }
    });
}

// Fetch orders when the page loads
$(document).ready(function() {
    fetchOrders();
});


let orders = []; // Store the orders
let totalAmount = 0;

// Function to add an order to the list
function addOrder(item, price) {
    orders.push({ item, price });
    totalAmount += price;
    updateOrderSummary();
}

// Function to update the order summary display
function updateOrderSummary() {
    const orderList = document.getElementById('orderList');
    const totalAmountElem = document.getElementById('totalAmount');

    orderList.innerHTML = ''; // Clear current list
    orders.forEach(order => {
        const li = document.createElement('li');
        li.textContent = `${order.item} - ₦${order.price}`;
        orderList.appendChild(li);
    });

    totalAmountElem.textContent = totalAmount.toFixed(2);
}

// Function to clear all orders
function clearAllOrders() {
    orders = [];
    totalAmount = 0;
    updateOrderSummary();
}

document.getElementById('clearAllOrdersButton').addEventListener('click', clearAllOrders);


function confirmOrder() {
    const roomNumber = document.getElementById('roomNumber').value;
    const specialInstructions = document.getElementById('specialInstructions').value;

    const data = {
        roomNumber,
        orders,
        totalAmount,
        specialInstructions
    };

    // Send data to the server
    fetch('send_to_frontdesk.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(data)
    })
    .then(response => response.json())
    .then(responseData => {
        if (responseData.success) {
            alert('Order sent successfully!');
            clearAllOrders();
        } else {
            alert('Failed to send order. Please try again.');
        }
    })
    .catch(error => console.error('Error:', error));
}

document.getElementById('add-order-form').addEventListener('submit', function (e) {
    e.preventDefault(); // Prevent page reload on form submission
    
    let formData = new FormData(this); // Collect form data

    fetch('add_order.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Order added successfully!');
            // Optionally, update the table or clear the form
            location.reload(); // Reload the page to show the updated orders
        } else {
            alert('Failed to add order. Please try again.');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error occurred. Please try again.');
    });
});


