<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
// Determine relative path to root based on current script location
$rootPath = '';
if (strpos($_SERVER['SCRIPT_NAME'], '/modules/') !== false) {
    $rootPath = '../../';
} elseif (strpos($_SERVER['SCRIPT_NAME'], '/admin/') !== false || strpos($_SERVER['SCRIPT_NAME'], '/driver/') !== false) {
    $rootPath = '../';
}
?>
<!-- Top Bar -->
<div class="top-bar header-top-bar">
    <div class="container header-container">
        <div class="contact-info contact-list">
            <span><i class="ph-fill ph-phone"></i> +91 98765 43210</span>
            <span><i class="ph-fill ph-envelope"></i> sales@krbluemetals.com</span>
        </div>
        <div>
            <?php if (isset($_SESSION['user_id'])): ?>
                <span style="color: rgba(255,255,255,0.7); margin-right: 15px;">Welcome, <?php echo htmlspecialchars($_SESSION['user_name']); ?></span>
                <?php if ($_SESSION['user_role'] === 'admin'): ?>
                    <a href="<?php echo $rootPath; ?>modules/AdminPortal/dashboard.php" style="color: var(--primary-orange); font-size: 0.8rem; margin-right: 15px;">Admin Panel</a>
                <?php endif; ?>
                <a href="<?php echo $rootPath; ?>api/auth_logout.php" style="color: rgba(255,255,255,0.7); font-size: 0.8rem;">Logout</a>
            <?php else: ?>
                <a href="<?php echo $rootPath; ?>modules/CustomerPortal/login.php" style="color: var(--primary-orange); font-size: 0.8rem; display: flex; align-items: center; gap: 4px;">
                    <i class="ph ph-user" style="font-size: 1.2em;"></i> Sign In
                </a>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Header -->
<header class="site-header">
    <div class="container main-header-content">
        <a href="<?php echo $rootPath; ?>index.php" class="logo brand-logo">
            <img src="<?php echo $rootPath; ?>assets/img/logo.png" alt="KR Blue Metals" style="height: 80px;">
            <div>
                <h2 style="margin: 0; font-size: 1.5rem; line-height: 1;">KR BLUE METALS</h2>
                <small style="font-size: 0.75rem; letter-spacing: 1px; color: var(--text-medium);">INDUSTRIAL
                    AGGREGATES & SAND</small>
            </div>
        </a>

        <!-- Mobile Menu Toggle -->
        <button class="mobile-menu-btn" onclick="document.getElementById('mainNav').classList.toggle('active')">
            <i class="ph ph-list"></i>
        </button>

        <nav style="display: flex; gap: 25px; align-items: center; flex-wrap: wrap; justify-content: flex-end;">
            <a href="<?php echo $rootPath; ?>index.php" style="font-weight: 600;" class="<?php echo basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : ''; ?>">HOME</a>
            <div class="dropdown">
                <a href="<?php echo $rootPath; ?>catalog.php" style="font-weight: 600; display: flex; align-items: center; gap: 4px;" class="<?php echo basename($_SERVER['PHP_SELF']) == 'catalog.php' ? 'active' : ''; ?>">PRODUCTS <i class="ph-bold ph-caret-down" style="font-size: 0.8em;"></i></a>
                <div class="dropdown-content">
                    <a href="<?php echo $rootPath; ?>catalog.php?category=sand">Premium Concrete Sand</a>
                    <a href="<?php echo $rootPath; ?>catalog.php?category=sand">Premium Plaster Sand</a>
                    <a href="<?php echo $rootPath; ?>catalog.php?category=aggregate">SR Aggregates</a>
                    <a href="<?php echo $rootPath; ?>catalog.php?category=stone">Construction Stones</a>
                    <a href="<?php echo $rootPath; ?>catalog.php?category=other">Soil & Gravel</a>
                    <a href="<?php echo $rootPath; ?>catalog.php">All Products</a>
                </div>
            </div>
            <a href="<?php echo $rootPath; ?>about.php" style="font-weight: 600;" class="<?php echo basename($_SERVER['PHP_SELF']) == 'about.php' ? 'active' : ''; ?>">ABOUT US</a>
            <a href="<?php echo $rootPath; ?>contact.php" style="font-weight: 600;" class="<?php echo basename($_SERVER['PHP_SELF']) == 'contact.php' ? 'active' : ''; ?>">CONTACT US</a>
            <a href="<?php echo $rootPath; ?>track_order.php" style="font-weight: 600;" class="<?php echo basename($_SERVER['PHP_SELF']) == 'track_order.php' ? 'active' : ''; ?>">TRACK</a>
            
            <?php if (isset($_SESSION['user_id'])): ?>
                <a href="<?php echo $rootPath; ?>modules/CustomerPortal/my_account.php" style="font-weight: 600;" class="<?php echo basename($_SERVER['PHP_SELF']) == 'my_account.php' ? 'active' : ''; ?>">MY ACCOUNT</a>
            <?php endif; ?>

            <a href="<?php echo $rootPath; ?>checkout.php" class="cart-btn btn btn-primary" style="padding: 8px 15px;">
                <i class="ph-bold ph-shopping-cart"></i>
                <span id="cart-count">0</span>
            </a>
        </nav>
    </div>
</header>
