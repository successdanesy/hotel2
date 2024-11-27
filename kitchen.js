





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


// Handle marking an order as completed via AJAX
function markAsComplete(orderId) {
    $.ajax({
        url: 'kitchen.php',  // Ensure this points to your script handling the request
        type: 'POST',
        data: {
            mark_completed: true,
            order_id: orderId
        },
        success: function(response) {
            try {
                var data = JSON.parse(response); // Parse the JSON response
                if (data.status === 'Completed') {
                    // Dynamically update the order status
                    $('#order-status-' + orderId).text('Completed'); 
                    $('#order-status-' + orderId).addClass('completed'); // Optional: Add a styling class
                    $(`#order-status-${orderId}`).prop('disabled', true);

                } else {
                    alert('Failed to update status: ' + data.message);
                }
            } catch (e) {
                console.error('Error parsing response:', e);
            }
        },
        error: function() {
            alert('Error updating order. Please try again.');
        }
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


