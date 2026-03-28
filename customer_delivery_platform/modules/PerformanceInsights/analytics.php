<?php
require_once '../../includes/db_connect.php';
require_once '../AdminPortal/includes/auth_check.php';
require_once '../AdminPortal/includes/header.php';

// --- DATA FETCHING ---

// 1. KPI Summaries
// Total Revenue
$revQuery = $conn->query("SELECT SUM(total_amount) as total FROM orders WHERE status != 'cancelled'");
$totalRevenue = $revQuery->fetch_assoc()['total'] ?? 0;

// Total Orders
$ordQuery = $conn->query("SELECT COUNT(*) as count FROM orders");
$totalOrders = $ordQuery->fetch_assoc()['count'] ?? 0;

// Average Order Value
$avgOrderValue = $totalOrders > 0 ? $totalRevenue / $totalOrders : 0;

// Best Selling Category (Simple generic query)
$catQuery = $conn->query("SELECT category, COUNT(*) as count FROM products p JOIN order_items oi ON p.id = oi.product_id GROUP BY category ORDER BY count DESC LIMIT 1");
$topCategory = $catQuery->num_rows > 0 ? ucfirst($catQuery->fetch_assoc()['category']) : 'N/A';


// 2. Revenue Trend (Last 7 Days)
$revenueData = [];
$dates = [];
for ($i = 6; $i >= 0; $i--) {
    $date = date('Y-m-d', strtotime("-$i days"));
    $sql = "SELECT SUM(total_amount) as total FROM orders WHERE DATE(created_at) = '$date' AND status != 'cancelled'";
    $result = $conn->query($sql);
    $val = $result->fetch_assoc()['total'] ?? 0;
    
    $revenueData[] = $val;
    $dates[] = date('M d', strtotime($date));
}

// 3. Order Status Distribution
$statusCounts = ['pending' => 0, 'shipped' => 0, 'delivered' => 0, 'cancelled' => 0];
$sql = "SELECT status, COUNT(*) as count FROM orders GROUP BY status";
$result = $conn->query($sql);
while ($row = $result->fetch_assoc()) {
    $statusCounts[$row['status']] = $row['count'];
}

// 4. Top 5 Selling Products (REAL DATA)
$topProducts = [];
$productLabels = [];
$sql = "SELECT p.name, SUM(oi.quantity) as total_sold 
        FROM order_items oi 
        JOIN products p ON oi.product_id = p.id 
        GROUP BY p.id 
        ORDER BY total_sold DESC 
        LIMIT 5";
$result = $conn->query($sql);

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $productLabels[] = $row['name'];
        $topProducts[] = $row['total_sold'];
    }
} else {
    // Graceful fallback if no items sold yet
    $productLabels = ['No Sales Yet'];
    $topProducts = [0];
}
?>

<div class="analytics-container" style="padding-bottom: 50px;">
    
    <!-- Page Header -->
    <div class="flex-between" style="margin-bottom: 30px;">
        <div>
            <h2 style="margin:0; font-size:1.8rem; color:var(--primary-navy);">Performance Overview</h2>
            <p style="color:var(--text-light); margin-top:5px;">Real-time data analysis of your sales and inventory</p>
        </div>
        <div style="background:white; padding:8px 15px; border-radius:50px; border:1px solid #e5e7eb; font-size:0.9rem; color:var(--text-medium); display:flex; align-items:center; gap:8px;">
            <div style="width:8px; height:8px; background:#16a34a; border-radius:50%;"></div>
            Live Updates
        </div>
    </div>

    <!-- 1. KPI Cards Row -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 20px; margin-bottom: 30px;">
        <!-- Card 1 -->
        <div class="stat-card" style="background: linear-gradient(135deg, #142850 0%, #0f1e3c 100%); color:white; border:none;">
            <div class="stat-info">
                <h3 style="color:rgba(255,255,255,0.7);">Total Revenue</h3>
                <div class="value" style="color:white;">₹ <?= number_format($totalRevenue) ?></div>
                <small style="color:#4ade80; display:flex; align-items:center; gap:5px; margin-top:5px;">
                    <i class="ph-bold ph-trend-up"></i> All time
                </small>
            </div>
            <div class="stat-icon" style="background:rgba(255,255,255,0.1); width:50px; height:50px; display:flex; align-items:center; justify-content:center; border-radius:12px; color:white;">
                <i class="ph-fill ph-currency-inr"></i>
            </div>
        </div>

        <!-- Card 2 -->
        <div class="stat-card" style="border-left: 4px solid var(--primary-orange);">
            <div class="stat-info">
                <h3>Total Orders</h3>
                <div class="value"><?= number_format($totalOrders) ?></div>
                <small style="color:var(--text-light);">Processed orders</small>
            </div>
            <div class="stat-icon" style="color:var(--primary-orange);">
                <i class="ph-fill ph-receipt"></i>
            </div>
        </div>

        <!-- Card 3 -->
        <div class="stat-card">
            <div class="stat-info">
                <h3>Avg. Order Value</h3>
                <div class="value">₹ <?= number_format($avgOrderValue, 0) ?></div>
                <small style="color:var(--text-light);">Revenue / Order</small>
            </div>
            <div class="stat-icon" style="color:#3b82f6;">
                <i class="ph-fill ph-chart-bar"></i>
            </div>
        </div>

        <!-- Card 4 -->
        <div class="stat-card">
            <div class="stat-info">
                <h3>Top Category</h3>
                <div class="value" style="font-size:1.4rem;"><?= $topCategory ?></div>
                <small style="color:var(--text-light);">Most popular segment</small>
            </div>
            <div class="stat-icon" style="color:#8b5cf6;">
                <i class="ph-fill ph-tag"></i>
            </div>
        </div>
    </div>

    <!-- 2. Charts Section -->
    <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 25px; margin-bottom: 25px;">
        
        <!-- Main Line Chart -->
        <div class="admin-card" style="min-height: 400px; display:flex; flex-direction:column;">
            <div class="flex-between" style="margin-bottom:20px;">
                <h3 style="margin:0;">Revenue Trend (7 Days)</h3>
            </div>
            <div style="flex:1; position:relative;">
                <canvas id="revenueChart"></canvas>
            </div>
        </div>

        <!-- Doughnut Chart -->
        <div class="admin-card" style="min-height: 400px; display:flex; flex-direction:column;">
            <h3 style="margin:0 0 20px 0;">Order Status</h3>
            <div style="flex:1; display:flex; align-items:center; justify-content:center; position:relative;">
                <canvas id="statusChart"></canvas>
            </div>
        </div>
    </div>

    <!-- 3. Bottom Row -->
    <div class="admin-card">
        <h3 style="margin-bottom:20px;">Top Selling Products (Units Sold)</h3>
        <div style="height: 300px;">
            <canvas id="productChart"></canvas>
        </div>
    </div>

