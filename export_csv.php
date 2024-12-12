<?php
require_once 'db_connect.php';

if (isset($_POST['export'])) {
    // Fetch completed imprest_requests
    $sql = "SELECT * FROM imprest_requests WHERE status = 'Completed'";
    $result = mysqli_query($conn, $sql);

    header('Content-Type: text/csv');
    header('Content-Disposition: attachment;filename=completed_imprest_requests.csv');

    $output = fopen('php://output', 'w');
    fputcsv($output, array('ID', 'Request Description', 'Amount'));

    while ($row = mysqli_fetch_assoc($result)) {
        fputcsv($output, $row);
    }

    fclose($output);
    exit;
}
?>
