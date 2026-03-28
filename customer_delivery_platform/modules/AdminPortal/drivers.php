<?php
require_once '../../includes/db_connect.php';
require_once 'includes/auth_check.php';

// Handle Add Driver
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_driver'])) {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $raw_password = $_POST['password'];

    if (strlen($raw_password) < 8 || !preg_match('/[A-Z]/', $raw_password) || !preg_match('/\d/', $raw_password) || !preg_match('/[^a-zA-Z\d]/', $raw_password)) {
        $error = "Password must contain at least 8 chars, 1 uppercase, 1 digit, and 1 special character.";
    } else {
        $password = password_hash($raw_password, PASSWORD_DEFAULT); // Hash the password

        // Check if email or phone exists
        $check = $conn->prepare("SELECT id FROM users WHERE email=? OR phone=?");
        $check->bind_param("ss", $email, $phone);
        $check->execute();
        if ($check->get_result()->num_rows > 0) {
            $error = "Driver with this Email or Phone already exists!";
        } else {
            $stmt = $conn->prepare("INSERT INTO users (name, email, phone, password, role) VALUES (?, ?, ?, ?, 'driver')");
            $stmt->bind_param("ssss", $name, $email, $phone, $password);
            if ($stmt->execute()) {
                $success = "Driver added successfully!";
            } else {
                $error = "Error adding driver: " . $conn->error;
            }
        }
    }
}

// Handle Delete Driver
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $conn->query("DELETE FROM users WHERE id=$id AND role='driver'");
    header("Location: drivers.php?msg=deleted");
    exit;
}

// Fetch Drivers
$result = $conn->query("SELECT * FROM users WHERE role='driver' ORDER BY id DESC");

require_once 'includes/header.php';
?>

<div class="section-title flex-between">
    <div style="display:flex; align-items:center; gap:15px;">
        <h2>Driver Management</h2>
        <span style="background:var(--primary-orange); color:white; padding:5px 12px; border-radius:20px; font-size:0.9rem; font-weight:bold;">
            Total: <?= $result->num_rows ?>
        </span>
    </div>
</div>

<!-- Add Driver Form -->
<div class="admin-card" style="margin-bottom: 30px; padding: 20px;">
    <h3 style="margin-top: 0; font-size: 1.1rem; border-bottom: 1px solid #eee; padding-bottom: 10px;">Register New
        Driver</h3>
    <?php if (isset($error))
        echo "<p style='color:red'>$error</p>"; ?>
    <?php if (isset($success))
        echo "<p style='color:green'>$success</p>"; ?>

    <form method="POST"
        style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; align-items: end;">
        <div>
            <label style="display:block; margin-bottom:5px; font-weight:600;">Full Name</label>
            <input type="text" name="name" class="form-control" required
                style="width:100%; padding:8px; border:1px solid #ccc;" placeholder="Enter Driver Name">
        </div>
        <div>
            <label style="display:block; margin-bottom:5px; font-weight:600;">Email</label>
            <input type="email" name="email" class="form-control" required
                style="width:100%; padding:8px; border:1px solid #ccc;" placeholder="driver@example.com">
        </div>
        <div>
            <label style="display:block; margin-bottom:5px; font-weight:600;">Phone</label>
            <input type="text" name="phone" class="form-control" required
                style="width:100%; padding:8px; border:1px solid #ccc;" placeholder="e.g. 9876543210">
        </div>
        <div>
            <label style="display:block; margin-bottom:5px; font-weight:600;">Password</label>
            <input type="text" name="password" class="form-control" required pattern="(?=.*[A-Z])(?=.*\d)(?=.*[^a-zA-Z0-9]).{8,}" title="Must contain at least 8 characters, 1 uppercase, 1 digit, and 1 special character."
                style="width:100%; padding:8px; border:1px solid #ccc;" placeholder="Enter Password">
        </div>
        <div>
            <button type="submit" name="add_driver" class="btn btn-primary" style="width:100%; height: 35px;">ADD
                DRIVER</button>
        </div>
    </form>
</div>

<!-- Reset Password Logic -->
<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reset_password_id'])) {
    $driver_id = $_POST['reset_password_id'];
    $new_pass = $_POST['new_password'];
    
    if (!empty($new_pass)) {
        if (strlen($new_pass) < 8 || !preg_match('/[A-Z]/', $new_pass) || !preg_match('/\d/', $new_pass) || !preg_match('/[^a-zA-Z\d]/', $new_pass)) {
            $error = "New password must contain at least 8 chars, 1 uppercase, 1 digit, and 1 special character.";
        } else {
            $hashed = password_hash($new_pass, PASSWORD_DEFAULT);
            $stmt_reset = $conn->prepare("UPDATE users SET password = ? WHERE id = ? AND role = 'driver'");
            $stmt_reset->bind_param("si", $hashed, $driver_id);
            
            if ($stmt_reset->execute()) {
                $success = "Password updated successfully!";
            } else {
                $error = "Error updating password.";
            }
        }
    }
}
?>

<!-- Drivers List -->
<div class="admin-card" style="padding: 0;">
    <div class="table-container">
        <table class="data-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Contact Info</th>
                    <th>Status</th>
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
                            <td><span class="badge badge-success">Active</span></td>
                            <td>
                                <div style="display: flex; gap: 10px;">
                                    <button onclick="openResetModal(<?= $row['id'] ?>, '<?= addslashes($row['name']) ?>')" 
                                            class="btn-action" style="color:var(--primary-navy); background:none; border:none; cursor:pointer;" title="Reset Password">
                                        <i class="ph ph-key"></i>
                                    </button>
                                    <a href="delete_item.php?type=driver&id=<?= $row['id'] ?>" class="btn-action" style="color:red;"
                                        onclick="return confirm('Remove this driver?')" title="Delete">
                                        <i class="ph ph-trash"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5" style="text-align: center; padding: 30px; color: #999;">No drivers registered.</td>
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
        <p style="color:gray; font-size:0.9rem; margin-bottom:15px;">Set new password for <strong id="reset-driver-name"></strong></p>
        
        <form method="POST">
            <input type="hidden" name="reset_password_id" id="reset-driver-id">
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
        document.getElementById('reset-driver-id').value = id;
        document.getElementById('reset-driver-name').innerText = name;
    }
    
    // Close on outside click
    window.onclick = function(event) {
        if (event.target == document.getElementById('resetModal')) {
            document.getElementById('resetModal').style.display = 'none';
        }
    }
</script>

<?php require_once 'includes/footer.php'; ?>