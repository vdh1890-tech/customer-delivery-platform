<?php
require_once '../../includes/db_connect.php';

// Simple Admin Check (In a real app, use session/auth check)
// session_start();
// if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
//     header("Location: ../../index.php");
//     exit;
// }

if (isset($_GET['type']) && isset($_GET['id'])) {
    $type = $_GET['type'];
    $id = $_GET['id']; // IDs can be int or string (orders)

    switch ($type) {
        case 'product':
            // Verify ID is int
            $stmt = $conn->prepare("DELETE FROM products WHERE id = ?");
            $stmt->bind_param("i", $id);
            if ($stmt->execute()) {
                if (isset($_GET['ajax'])) {
                    echo json_encode(['success' => true]);
                } else {
                    header("Location: ../ProductManagement/index.php?deleted=1");
                }
            } else {
                if (isset($_GET['ajax'])) {
                    echo json_encode(['success' => false, 'message' => $stmt->error]);
                } else {
                    header("Location: ../ProductManagement/index.php?error=DeleteFailed");
                }
            }
            break;

        case 'driver':
            $stmt = $conn->prepare("DELETE FROM users WHERE id = ? AND role = 'driver'");
            $stmt->bind_param("i", $id);
            if ($stmt->execute()) {
                header("Location: drivers.php?deleted=1");
            } else {
                header("Location: drivers.php?error=DeleteFailed");
            }
            break;

        case 'order':
            // Delete order items first (if no CASCADE)
            $conn->query("DELETE FROM order_items WHERE order_id = '$id'");
            // Delete order
            $stmt = $conn->prepare("DELETE FROM orders WHERE id = ?");
            $stmt->bind_param("s", $id);
            if ($stmt->execute()) {
                if (isset($_GET['ajax'])) {
                    echo json_encode(['success' => true]);
                } else {
                    header("Location: orders.php?deleted=1");
                }
            } else {
                if (isset($_GET['ajax'])) {
                    echo json_encode(['success' => false, 'message' => $stmt->error]);
                } else {
                    header("Location: orders.php?error=DeleteFailed");
                }
            }
            break;

        default:
            echo "Invalid type";
            exit;
    }
} else {
    header("Location: dashboard.php");
}
?>