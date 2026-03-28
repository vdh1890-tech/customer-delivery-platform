<?php
require_once '../../includes/db_connect.php';


// Date Filter Logic
$date_filter = isset($_GET['date']) ? $_GET['date'] : '';
$where_clause = "";
if ($date_filter) {
    $where_clause = "WHERE DATE(orders.created_at) = '$date_filter'";
}

$sql = "SELECT orders.*, users.name as customer_name, users.phone as customer_phone 
        FROM orders 
        LEFT JOIN users ON orders.customer_id = users.id 
        $where_clause 
        ORDER BY orders.created_at DESC";
$result = $conn->query($sql);

// Fetch Drivers for Assignment
$drivers_res = $conn->query("SELECT id, name FROM users WHERE role='driver'");
$drivers = [];
if ($drivers_res) {
    while($d = $drivers_res->fetch_assoc()) $drivers[] = $d;
}

require_once 'includes/header.php';
?>

<div class="section-title flex-between">
    <h2>Order Management</h2>
    <div style="display:flex; align-items:center; gap:20px;">
        <form method="GET" style="display:flex; align-items:center; gap:10px;">
            <label style="font-weight:600; font-size:0.9rem;">Filter Date:</label>
            <input type="date" name="date" value="<?= $date_filter ?>" class="form-control" style="padding:5px; border:1px solid #ccc;">
            <?php if($date_filter): ?>
                <a href="orders.php" class="btn btn-outline btn-sm" style="color:red; border-color:red;">Clear</a>
            <?php else: ?>
                <button type="submit" class="btn btn-primary btn-sm">Filter</button>
            <?php endif; ?>
        </form>
        <div style="font-size: 0.9rem; color: #666; border-left:1px solid #ddd; padding-left:15px;">
            Total: <strong><?= $result->num_rows ?></strong>
        </div>
    </div>
</div>

