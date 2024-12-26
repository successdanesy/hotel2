// Function to fetch Guest ID by Room Number
function fetchGuestId() {
    const roomNumber = document.getElementById('room_number').value;

    if (!roomNumber) {
        alert("Please select a room number.");
        return;
    }

    fetch(`get_guest_id_by_room.php?room_number=${roomNumber}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                document.getElementById('guest_id').value = data.guest_id;
                document.getElementById('status').innerText = 'Guest ID found: ' + data.guest_id;
            } else {
                document.getElementById('guest_id').value = '';
                document.getElementById('status').innerText = 'Error: ' + data.error;
            }
        })
        .catch(error => {
            console.error('Error fetching guest ID:', error);
            document.getElementById('status').innerText = 'An error occurred.';
        });
}

// Update menu items based on category selection
document.getElementById('category').addEventListener('change', function () {
    const categoryId = this.value;
    updateMenuItems(categoryId);
});

function updateMenuItems(categoryId) {
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
}

// Order Tray Logic
const orderTray = [];
const orderTrayTable = document.getElementById('orderTray').querySelector('tbody');

// Add item to the tray
document.getElementById('addToTray').addEventListener('click', () => {
    const guestType = document.getElementById('guest_type').value;
    const roomNumber = document.getElementById('room_number').value;
    const menuItemId = document.getElementById('menu_item').value;
    const menuItemText = document.getElementById('menu_item').selectedOptions[0]?.textContent || '';
    const specialInstructions = document.getElementById('special_instructions').value;

    if (guestType === 'guest' && (!roomNumber || !menuItemId)) {
        alert('Please select a room and menu item.');
        return;
    }

    if (guestType === 'non_guest' && !menuItemId) {
        alert('Please select a menu item.');
        return;
    }

    const price = parseFloat(menuItemText.match(/\(₦([\d.]+)\)/)?.[1] || 0);
    addItemToTray(menuItemId, menuItemText, price, specialInstructions);
});

function addItemToTray(menuItemId, menuItemText, price, specialInstructions) {
    orderTray.push({ menuItemId, menuItemText, price, specialInstructions });
    const row = document.createElement('tr');
    row.innerHTML = `
        <td>${menuItemText}</td>
        <td>₦${price.toFixed(2)}</td>
        <td>${specialInstructions}</td>
        <td><button class="remove-item">Remove</button></td>
    `;
    orderTrayTable.appendChild(row);

    row.querySelector('.remove-item').addEventListener('click', () => {
        orderTray.splice(Array.from(orderTrayTable.children).indexOf(row), 1);
        row.remove();
    });
}

// Submit Orders
document.getElementById('submitOrders').addEventListener('click', () => {
    if (!orderTray.length) {
        alert('The order tray is empty.');
        return;
    }

    const guestType = document.getElementById('guest_type').value;
    const guestId = document.getElementById('guest_id').value;
    const roomNumber = document.getElementById('room_number').value;

    if (guestType === 'guest' && (!guestId || !roomNumber)) {
        alert('Please ensure both guest ID and room number are selected.');
        return;
    }

    const orderData = {
        guestType,
        roomNumber: guestType === 'guest' ? roomNumber : null,
        guestId: guestType === 'guest' ? guestId : null,
        orders: orderTray,
        specialInstructions: document.getElementById('special_instructions').value
    };

    sendOrderToServer(orderData);
});

function sendOrderToServer(orderData) {
    fetch('submit_orders_bar.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(orderData)
    })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Order submitted successfully!');
                orderTray.length = 0;
                orderTrayTable.innerHTML = '';
            } else {
                alert('Error: ' + data.error);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred. Please try again.');
        });
}

// Mark Order as Completed
function markAsComplete(orderId) {
    fetch('bar.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `mark_completed=1&order_id=${orderId}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'Completed') {
            const button = document.getElementById(`mark-completed-btn-${orderId}`);
            button.disabled = true;
            button.textContent = 'Completed';

            const statusElement = document.getElementById(`status-${orderId}`);
            if (statusElement) statusElement.textContent = 'Completed';
        } else {
            alert('Failed to mark as completed.');
        }
    })
    .catch(error => {
        console.error('Error updating order:', error);
        alert('Failed to update order status. Please try again.');
    });
}

// Fetch Orders
function fetchOrders() {
    $.ajax({
        url: 'fetch_orders_bar.php',
        success: function (response) {
            $('#orders-table tbody').html(response);
        }
    });
}

// Order Summary Logic
let orders = [];
let totalAmount = 0;

function addOrderToSummary(item, price) {
    orders.push({ item, price });
    totalAmount += price;
    updateOrderSummary();
}

function updateOrderSummary() {
    const orderList = document.getElementById('orderList');
    const totalAmountElem = document.getElementById('totalAmount');

    orderList.innerHTML = '';
    orders.forEach(order => {
        const li = document.createElement('li');
        li.textContent = `${order.item} - ₦${order.price}`;
        orderList.appendChild(li);
    });

    totalAmountElem.textContent = totalAmount.toFixed(2);
}

// Clear All Orders
function clearAllOrders() {
    orders = [];
    totalAmount = 0;
    updateOrderSummary();
}

// Confirm Order and Send to Front Desk
function confirmOrder() {
    const roomNumber = document.getElementById('roomNumber').value;
    const specialInstructions = document.getElementById('specialInstructions').value;

    const data = { roomNumber, orders, totalAmount, specialInstructions };

    fetch('send_to_frontdesk.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
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

// Dynamically Load Content
function loadDynamicContent() {
    const clearButton = document.getElementById('clearAllOrdersButton');
    if (clearButton) {
        clearButton.addEventListener('click', clearAllOrders);
    }

    const addOrderForm = document.getElementById('add-order-form');
    if (addOrderForm) {
        addOrderForm.addEventListener('submit', function (e) {
            e.preventDefault();
            const formData = new FormData(this);

            fetch('add_order_bar.php', {
                method: 'POST',
                body: formData
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Order added successfully!');
                        location.reload();
                    } else {
                        alert('Failed to add order. Please try again.');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error occurred. Please try again.');
                });
        });
    }
}

// Call this function after the content is dynamically added
loadDynamicContent();
