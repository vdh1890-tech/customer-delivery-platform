<?php
header('Content-Type: application/json');
require_once '../includes/db_connect.php';

// Get count of pending orders
$sql = "SELECT COUNT(*) as count FROM orders WHERE status = 'pending'";
$result = $conn->query($sql);
$count = 0;

if ($result) {
    $row = $result->fetch_assoc();
    $count = (int)$row['count'];
}

echo json_encode(['success' => true, 'pending_count' => $count]);
?>
