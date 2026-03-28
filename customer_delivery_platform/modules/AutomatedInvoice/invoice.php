<?php
session_start();
require_once '../../includes/db_connect.php';

// Validate inputs
if (!isset($_GET['id'])) {
    die("Invalid Order ID");
}

$order_id = $conn->real_escape_string($_GET['id']);

// Security: Check if user is owner OR admin
$user_id = $_SESSION['user_id'] ?? 0;
$user_role = $_SESSION['user_role'] ?? 'guest';

// Fetch Order Details
$sql = "SELECT o.*, u.name as customer_name, u.phone as customer_phone, u.email as customer_email 
        FROM orders o 
        JOIN users u ON o.customer_id = u.id 
        WHERE o.id = '$order_id'";
$result = $conn->query($sql);

if ($result->num_rows === 0) {
    die("Order not found");
}

$order = $result->fetch_assoc();

// Access Control
if ($user_role !== 'admin' && $order['customer_id'] != $user_id) {
    die("Unauthorized Access");
}

// Fetch Order Items
$items_sql = "SELECT oi.quantity, oi.price_at_time, p.name 
              FROM order_items oi 
              JOIN products p ON oi.product_id = p.id 
              WHERE oi.order_id = '$order_id'";
$items_result = $conn->query($items_sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice - #<?= $order['id'] ?></title>
    <style>
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            color: #333;
            line-height: 1.6;
            margin: 0;
            padding: 40px;
            background: #f9f9f9;
        }
        .invoice-container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            padding: 40px;
            box-shadow: 0 0 20px rgba(0,0,0,0.05);
        }
        .invoice-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 2px solid #eee;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }
        .company-details h1 {
            color: #2c3e50;
            margin: 0;
            font-size: 24px;
        }
        .company-details p {
            margin: 0;
            color: #7f8c8d;
            font-size: 14px;
        }
        .invoice-details {
            text-align: right;
        }
        .invoice-details h2 {
            margin: 0;
            color: #e67e22; /* Orange accent */
        }
        .bill-to {
            margin-bottom: 30px;
        }
        .bill-to h3 {
            margin-bottom: 10px;
            color: #2c3e50;
        }
        .invoice-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }
        .invoice-table th {
            background: #f8f9fa;
            color: #2c3e50;
            font-weight: 600;
            text-align: left;
            padding: 12px;
            border-bottom: 2px solid #ddd;
        }
        .invoice-table td {
            padding: 12px;
            border-bottom: 1px solid #eee;
        }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        
        .totals {
            display: flex;
            justify-content: flex-end;
        }
        .totals-table {
            width: 300px;
            border-collapse: collapse;
        }
        .totals-table td {
            padding: 8px;
        }
        .grand-total {
            font-size: 1.2em;
            font-weight: bold;
            color: #2c3e50;
            border-top: 2px solid #2c3e50;
        }
        .footer {
            margin-top: 50px;
            text-align: center;
            color: #95a5a6;
            font-size: 12px;
            border-top: 1px solid #eee;
            padding-top: 20px;
        }
        .print-btn {
            background: #2c3e50;
            color: white;
            border: none;
            padding: 10px 20px;
            cursor: pointer;
            border-radius: 4px;
            font-size: 14px;
            display: block;
            margin: 0 auto 20px;
        }
        .print-btn:hover {
            background: #34495e;
        }
        @media print {
            body { background: white; padding: 0; }
            .invoice-container { box-shadow: none; padding: 0; }
            .print-btn { display: none; }
        }
    </style>
</head>
<body>

    <button onclick="window.print()" class="print-btn">Print Invoice / Save as PDF</button>

    <div class="invoice-container">
        <header class="invoice-header">
            <div class="company-details">
                <h1>KR BLUE METALS</h1>
                <p>123 Quarry Road, Madurai, TN 625001</p>
                <p>Phone: +91 98765 43210</p>
                <p>Email: sales@krbluemetals.com</p>
            </div>
            <div class="invoice-details">
                <h2>INVOICE</h2>
                <p><strong>Order ID:</strong> #<?= htmlspecialchars($order['id']) ?></p>
                <p><strong>Date:</strong> <?= date('d M Y', strtotime($order['order_date'])) ?></p>
                <p><strong>Status:</strong> <?= ucfirst($order['status']) ?></p>
            </div>
        </header>

        <section class="bill-to">
            <h3>Bill To:</h3>
            <p><strong><?= htmlspecialchars($order['customer_name']) ?></strong></p>
            <p><?= htmlspecialchars($order['customer_phone']) ?></p>
            <p style="white-space: pre-line; max-width: 300px;"><?= htmlspecialchars($order['delivery_address']) ?></p>
        </section>

        <table class="invoice-table">
            <thead>
                <tr>
                    <th>Item Description</th>
                    <th class="text-center">Rate</th>
                    <th class="text-center">Qty</th>
                    <th class="text-right">Amount</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($item = $items_result->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($item['name']) ?></td>
                    <td class="text-center">₹<?= number_format($item['price_at_time']) ?></td>
                    <td class="text-center"><?= $item['quantity'] ?></td>
                    <td class="text-right">₹<?= number_format($item['price_at_time'] * $item['quantity']) ?></td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>

        <div class="totals">
            <table class="totals-table">
                <tr>
                    <td>Subtotal:</td>
                    <td class="text-right">₹<?= number_format($order['total_amount']) ?></td>
                </tr>
                <tr>
                    <td>Tax:</td>
                    <td class="text-right">₹0.00</td>
                </tr>
                <tr class="grand-total">
                    <td>Total:</td>
                    <td class="text-right">₹<?= number_format($order['total_amount']) ?></td>
                </tr>
            </table>
        </div>

        <footer class="footer">
            <p>Thank you for your business!</p>
            <p>This is a computer-generated invoice.</p>
        </footer>
    </div>

</body>
</html>
