<?php
if ($_SERVER['HTTP_HOST'] == 'localhost') {
    $host = 'localhost';
    $user = 'root';
    $pass = ''; // Default XAMPP password
    $db = 'madurai_aggregates';
} else {
    // InfinityFree Live Credentials
    $host = 'sql101.infinityfree.com';
    $user = 'if0_41461545';
    $pass = 'an1fm9Lu3XBZ';
    $db = 'if0_41461545_krblue';
}

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Set charset to utf8mb4
$conn->set_charset("utf8mb4");
?>