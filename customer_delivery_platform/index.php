<?php
require_once 'includes/db_connect.php';

// Fetch 3 featured products securely
$sql = "SELECT * FROM products WHERE stock_status != 'out_of_stock' LIMIT 3";
$featured_result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>KR BLUE METALS | Construction Material Delivery</title>
    <link rel="stylesheet" href="assets/css/style.css?v=<?php echo time(); ?>_2">
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
</head>

<body>

    <?php include 'includes/header.php'; ?>


    <!-- Hero Slider Section -->
    <div class="hero-slider-wrapper">
        <div class="hero-slider-container">
            <!-- Slide 1 -->
            <div class="hero-slide active" style="background-image: url('assets/img/slider-1.jpg');">
                <div class="slide-content">
                    <h1>QUALITY IS OUR FIRST PRIORITY</h1>
                    <p>Industrial grade Blue Metal Aggregates derived from the finest quarries.</p>
                </div>
            </div>
            
            <!-- Slide 2 -->
            <div class="hero-slide" style="background-image: url('assets/img/slider-2.jpg');">
                <div class="slide-content">
                    <h1>PREMIUM M-SAND & P-SAND</h1>
                    <p>Washed and graded sand for superior construction strength.</p>
                </div>
            </div>
            
            <!-- Slide 3 -->
            <div class="hero-slide" style="background-image: url('assets/img/slider-3.jpg');">
                <div class="slide-content">
                    <h1>RELIABLE MATERIAL DELIVERY</h1>
                    <p>Ensuring timely delivery of construction materials across Tamilnadu.</p>
                </div>
            </div>
            
            <!-- Navigation -->
            <button class="slider-nav prev-slide" onclick="changeSlide(-1)">&#10094;</button>
            <button class="slider-nav next-slide" onclick="changeSlide(1)">&#10095;</button>
            
            <!-- Dots -->
            <div class="slider-dots">
                <div class="dot active" onclick="goToSlide(0)"></div>
                <div class="dot" onclick="goToSlide(1)"></div>
                <div class="dot" onclick="goToSlide(2)"></div>
            </div>
        </div>
    </div>

    <!-- Trust Metrics Cards (Overlapping Hero) -->
    <section class="trust-cards-section">
        <div class="container trust-cards-grid">
            <div class="trust-card">
                <div class="trust-card-icon"><i class="ph-fill ph-seal-check"></i></div>
                <h3>100% PWD Approved</h3>
                <p>Stringent quality tests ensuring perfect cubical shape and gradation for our aggregates and sand.</p>
            </div>
            <div class="trust-card">
                <div class="trust-card-icon"><i class="ph-fill ph-truck"></i></div>
                <h3>20+ Active Fleet</h3>
                <p>In-house transportation ensuring timely delivery of construction materials across Tamilnadu.</p>
            </div>

        </div>
    </section>

    <!-- Features -->
    <!-- Quality Section -->
    <section class="feature-section quality-section">
        <div class="feature-image">
            <img src="assets/img/quality-lab.jpg" alt="Quality Testing Lab" onerror="this.src='assets/img/slider-1.jpg'">
        </div>
        <div class="feature-content">
            <h2>Quality</h2>
            <p style="margin-bottom: 20px;">Our company has integrated an internal testing process to verify product quality. Quality check is stringent in terms of Strength, Shape and Grade to ensure the quality of aggregates, M-Sand and P-Sand.</p>
            <ul>
                <li>We have obtained PWD approval for M-Sand and P-Sand with no compromise quality.</li>
                <li>State-of-the-art VSI technology ensures perfect cubical shape.</li>
                <li>Running rigorous sieve analysis to maintain consistent gradation.</li>
            </ul>
        </div>
    </section>

    <!-- Transportation Section -->
    <section class="feature-section transport-section">
        <div class="feature-image">
            <img src="assets/img/transport-real.jpg" alt="Transport Fleet Tiper Truck">
        </div>
        <div class="feature-content">
            <h2>Transportation</h2>
            <p style="margin-bottom: 20px;">KR Blue Metals is highly transparent in Transportation. We have employed 20+ vehicles for transportation of our products to various areas across Tamilnadu.</p>
            <ul>
                <li>We have ensured that all our manufacturing plants are easily accessible to Major Demographics.</li>
                <li>Our in-house transportation adds to the reliability factor, independence and aids in timely delivery.</li>

            </ul>
        </div>
    </section>

    <!-- Featured Products Section -->
    <section class="catalog-section" style="padding: 60px 0; background: #f8f9fa;">
        <div class="container" style="max-width: 1200px; margin: 0 auto; padding: 0 20px;">
            <div style="text-align: center; margin-bottom: 40px;">
                <h2 style="font-size: 2rem; color: var(--primary-navy-dark);">Featured Materials</h2>
                <p style="color: var(--text-medium);">Premium quality aggregates ready for immediate delivery.</p>
            </div>
            
            <div class="product-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 30px;">
                <?php if ($featured_result && $featured_result->num_rows > 0): ?>
                    <?php while ($row = $featured_result->fetch_assoc()): ?>
                        <div class="product-card">
                            <div class="product-image">
                                <img src="<?= $row['image_url'] ?>" alt="<?= $row['name'] ?>" onerror="this.src='assets/img/slider-1.jpg'">
                            </div>
                            <div class="product-details">
                                <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 10px;">
                                    <h3 style="margin: 0; font-size: 1.25rem;"><?= htmlspecialchars($row['name']) ?></h3>
                                    <span class="badge badge-info" style="font-size: 0.7rem;"><?= strtoupper($row['category']) ?></span>
                                </div>
                                <p style="color: var(--text-medium); font-size: 0.9rem; margin-bottom: 20px; line-height: 1.5;">
                                    <?= htmlspecialchars($row['description']) ?>
                                </p>
                                <div class="price-action" style="margin-top: auto; display: flex; justify-content: space-between; align-items: center; border-top: 1px solid var(--border-color); padding-top: 20px;">
                                    <div class="price">
                                        <small style="color: var(--text-light); text-transform: uppercase; font-size: 0.75rem; font-weight: 600;">Price per Ton</small>
                                        <div style="font-size: 1.4rem; font-weight: 700; color: var(--primary-navy);">₹<?= number_format($row['price_per_ton']) ?></div>
                                    </div>
                                    <button onclick="addToCart(<?= $row['id'] ?>, '<?= htmlspecialchars($row['name']) ?>', <?= $row['price_per_ton'] ?>, '<?= $row['image_url'] ?>')" class="btn btn-outline" style="border-width: 2px;">
                                        ADD TO CART
                                    </button>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <p style="text-align: center; width: 100%;">No featured products available at the moment.</p>
                <?php endif; ?>
            </div>
            
            <div style="text-align: center; margin-top: 40px;">
                <a href="catalog.php" class="btn btn-primary" style="padding: 12px 30px; font-size: 1.1rem; border-radius: 4px;">View Full Catalog</a>
            </div>
        </div>
    </section>

    <!-- Director's Message -->
    <section class="director-section">
        <div class="director-content">
            <h2>Director's Message</h2>
            <p>
                Globally, the perpetual growth in construction sector has made mining an important sector shattering barriers, providing utmost importance to the products... Aggregates, M-Sand and P-Sand, which are the major contributors to construction industry. Owing to various reasons and consequences, river sand is completely not available across the globe.
            </p>
            <p>
                With the construction sectors booming in the Domestic, Commercial, Industrial and Public sectors; the need for an alternative sand came to the forefront. Even though import of River Sand is rare, the supply could not match the huge demand in the Market. This huge demand instituted the need of M-Sand and P-Sand to be produced locally.
            </p>
            <p>
                At this juncture, understanding the social perspective and the industrial demand, KR Blue Metals ventured into the production of aggregates, M-Sand and P-Sand to be employed into end-use applications in the construction sectors. We have set foot in this industry because we have understood the business and the hurdles very well and so we are now in the phase of moving towards our goal of harvesting the nation’s leading player.
            </p>
            <p style="font-weight: 600; margin-top: 30px; font-style: italic;">
                - The Management, KR Blue Metals
            </p>
        </div>
    </section>

    <!-- Our Team Section -->
    <section class="team-section">
        <div class="team-content">
            <h2>Our Team</h2>
            <p>We have to our credit a very strong team with a wide industrial experience in mining and production of aggregates, M-Sand and P-Sand. Our prowess in this field for more than a decade has honed us master the processes and to set up a stringent quality control unit to monitor the quality of products.</p>
            <p>Our team is very strong with dedication and commitment, fostering interpersonal growth, bestowing complementary strengths thereby making it an efficient one.</p>
            <p>Over the years, we have evolved by understanding capabilities and assigning the most suitable processes to the pertinent people to make the production process more efficient.</p>
        </div>
    </section>

    <!-- Home Footer Widgets (Vision/Mission) -->
    <section class="home-footer-widgets">
        <div class="footer-grid">
            <div class="footer-widget">
                <h3>Our Vision</h3>
                <p>We are the pioneers of mining industry across South India with a strong infrastructure to produce all mining products with unparalleled quality. We intend to become the distinctive world class mining company by expanding our footprint across the nation with utmost significance to quality in products and in services.</p>
            </div>
            <div class="footer-widget">
                <h3>Our Mission</h3>
                <p>Our entire team works with a mission of generating value from our mining assets, delivering the highest quality products persistently by adopting safe and responsible methods of development, exploration and safety. Our strong infrastructure and our passion towards the business aids us in producing the best quality at all times at a reasonable price, respecting the social and environmental values.</p>
            </div>
        </div>
    </section>

    <!-- Our Strengths Section -->
    <section class="strengths-section">
        <div class="container" style="max-width: 1200px; margin: 0 auto;">
            <h2>Our Strengths</h2>
            <div class="strengths-grid">
                <div class="strength-card">
                    <i class="ph-duotone ph-handshake"></i>
                    <h3>Procurement Strategy</h3>
                    <p>We have an innovative plan and robust execution strategy enabling smooth procurement of raw materials that helps us create sustainable value for our clients.</p>
                </div>
                <div class="strength-card">
                    <i class="ph-duotone ph-shield-check"></i>
                    <h3>Safety & Administration</h3>
                    <p>Our workplace emphasizes total safety and seamless administration. We operate under stringent safety norms ensuring zero-hazard environments for our employees.</p>
                </div>
                <div class="strength-card">
                    <i class="ph-duotone ph-lightning"></i>
                    <h3>Resource Management</h3>
                    <p>Safeguarding energy resources is our priority. We employ eco-friendly practices and energy-efficient machinery to minimize our carbon footprint.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Fat Footer -->
    <footer class="fat-footer">
        <div class="container footer-grid-3">
            <!-- Column 1: About -->
            <div class="footer-col">
                <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 20px;">
                    <img src="assets/img/logo.png" alt="KR Blue Metals Logo" style="height: 50px;">
                    <h3 style="margin: 0; color: white;">KR BLUE METALS</h3>
                </div>
                <p style="color: rgba(255,255,255,0.8); line-height: 1.6; font-size: 0.95rem;">
                    We are the pioneers of the mining industry across South India, committed to delivering unparalleled quality aggregates and sand for major construction projects.
                </p>
                <div style="display: flex; gap: 15px; margin-top: 20px;">
                    <a href="#" style="color: white; font-size: 1.2rem;"><i class="ph-fill ph-facebook-logo"></i></a>
                    <a href="#" style="color: white; font-size: 1.2rem;"><i class="ph-fill ph-twitter-logo"></i></a>
                    <a href="#" style="color: white; font-size: 1.2rem;"><i class="ph-fill ph-linkedin-logo"></i></a>
                </div>
            </div>

            <!-- Column 2: Quick Links -->
            <div class="footer-col">
                <h3>Quick Links</h3>
                <ul>
                    <li><a href="index.php">Home</a></li>
                    <li><a href="catalog.php">Products</a></li>
                    <li><a href="about.php">About Us</a></li>
                    <li><a href="track_order.php">Track Order</a></li>
                </ul>
            </div>

            <!-- Column 3: Contact Info -->
            <div class="footer-col">
                <h3>Contact Us</h3>
                <ul>
                    <li style="display: flex; gap: 10px; align-items: start; color: rgba(255,255,255,0.8);">
                        <i class="ph-fill ph-map-pin" style="margin-top: 4px; color: var(--primary-orange);"></i>
                        123 Industrial Estate, Madurai, Tamil Nadu - 625020
                    </li>
                    <li style="display: flex; gap: 10px; align-items: center; color: rgba(255,255,255,0.8);">
                        <i class="ph-fill ph-phone" style="color: var(--primary-orange);"></i>
                        +91 98765 43210
                    </li>
                    <li style="display: flex; gap: 10px; align-items: center; color: rgba(255,255,255,0.8);">
                        <i class="ph-fill ph-envelope" style="color: var(--primary-orange);"></i>
                        sales@krbluemetals.com
                    </li>
                </ul>
            </div>
        </div>
        
        <div class="footer-bottom">
            <p>&copy; <?php echo date('Y'); ?> KR BLUE METALS. All rights reserved.</p>
        </div>
    </footer>

    <script src="assets/js/app.js?v=<?php echo time(); ?>"></script>
</body>

</html>
