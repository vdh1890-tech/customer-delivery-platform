<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up | KR BLUE METALS</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    <script src="assets/js/app.js"></script>
</head>
<body class="auth-body">
    <div class="auth-wrapper">
        <div class="auth-container">
            <div class="auth-header">
                <h2>Create Account</h2>
                <p>Join us to order materials easily</p>
            </div>

            <div id="msg" style="text-align: center; margin-bottom: 20px; display: none;"></div>

            <form id="signupForm">
                <div class="form-group">
                    <label>Full Name</label>
                    <input type="text" name="name" class="form-control" required placeholder="John Doe">
                </div>
                <div class="form-group">
                    <label>Email Address</label>
                    <input type="email" name="email" class="form-control" required placeholder="name@example.com">
                </div>
                <div class="form-group">
                    <label>Phone Number</label>
                    <input type="tel" name="phone" class="form-control" required placeholder="+91 98765 43210">
                </div>
                <div class="form-group">
                    <label>Delivery Address</label>
                    <textarea name="address" class="form-control" rows="3" required placeholder="Street address, City, Pincode"></textarea>
                </div>
                <div class="form-group">
                    <label>Password</label>
                    <div class="password-wrapper">
                        <input type="password" name="password" id="passwd" class="form-control" required placeholder="••••••••" oninput="validatePassword(this.value)">
                        <i class="ph ph-eye password-toggle-icon" onclick="togglePasswordVisibility(this)"></i>
                    </div>
                    <small id="password-error" style="color: red; display: none; margin-top: 5px;"></small>
                </div>
                <button type="submit" class="btn btn-primary" style="width: 100%; justify-content: center;">CREATE ACCOUNT</button>
            </form>


            <p style="text-align: center; margin-top: 20px;">
                Already have an account? <a href="login.php" style="color: var(--primary-orange); font-weight: 600;">Login</a>
                <br>
                <a href="index.php" style="color: var(--text-light); font-size: 0.9rem; margin-top: 10px; display: inline-block;">&larr; Back to Home</a>
            </p>
        </div>
    </div>

    <script>
        function validatePassword(val) {
            const errorElement = document.getElementById('password-error');
            if (val.length < 8) {
                errorElement.textContent = 'Password must be at least 8 characters.';
                errorElement.style.display = 'block';
                return false;
            }
            const hasUpper = /[A-Z]/.test(val);
            const hasDigit = /\d/.test(val);
            const hasSpecial = /[^A-Za-z0-9]/.test(val);
            if (!hasUpper || !hasDigit || !hasSpecial) {
                errorElement.textContent = 'Password must contain at least 1 uppercase letter, 1 digit, and 1 special character.';
                errorElement.style.display = 'block';
                return false;
            }
            errorElement.style.display = 'none';
            return true;
        }

        document.getElementById('signupForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const password = document.getElementById('passwd').value;
            if (!validatePassword(password)) {
                return;
            }

            const formData = new FormData(this);
            const data = Object.fromEntries(formData.entries());

            try {
                const response = await fetch('api/auth_register.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(data)
                });
                const result = await response.json();

                const msgDiv = document.getElementById('msg');
                msgDiv.style.display = 'block';
                if (result.success) {
                    msgDiv.style.color = 'green';
                    msgDiv.textContent = 'Account created successfully! Redirecting...';
                    setTimeout(() => window.location.href = 'login.php', 2000);
                } else {
                    msgDiv.style.color = 'red';
                    msgDiv.textContent = result.message;
                }
            } catch (error) {
                console.error('Error:', error);
            }
        });
    </script>
</body>
</html>
