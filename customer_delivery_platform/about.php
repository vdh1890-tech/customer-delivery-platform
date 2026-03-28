<?php require_once 'includes/db_connect.php'; ?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About Us | KR BLUE METALS</title>
    <link rel="stylesheet" href="assets/css/style.css?v=<?php echo time(); ?>_3">
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
</head>

<body>
    <?php include 'includes/header.php'; ?>


    <!-- About Us Overlay Section -->
    <section class="about-overlay-section">
        <div class="about-card">
            <small>Who We Are</small>
            <h2>About <span>Us</span></h2>
            
            <p>
                KR Blue Metals was first established at Madurai in 2010 and started venturing into the mining sector with our first manufacturing plant. We began with a humble vision to provide high-quality construction materials to the local region.
            </p>
            
            <p>
                A second plant was set up in the year 2015 at Dingigul to meet the growing demand. Soon after, in 2018, with gained expertise and manual dexterity, we successfully commenced our third plant in Madurai and a fourth in Virudhunagar, expanding our footprint across Southern Tamil Nadu.
            </p>
            
            <p>
                Our company's fifth plant is expected to start production in Q1 2026, equipped with state-of-the-art Vertical Shaft Impactor (VSI) technology for superior aggregate shape and M-Sand quality.
            </p>
            
            <p>
                KR Blue Metals currently has an exceptional production capacity of <strong>1400 tonnes/hour</strong>.
            </p>

            <p>
                We have equipped ourselves with all the prerequisites and have started our journey towards our company’s vision of becoming the most trusted aggregate supplier in the region.
            </p>
        </div>
    </section>

    <!-- Footer -->
    <footer style="background: var(--primary-navy-dark); color: white; padding: 40px 0; text-align: center;">
        <p>&copy; <?= date('Y') ?> KR BLUE METALS. All rights reserved.</p>
    </footer>
    <script src="assets/js/app.js"></script>
</body>

</html>