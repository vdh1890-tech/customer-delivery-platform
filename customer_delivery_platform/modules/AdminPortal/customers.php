<?php
require_once '../../includes/db_connect.php';
require_once 'includes/auth_check.php';

// Handle Add Customer (Optional, since they can self-register)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_customer'])) {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $raw_password = $_POST['password'];
    $address = $_POST['address'] ?? ''; // Customers have addresses

    if (strlen($raw_password) < 8 || !preg_match('/[A-Z]/', $raw_password) || !preg_match('/\d/', $raw_password) || !preg_match('/[^a-zA-Z\d]/', $raw_password)) {
        $error = "Password must contain at least 8 chars, 1 uppercase, 1 digit, and 1 special character.";
    } else {
        $password = password_hash($raw_password, PASSWORD_DEFAULT); // Hash the password

        // Check if email or phone exists
        $check = $conn->prepare("SELECT id FROM users WHERE email=? OR phone=?");
        $check->bind_param("ss", $email, $phone);
        $check->execute();
        if ($check->get_result()->num_rows > 0) {
            $error = "Customer with this Email or Phone already exists!";
        } else {
            $stmt = $conn->prepare("INSERT INTO users (name, email, phone, password, role, address) VALUES (?, ?, ?, ?, 'customer', ?)");
            $stmt->bind_param("sssss", $name, $email, $phone, $password, $address);
            if ($stmt->execute()) {
                $success = "Customer added successfully!";
            } else {
                $error = "Error adding customer: " . $conn->error;
            }
        }
    }
}

// Handle Reset Password
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reset_password_id'])) {
    $cust_id = $_POST['reset_password_id'];
    $new_pass = $_POST['new_password'];
    
    if (!empty($new_pass)) {
        if (strlen($new_pass) < 8 || !preg_match('/[A-Z]/', $new_pass) || !preg_match('/\d/', $new_pass) || !preg_match('/[^a-zA-Z\d]/', $new_pass)) {
            $pass_error = "New password must contain at least 8 chars, 1 uppercase, 1 digit, and 1 special character.";
        } else {
            $hashed = password_hash($new_pass, PASSWORD_DEFAULT);
            $stmt_reset = $conn->prepare("UPDATE users SET password = ? WHERE id = ? AND role = 'customer'");
            $stmt_reset->bind_param("si", $hashed, $cust_id);
            
            if ($stmt_reset->execute()) {
                $pass_success = "Password updated successfully!";
            } else {
                $pass_error = "Error updating password.";
            }
        }
    }
}

// Handle Delete Customer
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    // Optional: Check for existing orders before deleting to prevent data integrity issues?
    // For now, simple delete as requested.
    $conn->query("DELETE FROM users WHERE id=$id AND role='customer'");
    header("Location: customers.php?msg=deleted");
    exit;
}

// Fetch Customers
$result = $conn->query("SELECT * FROM users WHERE role='customer' ORDER BY id DESC");

require_once 'includes/header.php';
?>

<div class="section-title flex-between">
    <div style="display:flex; align-items:center; gap:15px;">
        <h2>Customer Management</h2>
        <span style="background:var(--primary-orange); color:white; padding:5px 12px; border-radius:20px; font-size:0.9rem; font-weight:bold;">
            Total: <?= $result->num_rows ?>
        </span>
    </div>
</div>

<?php if (isset($pass_success)) echo "<p class='alert alert-success'>$pass_success</p>"; ?>
<?php if (isset($pass_error)) echo "<p class='alert alert-danger'>$pass_error</p>"; ?>

