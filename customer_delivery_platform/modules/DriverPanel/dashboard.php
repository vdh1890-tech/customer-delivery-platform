<?php
session_start();
if (!isset($_SESSION['driver_id'])) {
    header("Location: login.php");
    exit;
}
require_once '../../includes/db_connect.php';

// Fetch Pending Orders for Delivery (Simulated assignment: Show all 'pending' orders)
// Fetch Pending and Delivered Orders (Assigned to this driver)
$driver_id = $_SESSION['driver_id'];
$sql = "SELECT orders.*, users.phone as customer_phone 
        FROM orders 
        LEFT JOIN users ON orders.customer_id = users.id 
        WHERE orders.driver_id = $driver_id AND orders.status != 'cancelled' 
        ORDER BY CASE WHEN orders.status = 'delivered' THEN 1 ELSE 0 END, orders.id DESC LIMIT 20";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Driver Dashboard</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    <style>
        .driver-header {
            background: var(--primary-navy);
            color: white;
            padding: 15px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .order-card {
            background: white;
            margin: 15px;
            padding: 15px;
            border-radius: 4px;
            box-shadow: var(--shadow-sm);
            border-left: 4px solid var(--primary-orange);
        }

        .order-card h3 {
            margin: 0 0 10px 0;
            display: flex;
            justify-content: space-between;
            font-size: 1.1rem;
        }

        .detail-row {
            display: flex;
            gap: 10px;
            margin-bottom: 8px;
            font-size: 0.9rem;
            color: #555;
        }

        .action-row {
            margin-top: 15px;
            padding-top: 15px;
            border-top: 1px solid #eee;
            display: flex;
            gap: 10px;
        }

        .btn-map {
            background: #e0f2fe;
            color: #0284c7;
            flex: 1;
            text-align: center;
            padding: 10px;
            border-radius: 4px;
            text-decoration: none;
            font-weight: 600;
        }

        .btn-done {
            background: #dcfce7;
            color: #16a34a;
            flex: 1;
            text-align: center;
            padding: 10px;
            border-radius: 4px;
            text-decoration: none;
            font-weight: 600;
        }
    </style>
</head>

<body style="background: #f4f6f8;">

    <header class="driver-header">
        <div style="font-weight: 700; display: flex; align-items: center; gap: 8px;">
            <i class="ph-fill ph-steering-wheel" style="font-size: 1.2rem; color: var(--primary-orange);"></i> KR DRIVER
        </div>
        <div style="display: flex; align-items: center; gap: 15px;">
            <div style="font-size: 0.9rem; opacity: 0.9;">Hello, <?= htmlspecialchars($_SESSION['driver_name']) ?></div>
            <a href="../../api/auth_logout.php" title="Logout" style="color: white; text-decoration: none; font-size: 1.2rem; display: flex; align-items: center;">
                <i class="ph ph-sign-out"></i>
            </a>
        </div>
    </header>

    <div style="padding: 15px 15px 5px 15px;">
        <h2 style="margin: 0; font-size: 1.25rem; color: var(--primary-navy);">Your Deliveries</h2>
    </div>

    <?php if ($result && $result->num_rows > 0): ?>
        <?php while ($order = $result->fetch_assoc()): ?>
            <div class="order-card">
                <h3>
                    <span>Order #<?= $order['id'] ?></span>
                    <?php 
                        $badge_class = 'badge-warning';
                        if ($order['status'] == 'delivered') $badge_class = 'badge-success';
                        elseif (in_array($order['status'], ['dispatched', 'out_for_delivery'])) $badge_class = 'badge-info';
                        elseif ($order['status'] == 'processing') $badge_class = 'badge-primary';
                    ?>
                    <span class="badge <?= $badge_class ?>" style="<?= $badge_class=='badge-info' ? 'background:#dbeafe; color:#1e40af; padding:4px 8px; border-radius:4px; font-size:0.75rem; font-weight:700;' : ($badge_class=='badge-primary' ? 'background:#e0e7ff; color:#3730a3; padding:4px 8px; border-radius:4px; font-size:0.75rem; font-weight:700;' : '') ?>"><?= strtoupper(str_replace('_', ' ', $order['status'])) ?></span>
                </h3>
                <div class="detail-row">
                    <i class="ph-fill ph-map-pin"></i>
                    <div style="white-space: pre-line;"><?= $order['delivery_address'] ?></div>
                </div>

                <!-- ITEMS LIST -->
                <div style="margin: 10px 0; padding: 10px; background: #f9fafb; border-radius: 4px; font-size: 0.85rem;">
                    <strong>Items:</strong>
                    <ul style="margin: 5px 0 0 20px; padding: 0; color: #444;">
                        <?php
                        $items_sql = "SELECT order_items.quantity, products.name 
                                      FROM order_items 
                                      JOIN products ON order_items.product_id = products.id 
                                      WHERE order_items.order_id = '" . $order['id'] . "'";
                        $items_res = $conn->query($items_sql);
                        if ($items_res && $items_res->num_rows > 0) {
                            while ($item = $items_res->fetch_assoc()) {
                                echo "<li>" . htmlspecialchars($item['name']) . " x <b>" . $item['quantity'] . "</b></li>";
                            }
                        } else {
                            echo "<li>No items found</li>";
                        }
                        ?>
                    </ul>
                </div>

                <!-- AMOUNT & PAYMENT -->
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px; padding-top: 10px; border-top: 1px dashed #eee;">
                    <div>
                        <span style="font-size: 0.85rem; color: gray;">Payment</span><br>
                        <strong><?= strtoupper($order['payment_method'] ?? 'COD') ?></strong>
                    </div>
                    <div style="text-align: right;">
                        <span style="font-size: 0.85rem; color: gray;">Amount</span><br>
                        <strong style="color: <?= strtolower($order['payment_method'] ?? 'cod') == 'cod' ? '#16a34a' : 'inherit' ?>; font-size: 1.1rem;">
                            ₹<?= number_format($order['total_amount']) ?>
                        </strong>
                    </div>
                </div>

                <div class="action-row">
                    <!-- Call Customer -->
                    <?php if (!empty($order['customer_phone'])): ?>
                        <a href="tel:<?= htmlspecialchars($order['customer_phone']) ?>" class="btn-map" style="background:#fefce8; color:#a16207; flex:0.4;">
                            <i class="ph-fill ph-phone"></i> CALL
                        </a>
                    <?php endif; ?>
                    <!-- Navigation -->
                    <a href="https://www.google.com/maps/dir/?api=1&destination=<?= urlencode(!empty(trim($order['delivery_address'])) ? $order['delivery_address'] : $order['delivery_lat'].','.$order['delivery_lng']) ?>"
                        target="_blank" class="btn-map">
                        <i class="ph-fill ph-navigation-arrow"></i> NAVIGATE
                    </a>
                    
                    <!-- Status Update Dropdown -->
                    <?php if ($order['status'] !== 'delivered'): ?>
                        <select onchange="updateDriverStatus('<?= $order['id'] ?>', this.value)" 
                                style="padding:10px; border-radius:4px; border:1px solid #c7d2fe; background:#e0e7ff; color:#3730a3; font-weight:600; cursor:pointer; flex: 1.5; outline: none; appearance: auto;">
                            <option value="pending" <?= $order['status'] == 'pending' ? 'selected' : '' ?>>Pending</option>
                            <option value="processing" <?= $order['status'] == 'processing' ? 'selected' : '' ?>>Processing</option>
                            <option value="dispatched" <?= $order['status'] == 'dispatched' ? 'selected' : '' ?>>Dispatched</option>
                            <option value="out_for_delivery" <?= $order['status'] == 'out_for_delivery' ? 'selected' : '' ?>>Out for Delivery</option>
                            <option value="delivered" <?= $order['status'] == 'delivered' ? 'selected' : '' ?>>Mark Delivered</option>
                        </select>
                    <?php endif; ?>
                </div>
            </div>
        <?php endwhile; ?>
    <?php else: ?>
        <div style="text-align: center; padding: 40px; color: gray;">
            <i class="ph-fill ph-check-circle" style="font-size: 3rem; opacity: 0.5;"></i>
            <p>No pending deliveries.</p>
        </div>
    <?php endif; ?>

    <!-- Logout moved to header -->

    <script>
        function updateDriverStatus(orderId, newStatus) {
            let confirmMsg = 'Update status to ' + newStatus.replace('_', ' ').toUpperCase() + '?';
            if (newStatus === 'delivered') confirmMsg = 'Are you sure you delivered Order #' + orderId + '?';
            
            if (confirm(confirmMsg)) {
                fetch('../AdminPortal/update_order_status.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ id: orderId, status: newStatus })
                })
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) {
                            location.reload();
                        } else {
                            alert('Error: ' + data.message);
                            location.reload();
                        }
                    })
                    .catch(err => alert('Network Error'));
            } else {
                location.reload(); // reset select visually if cancelled
            }
        }
    </script>

</body>

</html>