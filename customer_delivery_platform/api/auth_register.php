<?php
header('Content-Type: application/json');
require_once '../includes/db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents("php://input"), true);

    $name = $conn->real_escape_string($data['name']);
    $email = $conn->real_escape_string($data['email']);
    $phone = $conn->real_escape_string($data['phone']);
    $raw_password = $data['password'];

    // Password complexity check
    if (strlen($raw_password) < 8 || !preg_match('/[A-Z]/', $raw_password) || !preg_match('/\d/', $raw_password) || !preg_match('/[^a-zA-Z\d]/', $raw_password)) {
        echo json_encode(['success' => false, 'message' => 'Password must contain at least 1 uppercase letter, 1 digit, and 1 special character.']);
        exit;
    }

    $password = password_hash($raw_password, PASSWORD_DEFAULT);
    $address = $conn->real_escape_string($data['address']);

    // Check if email or phone already exists
    $stmt_check = $conn->prepare("SELECT id FROM users WHERE email = ? OR phone = ?");
    $stmt_check->bind_param("ss", $email, $phone);
    $stmt_check->execute();
    $result = $stmt_check->get_result();

    if ($result->num_rows > 0) {
        echo json_encode(['success' => false, 'message' => 'Email or Phone already registered']);
        exit;
    }

    $role = 'customer';
    $stmt_insert = $conn->prepare("INSERT INTO users (name, email, phone, password, address, role) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt_insert->bind_param("ssssss", $name, $email, $phone, $password, $address, $role);

    if ($stmt_insert->execute()) {
        echo json_encode(['success' => true, 'message' => 'Registration successful']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error: ' . $stmt_insert->error]);
    }
}
?>
