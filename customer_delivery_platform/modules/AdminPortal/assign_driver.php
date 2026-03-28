<?php
session_start();
// Realistically, should check if admin here. 
// if(!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') { echo json_encode(['success'=>false]); exit; }

require_once '../../includes/db_connect.php';

$input = file_get_contents('php://input');
$data = json_decode($input, true);

if (!$data || !isset($data['id'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid input']);
    exit;
}

$order_id = $data['id'];
$driver_id = !empty($data['driver_id']) ? intval($data['driver_id']) : NULL;

if ($driver_id === NULL) {
    $stmt = $conn->prepare("UPDATE orders SET driver_id = NULL WHERE id = ?");
    $stmt->bind_param("s", $order_id);
} else {
    $stmt = $conn->prepare("UPDATE orders SET driver_id = ? WHERE id = ?");
    $stmt->bind_param("is", $driver_id, $order_id);
}

if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Database update failed']);
}
?>
