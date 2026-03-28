<?php
session_start();
require_once '../../includes/db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Simple Role Check (In real app, hash verify)
    $sql = "SELECT * FROM users WHERE email = ? AND role = 'driver'";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();

        // Verify Password (Supports both hashed passwords and legacy plain text)
        if (password_verify($password, $user['password']) || $password === $user['password']) {
            $_SESSION['driver_id'] = $user['id'];
            $_SESSION['driver_name'] = $user['name'];
            
            // Sync with main app session
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['user_role'] = 'driver';

            header("Location: dashboard.php");
            exit;
        } else {
            $error = "Invalid password";
        }
    } else {
        $error = "User not found";
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Driver Login | KR BLUE METALS</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    <script src="../../assets/js/app.js"></script>
    <style>
        body {
            background: var(--primary-navy);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100vh;
            margin: 0;
        }

        .login-card {
            background: white;
            color: var(--text-dark);
            padding: 30px;
            border-radius: 4px;
            width: 100%;
            max-width: 350px;
            text-align: center;
        }
    </style>
</head>

<body>
    <div class="login-card">
        <img src="../../assets/img/logo.png" alt="Logo" style="height: 60px; margin-bottom: 20px;">
        <h2 style="color: var(--primary-navy); margin-bottom: 20px;">Driver Portal</h2>

        <?php if (isset($error)): ?>
            <div style="color: red; margin-bottom: 10px;"><?= $error ?></div>
        <?php endif; ?>

        <form method="POST">
            <div style="margin-bottom: 15px; text-align: left;">
                <label>Email ID</label>
                <input type="email" name="email" class="form-control" required
                    style="width:100%; padding:10px; margin-top:5px; border:1px solid #ccc;">
            </div>
            <div style="margin-bottom: 20px; text-align: left;">
                <label>Password</label>
                <div class="password-wrapper" style="margin-top:5px;">
                    <input type="password" name="password" class="form-control" required
                        style="width:100%; padding:10px; border:1px solid #ccc;">
                    <i class="ph ph-eye password-toggle-icon" onclick="togglePasswordVisibility(this)"></i>
                </div>
            </div>
            <button type="submit" class="btn btn-primary"
                style="width:100%; justify-content:center; padding:12px;">LOGIN</button>
        </form>
        <div style="margin-top: 20px; font-size: 0.8rem; color: #666;">
            Demo: test_driver@krbluemetals.com / password123
        </div>
    </div>
</body>

</html>