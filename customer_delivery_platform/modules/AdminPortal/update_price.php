<?php
require_once '../../includes/db_connect.php';
header('Content-Type: application/json');

// Simple Admin Check
// session_start();
// if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
//     echo json_encode(['success' => false, 'message' => 'Unauthorized']);
//     exit;
// }

$input = file_get_contents('php://input');
$data = json_decode($input, true);

if (isset($data['id']) && isset($data['price'])) {
    $id = intval($data['id']);
    $price = floatval($data['price']);

    if ($price < 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid price']);
        exit;
    }

    $stmt = $conn->prepare("UPDATE products SET price_per_ton = ? WHERE id = ?");
    $stmt->bind_param("di", $price, $id);

    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $stmt->error]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Missing parameters']);
}
?>