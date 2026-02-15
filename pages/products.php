<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shop - Electronics Store</title>
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
            <ul class="nav-menu" id="navMenu">
                <li><a href="../index.php" class="nav-link">Home</a></li>
                <li class="dropdown">
                    <a href="#" class="nav-link dropdown-toggle">Categories</a>
                    <div class="dropdown-menu" id="categoryDropdown"></div>
                </li>
                <li><a href="products.php" class="nav-link active">Shop</a></li>
                <li><a href="cart.php" class="nav-link">🛒 Cart (<span id="cartCount">0</span>)</a></li>
                <li id="authLinks">
                    <a href="login.php" class="nav-link">Login</a>
                    <a href="register.php" class="nav-link">Register</a>
                </li>
                <li id="userLinks" style="display: none;">
                    <a href="orders.php" class="nav-link">Orders</a>
                    <a href="cart.php" class="nav-link">Cart</a>
                    <a href="#" id="logoutBtn" class="nav-link">Logout</a>
                </li>
            </ul>
        </div>
    </nav>

    <!-- Search & Filter Section -->
    <section class="search-section">
        <div class="container">
            <div class="search-container">
                <input type="text" id="searchInput" placeholder="Search products..." class="search-input">
                <input type="number" id="minPrice" placeholder="Min Price" class="filter-input" min="0">
                <input type="number" id="maxPrice" placeholder="Max Price" class="filter-input" min="0">
                <select id="searchCategory" class="filter-input">
                    <option value="">All Categories</option>
                </select>
                <button id="searchBtn" class="btn btn-secondary">Search</button>
            </div>
        </div>
    </section>

    <!-- Products Section -->
    <section class="featured-products">
        <div class="container">
            <h2>All Products</h2>
            <div class="products-grid" id="productsGrid"></div>
            <div id="paginationContainer"></div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="footer-content">
                <div class="footer-section">
                    <h3>About Us</h3>
                    <p>ElectroHub is your trusted online electronics store.</p>
                </div>
                <div class="footer-section">
                    <h3>Quick Links</h3>
                    <ul>
                        <li><a href="products.php">Products</a></li>
                        <li><a href="cart.php">Cart</a></li>
                    </ul>
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

    <script src="../assets/js/common.js"></script>
    <script>
        let currentPage = 1;
        let currentFilters = {};

        document.addEventListener('DOMContentLoaded', () => {
            loadProducts();
            setupSearch();
        });

        function loadProducts(page = 1) {
            currentPage = page;
            const container = document.getElementById('productsGrid');
            container.innerHTML = '<div class="loading"><div class="spinner"></div></div>';

            let url = `${API_BASE}/products.php?action=list&page=${page}`;

            // Apply filters
            if (currentFilters.search) {
                url = `${API_BASE}/products.php?action=search&q=${encodeURIComponent(currentFilters.search)}&page=${page}`;
            }
            if (currentFilters.minPrice) {
                url += `&min_price=${currentFilters.minPrice}`;
            }
            if (currentFilters.maxPrice) {
                url += `&max_price=${currentFilters.maxPrice}`;
            }
            if (currentFilters.category) {
                url = `${API_BASE}/products.php?action=by-category&id=${currentFilters.category}&page=${page}`;
            }

            console.log('Loading products from:', url);

            fetch(url, { credentials: 'same-origin' })
                .then(response => {
                    console.log('Response status:', response.status);
                    console.log('Response headers:', response.headers);
                    return response.text();
                })
                .then(text => {
                    console.log('Raw response text:', text);
                    
                    try {
                        const data = JSON.parse(text);
                        console.log('Parsed JSON data:', data);
                        
                        container.innerHTML = '';
                        
                        if (data.success && data.data && data.data.products && data.data.products.length > 0) {
                            data.data.products.forEach(product => {
                                const card = createProductCard(product);
                                container.appendChild(card);
                            });

                            // Add pagination
                            const paginationContainer = document.getElementById('paginationContainer');
                            paginationContainer.innerHTML = '';
                            const pagination = createPagination(
                                data.data.pagination.current_page,
                                data.data.pagination.total_pages,
                                loadProducts
                            );
                            paginationContainer.appendChild(pagination);
                        } else if (data.success) {
                            container.innerHTML = '<p class="text-center">No products found</p>';
                        } else {
                            console.error('API error:', data.message);
                            container.innerHTML = `<p class="text-center text-danger">Error: ${data.message || 'Unknown error'}</p>`;
                        }
                    } catch(e) {
                        console.error('JSON Parse Error:', e);
                        console.error('Raw response was:', text);
                        container.innerHTML = '<p class="text-center text-danger">Error loading products: Invalid server response. Check browser console for details.</p>';
                    }
                })
                .catch(error => {
                    console.error('Fetch error:', error);
                    container.innerHTML = `<p class="text-center text-danger">Error: ${error.message}</p>`;
                });
        }

        function setupSearch() {
            const searchBtn = document.getElementById('searchBtn');
            const searchInput = document.getElementById('searchInput');
            const searchCategory = document.getElementById('searchCategory');

            if (searchBtn) {
                searchBtn.addEventListener('click', performSearch);
            }

            if (searchInput) {
                searchInput.addEventListener('keypress', (e) => {
                    if (e.key === 'Enter') performSearch();
                });
            }

            if (searchCategory) {
                searchCategory.addEventListener('change', performSearch);
            }
        }

        function performSearch() {
            const searchInput = document.getElementById('searchInput');
            const minPrice = document.getElementById('minPrice');
            const maxPrice = document.getElementById('maxPrice');
            const searchCategory = document.getElementById('searchCategory');

            currentFilters = {
                search: searchInput ? searchInput.value : '',
                minPrice: minPrice ? minPrice.value : '',
                maxPrice: maxPrice ? maxPrice.value : '',
                category: searchCategory ? searchCategory.value : ''
            };

            loadProducts(1);
        }
    </script>
</body>
</html>
