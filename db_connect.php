<?php
$url = getenv('JAWSDB_URL');
$dbparts = parse_url($url);

$hostname = $dbparts['host'];
$username = $dbparts['user'];
$password = $dbparts['pass'];
$database = ltrim($dbparts['path'],'/');
// Create connection
$conn = new mysqli($hostname, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
echo "Connection was successfully established!";
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

