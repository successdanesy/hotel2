<?php
$servername = "sql109.infinityfree.com"; // MySQL Hostname
$username = "if0_38659407"; // MySQL Username
$password = "YABAM509am"; // MySQL Password
$database = "if0_38659407_XXX"; // MySQL Database Name

// Create connection
$conn = new mysqli($servername, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
