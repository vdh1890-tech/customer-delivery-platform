<?php require_once 'includes/db_connect.php'; ?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Us | KR BLUE METALS</title>
    <link rel="stylesheet" href="assets/css/style.css?v=<?php echo time(); ?>">
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
</head>

<body>
    <?php include 'includes/header.php'; ?>


    <!-- Hero Section -->
    <div class="contact-hero">
        <div class="hero-overlay"></div>
        <div class="hero-content">
            <h1>Contact Us</h1>
            <p>Get in touch with KR BLUE METALS for all your industrial aggregate and sand needs.</p>
        </div>
    </div>

    <!-- Contact Grid -->
    <div class="container contact-section">
        <div class="contact-grid">
            <!-- Left Column: Contact Info -->
            <div class="contact-info-block">
                <h2>Get In Touch</h2>
                <p>We are here to answer any questions you may have about our products or services. Reach out to us and we'll respond as soon as we can.</p>

                <div class="info-list">
                    <div class="info-item">
                        <i class="ph-duotone ph-map-pin"></i>
                        <div>
                            <h3>Office Address</h3>
                            <p>123 Industrial Area, Mining Road<br>Coimbatore, Tamil Nadu 641001</p>
                        </div>
                    </div>
                    
                    <div class="info-item">
                        <i class="ph-duotone ph-phone"></i>
                        <div>
                            <h3>Phone Number</h3>
                            <p>+91 98765 43210<br>+91 98765 43211</p>
                        </div>
                    </div>

                    <div class="info-item">
                        <i class="ph-duotone ph-envelope-open"></i>
                        <div>
                            <h3>Email Address</h3>
                            <p>sales@krbluemetals.com<br>info@krbluemetals.com</p>
                        </div>
                    </div>

                    <div class="info-item">
                        <i class="ph-duotone ph-clock"></i>
                        <div>
                            <h3>Business Hours</h3>
                            <p>Mon - Sat: 8:00 AM - 6:00 PM<br>Sunday: Closed</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Column: Contact Form -->
            <div class="contact-form-block">
                <h2>Send Us A Message</h2>
                <form class="contact-form" action="#" method="POST">
                    <div class="form-group">
                        <label for="name">Full Name</label>
                        <input type="text" id="name" name="name" placeholder="John Doe" required>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="email">Email Address</label>
                            <input type="email" id="email" name="email" placeholder="john@example.com" required>
                        </div>
                        <div class="form-group">
                            <label for="phone">Phone Number</label>
                            <input type="tel" id="phone" name="phone" placeholder="+91 98765 43210" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="subject">Subject</label>
                        <select id="subject" name="subject" required>
                            <option value="">Select a Subject</option>
                            <option value="Aggregates Inquiry">Aggregates Inquiry</option>
                            <option value="M-Sand/P-Sand Inquiry">M-Sand / P-Sand Inquiry</option>
                            <option value="Bulk Order">Bulk Order</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="message">Message</label>
                        <textarea id="message" name="message" rows="5" placeholder="How can we help you?" required></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary btn-full">
                        <i class="ph-bold ph-paper-plane-tilt"></i> Send Message
                    </button>
                </form>
            </div>
        </div>
    </div>


    <footer style="background: var(--primary-navy-dark); color: white; padding: 40px 0; text-align: center;">
        <p>&copy; <?= date('Y') ?> KR BLUE METALS. All rights reserved.</p>
    </footer>
    <script src="assets/js/app.js"></script>
</body>

</html>