<div class="admin-card" style="padding: 0;">
    <div class="table-container">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Order ID</th>
                    <th>Date</th>
                    <th>Customer & Location</th>
                    <th>Amount</th>
                    <th>Status</th>
                    <th>Driver</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($result && $result->num_rows > 0): ?>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td>#<?= $row['id'] ?></td>
                            <td><?= date('M d, Y H:i', strtotime($row['created_at'])) ?></td>
                            <td>
                                <div style="display:flex; align-items:center; gap:8px;">
                                    <i class="ph-fill ph-user" style="color:var(--primary-navy);"></i>
                                    <div style="font-weight: 600; font-size: 0.95rem;">
                                        <?= htmlspecialchars($row['customer_name'] ?? 'Guest') ?></div>
                                </div>
                                <div style="display:flex; align-items:center; gap:8px; margin-top:5px;">
                                    <i class="ph-fill ph-map-pin" style="color:var(--primary-orange);"></i>
                                    <div style="white-space: pre-line; font-size: 0.9rem;">
                                        <?= htmlspecialchars($row['delivery_address']) ?></div>
                                </div>
                            </td>
                            <td style="font-weight: 600;">₹ <?= number_format($row['total_amount']) ?></td>
                            <td>
                                <?php
                                $raw_status = strtolower($row['status']);
                                $status_colors = [
                                    'pending' => ['bg' => '#fef3c7', 'text' => '#92400e'],
                                    'processing' => ['bg' => '#e0e7ff', 'text' => '#3730a3'],
                                    'dispatched' => ['bg' => '#ffedd5', 'text' => '#9a3412'],
                                    'out_for_delivery' => ['bg' => '#dbeafe', 'text' => '#1e40af'],
                                    'delivered' => ['bg' => '#d1fae5', 'text' => '#065f46'],
                                    'cancelled' => ['bg' => '#fce7f3', 'text' => '#9d174d']
                                ];
                                $bg_col = $status_colors[$raw_status]['bg'] ?? '#fef3c7';
                                $text_col = $status_colors[$raw_status]['text'] ?? '#92400e';
                                ?>
                                <select onchange="updateStatus('<?= $row['id'] ?>', this.value)" 
                                        style="padding:5px 8px; border-radius:4px; border:1px solid #ddd; cursor:pointer; font-size:0.85rem; font-weight:500;
                                        background-color: <?= $bg_col ?>; color: <?= $text_col ?>; 
                                        appearance: auto;">
                                    <option value="pending" <?= $raw_status == 'pending' ? 'selected' : '' ?>>Pending</option>
                                    <option value="processing" <?= $raw_status == 'processing' ? 'selected' : '' ?>>Processing</option>
                                    <option value="dispatched" <?= $raw_status == 'dispatched' ? 'selected' : '' ?>>Dispatched</option>
                                    <option value="out_for_delivery" <?= $raw_status == 'out_for_delivery' ? 'selected' : '' ?>>Out for Delivery</option>
                                    <option value="delivered" <?= $raw_status == 'delivered' ? 'selected' : '' ?>>Delivered</option>
                                    <option value="cancelled" <?= $raw_status == 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
                                </select>
                            </td>

                            <td>
                                <select onchange="assignDriver('<?= $row['id'] ?>', this.value)" 
                                        style="padding:5px; border-radius:4px; border:1px solid #ddd; cursor:pointer; font-size:0.85rem;">
                                    <option value="">Unassigned</option>
                                    <?php foreach($drivers as $dr): ?>
                                        <option value="<?= $dr['id'] ?>" <?= $row['driver_id'] == $dr['id'] ? 'selected' : '' ?>><?= htmlspecialchars($dr['name']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </td>

                            <td>
                                <div style="display: flex; gap: 8px;">
                                    <?php
                                        // Format Phone Number for WhatsApp (Remove spaces/dashes)
                                        $phone_raw = $row['customer_phone'] ?? '';
                                        $phone_wa = preg_replace('/[^0-9]/', '', $phone_raw);
                                        // If 10 digits, assume India and prepend 91
                                        if (strlen($phone_wa) == 10) $phone_wa = "91" . $phone_wa;

                                        // URL for the invoice
                                        $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
                                        $invoice_endpoint = $protocol . "://" . $_SERVER['HTTP_HOST'] . "/customer_delivery_platform/modules/AutomatedInvoice/invoice.php?id=" . $row['id'];
                                        
                                        $msg = "Hello *" . trim($row['customer_name'] ?? 'Customer') . "*, your order (*#ORD-" . $row['id'] . "*) for ₹" . number_format($row['total_amount']) . " is confirmed! You can view and download your invoice here: " . $invoice_endpoint;
                                        
                                        $wa_link = "https://wa.me/" . $phone_wa . "?text=" . urlencode($msg);
                                    ?>
                                    <a href="<?= $wa_link ?>" target="_blank" class="btn-action" title="Send WhatsApp Invoice" style="color:#25d366; background:none; border:none; cursor:pointer; font-size:1.2rem;">
                                        <i class="ph ph-whatsapp-logo"></i>
                                    </a>

                                    <a href="https://www.google.com/maps/search/?api=1&query=<?= urlencode($row['delivery_address']) ?>"
                                        target="_blank" class="btn-action" title="View Map" style="color:var(--primary-navy);">
                                        <i class="ph ph-map-trifold"></i>
                                    </a>
                                    <a href="../AutomatedInvoice/invoice.php?id=<?= $row['id'] ?>" target="_blank" class="btn-action" title="View Invoice" style="color:var(--primary-navy);">
                                        <i class="ph ph-file-text"></i>
                                    </a>
                                    <button onclick="deleteOrder('<?= $row['id'] ?>')" class="btn-action" title="Delete Order" style="color:red; background:none; border:none; cursor:pointer; font-size:1.1rem;">
                                        <i class="ph ph-trash"></i>
                                    </button>
                                </div>
                            </td>

                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6" style="text-align: center; padding: 30px; color: #999;">No orders found.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>

<script>
    function updateStatus(id, newStatus) {
        fetch('update_order_status.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({ id: id, status: newStatus })
        })
        .then(res => res.json())
        .then(data => {
            if(data.success) {
                // Ideally show a toast, but reload works to update colors
                location.reload();
            } else {
                alert("Failed to update status");
            }
        })
        .catch(err => console.error(err));
    }

    function assignDriver(id, driverId) {
        fetch('assign_driver.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({ id: id, driver_id: driverId })
        })
        .then(res => res.json())
        .then(data => {
            if(!data.success) {
                alert("Failed to assign driver: " + (data.message || "Unknown error"));
                location.reload(); 
            }
        })
        .catch(err => alert("Network error"));
    }

    function deleteOrder(id) {
        if(confirm("Are you sure you want to permanently delete Order #" + id + "?")) {
            fetch('delete_item.php?type=order&ajax=1&id=' + id)
            .then(res => res.json())
            .then(data => {
                if(data.success) {
                    location.reload();
                } else {
                    alert("Error deleting order: " + (data.message || 'Unknown error'));
                }
            })
            .catch(err => alert("Network error"));
        }
    }

    // --- NEW ORDER NOTIFICATION POLLING ---
    let currentPendingCount = 0;
    
    // Initialize current count
    fetch('../../api/check_new_orders.php')
        .then(res => res.json())
        .then(data => { if(data.success) currentPendingCount = data.pending_count; });

    function playNotificationSound() {
        try {
            const context = new (window.AudioContext || window.webkitAudioContext)();
            const osc = context.createOscillator();
            const gainNode = context.createGain();
            
            osc.connect(gainNode);
            gainNode.connect(context.destination);
            
            osc.type = 'sine';
            osc.frequency.setValueAtTime(880, context.currentTime); // A5 note
            gainNode.gain.setValueAtTime(0.1, context.currentTime);
            
            osc.start();
            gainNode.gain.exponentialRampToValueAtTime(0.00001, context.currentTime + 0.5);
            osc.stop(context.currentTime + 0.5);
        } catch (e) {
            console.log("Audio play blocked by browser auto-play policy until user interacts with the page.");
        }
    }

    // Poll every 10 seconds
    setInterval(() => {
        fetch('../../api/check_new_orders.php')
            .then(res => res.json())
            .then(data => {
                if(data.success && data.pending_count > currentPendingCount) {
                    currentPendingCount = data.pending_count;
                    playNotificationSound();
                    
                    if(confirm("🔔 New Order Received! Refresh the page to view it?")) {
                        location.reload();
                    }
                }
            })
            .catch(err => console.error("Polling error:", err));
    }, 10000);

</script>