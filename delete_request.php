<?php
require_once 'db_connect.php';

if (isset($_POST['delete'])) {
    $id = $_POST['id'];

    // Deleting from `imprest_requests`
    $sql1 = "DELETE FROM imprest_requests WHERE id = ?";
    $stmt1 = $conn->prepare($sql1);
    $stmt1->bind_param('i', $id);

    // Deleting from `imprest_requests_bar`
    $sql2 = "DELETE FROM imprest_requests_bar WHERE id = ?";
    $stmt2 = $conn->prepare($sql2);
    $stmt2->bind_param('i', $id);

    // Execute both deletions
    $success1 = $stmt1->execute();
    $success2 = $stmt2->execute();

    if ($success1 && $success2) {
        header("Location: manager.php?message=deleted"); // Adding query parameter for feedback
    } else {
        echo "Error: " . ($stmt1->error ?? $stmt2->error);
    }
}
?>
