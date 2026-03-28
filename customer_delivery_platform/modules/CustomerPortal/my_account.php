<?php
// Ensure session is started in header.php
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Account | KR BLUE METALS</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    <style>
        .dashboard-container {
            max-width: 1000px;
            margin: 40px auto;
            padding: 20px;
        }
        .welcome-card {
            background: var(--primary-navy);
            color: white;
            padding: 30px;
            border-radius: var(--border-radius);
            margin-bottom: 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .orders-section {
            background: white;
            padding: 30px;
            border-radius: var(--border-radius);
            box-shadow: var(--shadow-sm);
        }
        .order-item {
            border-bottom: 1px solid var(--border-color);
            padding: 20px 0;
        }
        .order-item:last-child {
            border-bottom: none;
        }
        .status-badge {
            padding: 5px 10px;
            border-radius: 4px;
            font-size: 0.8rem;
            font-weight: 600;
            text-transform: uppercase;
        }
        .status-pending { background: #fef9c3; color: #854d0e; }
        .status-confirmed { background: #dbeafe; color: #1e40af; }
        .status-delivered { background: #dcfce7; color: #166534; }
    </style>
</head>
<body>
    <?php include '../../includes/header.php'; ?>

    <?php
    if (!isset($_SESSION['user_id'])) {
        header("Location: login.php");
        exit;
    }
    
    // Fetch user details locally if needed, or rely on SESSION
    require_once '../../includes/db_connect.php';
    $user_id = $_SESSION['user_id'];
    
    // Fetch Orders
    $orders_sql = "SELECT * FROM orders WHERE customer_id = $user_id ORDER BY created_at DESC";
    $orders_result = $conn->query($orders_sql);
    ?>

    <div class="dashboard-container">
        <div class="welcome-card">
            <div>
                <h2 style="color: white; margin-bottom: 5px;">Hello, <?php echo htmlspecialchars($_SESSION['user_name']); ?></h2>
                <p style="opacity: 0.8;">Welcome to your dashboard</p>
            </div>
            <a href="../../api/auth_logout.php" class="btn" style="color: white; border: 1px solid white; padding: 8px 20px; border-radius: 4px; text-decoration: none; transition: 0.3s; background: rgba(255,255,255,0.1);">Logout</a>
        </div>

        <?php
        // Fetch User Details
        $u_sql = "SELECT name, email, phone, address FROM users WHERE id = $user_id";
        $u_res = $conn->query($u_sql);
        $user = $u_res->fetch_assoc();
        ?>

        <!-- Profile Section -->
        <div class="orders-section" style="margin-bottom: 30px;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                <h3 style="margin: 0; color: var(--primary-navy);">Profile Details</h3>
                <button onclick="openEditModal()" class="btn btn-primary" style="padding: 8px 16px; font-size: 0.9rem;">
                    <i class="ph-bold ph-pencil-simple"></i> Edit Profile
                </button>
            </div>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; color: var(--text-medium);">
                <div>
                    <small>Full Name</small>
                    <div style="font-weight: 600; color: var(--primary-navy);"><?php echo htmlspecialchars($user['name']); ?></div>
                </div>
                <div>
                    <small>Email Address</small>
                    <div style="font-weight: 600; color: var(--primary-navy);"><?php echo htmlspecialchars($user['email']); ?></div>
                </div>
                <div>
                    <small>Phone Number</small>
                    <div style="font-weight: 600; color: var(--primary-navy);"><?php echo htmlspecialchars($user['phone']); ?></div>
                </div>
                <div>
                    <small>Address</small>
                    <div style="font-weight: 600; color: var(--primary-navy);"><?php echo htmlspecialchars($user['address']); ?></div>
                </div>
            </div>
        </div>

        <!-- Edit Profile Modal -->
        <div id="editProfileModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000; align-items: center; justify-content: center;">
            <div style="background: white; padding: 30px; border-radius: 8px; width: 90%; max-width: 500px; position: relative;">
                <button onclick="closeEditModal()" style="position: absolute; top: 15px; right: 15px; border: none; background: none; font-size: 1.5rem; cursor: pointer;">&times;</button>
                <h3 style="margin-bottom: 20px;">Edit Profile</h3>
                
                <div id="profileStatus" style="display:none; padding: 10px; margin-bottom: 15px; border-radius: 4px; font-size: 0.9rem;"></div>

                <form id="editProfileForm">
                    <div class="form-group" style="margin-bottom: 15px;">
                        <label style="display: block; margin-bottom: 5px; font-weight: 600;">Full Name</label>
                        <input type="text" name="name" value="<?php echo htmlspecialchars($user['name']); ?>" class="form-control" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px;" required>
                    </div>
                    <div class="form-group" style="margin-bottom: 15px;">
                        <label style="display: block; margin-bottom: 5px; font-weight: 600;">Email</label>
                        <input type="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" class="form-control" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px;" required>
                    </div>
                    <div class="form-group" style="margin-bottom: 15px;">
                        <label style="display: block; margin-bottom: 5px; font-weight: 600;">Phone</label>
                        <input type="tel" name="phone" value="<?php echo htmlspecialchars($user['phone']); ?>" class="form-control" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px;" required>
                    </div>
                    <div class="form-group" style="margin-bottom: 20px;">
                        <label style="display: block; margin-bottom: 5px; font-weight: 600;">Address</label>
                        <textarea name="address" class="form-control" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px;" rows="3" required><?php echo htmlspecialchars($user['address']); ?></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary" style="width: 100%; justify-content: center;">Save Changes</button>
                </form>
            </div>
        </div>

        <script>
            const modal = document.getElementById('editProfileModal');
            function openEditModal() { modal.style.display = 'flex'; }
            function closeEditModal() { modal.style.display = 'none'; }
            
            // Close if clicking outside
            window.onclick = function(event) {
                if (event.target == modal) closeEditModal();
            }

            document.getElementById('editProfileForm').addEventListener('submit', async function(e) {
                e.preventDefault();
                console.log("Submitting profile update...");
                
                const submitBtn = this.querySelector('button[type="submit"]');
                const originalText = submitBtn.innerText;
                submitBtn.innerText = "Saving...";
                submitBtn.disabled = true;

                const formData = new FormData(this);
                const data = Object.fromEntries(formData.entries());
                
                const statusDiv = document.getElementById('profileStatus');
                statusDiv.style.display = 'none';

                try {
                    const response = await fetch('../../api/update_profile.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify(data)
                    });
                    
                    const contentType = response.headers.get("content-type");
                    if (!contentType || !contentType.includes("application/json")) {
                        throw new Error("Server returned non-JSON response");
                    }

                    const result = await response.json();
                    
                    if (result.success) {
                        statusDiv.textContent = 'Profile updated successfully! Reloading...';
                        statusDiv.style.display = 'block';
                        statusDiv.style.backgroundColor = '#dcfce7'; // green-100
                        statusDiv.style.color = '#166534'; // green-800
                        statusDiv.style.border = '1px solid #bbf7d0';
                        
                        setTimeout(() => {
                            location.reload();
                        }, 1500);
                    } else {
                        statusDiv.textContent = result.message || "Unknown error";
                        statusDiv.style.display = 'block';
                        statusDiv.style.backgroundColor = '#fee2e2'; // red-100
                        statusDiv.style.color = '#dc2626'; // red-600
                        statusDiv.style.border = '1px solid #fecaca';
                        
                        submitBtn.innerText = originalText;
                        submitBtn.disabled = false;
                    }
                } catch (error) {
                    console.error('Error:', error);
                    statusDiv.textContent = 'An error occurred: ' + error.message;
                    statusDiv.style.display = 'block';
                    statusDiv.style.backgroundColor = '#fee2e2';
                    statusDiv.style.color = '#dc2626';
                    statusDiv.style.border = '1px solid #fecaca';

                    submitBtn.innerText = originalText;
                    submitBtn.disabled = false;
                }
            });
        </script>

        <div class="orders-section">
            <h3 style="margin-bottom: 20px; color: var(--primary-navy);">Order History</h3>
            
            <?php if ($orders_result->num_rows > 0): ?>
                <?php while($order = $orders_result->fetch_assoc()): ?>
                    <div class="order-item">
                        <div style="display: flex; justify-content: space-between; margin-bottom: 10px;">
                            <h4 style="margin: 0;">Order #<?php echo $order['id']; ?></h4>
                            <span class="status-badge status-<?php echo $order['status']; ?>"><?php echo $order['status']; ?></span>
                        </div>
                        <div style="color: var(--text-light); font-size: 0.9rem;">
                            <p>Date: <?php echo date('d M Y', strtotime($order['created_at'])); ?></p>
                            <p>Total: <strong>₹<?php echo number_format($order['total_amount'], 2); ?></strong></p>
                            <p>Address: <?php echo htmlspecialchars($order['delivery_address']); ?></p>
                        </div>
                        <div style="margin-top: 10px; display: flex; gap: 15px;">
                            <a href="../../track_order.php?id=<?php echo $order['id']; ?>" style="color: var(--primary-orange); font-weight: 600; font-size: 0.9rem;">Track Order &rarr;</a>
                            <a href="../AutomatedInvoice/invoice.php?id=<?php echo $order['id']; ?>" target="_blank" style="color: var(--primary-navy); font-weight: 600; font-size: 0.9rem; display: flex; align-items: center; gap: 4px;">
                                <i class="ph-bold ph-download-simple"></i> Invoice
                            </a>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p style="color: var(--text-light); text-align: center; padding: 20px;">No orders found. <a href="../../catalog.php" style="color: var(--primary-orange);">Start Shopping</a></p>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
