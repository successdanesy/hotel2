$query = "SELECT b.*, 
          r.price AS room_price, 
          COALESCE(SUM(bo.total_amount), 0) AS bar_total,
          COALESCE(SUM(ko.total_amount), 0) AS kitchen_total,
          ((DATEDIFF(b.checkout_date, b.checkin_date) + 1) * r.price) + 
          COALESCE(SUM(bo.total_amount), 0) + 
          COALESCE(SUM(ko.total_amount), 0) AS total_price
          FROM bookings b
          LEFT JOIN bar_orders bo ON b.room_number = bo.room_number
          LEFT JOIN kitchen_orders ko ON b.room_number = ko.room_number
          LEFT JOIN rooms r ON b.room_number = r.room_number
          WHERE b.id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $_GET['guest_id']);
$stmt->execute();
$guest = $stmt->get_result()->fetch_assoc();
