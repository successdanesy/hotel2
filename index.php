<?php 
session_start(); 
if (!isset($_SESSION['username'])) {
    $_SESSION['msg'] = "You must log in first";
    header('location: login.php');
    exit;
}

if (isset($_GET['logout'])) {
    session_destroy();
    unset($_SESSION['username']);
    header('location: login.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard | Antilla Apartments & Suites</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="header">
        <h2>Dashboard</h2>
    </div>
    <div class="content">
        <p>Welcome, <strong><?php echo $_SESSION['username']; ?></strong>!</p>
        <p>This is your personalized dashboard.</p>
        <p><a href="index.php?logout='1'" style="color: red;">Logout</a></p>
    </div>
</body>
</html>
