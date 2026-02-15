<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Electronics Store - Premium Electronics</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/responsive.css">
</head>
<body>
    <!-- Navigation Bar -->
    <nav class="navbar">
        <div class="container">
            <div class="navbar-brand">
                <h1>⚡ ElectroHub</h1>
            </div>
            <ul class="nav-menu" id="navMenu">
                <li><a href="index.php" class="nav-link active">Home</a></li>
                <li class="dropdown">
                    <a href="#" class="nav-link dropdown-toggle">Categories</a>
                    <div class="dropdown-menu" id="categoryDropdown">
                        <!-- Categories loaded dynamically -->
                    </div>
                </li>
                <li><a href="pages/products.php" class="nav-link">Shop</a></li>
                <li><a href="pages/cart.php" class="nav-link">
                    <span class="cart-icon">🛒</span>
                    Cart (<span id="cartCount">0</span>)
                </a></li>
                <li id="authLinks">
                    <a href="pages/login.php" class="nav-link">Login</a>
                    <a href="pages/register.php" class="nav-link">Register</a>
                </li>
                <li id="userLinks" style="display: none;">
                    <a href="pages/orders.php" class="nav-link">Orders</a>
                    <a href="#" id="userProfile" class="nav-link">👤 <span id="userName">User</span></a>
                    <a href="#" id="adminLink" class="nav-link" style="display: none;">Admin</a>
                    <a href="#" id="logoutBtn" class="nav-link">Logout</a>
                </li>
            </ul>
            <div class="hamburger" id="hamburger">
                <span></span>
                <span></span>
                <span></span>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero" id="heroSection">
        <div class="hero-content">
            <h2>Welcome to ElectroHub</h2>
            <p>Your Destination for Premium Electronics</p>
            <a href="pages/products.php" class="btn btn-primary">Shop Now</a>
        </div>
    </section>

    <!-- Search Bar -->
    <section class="search-section">
        <div class="container">
            <div class="search-container">
                <input type="text" id="searchInput" placeholder="Search for products..." class="search-input">
                <input type="number" id="minPrice" placeholder="Min Price" class="filter-input" min="0">
                <input type="number" id="maxPrice" placeholder="Max Price" class="filter-input" min="0">
                <select id="searchCategory" class="filter-input">
                    <option value="">All Categories</option>
                </select>
                <button id="searchBtn" class="btn btn-secondary">Search</button>
            </div>
        </div>
    </section>

    <!-- Featured Products Section -->
    <section class="featured-products">
        <div class="container">
            <h2>Featured Products</h2>
            <div class="products-grid" id="featuredProducts">
                <!-- Products loaded dynamically -->
            </div>
        </div>
    </section>

    <!-- Categories Section -->
    <section class="categories-section">
        <div class="container">
            <h2>Shop by Category</h2>
            <div class="categories-grid" id="categoriesGrid">
                <!-- Categories loaded dynamically -->
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="footer-content">
                <div class="footer-section">
                    <h3>About Us</h3>
                    <p>ElectroHub is your trusted online electronics store offering premium products at competitive prices.</p>
                </div>
                <div class="footer-section">
                    <h3>Quick Links</h3>
                    <ul>
                        <li><a href="pages/products.php">Products</a></li>
                        <li><a href="pages/cart.php">Cart</a></li>
                        <li><a href="pages/orders.php">Orders</a></li>
                    </ul>
                </div>
                <div class="footer-section">
                    <h3>Contact</h3>
                    <p>Email: info@electrohub.com</p>
                    <p>Phone: 1-800-ELECTRONICS</p>
                </div>
                <div class="footer-section">
                    <h3>Follow Us</h3>
                    <div class="social-links">
                        <a href="#" >Facebook</a>
                        <a href="#" >Twitter</a>
                        <a href="#" >Instagram</a>
                    </div>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; 2026 ElectroHub. All rights reserved.</p>
            </div>
        </div>
    </footer>

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

    <script src="assets/js/common.js"></script>
    <script src="assets/js/index.js"></script>
    <script>
        // Page initialization and verification
        document.addEventListener('DOMContentLoaded', function() {
            // Verify that core functions are available
            if (typeof checkSession === 'undefined') {
                console.error('ERROR: common.js did not load properly');
            } else {
                checkSession();
            }
            
            // Log API base for debugging
            console.log('✓ Application initialized');
            console.log('API Base:', API_BASE);
            console.log('Page Path:', window.location.pathname);
        });
        
        // Error handler for global fetch failures
        window.addEventListener('error', function(event) {
            if (event.message.includes('JSON')) {
                console.error('JSON Parse Error - likely API response issue');
                console.error('Check DIAGNOSTIC.html or browser Network tab');
            }
        });
    </script>
</body>
</html>
