<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | KR BLUE METALS</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    <script src="../../assets/js/app.js"></script>
    <style>
        body {
            background-color: #0f172a; /* Fallback for primary-navy-dark */
            background-color: var(--primary-navy-dark, #0f172a);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0;
            padding: 0;
            font-family: 'Inter', sans-serif;
        }
        .auth-wrapper {
            width: 100%;
            padding: 20px;
            display: flex;
            justify-content: center;
            box-sizing: border-box;
        }
        .auth-container {
            width: 100%;
            max-width: 400px;
            background: white;
            padding: 40px;
            border-radius: 12px;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
        }
        select.form-control {
            appearance: none;
            -webkit-appearance: none;
            -moz-appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke='%2364748b'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M19 9l-7 7-7-7'%3E%3C/path%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 12px center;
            background-size: 16px;
            cursor: pointer;
        }
        .form-group {
            margin-bottom: 20px;
            text-align: left;
        }
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: var(--primary-navy);
            font-size: 0.9rem;
        }
        .form-control {
            width: 100%;
            padding: 12px;
            border: 1px solid var(--border-color);
            border-radius: 4px;
            font-size: 1rem;
            transition: 0.3s;
        }
        .form-control:focus {
            border-color: var(--primary-orange);
            outline: none;
            box-shadow: 0 0 0 3px rgba(255, 184, 0, 0.1);
        }
        .auth-header {
            text-align: center;
            margin-bottom: 30px;
        }
        .auth-header h2 {
            color: var(--primary-navy);
            margin-bottom: 5px;
            font-size: 1.8rem;
        }
        .auth-header p {
            color: var(--text-light);
            font-size: 0.95rem;
        }
    </style>
</head>
<body>
    <div class="auth-wrapper">
        <div class="auth-container">
            <div class="auth-header">
                <h2>Login</h2>
                <p>Select your role to continue</p>
            </div>
            
            <div id="error-msg" style="color: red; text-align: center; margin-bottom: 20px; display: none;"></div>

            <form id="loginForm">
                <div class="form-group">
                    <label>Select User Type</label>
                    <div style="position: relative;">
                         <i class="ph-fill ph-user-circle" style="position:absolute; left:12px; top:14px; color:#cbd5e1;"></i>
                        <select name="login_role" id="loginRole" class="form-control" style="padding-left: 40px;">
                            <option value="customer">Customer</option>
                            <option value="driver">Driver</option>
                            <option value="admin">Admin</option>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label>Email Address</label>
                    <div style="position: relative;">
                        <i class="ph-fill ph-envelope" style="position:absolute; left:12px; top:14px; color:#cbd5e1;"></i>
                        <input type="email" name="email" class="form-control" style="padding-left: 40px;" required placeholder="name@example.com">
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

                <button type="submit" class="btn btn-primary" style="width: 100%; justify-content: center;">LOGIN</button>
            </form>

            <p id="signupLink" style="text-align: center; margin-top: 20px;">
                Don't have an account? <a href="signup.php" style="color: var(--primary-orange); font-weight: 600;">Sign Up</a>
            </p>
            <p style="text-align: center; margin-top: 10px;">
                <a href="../../index.php" style="color: var(--text-light); font-size: 0.9rem; display: inline-block;">&larr; Back to Home</a>
            </p>
        </div>
    </div>

    <script>
        const loginRole = document.getElementById('loginRole');
        const signupLink = document.getElementById('signupLink');

        function toggleSignupLink() {
            if (loginRole.value === 'admin' || loginRole.value === 'driver') {
                signupLink.style.display = 'none';
            } else {
                signupLink.style.display = 'block';
            }
        }

        loginRole.addEventListener('change', toggleSignupLink);
        // Initial check
        toggleSignupLink();

        document.getElementById('loginForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            const data = Object.fromEntries(formData.entries());
            const selectedRole = document.getElementById('loginRole').value;

            try {
                const response = await fetch('../../api/auth_login.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(data)
                });
                const result = await response.json();

                if (result.success) {
                    // Role verification
                    if (result.user.role !== selectedRole) {
                        document.getElementById('error-msg').textContent = `Access Denied: This account is not a ${selectedRole} account.`;
                        document.getElementById('error-msg').style.display = 'block';
                        return;
                    }

                    // Redirection
                    if (selectedRole === 'admin') {
                        window.location.href = '../AdminPortal/dashboard.php';
                    } else if (selectedRole === 'driver') {
                        window.location.href = '../DriverPanel/dashboard.php';
                    } else {
                        window.location.href = '../../index.php';
                    }
                } else {
                    document.getElementById('error-msg').textContent = result.message;
                    document.getElementById('error-msg').style.display = 'block';
                }
            } catch (error) {
                console.error('Error:', error);
            }
        });
    </script>
</body>
</html>
