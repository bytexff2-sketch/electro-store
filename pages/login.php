<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Electronics Store</title>
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
                <li><a href="register.php" class="nav-link">Create Account</a></li>
            </ul>
        </div>
    </nav>

    <!-- Login Container -->
    <div class="container" style="max-width: 500px; margin-top: 3rem;">
        <div class="p-4" style="background: white; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
            <h2 style="color: var(--secondary-color); text-align: center; margin-bottom: 2rem;">
                Login to ElectroHub
            </h2>

            <form id="loginForm">
                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username" required>
                    <div class="form-error error-username"></div>
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required>
                    <div class="form-error error-password"></div>
                </div>

                <button type="submit" class="btn btn-primary btn-block">Login</button>
            </form>

            <div style="text-align: center; margin-top: 2rem;">
                <p>Don't have an account? 
                    <a href="register.php" style="color: var(--primary-color); text-decoration: none; font-weight: bold;">
                        Create one here
                    </a>
                </p>
            </div>

            <!-- Demo Credentials -->
            <div style="background: var(--light-color); padding: 1rem; border-radius: 4px; margin-top: 2rem;">
                
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
        document.getElementById('loginForm').addEventListener('submit', (e) => {
            e.preventDefault();

            const username = document.getElementById('username').value.trim();
            const password = document.getElementById('password').value;

            // Validate
            const errors = validateForm({
                username: { required: true, value: username },
                password: { required: true, value: password }
            });

            if (Object.keys(errors).length > 0) {
                displayFormErrors(errors, 'loginForm');
                return;
            }

            // Send login request
            fetch(`${API_BASE}/auth.php?action=login`, {
                method: 'POST',
                credentials: 'same-origin',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    username: username,
                    password: password
                })
            })
            .then(response => response.text())
            .then(text => {
                try {
                    const data = JSON.parse(text);
                    if (data.success) {
                        // Store user data immediately from login response
                        const userData = data.data || {};
                        
                        // Store session id for header-based session fallback
                        try {
                            if (userData.session_id) {
                                localStorage.setItem('session_id', userData.session_id);
                            }
                        } catch (e) {
                            console.debug('Could not store session_id:', e);
                        }

                        // Immediately store role and other user data in sessionStorage
                        try {
                            if (userData.user_id) {
                                sessionStorage.setItem('user_id', userData.user_id);
                                sessionStorage.setItem('username', userData.username);
                                sessionStorage.setItem('user_role', userData.role || 'customer');
                                console.log('User data stored in sessionStorage - Role:', userData.role);
                            }
                        } catch (e) {
                            console.error('Could not store user data to sessionStorage:', e);
                        }

                        showSuccess('Login successful! Redirecting...');
                        
                        // Determine redirect immediately based on role from login response
                        setTimeout(() => { 
                            const userRole = userData.role || sessionStorage.getItem('user_role');
                            if (userRole === 'admin') {
                                console.log('Admin logged in (' + userData.username + '), redirecting to admin dashboard');
                                window.location.href = '../admin/dashboard.php';
                            } else {
                                console.log('Customer logged in (' + userData.username + '), redirecting to home');
                                window.location.href = '../index.php';
                            }
                        }, 500);
                    } else {
                        showError(data.message || 'Login failed');
                    }
                } catch(e) {
                    console.error('JSON Parse Error:', e);
                    console.error('Response:', text);
                    showError('Login failed: Invalid server response. Check browser console.');
                }
            })
            .catch(error => {
                showError('Login failed: ' + error.message);
            });
        });
    </script>
</body>
</html>
