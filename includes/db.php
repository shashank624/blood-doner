<?php
// Database connection settings
// Change these according to your own server (XAMPP/WAMP/hosting)

$host = "localhost";
$db_user = "root";
$db_pass = "";
$db_name = "blood_donor_system";

$conn = new mysqli($host, $db_user, $db_pass, $db_name);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>