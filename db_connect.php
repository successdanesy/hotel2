<?php
$servername = "zpfp07ebhm2zgmrm.chr7pe7iynqr.eu-west-1.rds.amazonaws.com";
$username = "hy7x3wktu25w0pae";
$password = "ak9uacoz59zlwhiv";
$database = "l1oe4lyo2lg1i3dg";
$port = 3306; // MySQL default port

// Create connection
$conn = new mysqli($servername, $username, $password, $database, $port);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>




<!-- //Database connection details
//$host = 'localhost';  // Host name (usually 'localhost')
//$username = 'root';   // Your database username (typically 'root' for local dev)
//$password = '';       // Your database password (empty for XAMPP by default)
//$dbname = 'project';  // The name of the database

// Create a connection to the database
//$conn = new mysqli($host, $username, $password, $dbname);

// Check if the connection is successful
//if ($conn->connect_error) {
    //die("Connection failed: " . $conn->connect_error);
//}
// echo "Connected successfully"; // You can keep this for debugging purposes -->
