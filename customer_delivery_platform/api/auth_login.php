<?php
header('Content-Type: application/json');
session_start();
require_once '../includes/db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents("php://input"), true);

    $email = $conn->real_escape_string($data['email']);
    $password = $data['password'];

    $stmt = $conn->prepare("SELECT id, name, password, role, address, phone, email FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        if (password_verify($password, $user['password']) || $password === $user['password']) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['user_role'] = $user['role'];

            // Support DriverPanel specific session vars
            if ($user['role'] === 'driver') {
                $_SESSION['driver_id'] = $user['id'];
                $_SESSION['driver_name'] = $user['name'];
            }
            
            // Return user data (excluding password)
            unset($user['password']);
            echo json_encode(['success' => true, 'user' => $user]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Invalid password']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'User not found']);
    }
}
?>
