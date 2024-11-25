let order = [];
let totalAmount = 0;

function filterCategory(category) {
  document.querySelectorAll('.dish-card').forEach(card => {
    card.style.display = card.getAttribute('data-category') === category ? 'block' : 'none';
  });
}

function addToOrder(dishName, price) {
  order.push({ name: dishName, price: price });
  updateOrderSummary();
}

function updateOrderSummary() {
  const orderList = document.getElementById('orderList');
  orderList.innerHTML = '';
  totalAmount = order.reduce((sum, item) => sum + item.price, 0);

  order.forEach((item, index) => {
    const li = document.createElement('li');
    li.innerHTML = `${item.name} - ₦${item.price.toFixed(2)} <button class="delete-btn" onclick="removeFromOrder(${index})">Remove</button>`;
    orderList.appendChild(li);
  });

  document.getElementById('totalAmount').textContent = totalAmount.toFixed(2);
}

function removeFromOrder(index) {
  order.splice(index, 1);
  updateOrderSummary();
}

function confirmOrder() {
  const roomNumber = document.getElementById('roomDropdown').value;
  if (!roomNumber) {
    alert('Please select a room number.');
    return;
  }

  const summaryPopup = document.getElementById('orderSummaryPopup');
  const orderSummaryList = document.getElementById('orderSummaryList');

  orderSummaryList.innerHTML = '';
  order.forEach(item => {
    const li = document.createElement('li');
    li.textContent = `${item.name} - ₦${item.price.toFixed(2)}`;
    orderSummaryList.appendChild(li);
  });

  document.getElementById('orderSummaryTotal').textContent = totalAmount.toFixed(2);
  document.getElementById('orderSummaryRoomNumber').textContent = roomNumber;
  summaryPopup.style.display = 'block';
}

function closeSummaryPopup() {
  document.getElementById('orderSummaryPopup').style.display = 'none';
}

function sendToFrontDesk() {
  alert(`Order sent! Total: ₦${totalAmount}`);
  order = [];
  totalAmount = 0;
  updateOrderSummary();
  closeSummaryPopup();
}