</div>

<script>
    // Global Defaults for better visuals
    Chart.defaults.font.family = "'Inter', sans-serif";
    Chart.defaults.color = '#6b7280';
    Chart.defaults.scale.grid.color = '#f3f4f6';

    // 1. Revenue Chart
    const revCtx = document.getElementById('revenueChart').getContext('2d');
    
    // Gradient Fill
    let gradient = revCtx.createLinearGradient(0, 0, 0, 400);
    gradient.addColorStop(0, 'rgba(20, 40, 80, 0.2)');
    gradient.addColorStop(1, 'rgba(20, 40, 80, 0)');

    new Chart(revCtx, {
        type: 'line',
        data: {
            labels: <?= json_encode($dates) ?>,
            datasets: [{
                label: 'Revenue',
                data: <?= json_encode($revenueData) ?>,
                borderColor: '#142850',
                backgroundColor: gradient,
                borderWidth: 2,
                pointBackgroundColor: '#ffb800',
                pointBorderColor: '#fff',
                pointRadius: 4,
                pointHoverRadius: 6,
                fill: true,
                tension: 0.4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    backgroundColor: '#142850',
                    padding: 12,
                    cornerRadius: 4,
                    displayColors: false,
                    callbacks: {
                        label: function(context) {
                            return '₹ ' + context.parsed.y.toLocaleString();
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) { 
                            if(value >= 1000) return '₹' + (value/1000).toFixed(1) + 'k';
                            return '₹' + value;
                        }
                    }
                },
                x: {
                    grid: { display: false }
                }
            }
        }
    });

    // 2. Status Chart
    new Chart(document.getElementById('statusChart'), {
        type: 'doughnut',
        data: {
            labels: ['Pending', 'Shipped', 'Delivered', 'Cancelled'],
            datasets: [{
                data: [
                    <?= $statusCounts['pending'] ?? 0 ?>,
                    <?= $statusCounts['shipped'] ?? 0 ?>,
                    <?= $statusCounts['delivered'] ?? 0 ?>,
                    <?= $statusCounts['cancelled'] ?? 0 ?>
                ],
                backgroundColor: ['#f59e0b', '#3b82f6', '#10b981', '#ef4444'],
                borderWidth: 0,
                hoverOffset: 4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '70%',
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        usePointStyle: true,
                        padding: 20,
                        font: { size: 12 }
                    }
                }
            }
        }
    });

    // 3. Product Chart
    new Chart(document.getElementById('productChart'), {
        type: 'bar',
        data: {
            labels: <?= json_encode($productLabels) ?>,
            datasets: [{
                label: 'Units Sold',
                data: <?= json_encode($topProducts) ?>,
                backgroundColor: '#142850',
                borderRadius: 4,
                barPercentage: 0.5
            }]
        },
        options: {
            indexAxis: 'y', // Horizontal Layout
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false }
            },
            scales: {
                x: {
                    grid: { display: false },
                    ticks: { precision: 0 }
                },
                y: {
                    grid: { display: false }
                }
            }
        }
    });
</script>

<?php require_once '../AdminPortal/includes/footer.php'; ?>