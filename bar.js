// FUNCTIONS TO UPDATE MENU ITEM PRICE 
document.addEventListener("DOMContentLoaded", function () {
    const priceDisplay = document.getElementById("current_price");
    const menuSelect = document.getElementById("menu_item");

    // ✅ When a menu item is selected, reset price to ₦0
    menuSelect.addEventListener("change", function () {
        if (menuSelect.value) { 
            priceDisplay.textContent = "₦0"; // Start from zero
        } else {
            priceDisplay.textContent = "₦0"; // Reset if no item is selected
        }
    });


    // ✅ Handle price increment and decrement
    document.querySelectorAll(".adjust-price").forEach(button => {
        button.addEventListener("click", function () {
            let changeAmount = parseInt(this.dataset.amount);
            let currentPrice = parseFloat(priceDisplay.textContent.replace("₦", "").replace(",", "")) || 0;

            let newPrice = Math.max(0, currentPrice + changeAmount);
            priceDisplay.textContent = `₦${newPrice.toLocaleString()}`;
        });
    });
});
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
            option.textContent = item.name; // ✅ Only show item name, no price
            menuSelect.appendChild(option);
        });
    }
}

// Order Tray Logic
const orderTray = [];
const orderTrayTable = document.getElementById("orderTray").querySelector("tbody");

document.getElementById("addToTray").addEventListener("click", () => {
    let menuItemId = document.getElementById("menu_item").value;
    let menuItemText = document.getElementById("menu_item").selectedOptions[0]?.textContent || "";
    let specialInstructions = document.getElementById("special_instructions").value;
    let displayedPrice = parseFloat(document.getElementById("current_price").textContent.replace("₦", "").replace(",", "")) || 0;

    if (!menuItemId) {
        alert("Please select a menu item.");
        return;
    }

    addItemToTray(menuItemId, menuItemText, displayedPrice, specialInstructions);
});

function addItemToTray(menuItemId, menuItemText, price, specialInstructions) {
    // ✅ Check if the item is already in the tray
    let existingItem = orderTray.find(item => item.menuItemId === menuItemId);

    if (existingItem) {
        // ✅ If the item exists, increase the quantity
        existingItem.quantity += 1;
        existingItem.totalPrice += price;

        // ✅ Update the quantity and price in the UI
        let existingRow = document.getElementById(`row-${menuItemId}`);
        existingRow.querySelector(".item-quantity").textContent = existingItem.quantity;
        existingRow.querySelector(".item-price").textContent = `₦${existingItem.totalPrice.toLocaleString()}`;
    } else {
        // ✅ If the item is new, add it to the tray with quantity 1
        let newItem = { menuItemId, menuItemText, price, totalPrice: price, specialInstructions, quantity: 1 };
        orderTray.push(newItem);

        const row = document.createElement("tr");
        row.id = `row-${menuItemId}`;
        row.innerHTML = `
            <td>${menuItemText}</td>
            <td class="item-quantity">1</td>
            <td class="item-price">₦${price.toLocaleString()}</td>
            <td>${specialInstructions}</td>
            <td><button class="remove-item">Remove</button></td>
        `;
        orderTrayTable.appendChild(row);

        // ✅ Remove item from tray when clicking the remove button
        row.querySelector(".remove-item").addEventListener("click", () => {
            let index = orderTray.findIndex(item => item.menuItemId === menuItemId);
            if (index !== -1) {
                orderTray.splice(index, 1);
            }
            row.remove();
        });
    }

    
    // ✅ Remove item from tray
    row.querySelector(".remove-item").addEventListener("click", () => {
        orderTray.splice(orderTray.indexOf(menuItemId), 1);
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

function loadDynamicContent() {
    // Your dynamic content loading logic here
    // After content is loaded, attach the event listener:

    const clearButton = document.getElementById('clearAllOrdersButton');
    if (clearButton) {
        clearButton.addEventListener('click', clearAllOrders);
    }
}
// Call this function after the content is dynamically added
loadDynamicContent();

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
