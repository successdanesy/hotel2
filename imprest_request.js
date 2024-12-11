document.getElementById('imprestForm').addEventListener('submit', function(event) {
    event.preventDefault();  // Prevent default form submission
    const formData = new FormData(this);  // Get form data

    fetch('imprest_requests.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())  // Parse JSON response
    .then(data => {
        if (data.success) {
            alert(data.message);
            document.getElementById('imprestForm').reset();  // Reset form
            loadImprestRequests();  // Optionally reload the table
        } else {
            alert(data.error);  // Display error if any
        }
    })
    .catch(error => console.error('Error:', error));
});


function loadImprestRequests() {
    fetch('fetch_imprest_requests.php')  // Assuming a separate endpoint to load data
    .then(response => response.json())
    .then(data => {
        const tableBody = document.getElementById('imprestTable').querySelector('tbody');
        tableBody.innerHTML = '';

        data.requests.forEach(request => {
            const row = document.createElement('tr');
            row.innerHTML = `
                <td>${request.id}</td>
                <td>${request.item_name}</td>
                <td>${request.quantity}</td>
                <td>${request.status}</td>
                <td>${request.price ? request.price : 'Pending'}</td>
                <td>
                    ${request.status === 'Pending' ? '<button onclick="completeRequest(' + request.id + ')">Complete</button>' : ''}
                </td>
            `;
            tableBody.appendChild(row);
        });
    })
    .catch(error => console.error('Error:', error));
}

function completeRequest(id) {
    const price = prompt('Enter the price:');
    if (price !== null) {
        fetch('imprest_requests.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ action: 'complete', id: id, price: parseFloat(price) })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert(data.message);
                loadImprestRequests();  // Reload the table
            } else {
                alert(data.error);
            }
        })
        .catch(error => console.error('Error:', error));
    }
}
