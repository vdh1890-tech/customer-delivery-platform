<?php
require_once '../../includes/db_connect.php';

// 1. Get Products
$products = [];
$res = $conn->query("SELECT id, price_per_ton FROM products");
while($row = $res->fetch_assoc()) {
    $products[] = $row;
}

if(empty($products)) {
    die("No products found. Please import database.sql first.");
}

// 2. Get Users (Drivers/Customers)
$customers = [];
$res = $conn->query("SELECT id, address FROM users WHERE role='customer'");
while($row = $res->fetch_assoc()) {
    $customers[] = $row;
}
// Create a dummy customer if none
if(empty($customers)) {
    $conn->query("INSERT INTO users (name, email, phone, password, role) VALUES ('Demo Cus', 'demo@test.com', '5555555555', '123', 'customer')");
    $customers[] = ['id' => $conn->insert_id, 'address' => 'Demo Address'];
}

// 3. Generate Orders for last 7 days
$statuses = ['pending', 'shipped', 'delivered', 'cancelled'];

echo "Generating data...<br>";

for ($i = 0; $i < 35; $i++) {
    $daysAgo = rand(0, 7);
    $date = date('Y-m-d H:i:s', strtotime("-$daysAgo days"));
    
    // Random Customer
    $cust = $customers[array_rand($customers)];
    
    // Random Status (weighted towards delivered)
    $rand = rand(1, 10);
    $status = 'delivered';
    if($rand > 8) $status = 'pending';
    elseif($rand > 9) $status = 'cancelled';
    
    // Random Items (1 to 3 items)
    $numItems = rand(1, 3);
    $orderTotal = 0;
    $orderItems = [];
    
    for($j=0; $j<$numItems; $j++) {
        $prod = $products[array_rand($products)];
        $qty = rand(5, 50); // Tons
        $lineTotal = $prod['price_per_ton'] * $qty;
        $orderTotal += $lineTotal;
        $orderItems[] = ['id' => $prod['id'], 'qty' => $qty, 'price' => $prod['price_per_ton']];
    }
    
    // Insert Order
    $orderId = "ORD-" . date('Ymd', strtotime($date)) . "-" . rand(1000,9999);
    $stmt = $conn->prepare("INSERT INTO orders (id, customer_id, total_amount, status, created_at, delivery_address, delivery_lat, delivery_lng) VALUES (?, ?, ?, ?, ?, ?, 0, 0)");
    $addr = $cust['address'] ? $cust['address'] : '123 Main St, Demo City';
    $stmt->bind_param("sissss", $orderId, $cust['id'], $orderTotal, $status, $date, $addr);
    $stmt->execute();
    
    // Insert Items
    $stmtItem = $conn->prepare("INSERT INTO order_items (order_id, product_id, quantity, price_at_time) VALUES (?, ?, ?, ?)");
    foreach($orderItems as $item) {
        $stmtItem->bind_param("siid", $orderId, $item['id'], $item['qty'], $item['price']);
        $stmtItem->execute();
    }
    
    echo "Created Order $orderId ($status) - ₹$orderTotal<br>";
}

echo "Done! <a href='../PerformanceInsights/analytics.php'>Go to Analytics</a>";
?>
