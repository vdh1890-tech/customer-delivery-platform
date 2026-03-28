<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login | KR BLUE METALS</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Raleway:wght@700&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    <link rel="stylesheet" href="../../assets/css/style.css">
    <script src="../../assets/js/app.js"></script>
    <style>
        body {
            background-color: var(--primary-navy-dark);
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0;
        }
        .login-card {
            background: white;
            padding: 40px;
            border-radius: 4px;
            width: 100%;
            max-width: 400px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.2);
            text-align: center;
        }
        .login-logo {
            height: 80px;
            margin-bottom: 20px;
        }
        .form-group {
            margin-bottom: 20px;
            text-align: left;
        }
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: var(--primary-navy);
        }
        .form-control {
            width: 100%;
            padding: 12px;
            border: 1px solid var(--border-color);
            border-radius: 4px;
            font-size: 1rem;
            transition: border-color 0.2s;
        }
        .form-control:focus {
            border-color: var(--primary-orange);
            outline: none;
        }
        .error-msg {
            color: #dc2626;
            margin-bottom: 20px;
            font-size: 0.9rem;
            display: none;
        }
    </style>
</head>
<body>

    <div class="login-card">
        <img src="../../assets/img/logo.png" alt="KR Blue Metals" class="login-logo">
        <h2 style="color: var(--primary-navy); margin-bottom: 5px;">Admin Login</h2>
        <p style="color: var(--text-medium); margin-bottom: 30px; font-size: 0.9rem;">Secure Access Panel</p>

        <form id="adminLoginForm" onsubmit="handleLogin(event)">
            <div class="error-msg" id="errorMsg"></div>
            
            <div class="form-group">
                <label>Email Address</label>
                <div style="position: relative;">
                    <i class="ph-fill ph-envelope" style="position:absolute; left:12px; top:14px; color:#cbd5e1;"></i>
                    <input type="email" name="email" class="form-control" style="padding-left: 40px;" required placeholder="admin@krbluemetals.com">
                </div>
            </div>

            <div class="form-group">
                <label>Password</label>
                <div style="position: relative;">
                    <i class="ph-fill ph-lock-key" style="position:absolute; left:12px; top:14px; color:#cbd5e1;"></i>
                    <div class="password-wrapper">
                        <input type="password" name="password" class="form-control" style="padding-left: 40px;" required placeholder="••••••••">
                        <i class="ph ph-eye password-toggle-icon" onclick="togglePasswordVisibility(this)"></i>
                    </div>
                </div>
            </div>

            <button type="submit" id="loginBtn" class="btn btn-primary" style="width: 100%; justify-content: center; padding: 12px;">
                Authenticate
            </button> <!-- Reverted to simple button text -->
            
            <a href="../../index.php" style="display: block; margin-top: 20px; color: var(--text-light); font-size: 0.85rem;">
                &larr; Back to Website
            </a>
        </form>
    </div>

    <script>
        async function handleLogin(e) {
            e.preventDefault();
            const btn = document.getElementById('loginBtn');
            const errorDiv = document.getElementById('errorMsg');
            const form = document.getElementById('adminLoginForm');
            
            btn.innerHTML = '<i class="ph-bold ph-spinner ph-spin"></i> Verifying...';
            btn.disabled = true;
            errorDiv.style.display = 'none';

            const formData = new FormData(form);
            const data = Object.fromEntries(formData.entries());

            try {
                const response = await fetch('../../api/auth_login.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(data)
                });

                const result = await response.json();

                if (result.success) {
                    if (result.user.role === 'admin') {
                        window.location.href = 'dashboard.php';
                    } else {
                        throw new Error("Access Denied: Administrator privileges required.");
                    }
                } else {
                    throw new Error(result.message || "Login failed");
                }
            } catch (err) {
                errorDiv.textContent = err.message;
                errorDiv.style.display = 'block';
                btn.innerHTML = 'Authenticate';
                btn.disabled = false;
            }
        }
    </script>
</body>
</html>
