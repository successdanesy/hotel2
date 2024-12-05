<?php
include('db_connect.php');

// Fetch all orders from the kitchen_orders table
$query = "SELECT * FROM bar_orders ORDER BY timestamp DESC";
$result = $conn->query($query);

// Output the orders in a table row format
while ($order = $result->fetch_assoc()):
?>
<tr>
    <td><?php echo $order['id']; ?></td>
    <td><?php echo $order['room_number']; ?></td>
    <td><?php echo $order['order_description']; ?></td>
    <td><?php echo number_format($order['total_amount'], 2); ?></td> <!-- Total Amount -->
    <td><?php echo ucfirst($order['special_instructions']); ?></td> <!-- Special Instructions -->
    <td id="order-status-<?php echo $order['id']; ?>">
        <?php echo ucfirst($order['status']); ?>
    </td>
    <td>
        <?php if ($order['status'] === 'pending'): ?>
            <!-- Mark as complete button -->
            <button type="button" class="button" onclick="markAsComplete(<?php echo $order['id']; ?>)">Mark as Complete</button>
        <?php else: ?>
            Sent to Front Desk
        <?php endif; ?>
    </td>
</tr>
<?php endwhile; ?>
