<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once 'includes/db_connect.php'; ?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout | KR BLUE METALS</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    
    <!-- Leaflet Configuration for Free Maps -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
</head>

<body>

    <?php include 'includes/header.php'; ?>

    <?php
    $userName = '';
    $userPhone = '';
    $userAddress = '';
    
    if (isset($_SESSION['user_id'])) {
        require_once 'includes/db_connect.php';
        $uid = $_SESSION['user_id'];
        $u_sql = "SELECT name, phone, address FROM users WHERE id = $uid";
        $u_res = $conn->query($u_sql);
        if ($u_res->num_rows > 0) {
            $u_data = $u_res->fetch_assoc();
            $userName = $u_data['name'];
            $userPhone = $u_data['phone'];
            $userAddress = $u_data['address'];
        }
    }
    ?>

    <div class="container" style="max-width: 1200px; margin: 0 auto; padding: 40px 20px;">
        <h1>Complete Your Order</h1>

        <div class="checkout-grid">
            <!-- Left: Form -->
            <div class="checkout-form">
                <form id="orderForm" onsubmit="return false;">
                    <section
                        style="background: white; padding: 30px; border-radius: 2px; box-shadow: var(--shadow-sm); margin-bottom: 30px;">
                        <h3 class="section-title">Delivery Details</h3>
                        <div class="form-group">
                            <label>Full Name</label>
                            <input type="text" name="name" class="form-control" required placeholder="Enter your name" value="<?php echo htmlspecialchars($userName); ?>">
                        </div>
                        <div class="form-group">
                            <label>Phone Number</label>
                            <input type="tel" name="phone" id="phoneInput" class="form-control" required 
                                placeholder="Enter your phone number" value="<?php echo htmlspecialchars($userPhone); ?>">
                        </div>
                        <div class="form-group">
                            <label>Delivery Address</label>
                            <textarea name="address" id="addressInput" class="form-control" rows="3" required
                                placeholder="Street, Area, City"><?php echo htmlspecialchars($userAddress); ?></textarea>
                        </div>
                        
                        <div class="form-group" style="margin-top: 20px;">
                            <label>Pinpoint Exact Location <span style="color:red; font-size:0.8rem;">* Drag pin to adjust</span></label>
                            <div id="checkoutMap" style="height: 250px; width: 100%; border-radius: 4px; border: 1px solid #ccc; z-index: 1;"></div>
                            <input type="hidden" name="lat" id="latInput" value="9.9252">
                            <input type="hidden" name="lng" id="lngInput" value="78.1198">
                        </div>
                    </section>
                </form>
            </div>

            <!-- Right: Summary -->
            <div class="order-summary">
                <h3 class="section-title">Order Summary</h3>
                <div id="cartItemsList">
                    <!-- Items injected by JS -->
                </div>

                <div class="summary-row">
                    <span>Subtotal</span>
                    <span id="subtotal">₹ 0</span>
                </div>
                <div class="summary-row">
                    <span>Delivery Charges</span>
                    <span class="badge badge-success">FREE</span>
                </div>
                <div class="summary-row total-row">
                    <span>Total</span>
                    <span id="total">₹ 0</span>
                </div>

    <div style="margin-top: 30px;">
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <button id="proceedToPayBtn" type="button" class="btn btn-primary"
                            style="width: 100%; justify-content: center; padding: 15px;">
                            PROCEED TO PAYMENT
                        </button>
                    <?php else: ?>
                        <a href="login.php" class="btn btn-primary"
                            style="width: 100%; justify-content: center; padding: 15px;">
                            LOGIN TO PLACE ORDER
                        </a>
                    <?php endif; ?>
                    <p style="text-align: center; margin-top: 15px; font-size: 0.85rem; color: var(--text-light);">
                        <i class="ph-fill ph-shield-check"></i> Secure Checkout
                    </p>
                    <div id="error-msg"
                        style="display:none; margin-top:10px; padding:10px; background:#fee2e2; color:#dc2626; border-radius:4px; font-size:0.9rem; text-align:center;">
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Payment Modal -->
    <div id="paymentModal" class="modal-overlay" style="display:none;">
        <div class="modal-content">
            <div class="modal-header">
                <span class="modal-title">Select Payment Method</span>
                <button type="button" class="close-modal" onclick="closePaymentModal()">&times;</button>
            </div>
            <div class="modal-body">
                <div style="margin-bottom: 20px; padding: 10px; background: #e8f0fe; color: #1a73e8; border-radius: 4px; font-size: 0.9rem; text-align: center;">
                    <strong>Amount to Pay: <span id="paymentAmountDisplay">₹0</span></strong>
                </div>

                <label class="payment-option selected" onclick="selectPayment('cod')">
                    <input type="radio" name="payment_method" value="cod" checked class="payment-radio">
                    <div class="payment-icon"><i class="ph-bold ph-money"></i></div>
                    <div class="payment-details">
                        <span class="payment-name">Cash on Delivery</span>
                        <span class="payment-desc">Pay when you receive</span>
                    </div>
                </label>
            </div>
            <div class="pay-btn-container">
                <button id="payNowBtn" type="button" class="btn btn-primary" onclick="processPayment()" 
                    style="width: 100%; justify-content: center; padding: 12px; font-size: 1rem;">
                    PAY NOW
                </button>
            </div>
        </div>
    </div>

    <script src="assets/js/app.js"></script>
    <script>
        // Global Error Handler to catch silent failures
        window.onerror = function (msg, url, line) {
            alert("System Error: " + msg + "\nLine: " + line);
            return false;
        };

        // Load Cart Data
        function initCheckoutRender() {
            // Wait for app.js to theoretically have loaded appCart
            // Instead of interval looping rapidly, wait exactly once for DOMContentLoaded
            // Or use a more resilient approach since app.js is loaded synchronously above.
            if (!window.appCart) {
                console.warn("Cart system not immediately ready, retrying once...");
                setTimeout(() => {
                    if (window.appCart) {
                        renderCart();
                    } else {
                        alert("Error: Cart system failed to load. Please refresh the page.");
                    }
                }, 100);
            } else {
                renderCart();
            }
        }

        function renderCart() {
            let cart = [];
            try {
                cart = window.appCart.getCart();
            } catch (e) {
                console.error("Cart retrieval error", e);
            }

            const container = document.getElementById('cartItemsList');
            const subtotalEl = document.getElementById('subtotal');
            const totalEl = document.getElementById('total');

            if (!cart || cart.length === 0) {
                container.innerHTML = '<p style="text-align:center; padding:20px;">Your cart is empty. <br><br><a href="catalog.php" class="btn btn-primary" style="display:inline-block; padding:8px 16px; font-size:0.9rem;">GO TO PRODUCT</a></p>';
                const btn = document.getElementById('proceedToPayBtn');
                if (btn) {
                    btn.disabled = true;
                    btn.innerText = "Cart is Empty";
                    btn.style.opacity = "0.6";
                    btn.style.cursor = "not-allowed";
                }
                subtotalEl.innerText = '₹ 0';
                totalEl.innerText = '₹ 0';
            } else {
                let total = 0;
                container.innerHTML = ''; // Clear previous content
                cart.forEach(item => {
                    const itemTotal = item.price * item.quantity;
                    total += itemTotal;
                    container.innerHTML += `
                        <div class="summary-row" style="margin-bottom: 15px; border-bottom: 1px dashed #eee; padding-bottom: 15px; align-items: flex-start;">
                            <div style="flex:1; display:flex; gap:10px;">
                                <img src="${item.image}" style="width:50px; height:50px; object-fit:cover; border-radius:4px;">
                                <div style="flex:1;">
                                    <div style="display:flex; justify-content:space-between; align-items:flex-start;">
                                        <div style="font-weight:600; font-size:0.9rem; margin-bottom:4px;">${item.name}</div>
                                        <div style="font-weight:600; font-size:0.95rem;">₹${itemTotal.toLocaleString()}</div>
                                    </div>
                                    
                                    <div style="display:flex; align-items:center; justify-content:space-between; margin-top:5px;">
                                        <div style="font-size:0.8rem; color:gray;">₹${item.price.toLocaleString()} / Ton</div>
                                        
                                        <!-- Quantity Controls -->
                                        <div style="display:flex; align-items:center; background:#f3f4f6; border-radius:4px; padding:2px;">
                                            <button onclick="window.appCart.updateQuantity(${item.id}, -1)" type="button" 
                                                style="border:none; background:none; padding:4px 8px; cursor:pointer; color:var(--text-medium); font-weight:bold;">
                                                &minus;
                                            </button>
                                            <span style="font-size:0.85rem; font-weight:600; min-width:20px; text-align:center;">${item.quantity}</span>
                                            <button onclick="window.appCart.updateQuantity(${item.id}, 1)" type="button" 
                                                style="border:none; background:none; padding:4px 8px; cursor:pointer; color:var(--primary-navy); font-weight:bold;">
                                                &plus;
                                            </button>
                                        </div>
                                    </div>
                                    <div style="text-align:right; margin-top:5px;">
                                         <button onclick="window.appCart.removeItem(${item.id})" type="button" 
                                            style="border:none; background:none; color:#dc2626; font-size:0.75rem; cursor:pointer; padding:0; text-decoration:underline;">
                                            Remove
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    `;
                });
                subtotalEl.innerText = '₹ ' + total.toLocaleString();
                totalEl.innerText = '₹ ' + total.toLocaleString();
            }
        }

        // Run Init
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', initCheckoutRender);
        } else {
            initCheckoutRender();
        }

        // Listen for Cart Updates to Re-render
        window.addEventListener('cartUpdated', renderCart);

        // Initialize Interaction
        document.addEventListener('DOMContentLoaded', () => {
            const btn = document.getElementById('proceedToPayBtn');
            if (btn) {
                btn.addEventListener('click', function () {
                    // Trigger Validations
                    const form = document.getElementById('orderForm');
                    if (form.reportValidity()) {
                        openPaymentModal();
                    }
                });
            }
        });

        /* --- Payment Modal Logic --- */
        function openPaymentModal() {
            // Get current total
            const total = window.appCart ? window.appCart.getTotal() : 0;
            document.getElementById('paymentAmountDisplay').innerText = '₹ ' + total.toLocaleString();
            document.getElementById('payNowBtn').innerText = 'CONFIRM ORDER';
            
            document.getElementById('paymentModal').style.display = 'flex';
        }

        function closePaymentModal() {
            document.getElementById('paymentModal').style.display = 'none';
        }

        function selectPayment(method) {
            // Visual Update
            document.querySelectorAll('.payment-option').forEach(el => el.classList.remove('selected'));
            const selectedInput = document.querySelector(`input[name="payment_method"][value="${method}"]`);
            if(selectedInput) {
                selectedInput.parentElement.classList.add('selected');
                selectedInput.checked = true;
            }
            
            // Update Text Button logic if needed (e.g. COD might say 'Place Order' instead of Pay)
            const btn = document.getElementById('payNowBtn');
            btn.innerText = 'CONFIRM ORDER';
        }

        function processPayment() {
            const btn = document.getElementById('payNowBtn');
            const originalText = btn.innerText;
            
            // Lock UI
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner"></span> Processing...';

            // Simulate Network Delay (2 seconds)
            setTimeout(() => {
                // Determine Success (Simulated always true for now)
                submitOrderReal();
            }, 2000);
        }


        // Actual Submission after "Payment"
        function submitOrderReal() {
            try {
                const form = document.getElementById('orderForm');
                const formData = new FormData(form);
                const paymentMethod = document.querySelector('input[name="payment_method"]:checked').value;

                const orderData = {
                    customer: {
                        name: formData.get('name'),
                        phone: formData.get('phone'),
                        address: formData.get('address'),
                        lat: formData.get('lat'),
                        lng: formData.get('lng')
                    },
                    items: (window.appCart ? window.appCart.items : []),
                    total: (window.appCart ? window.appCart.getTotal() : 0),
                    paymentMethod: paymentMethod
                };

                // Validate Cart again just in case
                if (orderData.items.length === 0 || orderData.total <= 0) {
                    alert("Your cart is empty!");
                    window.location.reload();
                    return;
                }

                const endpoint = 'api/place_order.php';

                // Submit to API
                fetch(endpoint, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(orderData)
                })
                .then(async res => {
                    const text = await res.text();
                    try {
                        return JSON.parse(text);
                    } catch (err) {
                        throw new Error("Server Error: " + text.substring(0, 100));
                    }
                })
                .then(data => {
                    if (data.success) {
                        // Close Modal
                        closePaymentModal();
                        
                        // Show Success Animation
                        showSuccessAnimation(data.order_id, orderData);
                        
                        // Clear Cart
                        localStorage.removeItem('kr_cart'); 
                    } else {
                        throw new Error(data.message || "Order Failed");
                    }
                })
                .catch(err => {
                    alert('Error: ' + err.message);
                    const btn = document.getElementById('payNowBtn');
                    btn.disabled = false;
                    btn.innerText = "TRY AGAIN";
                });

            } catch (error) {
                alert("Client Error: " + error.message);
            }
        }

        function showSuccessAnimation(orderId, orderData) {
            let itemListText = orderData.items.map(item => `- ${item.name} x ${item.quantity} (₹${(item.price * item.quantity).toLocaleString()})`).join('\n');
            let messageText = `*New Order Placed!*\n\n` +
                              `*Order ID:* #${orderId}\n` +
                              `*Customer Name:* ${orderData.customer.name}\n` +
                              `*Phone:* ${orderData.customer.phone}\n` +
                              `*Delivery Address:* ${orderData.customer.address}\n\n` +
                              `*Order Details:*\n${itemListText}\n\n` +
                              `*Total Value:* ₹${orderData.total.toLocaleString()}\n` +
                              `*Payment Mode:* Cash on Delivery\n\n` +
                              `Please confirm the order delivery.`;
            
            let waUrl = `https://wa.me/918754751890?text=${encodeURIComponent(messageText)}`;

            document.body.innerHTML = `
                <style>
                    @keyframes scaleIn {
                        0% { transform: scale(0); opacity: 0; }
                        100% { transform: scale(1); opacity: 1; }
                    }
                    @keyframes checkmark {
                        0% { stroke-dashoffset: 100; }
                        100% { stroke-dashoffset: 0; }
                    }
                    .success-container {
                        height: 100vh;
                        display: flex;
                        flex-direction: column;
                        align-items: center;
                        justify-content: center;
                        text-align: center;
                        background: white;
                        font-family: 'Inter', sans-serif;
                    }
                    .checkmark-circle {
                        width: 100px;
                        height: 100px;
                        border-radius: 50%;
                        display: flex;
                        align-items: center;
                        justify-content: center;
                        background: #e6f4ea; 
                        margin-bottom: 24px;
                        animation: scaleIn 0.5s ease-out forwards;
                    }
                    .checkmark-svg {
                        width: 60px;
                        height: 60px;
                    }
                    .checkmark-path {
                        fill: none;
                        stroke: #16a34a;
                        stroke-width: 5;
                        stroke-linecap: round;
                        stroke-linejoin: round;
                        stroke-dasharray: 100;
                        stroke-dashoffset: 100;
                        animation: checkmark 0.6s 0.4s ease-out forwards;
                    }
                    .order-title {
                        color: #1f2937;
                        font-size: 1.5rem;
                        font-weight: 700;
                        margin-bottom: 8px;
                        opacity: 0;
                        animation: scaleIn 0.5s 0.8s forwards;
                    }
                    .order-sub {
                        color: #6b7280;
                        margin-bottom: 30px;
                        opacity: 0;
                        animation: scaleIn 0.5s 1s forwards;
                    }
                </style>
                <div class="success-container">
                    <div class="checkmark-circle">
                        <svg class="checkmark-svg" viewBox="0 0 52 52">
                            <path class="checkmark-path" d="M14.1 27.2l7.1 7.2 16.7-16.8"/>
                        </svg>
                    </div>
                    <h1 class="order-title">Order Placed Successfully!</h1>
                    <p class="order-sub">Order ID: #${orderId}</p>
                    
                    
                    <div style="opacity: 0; animation: scaleIn 0.5s 1.2s forwards; display: flex; flex-direction: column; align-items: center; gap: 10px;">
                        <p style="margin-bottom: 10px; font-size: 0.95rem; font-weight: 500;">Please send your order details to Admin via WhatsApp</p>
                        <a href="${waUrl}" target="_blank" class="btn btn-primary" style="padding: 12px 30px; border-radius: 50px; background-color: #25D366; border-color: #25D366; color: white; display: flex; align-items: center; justify-content: center; gap: 8px; text-decoration: none; font-weight: bold; width: 100%; max-width: 320px;">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" viewBox="0 0 256 256"><path d="M187.58,144.84l-32-16a8,8,0,0,0-8,1.5l-19.24,16a53.64,53.64,0,0,1-29.45-29.45l16-19.24a8,8,0,0,0,1.5-8l-16-32a8,8,0,0,0-13.94-2.79,48.11,48.11,0,0,0-11.27,15A72,72,0,0,0,82,143.52A120.73,120.73,0,0,0,143.52,182a72,72,0,0,0,51.62,6.88,48.11,48.11,0,0,0,15-11.27A8,8,0,0,0,187.58,144.84ZM128,24A104,104,0,0,0,36.18,176.88L24.83,210.93a16,16,0,0,0,20.24,20.24l34.05-11.35A104,104,0,1,0,128,24Zm0,192a87.87,87.87,0,0,1-44.06-11.81,8,8,0,0,0-6.54-.67L44,214.61l11.09-33.27a8,8,0,0,0-.66-6.54A88,88,0,1,1,128,216Z"></path></svg> Notify Admin on WhatsApp
                        </a>
                        <a href="index.php" style="color: #6b7280; font-size: 0.9rem; text-decoration: underline; margin-top: 10px; display: inline-block;">Return to Home</a>
                    </div>
                </div>
            `;
        }

        // --- MAP INITIALIZATION ---
        document.addEventListener('DOMContentLoaded', () => {
            // Default center: Madurai
            const defaultLat = 9.9252;
            const defaultLng = 78.1198;
            
            const map = L.map('checkoutMap').setView([defaultLat, defaultLng], 13);
            
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>'
            }).addTo(map);
            
            // Custom red pin
            const redIcon = new L.Icon({
                iconUrl: 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-2x-red.png',
                shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/0.7.7/images/marker-shadow.png',
                iconSize: [25, 41],
                iconAnchor: [12, 41],
                popupAnchor: [1, -34],
                shadowSize: [41, 41]
            });

            // Draggable Marker
            const marker = L.marker([defaultLat, defaultLng], {draggable: true, icon: redIcon}).addTo(map);
            
            marker.on('dragend', function() {
                const position = marker.getLatLng();
                document.getElementById('latInput').value = position.lat;
                document.getElementById('lngInput').value = position.lng;
                
                // Reverse Geocoding to auto-fill text area
                fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${position.lat}&lon=${position.lng}`)
                    .then(res => res.json())
                    .then(data => {
                        if(data && data.display_name) {
                            document.getElementById('addressInput').value = data.display_name;
                        }
                    })
                    .catch(err => console.log("Geocoding failed", err));
            });

            // If browser supports geolocation, find user's current spot
            if ("geolocation" in navigator) {
                navigator.geolocation.getCurrentPosition(position => {
                    const lat = position.coords.latitude;
                    const lng = position.coords.longitude;
                    
                    map.setView([lat, lng], 15);
                    marker.setLatLng([lat, lng]);
                    
                    document.getElementById('latInput').value = lat;
                    document.getElementById('lngInput').value = lng;
                }, err => {
                    console.log("Geolocation denied or unavailable.");
                }, { timeout: 10000 });
            }
        });

    </script>
</body>

</html>