<!-- Add Customer Form -->
<div class="admin-card" style="margin-bottom: 30px; padding: 20px;">
    <h3 style="margin-top: 0; font-size: 1.1rem; border-bottom: 1px solid #eee; padding-bottom: 10px;">Register New Customer</h3>
    <?php if (isset($error)) echo "<p style='color:red'>$error</p>"; ?>
    <?php if (isset($success)) echo "<p style='color:green'>$success</p>"; ?>

    <form method="POST" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; align-items: end;">
        <div>
            <label style="display:block; margin-bottom:5px; font-weight:600;">Full Name</label>
            <input type="text" name="name" class="form-control" required style="width:100%; padding:8px; border:1px solid #ccc;" placeholder="Enter Customer Name">
        </div>
        <div>
            <label style="display:block; margin-bottom:5px; font-weight:600;">Email</label>
            <input type="email" name="email" class="form-control" required style="width:100%; padding:8px; border:1px solid #ccc;" placeholder="customer@example.com">
        </div>
        <div>
            <label style="display:block; margin-bottom:5px; font-weight:600;">Phone</label>
            <input type="text" name="phone" class="form-control" required style="width:100%; padding:8px; border:1px solid #ccc;" placeholder="e.g. 9876543210">
        </div>
        <div>
            <label style="display:block; margin-bottom:5px; font-weight:600;">Password</label>
            <input type="text" name="password" class="form-control" required pattern="(?=.*[A-Z])(?=.*\d)(?=.*[^a-zA-Z0-9]).{8,}" title="Must contain at least 8 characters, 1 uppercase, 1 digit, and 1 special character." style="width:100%; padding:8px; border:1px solid #ccc;" placeholder="Enter Password">
        </div>
        <div style="grid-column: span 2;">
             <label style="display:block; margin-bottom:5px; font-weight:600;">Address</label>
             <input type="text" name="address" class="form-control" placeholder="Optional" style="width:100%; padding:8px; border:1px solid #ccc;">
        </div>
        <div>
            <button type="submit" name="add_customer" class="btn btn-primary" style="width:100%; height: 35px;">ADD CUSTOMER</button>
        </div>
    </form>
</div>

<!-- Customers List -->
<div class="admin-card" style="padding: 0;">
    <div class="table-container">
        <table class="data-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Contact Info</th>
                    <th>Address</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($result && $result->num_rows > 0): ?>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td>#<?= $row['id'] ?></td>
                            <td style="font-weight: 600;"><?= htmlspecialchars($row['name']) ?></td>
                            <td>
                                <div><i class="ph ph-envelope"></i> <?= htmlspecialchars($row['email']) ?></div>
                                <div><i class="ph ph-phone"></i> <?= htmlspecialchars($row['phone']) ?></div>
                            </td>
                            <td><small><?= htmlspecialchars(substr($row['address'], 0, 50)) . (strlen($row['address'])>50?'...':'') ?></small></td>
                            <td>
                                <div style="display: flex; gap: 10px;">
                                    <button onclick="openResetModal(<?= $row['id'] ?>, '<?= addslashes($row['name']) ?>')" 
                                            class="btn-action" style="color:var(--primary-navy); background:none; border:none; cursor:pointer;" title="Reset Password">
                                        <i class="ph ph-key"></i>
                                    </button>
                                    <a href="customers.php?delete=<?= $row['id'] ?>" class="btn-action" style="color:red;"
                                        onclick="return confirm('Remove this customer?')" title="Delete">
                                        <i class="ph ph-trash"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5" style="text-align: center; padding: 30px; color: #999;">No customers found.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Reset Password Modal -->
<div id="resetModal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); z-index:999; justify-content:center; align-items:center;">
    <div style="background:white; padding:25px; border-radius:8px; width:350px; box-shadow:0 10px 25px rgba(0,0,0,0.2);">
        <h3 style="margin-top:0; color:var(--primary-navy);">Reset Password</h3>
        <p style="color:gray; font-size:0.9rem; margin-bottom:15px;">Set new password for <strong id="reset-cust-name"></strong></p>
        
        <form method="POST">
            <input type="hidden" name="reset_password_id" id="reset-cust-id">
            <div style="margin-bottom: 20px;">
                <label style="display:block; margin-bottom:5px; font-size:0.9rem; font-weight:600;">New Password</label>
                <div style="position:relative;">
                    <input type="text" name="new_password" class="form-control" required pattern="(?=.*[A-Z])(?=.*\d)(?=.*[^a-zA-Z0-9]).{8,}" title="Must contain at least 8 characters, 1 uppercase, 1 digit, and 1 special character." style="width:100%; padding:10px; border:1px solid #ccc;" placeholder="Enter new password">
                </div>
            </div>
            
            <div style="display:flex; justify-content:flex-end; gap:10px;">
                <button type="button" onclick="document.getElementById('resetModal').style.display='none'" class="btn btn-outline" style="padding:8px 15px;">Cancel</button>
                <button type="submit" class="btn btn-primary" style="padding:8px 15px;">Update Password</button>
            </div>
        </form>
    </div>
</div>

<script>
    function openResetModal(id, name) {
        document.getElementById('resetModal').style.display = 'flex';
        document.getElementById('reset-cust-id').value = id;
        document.getElementById('reset-cust-name').innerText = name;
    }
    
    // Close on outside click
    window.onclick = function(event) {
        if (event.target == document.getElementById('resetModal')) {
            document.getElementById('resetModal').style.display = 'none';
        }
    }
</script>

<?php require_once 'includes/footer.php'; ?>
