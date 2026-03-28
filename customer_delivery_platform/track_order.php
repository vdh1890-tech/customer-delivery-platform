<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Track your construction material order in real-time with Madurai Aggregates.">
    <title>Track Order | KR BLUE METALS</title>

    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/@phosphor-icons/web"></script>

    <link rel="stylesheet" href="assets/css/style.css?v=2">
    <link rel="stylesheet" href="assets/css/animations.css">

</head>

<body class="track-page">
    <?php include 'includes/header.php'; ?>

    <!-- Track Hero -->
    <section class="track-hero">
        <div class="container">
            <h1><i class="ph ph-map-pin"></i> Track Your Order</h1>
            <p>Enter your order ID to see real-time delivery status</p>
        </div>
    </section>

    <!-- Track Container -->
    <div class="track-container">
        <div class="search-card animate-fadeInUp">
            <form class="search-form" id="trackForm">
                <div class="form-group">
                    <input type="text" id="orderIdInput" class="form-control"
                        placeholder="Enter Order ID (e.g., MA12345678)" required>
                </div>
                <button type="submit" class="btn btn-primary">
                    <i class="ph-bold ph-magnifying-glass"></i>
                    Track
                </button>
            </form>

            <!-- Order Result -->
            <div class="order-result" id="orderResult">
                <!-- Will be populated by JS -->
            </div>

            <!-- Not Found State -->
            <div class="order-result" id="orderNotFound">
                <div class="not-found">
                    <div class="not-found-icon">📦</div>
                    <h3>Order Not Found</h3>
                    <p>We couldn't find an order with that ID. Please check and try again.</p>
                </div>
            </div>
        </div>

        <!-- Real-time tracking enabled -->
    </div>

    <!-- Footer -->
    <footer class="simple">
        <div class="container">
            <p>&copy; 2025 KR BLUE METALS. All rights reserved.</p>
        </div>
    </footer>

    <script src="assets/js/app.js"></script>
    <script>
        // Tracking script initialized

        // Track form handler
        document.getElementById('trackForm').addEventListener('submit', function (e) {
            e.preventDefault(); // Prevent default form submission

            async function trackOrder() {
                let input = document.getElementById('orderIdInput').value.trim(); // Use existing ID
                
                // Ensure proper formatting (adds ORD- prefix if missing)
                input = input.replace(/^#/, ''); // Remove # if user copied it
                if (input && !input.toUpperCase().startsWith('ORD-')) {
                    input = 'ORD-' + input;
                }
                input = input.toUpperCase();
                
                // Update input field visually to help user
                document.getElementById('orderIdInput').value = input;
                
                const resultDiv = document.getElementById('orderResult'); // Use existing ID
                const notFoundContainer = document.getElementById('orderNotFound'); // Use existing ID
                const searchBtn = document.querySelector('.search-card .btn-primary'); // Adjusted selector for the button

                // Hide both initially
                resultDiv.classList.remove('active');
                notFoundContainer.classList.remove('active');

                if (!input) {
                    // Assuming 'toast' is defined globally or imported
                    if (typeof toast !== 'undefined') {
                        toast.error('Please enter an Order ID or Phone Number');
                    } else {
                        alert('Please enter an Order ID or Phone Number');
                    }
                    return;
                }

                // Show loading state
                searchBtn.disabled = true;
                searchBtn.innerHTML = '<i class="ph ph-spinner ph-spin"></i>';
                resultDiv.innerHTML = '<div style="text-align:center; padding:40px;"><div class="spinner"></div><p>Searching for order...</p></div>';
                resultDiv.classList.add('active'); // Show loading state

                try {
                    const response = await fetch(`api/track_order.php?id=${input}`);
                    const data = await response.json();

                    if (data.success) {
                        resultDiv.innerHTML = renderOrderDetails(data.order);
                        resultDiv.classList.add('active');

                        // Show map if dispatched (Simulated map update)
                        if (data.order.status === 'dispatched' && typeof updateMapLocation === 'function') {
                            // In a real app, we'd use driver coordinates
                            updateMapLocation(9.9252, 78.1198);
                        }
                    } else {
                        resultDiv.classList.remove('active'); // Hide result div if not found
                        notFoundContainer.innerHTML = `
                            <div class="not-found">
                                <div class="not-found-icon">📦</div>
                                <h3>Order Not Found</h3>
                                <p>${data.message || "We couldn't find an order with that ID. Please check and try again."}</p>
                            </div>
                        `;
                        notFoundContainer.classList.add('active');
                    }
                } catch (error) {
                    console.error('Tracking Error:', error);
                    resultDiv.innerHTML = `<div class="alert alert-danger">Error fetching order details. Please try again.</div>`;
                    resultDiv.classList.add('active'); // Show error message
                } finally {
                    searchBtn.disabled = false;
                    searchBtn.innerHTML = '<i class="ph-bold ph-magnifying-glass"></i> Track'; // Restore original button text
                }
            }
            trackOrder(); // Call the async function
        });

        function renderOrderDetails(order) {
            // Format Items List
            let itemsHtml = '';
            if (Array.isArray(order.items)) {
                itemsHtml = order.items.map(item => `
                    <div>${item.name} <span class="text-muted">x ${item.quantity} Tons</span></div>
                `).join('');
            } else {
                itemsHtml = order.material || 'Construction Material';
            }

            // Render Timeline
            const timelineHtml = order.timeline.map(item => `
                <div class="timeline-item ${item.status}">
                    <div class="timeline-icon">
                        ${item.status === 'completed' ? '<i class="ph-bold ph-check"></i>' :
                    item.status === 'current' ? '<i class="ph-bold ph-truck"></i>' :
                        '<i class="ph ph-circle"></i>'}
                    </div>
                    <div class="timeline-content">
                        <h4>${item.title}</h4>
                        <p>${item.message}</p>
                        <div class="timeline-time">${item.time}</div>
                    </div>
                </div>
            `).join('');

            // Driver Section (Conditional)
            let driverHtml = '';
            if (order.driver) {
                driverHtml = `
                    <div class="driver-card animate-fadeInUp stagger-1">
                        <div class="driver-header">
                            <div class="driver-avatar">
                                <i class="ph ph-user"></i>
                            </div>
                            <div class="driver-info">
                                <h3>${order.driver.name}</h3>
                                <p>Vehicle: ${order.driver.vehicle || 'TN 58 XX 0000'}</p>
                                <div class="rating">
                                    <i class="ph-fill ph-star"></i>
                                    <span>4.8</span>
                                </div>
                            </div>
                            <div class="driver-actions">
                                <a href="tel:${order.driver.phone}" class="call-btn">
                                    <i class="ph-fill ph-phone"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                `;
            }

            return `
                <div class="tracking-details animate-fadeInUp">
                    <div class="order-header">
                        <div>
                            <h2>Order #${order.id}</h2>
                            <span class="status-badge ${order.status}">${order.status.toUpperCase()}</span>
                        </div>
                        <div class="order-meta">
                            <span><i class="ph ph-calendar-blank"></i> Placed recently</span>
                        </div>
                    </div>

                    <div class="tracking-layout">
                        <div class="timeline-section">
                            <div class="timeline">
                                ${timelineHtml}
                            </div>
                        </div>

                        <div class="map-section">
                           ${driverHtml}

                            <div class="info-grid">
                                <div class="info-block">
                                    <h5>Material</h5>
                                    <div style="font-size: 0.95rem;">${itemsHtml}</div>
                                </div>
                                <div class="info-block">
                                    <h5>Delivery Address</h5>
                                    <p>${order.address || 'Madurai'}</p>
                                </div>
                                <div class="info-block">
                                    <h5>Total Amount</h5>
                                    <p class="price">₹${parseInt(order.total).toLocaleString('en-IN')}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            `;
        }
    </script>

</body>

</html>