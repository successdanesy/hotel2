// Imprest Request Tray Logic
const requestTray = [];
const requestTrayTable = document.getElementById('requestTray').querySelector('tbody');

// Add item to the tray
document.getElementById('addToTray').addEventListener('click', () => {
    const itemName = document.getElementById('item_name').value.trim();
    const quantity = document.getElementById('quantity').value.trim();
    const price = parseFloat(document.getElementById('price').value || 0).toFixed(2);

    if (!itemName || !quantity || isNaN(price)) {
        alert('Please provide valid item name, quantity, and price.');
        return;
    }

    addItemToTray(itemName, quantity, price);
    clearFormFields();
});

// Add item to tray and update the table
function addItemToTray(itemName, quantity, price) {
    requestTray.push({ itemName, quantity, price });
    updateRequestTrayTable();
}

// Clear form fields after adding an item
function clearFormFields() {
    document.getElementById('item_name').value = '';
    document.getElementById('quantity').value = '';
    document.getElementById('price').value = '';
}

// Update the request tray table with current items
function updateRequestTrayTable() {
    requestTrayTable.innerHTML = ''; // Clear existing table rows
    requestTray.forEach((item, index) => {
        const row = document.createElement('tr');
        row.innerHTML = `
            <td>${item.itemName}</td>
            <td>${item.quantity}</td>
            <td>₦${parseFloat(item.price).toFixed(2)}</td>
            <td>
                <button class="remove-item" data-index="${index}">Remove</button>
            </td>
        `;
        requestTrayTable.appendChild(row);
    });
}

// Remove item from tray
requestTrayTable.addEventListener('click', (event) => {
    if (event.target.classList.contains('remove-item')) {
        const index = event.target.getAttribute('data-index');
        requestTray.splice(index, 1);
        updateRequestTrayTable();
    }
});

// Submit requests to the server
document.getElementById('submitRequests').addEventListener('click', () => {
    if (requestTray.length === 0) {
        alert('No items in the tray to submit.');
        return;
    }

    fetch('submit_imprest_requests_bar.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify(requestTray),
    })
        .then((response) => response.json())
        .then((data) => {
            if (data.success) {
                alert('Imprest requests submitted successfully!');
                requestTray.length = 0; // Clear the tray
                updateRequestTrayTable();
            } else {
                alert('Failed to submit requests: ' + data.error);
            }
        })
        .catch((error) => {
            console.error('Error submitting imprest requests:', error);
            alert('An error occurred while submitting requests.');
        });
});
