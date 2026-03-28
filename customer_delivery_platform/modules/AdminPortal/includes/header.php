<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
// In a real app, strict session check here: if(!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') header("Location: ../login.html");
$current_page = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard | KR BLUE METALS</title>

    <!-- Fonts & Icons -->
    <link rel="stylesheet" href="../../assets/css/style.css?v=<?= time() ?>">
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    <!-- Simple Chart.js for Analytics -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>

<body>

    <div class="admin-container">
        <!-- Main Content Wrapper -->
        <div class="main-content">
            <!-- Top Header -->
            <header class="top-header">
                <div class="header-left">
                    <div class="logo-area" style="display: flex; align-items: center; gap: 10px; margin-right: 40px;">
                        <i class="ph-fill ph-truck" style="font-size: 1.8rem; color: var(--primary-orange);"></i>
                        <h2 style="margin:0; font-size: 1.2rem; color: white; letter-spacing: 0.5px;">KR BLUE METALS</h2>
                    </div>
                </div>

                <div class="header-center" style="flex: 1;">
                    <nav class="top-nav">
                        <a href="../AdminPortal/dashboard.php" class="<?= $current_page == 'dashboard.php' ? 'active' : '' ?>">
                            <i class="ph ph-squares-four"></i> Dashboard
                        </a>
                        <a href="../AdminPortal/orders.php" class="<?= $current_page == 'orders.php' ? 'active' : '' ?>">
                            <i class="ph ph-clipboard-text"></i> Orders
                        </a>
                        <a href="../ProductManagement/index.php" class="<?= $current_page == 'index.php' || $current_page == 'products.php' ? 'active' : '' ?>">
                            <i class="ph ph-package"></i> Products
                        </a>
                        <a href="../PerformanceInsights/analytics.php" class="<?= $current_page == 'analytics.php' ? 'active' : '' ?>">
                            <i class="ph ph-chart-line-up"></i> Analytics
                        </a>
                        <a href="../AdminPortal/customers.php" class="<?= $current_page == 'customers.php' ? 'active' : '' ?>">
                            <i class="ph ph-users"></i> Customers
                        </a>
                        <a href="../AdminPortal/drivers.php" class="<?= $current_page == 'drivers.php' ? 'active' : '' ?>">
                            <i class="ph ph-steering-wheel"></i> Drivers
                        </a>
                    </nav>
                </div>

                <div class="header-right" style="display:flex; align-items:center; gap:20px;">
                     <div class="user-info" style="text-align:right;">
                        <small style="color:gray;">Logged in as</small><br>
                        <strong>Administrator</strong>
                    </div>
                    <div class="avatar">
                        <img src="../../assets/img/admin_avatar.png" alt="Admin" style="width:40px; height:40px; border-radius:50%; object-fit:cover; border: 2px solid var(--primary-orange);">
                    </div>
                    <a href="../AdminPortal/logout.php" class="btn-logout" title="Logout">
                        <i class="ph ph-sign-out" style="font-size: 1.2rem;"></i>
                    </a>
                </div>
            </header>

            <!-- Page Content Start -->
            <div class="page-content">