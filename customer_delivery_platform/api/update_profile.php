<?php
session_start();
require_once '../includes/db_connect.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$input = file_get_contents('php://input');
$data = json_decode($input, true);

if (!$data) {
    echo json_encode(['success' => false, 'message' => 'Invalid data']);
    exit;
}

$user_id = $_SESSION['user_id'];
$name = $conn->real_escape_string($data['name']);
$email = $conn->real_escape_string($data['email']);
$phone = $conn->real_escape_string($data['phone']);
$address = $conn->real_escape_string($data['address']);

// Optional: Validate email uniqueness
$check = $conn->query("SELECT id FROM users WHERE (email='$email' OR phone='$phone') AND id != $user_id");
if ($check->num_rows > 0) {
    echo json_encode(['success' => false, 'message' => 'Email or Phone already exists used by another account']);
    exit;
}

$sql = "UPDATE users SET name='$name', email='$email', phone='$phone', address='$address' WHERE id=$user_id";

if ($conn->query($sql) === TRUE) {
    // Update Session Name if changed
    $_SESSION['user_name'] = $name;
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $conn->error]);
}
?>
