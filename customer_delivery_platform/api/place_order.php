<?php
session_start();
require_once '../includes/db_connect.php';

// Receive JSON payload
$input = file_get_contents('php://input');
$data = json_decode($input, true);

if (!$data) {
    echo json_encode(['success' => false, 'message' => 'Invalid data']);
    exit;
}

$name = $data['customer']['name'];
$phone = $data['customer']['phone'];
$address = $data['customer']['address'];
// Ensure we have coords, defaulting to 0 if missing (should be caught by frontend though)
$lat = $data['customer']['lat'] ?? 0;
$lng = $data['customer']['lng'] ?? 0;
// Stop trusting frontend total
// $total = $data['total']; 
$items = $data['items'];
// Extract payment method, default to COD if not provided
$payment_method = $data['paymentMethod'] ?? 'cod';
$transaction_id = $data['transactionId'] ?? null;

$conn->begin_transaction();

try {
    // 1. Get Customer ID
    if (isset($_SESSION['user_id'])) {
        $customer_id = $_SESSION['user_id'];
    } else {
        echo json_encode(['success' => false, 'message' => 'User not logged in. Please login to place an order.']);
        exit;
    }

    // 2. Generate Order ID (Format: ORD-YYYYMMDD-XXXX)
    $order_id = "ORD-" . date("Ymd") . "-" . rand(1000, 9999);

    // 3. SECURELY Calculate Total Amount from Database Prices
    $server_total = 0;
    $secure_items = []; // Store items with secure prices for insertion later
    
    $stmt_price = $conn->prepare("SELECT price_per_ton FROM products WHERE id = ? AND stock_status != 'out_of_stock'");
    
    foreach ($items as $item) {
        $product_id = $item['id'];
        $quantity = $item['quantity'];
        
        $stmt_price->bind_param("i", $product_id);
        $stmt_price->execute();
        $result = $stmt_price->get_result();
        
        if ($row = $result->fetch_assoc()) {
            $secure_price = $row['price_per_ton'];
            $server_total += ($secure_price * $quantity);
            
            // Save for the order_items insert
            $secure_items[] = [
                'id' => $product_id,
                'quantity' => $quantity,
                'price' => $secure_price
            ];
        } else {
             throw new Exception("Product ID $product_id is invalid or out of stock.");
        }
    }

    // 4. Insert Order (Now including payment_method and transaction_id)
    $full_address = $address; 

    $stmt_order = $conn->prepare("INSERT INTO orders (id, customer_id, total_amount, status, delivery_address, delivery_lat, delivery_lng, payment_method, transaction_id) VALUES (?, ?, ?, 'pending', ?, ?, ?, ?, ?)");
    $stmt_order->bind_param("sidsddss", $order_id, $customer_id, $server_total, $full_address, $lat, $lng, $payment_method, $transaction_id);
    require_once '../includes/sms_helper.php';

    if ($stmt_order->execute()) {
        // 5. Insert Items with Secure Prices
        $stmt_item = $conn->prepare("INSERT INTO order_items (order_id, product_id, quantity, price_at_time) VALUES (?, ?, ?, ?)");
        foreach ($secure_items as $sec_item) {
            $stmt_item->bind_param("siid", $order_id, $sec_item['id'], $sec_item['quantity'], $sec_item['price']);
            $stmt_item->execute();
        }

        $conn->commit();

        // Send SMS Notification (using calculated server total)
        sendSMSOrderConfirmation($phone, $order_id, $server_total, $items);

        // --- NEW DRIVER EMAIL NOTIFICATION ---
        $driver_res = $conn->query("SELECT email FROM users WHERE role = 'driver'");
        $driver_emails = [];
        if ($driver_res) {
            while ($d_row = $driver_res->fetch_assoc()) {
                if (!empty($d_row['email']) && filter_var($d_row['email'], FILTER_VALIDATE_EMAIL)) {
                    $driver_emails[] = $d_row['email'];
                }
            }
        }
        
        if (!empty($driver_emails)) {
            $to = implode(", ", $driver_emails);
            $subject = "New Delivery Available: " . $order_id;
            $message = "Hello Driver,\n\nA new order ($order_id) has been placed and is pending delivery.\nLocation: $full_address\n\nLogin to the Driver Dashboard to view and complete this delivery.\n\nKR Blue Metals System";
            $headers = "From: alerts@krbluemetals.com\r\n";
            @mail($to, $subject, $message, $headers); // Supress XAMPP local warnings
        }

        echo json_encode(['success' => true, 'order_id' => $order_id]);
    } else {
        throw new Exception("Failed to insert order.");
    }

} catch (Exception $e) {
    $conn->rollback();
    // Log error for server admin if needed
    // error_log($e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Database Error: ' . $e->getMessage()]);
}
?>