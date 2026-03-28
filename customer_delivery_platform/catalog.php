<?php
require_once 'includes/db_connect.php';
// Fetch products
// Fetch products with optional category filter
$category_filter = isset($_GET['category']) ? $_GET['category'] : null;

if ($category_filter) {
    // Secure simple filtering
    $valid_categories = ['sand', 'aggregate', 'stone', 'other'];
    if (in_array($category_filter, $valid_categories)) {
        $sql = "SELECT * FROM products WHERE stock_status != 'out_of_stock' AND category = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $category_filter);
        $stmt->execute();
        $result = $stmt->get_result();
    } else {
        $sql = "SELECT * FROM products WHERE stock_status != 'out_of_stock'";
        $result = $conn->query($sql);
    }
} else {
    $sql = "SELECT * FROM products WHERE stock_status != 'out_of_stock'";
    $result = $conn->query($sql);
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Material Catalog | KR BLUE METALS</title>
    <link rel="stylesheet" href="assets/css/style.css?v=<?php echo time(); ?>">
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
</head>

<body>

    <?php include 'includes/header.php'; ?>


    <!-- Static Hero Section -->
    <section class="hero">
        <div class="container">
            <h1>PREMIUM AGGREGATES & M-SAND<br><span style="color: var(--primary-orange);">DIRECT FROM QUARRY</span>
            </h1>
            <p>High-quality construction materials delivered to your site. Reliable, Transparent, and
                Fast.</p>
        </div>
    </section>

    <!-- Catalog Grid -->
    <section class="catalog-section" style="padding: 60px 0;">
        <div class="container" style="max-width: 1200px; margin: 0 auto; padding: 0 20px;">
            
            <!-- Category Filters -->
            <div class="category-filters" style="display: flex; justify-content: center; gap: 15px; margin-bottom: 40px; flex-wrap: wrap;">
                <a href="catalog.php" class="btn <?= !$category_filter ? 'btn-primary' : 'btn-outline' ?>" style="border-radius: 50px;">ALL</a>
                <a href="catalog.php?category=sand" class="btn <?= $category_filter == 'sand' ? 'btn-primary' : 'btn-outline' ?>" style="border-radius: 50px;">SAND</a>
                <a href="catalog.php?category=aggregate" class="btn <?= $category_filter == 'aggregate' ? 'btn-primary' : 'btn-outline' ?>" style="border-radius: 50px;">AGGREGATES</a>
                <a href="catalog.php?category=stone" class="btn <?= $category_filter == 'stone' ? 'btn-primary' : 'btn-outline' ?>" style="border-radius: 50px;">STONES</a>
            </div>

            <div class="product-grid"
                style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 30px;">

                <?php if ($result && $result->num_rows > 0): ?>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <div class="product-card">
                            <div class="product-image">
                                <img src="<?= $row['image_url'] ?>" alt="<?= $row['name'] ?>">
                            </div>
                            <div class="product-details">
                                <div
                                    style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 10px;">
                                    <h3 style="margin: 0; font-size: 1.25rem;"><?= $row['name'] ?></h3>
                                    <span class="badge badge-info"
                                        style="font-size: 0.7rem;"><?= strtoupper($row['category']) ?></span>
                                </div>
                                <p style="color: var(--text-medium); font-size: 0.9rem; margin-bottom: 20px; line-height: 1.5;">
                                    <?= $row['description'] ?>
                                </p>

                                <div class="price-action"
                                    style="display: flex; justify-content: space-between; align-items: center; border-top: 1px solid var(--border-color); padding-top: 20px;">
                                    <div class="price">
                                        <small
                                            style="color: var(--text-light); text-transform: uppercase; font-size: 0.75rem; font-weight: 600;">
                                            Price per Ton
                                        </small>
                                        <div style="font-size: 1.4rem; font-weight: 700; color: var(--primary-navy);">₹
                                            <?= number_format($row['price_per_ton']) ?>
                                        </div>
                                    </div>
                                    <button
                                        onclick="addToCart(<?= $row['id'] ?>, '<?= htmlspecialchars($row['name']) ?>', <?= $row['price_per_ton'] ?>, '<?= $row['image_url'] ?>')"
                                        class="btn btn-outline" style="border-width: 2px;">
                                        ADD TO CART
                                    </button>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <p>No products available at the moment.</p>
                <?php endif; ?>

            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer
        style="background: var(--primary-navy-dark); color: white; padding: 40px 0; margin-top: 60px; text-align: center;">
        <p>&copy; <?= date('Y') ?> KR BLUE METALS. All rights reserved.</p>
    </footer>

    <script src="assets/js/app.js"></script>
</body>

</html>