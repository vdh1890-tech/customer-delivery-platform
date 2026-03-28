<?php
require_once '../includes/db_connect.php';
header('Content-Type: application/json');

if (!isset($_GET['id'])) {
    echo json_encode(['success' => false, 'message' => 'Missing Order ID']);
    exit;
}

$order_id = $_GET['id'];

// 1. Fetch Order details and Customer info
$stmt = $conn->prepare("
    SELECT o.id, o.status, o.total_amount, o.delivery_address, o.created_at, c.name, c.phone 
    FROM orders o 
    JOIN users c ON o.customer_id = c.id 
    WHERE o.id = ?
");
$stmt->bind_param("s", $order_id);
$stmt->execute();
$order_result = $stmt->get_result();

if ($order_result->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => "Order ID not found."]);
    exit;
}

$order_data = $order_result->fetch_assoc();

// 2. Fetch Order Items to map 'material' and 'quantity'
$items_stmt = $conn->prepare("
    SELECT p.name, oi.quantity 
    FROM order_items oi 
    JOIN products p ON oi.product_id = p.id 
    WHERE oi.order_id = ?
");
$items_stmt->bind_param("s", $order_id);
$items_stmt->execute();
$items_result = $items_stmt->get_result();

$items_list = [];
$total_quantity = 0;
while ($row = $items_result->fetch_assoc()) {
    $items_list[] = $row;
    $total_quantity += $row['quantity'];
}

$material_name = count($items_list) > 0 ? $items_list[0]['name'] . (count($items_list) > 1 ? ' + ' . (count($items_list)-1) . ' more' : '') : 'Construction Material';

// 3. Build Timeline based on DB Status
$db_status = $order_data['status'];
$timeline = [];

// Determine the active step integer (1 to 5)
$status_map = [
    'pending' => 1,
    'processing' => 2,
    'dispatched' => 3,
    'out_for_delivery' => 4,
    'delivered' => 5,
    'cancelled' => -1
];

$current_step = isset($status_map[$db_status]) ? $status_map[$db_status] : 1;
$is_cancelled = ($current_step === -1);

if ($is_cancelled) {
    // If cancelled, show a simplified timeline
    $timeline[] = ['status' => 'completed', 'title' => 'Order Placed', 'message' => 'Order was received', 'time' => date('h:i A', strtotime($order_data['created_at']))];
    $timeline[] = ['status' => 'cancelled', 'title' => 'Cancelled', 'message' => 'This order was cancelled.', 'time' => date('h:i A', strtotime($order_data['created_at'] . ' + 1 hour'))];
} else {
    // Standard 5-step milestone tracking
    // Step 1: Order Placed
    $timeline[] = [
        'status' => ($current_step >= 1) ? 'completed' : 'pending',
        'title' => 'Order Placed',
        'message' => 'Your order has been confirmed',
        'time' => date('h:i A', strtotime($order_data['created_at']))
    ];

    // Step 2: Processing
    $timeline[] = [
        'status' => ($current_step > 2) ? 'completed' : ($current_step === 2 ? 'current' : 'pending'),
        'title' => 'Processing',
        'message' => 'Material is being prepared and tested',
        'time' => ($current_step >= 2) ? date('h:i A', strtotime($order_data['created_at'] . ' + 30 minutes')) : '--'
    ];

    // Step 3: Dispatched
    $timeline[] = [
        'status' => ($current_step > 3) ? 'completed' : ($current_step === 3 ? 'current' : 'pending'),
        'title' => 'Dispatched',
        'message' => 'Material loaded and left facility',
        'time' => ($current_step >= 3) ? date('h:i A', strtotime($order_data['created_at'] . ' + 1 hour')) : '--'
    ];

    // Step 4: Out for Delivery
    $timeline[] = [
        'status' => ($current_step > 4) ? 'completed' : ($current_step === 4 ? 'current' : 'pending'),
        'title' => 'In Transit',
        'message' => 'Driver is heading to your address',
        'time' => ($current_step >= 4) ? date('h:i A', strtotime($order_data['created_at'] . ' + 2 hours')) : '--'
    ];

    // Step 5: Delivered
    $timeline[] = [
        'status' => ($current_step === 5) ? 'completed' : 'pending',
        'title' => 'Delivered',
        'message' => ($current_step === 5) ? 'Material successfully unloaded' : 'Awaiting delivery',
        'time' => ($current_step === 5) ? date('h:i A', strtotime($order_data['created_at'] . ' + 3 hours')) : '--'
    ];
}

// Format the final response to match frontend expectations
$response = [
    'success' => true,
    'order' => [
        'id' => $order_data['id'],
        'status' => $db_status,
        'material' => $material_name,
        'quantity' => $total_quantity . ' Tons',
        'total' => $order_data['total_amount'],
        'customer' => $order_data['name'],
        'phone' => $order_data['phone'],
        'address' => $order_data['delivery_address'],
        'createdAt' => $order_data['created_at'],
        'driver' => null, // Backend assignment logic can be expanded here later
        'timeline' => $timeline,
        'items' => $items_list
    ]
];

echo json_encode($response);
?>
