
<?php


    
$host = 'localhost';
$user = 'root';
$password = '';
$database = 'user_management';

$conn = mysqli_connect($host, $user, $password, $database);

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}  else {
    error_log("Successfully connected to database"); // Check server logs
}

?>
