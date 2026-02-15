<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Electronics Store</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/responsive.css">
</head>
<body>
    <!-- Navigation Bar -->
    <nav class="navbar">
        <div class="container">
            <div class="navbar-brand">
                <h1>⚡ ElectroHub</h1>
            </div>
            <ul class="nav-menu">
                <li><a href="../index.php" class="nav-link">Home</a></li>
                <li><a href="login.php" class="nav-link">Login</a></li>
            </ul>
        </div>
    </nav>

    <!-- Register Container -->
    <div class="container" style="max-width: 600px; margin-top: 2rem; margin-bottom: 3rem;">
        <div class="p-4" style="background: white; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
            <h2 style="color: var(--secondary-color); text-align: center; margin-bottom: 2rem;">
                Create Your Account
            </h2>

            <form id="registerForm">
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                    <div class="form-group">
                        <label for="firstName">First Name</label>
                        <input type="text" id="firstName" name="firstName" required>
                        <div class="form-error error-firstName"></div>
                    </div>

                    <div class="form-group">
                        <label for="lastName">Last Name</label>
                        <input type="text" id="lastName" name="lastName" required>
                        <div class="form-error error-lastName"></div>
                    </div>
                </div>

                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input type="email" id="email" name="email" required>
                    <div class="form-error error-email"></div>
                </div>

                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username" required>
                    <div class="form-error error-username"></div>
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required>
                    <small style="color: #666;">
                        Min 8 characters, must include uppercase, lowercase, and number
                    </small>
                    <div class="form-error error-password"></div>
                </div>

                <div class="form-group">
                    <label for="confirmPassword">Confirm Password</label>
                    <input type="password" id="confirmPassword" name="confirmPassword" required>
                    <div class="form-error error-confirmPassword"></div>
                </div>

                <button type="submit" class="btn btn-primary btn-block">Create Account</button>
            </form>

            <div style="text-align: center; margin-top: 2rem;">
                <p>Already have an account? 
                    <a href="login.php" style="color: var(--primary-color); text-decoration: none; font-weight: bold;">
                        Login here
                    </a>
                </p>
            </div>
        </div>
    </div>

    <!-- Modals -->
    <div id="successModal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <h2>Success!</h2>
            <p id="successMessage"></p>
        </div>
    </div>

    <div id="errorModal" class="modal">
        <div class="modal-content modal-error">
            <span class="close">&times;</span>
            <h2>Error</h2>
            <p id="errorMessage"></p>
        </div>
    </div>

    <script src="../assets/js/common.js"></script>
    <script>
        document.getElementById('registerForm').addEventListener('submit', (e) => {
            e.preventDefault();

            const firstName = document.getElementById('firstName').value.trim();
            const lastName = document.getElementById('lastName').value.trim();
            const email = document.getElementById('email').value.trim();
            const username = document.getElementById('username').value.trim();
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirmPassword').value;

            // Validate
            const errors = validateForm({
                firstName: { required: true, value: firstName },
                lastName: { required: true, value: lastName },
                email: { required: true, value: email, type: 'email' },
                username: { required: true, value: username },
                password: { required: true, value: password, type: 'password' },
                confirmPassword: { required: true, value: confirmPassword }
            });

            if (password !== confirmPassword) {
                errors.confirmPassword = 'Passwords do not match';
            }

            if (Object.keys(errors).length > 0) {
                displayFormErrors(errors, 'registerForm');
                return;
            }

            // Send register request
            fetch(`${API_BASE}/auth.php?action=register`, {
                method: 'POST',
                credentials: 'same-origin',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    first_name: firstName,
                    last_name: lastName,
                    email: email,
                    username: username,
                    password: password
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showSuccess('Registration successful! Redirecting to login...');
                    setTimeout(() => {
                        window.location.href = 'login.php';
                    }, 1500);
                } else {
                    showError(data.message);
                }
            })
            .catch(error => {
                showError('Registration failed: ' + error.message);
            });
        });
    </script>
</body>
</html>
