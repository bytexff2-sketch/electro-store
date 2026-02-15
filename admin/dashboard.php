<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Electronics Store</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/responsive.css">
    <style>
        .admin-container {
            display: grid;
            grid-template-columns: 250px 1fr;
            gap: 0;
            min-height: calc(100vh - 70px);
        }
        
        .admin-sidebar {
            background-color: var(--secondary-color);
            color: white;
            padding: 2rem 1rem;
        }
        
        .admin-sidebar a {
            display: block;
            padding: 1rem;
            color: white;
            text-decoration: none;
            cursor: pointer;
            border-radius: 4px;
            margin-bottom: 0.5rem;
            transition: var(--transition);
        }
        
        .admin-sidebar a:hover,
        .admin-sidebar a.active {
            background-color: var(--primary-color);
        }
        
        .admin-content {
            padding: 2rem;
            background-color: var(--light-color);
        }
        
        .admin-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1rem;
            margin-bottom: 2rem;
        }
        
        .stat-card {
            background: white;
            padding: 2rem;
            border-radius: 8px;
            box-shadow: var(--shadow);
        }
        
        .stat-number {
            font-size: 2rem;
            font-weight: bold;
            color: var(--primary-color);
        }
        
        .stat-label {
            color: #666;
            margin-top: 0.5rem;
        }
        
        .tabs {
            display: flex;
            gap: 0;
            margin-bottom: 2rem;
            border-bottom: 2px solid var(--border-color);
        }
        
        .tab-link {
            padding: 1rem 2rem;
            background: white;
            border: none;
            cursor: pointer;
            font-weight: 600;
            color: #666;
            border-bottom: 3px solid transparent;
            transition: var(--transition);
        }
        
        .tab-link.active {
            color: var(--primary-color);
            border-bottom-color: var(--primary-color);
        }
        
        .tab-content {
            display: none;
        }
        
        .tab-content.active {
            display: block;
        }
    </style>
</head>
<body>
    <!-- Navigation Bar -->
    <nav class="navbar">
        <div class="container">
            <div class="navbar-brand">
                <h1>⚡ ElectroHub Admin</h1>
            </div>
            <ul class="nav-menu">
                <li><a href="../index.php" class="nav-link">Store</a></li>
                <li><a href="#" id="logoutBtn" class="nav-link">Logout</a></li>
            </ul>
        </div>
    </nav>

    <!-- Admin Container -->
    <div class="admin-container" style="display: none;">
        <!-- Sidebar -->
        <aside class="admin-sidebar">
            <h3 style="margin-bottom: 1.5rem;">Admin Menu</h3>
            <a href="#" onclick="switchTab('dashboard')" class="nav-item active">📊 Dashboard</a>
            <a href="#" onclick="switchTab('products')" class="nav-item">📦 Products</a>
            <a href="#" onclick="switchTab('categories')" class="nav-item">📂 Categories</a>
            <a href="#" onclick="switchTab('orders')" class="nav-item">🛍️ Orders</a>
        </aside>

        <!-- Main Content -->
        <main class="admin-content">
            <!-- Dashboard Tab -->
            <div id="dashboard" class="tab-content active">
                <h2>Dashboard</h2>
                <div class="admin-stats">
                    <div class="stat-card">
                        <div class="stat-number" id="totalProducts">0</div>
                        <div class="stat-label">Total Products</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-number" id="totalCategories">0</div>
                        <div class="stat-label">Categories</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-number" id="totalOrders">0</div>
                        <div class="stat-label">Total Orders</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-number" id="pendingOrders">0</div>
                        <div class="stat-label">Pending Orders</div>
                    </div>
                </div>
            </div>

            <!-- Products Tab -->
            <div id="products" class="tab-content">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
                    <h2>Products Management</h2>
                    <button class="btn btn-primary" id="addProductBtn">+ Add Product</button>
                </div>
                <div id="productsList" style="background: white; border-radius: 8px; box-shadow: var(--shadow); overflow: hidden;"></div>
            </div>

            <!-- Categories Tab -->
            <div id="categories" class="tab-content">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
                    <h2>Categories Management</h2>
                    <button class="btn btn-primary" id="addCategoryBtn">+ Add Category</button>
                </div>
                <div id="categoriesList" style="background: white; border-radius: 8px; box-shadow: var(--shadow);"></div>
            </div>

            <!-- Orders Tab -->
            <div id="orders" class="tab-content">
                <h2>Orders Management</h2>
                <div id="ordersList" style="background: white; border-radius: 8px; box-shadow: var(--shadow); overflow: hidden;"></div>
            </div>
        </main>
    </div>

    <!-- Add/Edit Product Modal -->
    <div id="productModal" class="modal">
        <div class="modal-content" style="max-width: 600px;">
            <span class="close">&times;</span>
            <h2 id="productModalTitle">Add Product</h2>
            <form id="productForm">
                <div class="form-group">
                    <label for="productName">Product Name</label>
                    <input type="text" id="productName" required>
                </div>

                <div class="form-group">
                    <label for="productCategory">Category</label>
                    <select id="productCategory" required></select>
                </div>

                <div class="form-group">
                    <label for="productPrice">Price</label>
                    <input type="number" id="productPrice" step="0.01" required>
                </div>

                <div class="form-group">
                    <label for="productStock">Stock Quantity</label>
                    <input type="number" id="productStock" min="0" required>
                </div>

                <div class="form-group">
                    <label for="productSKU">SKU</label>
                    <input type="text" id="productSKU" required>
                </div>

                <div class="form-group">
                    <label for="productDescription">Description</label>
                    <textarea id="productDescription" required></textarea>
                </div>

                <button type="submit" class="btn btn-primary btn-block">Save Product</button>
            </form>
        </div>
    </div>

    <!-- Add/Edit Category Modal -->
    <div id="categoryModal" class="modal">
        <div class="modal-content" style="max-width: 600px;">
            <span class="close">&times;</span>
            <h2 id="categoryModalTitle">Add Category</h2>
            <form id="categoryForm">
                <div class="form-group">
                    <label for="categoryName">Category Name</label>
                    <input type="text" id="categoryName" required>
                </div>

                <div class="form-group">
                    <label for="categoryDescription">Description</label>
                    <textarea id="categoryDescription"></textarea>
                </div>

                <button type="submit" class="btn btn-primary btn-block">Save Category</button>
            </form>
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
        let currentEditProduct = null;
        let currentEditCategory = null;
        let adminAccessVerified = false;

        document.addEventListener('DOMContentLoaded', () => {
            console.log('Admin dashboard loading...');
            
            // Verify admin access before showing any content
            verifyAdminAccessStrict().then((hasAccess) => {
                if (hasAccess) {
                    adminAccessVerified = true;
                    console.log('Admin access verified, loading dashboard content');
                    
                    // Show admin container
                    document.querySelector('.admin-container').style.display = 'grid';
                    
                    // Load dashboard data
                    try {
                        loadDashboard();
                        loadCategories();
                        setupProductForm();
                        setupCategoryForm();
                        setupProductModal();
                        setupCategoryModal();
                        setupLogout();
                    } catch (e) {
                        console.error('Error loading dashboard components:', e);
                        showError('Error loading dashboard: ' + e.message);
                    }
                }
            });
        });

        function verifyAdminAccessStrict() {
            return new Promise((resolve) => {
                // Check if already logged in from session storage first
                const storedRole = sessionStorage.getItem('user_role');
                console.log('Admin dashboard - stored role:', storedRole);
                
                if (storedRole === 'admin') {
                    console.log('User role confirmed as admin from sessionStorage');
                    resolve(true);
                    return;
                }
                
                // Otherwise, check session with server
                checkSession()
                    .then((sessionData) => {
                        console.log('Admin dashboard - session check response:', sessionData);
                        
                        if (!isLoggedIn()) {
                            console.warn('User not logged in - redirecting to login');
                            setTimeout(() => {
                                window.location.href = '../pages/login.php';
                            }, 100);
                            resolve(false);
                            return;
                        }

                        // Get role from either sessionStorage or response
                        const userRole = sessionStorage.getItem('user_role') || (sessionData.data && sessionData.data.role);
                        console.log('Admin dashboard - determined user role:', userRole);
                        
                        if (userRole !== 'admin') {
                            console.error('Access denied - user is not an admin. Role:', userRole);
                            alert('Access Denied: You must be an admin to access this page.');
                            setTimeout(() => {
                                window.location.href = '../index.php';
                            }, 100);
                            resolve(false);
                            return;
                        }

                        console.log('Admin verification passed');
                        resolve(true);
                    })
                    .catch((error) => {
                        console.error('Session check failed:', error);
                        setTimeout(() => {
                            window.location.href = '../pages/login.php';
                        }, 100);
                        resolve(false);
                    });
            });
        }

        function switchTab(tabName) {
            // Hide all tabs
            document.querySelectorAll('.tab-content').forEach(tab => {
                tab.classList.remove('active');
            });

            // Show selected tab
            document.getElementById(tabName).classList.add('active');

            // Load tab content
            if (tabName === 'products') loadProducts();
            if (tabName === 'categories') loadCategoriesList();
            if (tabName === 'orders') loadAllOrders();
        }

        function loadDashboard() {
            fetch(`${API_BASE}/products.php?action=list&page=1`, { credentials: 'same-origin' })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        document.getElementById('totalProducts').textContent = data.data.pagination.total;
                    }
                });
            fetch(`${API_BASE}/categories.php?action=list`, { credentials: 'same-origin' })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        document.getElementById('totalCategories').textContent = data.data.length;
                    }
                });
            fetch(`${API_BASE}/orders.php?action=all&page=1`, { credentials: 'same-origin' })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        document.getElementById('totalOrders').textContent = data.data.pagination.total;
                        const pending = data.data.orders.filter(o => o.status === 'pending').length;
                        document.getElementById('pendingOrders').textContent = pending;
                    }
                });
        }

        function loadProducts() {
            const container = document.getElementById('productsList');
            container.innerHTML = '<div class="loading"><div class="spinner"></div></div>';

            fetch(`${API_BASE}/products.php?action=list&page=1`, { credentials: 'same-origin' })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        let html = '<table class="table"><thead><tr><th>Product</th><th>Category</th><th>Price</th><th>Stock</th><th>Actions</th></tr></thead><tbody>';

                        data.data.products.forEach(product => {
                            html += `
                                <tr>
                                    <td>${product.product_name}</td>
                                    <td>${product.category_name || 'N/A'}</td>
                                    <td>${formatCurrency(product.price)}</td>
                                    <td>${product.stock_quantity}</td>
                                    <td>
                                        <button class="btn btn-small btn-secondary" onclick="editProduct(${product.product_id})">Edit</button>
                                        <button class="btn btn-small btn-danger" onclick="deleteProduct(${product.product_id})">Delete</button>
                                    </td>
                                </tr>
                            `;
                        });

                        html += '</tbody></table>';
                        container.innerHTML = html;
                    }
                });
        }

        function loadCategories() {
            fetch(`${API_BASE}/categories.php?action=list`, { credentials: 'same-origin' })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const select = document.getElementById('productCategory');
                        select.innerHTML = '<option value="">Select Category</option>';
                        data.data.forEach(cat => {
                            const option = document.createElement('option');
                            option.value = cat.category_id;
                            option.textContent = cat.category_name;
                            select.appendChild(option);
                        });
                    }
                });
        }

        function loadCategoriesList() {
            const container = document.getElementById('categoriesList');
            container.innerHTML = '<div class="loading"><div class="spinner"></div></div>';

            fetch(`${API_BASE}/categories.php?action=list`, { credentials: 'same-origin' })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        let html = '<table class="table"><thead><tr><th>Category</th><th>Products</th><th>Actions</th></tr></thead><tbody>';

                        data.data.forEach(cat => {
                            html += `
                                <tr>
                                    <td>${cat.category_name}</td>
                                    <td>${cat.product_count}</td>
                                    <td>
                                        <button class="btn btn-small btn-secondary" onclick="editCategory(${cat.category_id})">Edit</button>
                                        <button class="btn btn-small btn-danger" onclick="deleteCategory(${cat.category_id})">Delete</button>
                                    </td>
                                </tr>
                            `;
                        });

                        html += '</tbody></table>';
                        container.innerHTML = html;
                    }
                });
        }

        function loadAllOrders() {
            const container = document.getElementById('ordersList');
            container.innerHTML = '<div class="loading"><div class="spinner"></div></div>';

            fetch(`${API_BASE}/orders.php?action=all&page=1`, { credentials: 'same-origin' })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        let html = '<table class="table"><thead><tr><th>Order ID</th><th>Customer</th><th>Total</th><th>Status</th><th>Date</th><th>Action</th></tr></thead><tbody>';

                        data.data.orders.forEach(order => {
                            const date = new Date(order.order_date).toLocaleDateString();
                            html += `
                                <tr>
                                    <td>#${order.order_id}</td>
                                    <td>${order.username}</td>
                                    <td>${formatCurrency(order.total_amount)}</td>
                                    <td>${order.status}</td>
                                    <td>${date}</td>
                                    <td>
                                        <select onchange="updateOrderStatus(${order.order_id}, this.value)" style="padding: 0.25rem;">
                                            <option value="pending" ${order.status === 'pending' ? 'selected' : ''}>Pending</option>
                                            <option value="confirmed" ${order.status === 'confirmed' ? 'selected' : ''}>Confirmed</option>
                                            <option value="shipped" ${order.status === 'shipped' ? 'selected' : ''}>Shipped</option>
                                            <option value="delivered" ${order.status === 'delivered' ? 'selected' : ''}>Delivered</option>
                                            <option value="cancelled" ${order.status === 'cancelled' ? 'selected' : ''}>Cancelled</option>
                                        </select>
                                    </td>
                                </tr>
                            `;
                        });

                        html += '</tbody></table>';
                        container.innerHTML = html;
                    }
                });
        }

        function setupProductModal() {
            document.getElementById('addProductBtn').addEventListener('click', () => {
                currentEditProduct = null;
                document.getElementById('productModalTitle').textContent = 'Add Product';
                document.getElementById('productForm').reset();
                document.getElementById('productModal').classList.add('show');
            });
        }

        function setupCategoryModal() {
            document.getElementById('addCategoryBtn').addEventListener('click', () => {
                currentEditCategory = null;
                document.getElementById('categoryModalTitle').textContent = 'Add Category';
                document.getElementById('categoryForm').reset();
                document.getElementById('categoryModal').classList.add('show');
            });
        }

        function setupProductForm() {
            document.getElementById('productForm').addEventListener('submit', (e) => {
                e.preventDefault();

                const data = {
                    product_name: document.getElementById('productName').value,
                    category_id: document.getElementById('productCategory').value,
                    price: document.getElementById('productPrice').value,
                    stock_quantity: document.getElementById('productStock').value,
                    sku: document.getElementById('productSKU').value,
                    description: document.getElementById('productDescription').value
                };

                const url = currentEditProduct
                    ? `${API_BASE}/products.php?action=update&id=${currentEditProduct}`
                    : `${API_BASE}/products.php?action=create`;

                const method = currentEditProduct ? 'PUT' : 'POST';

                fetch(url, {
                    method: method,
                    credentials: 'same-origin',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(data)
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showSuccess(currentEditProduct ? 'Product updated!' : 'Product created!');
                        document.getElementById('productModal').classList.remove('show');
                        loadProducts();
                        loadDashboard();
                    } else {
                        showError(data.message);
                    }
                });
            });
        }

        function setupCategoryForm() {
            document.getElementById('categoryForm').addEventListener('submit', (e) => {
                e.preventDefault();

                const data = {
                    category_name: document.getElementById('categoryName').value,
                    description: document.getElementById('categoryDescription').value
                };

                const url = currentEditCategory
                    ? `${API_BASE}/categories.php?action=update&id=${currentEditCategory}`
                    : `${API_BASE}/categories.php?action=create`;

                const method = currentEditCategory ? 'PUT' : 'POST';

                fetch(url, {
                    method: method,
                    credentials: 'same-origin',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(data)
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showSuccess(currentEditCategory ? 'Category updated!' : 'Category created!');
                        document.getElementById('categoryModal').classList.remove('show');
                        loadCategoriesList();
                        loadCategories();
                        loadDashboard();
                    } else {
                        showError(data.message);
                    }
                });
            });
        }

        function editProduct(productId) {
            // Load product data and populate form
            currentEditProduct = productId;
            document.getElementById('productModalTitle').textContent = 'Edit Product';
            document.getElementById('productModal').classList.add('show');
        }

        function deleteProduct(productId) {
            if (confirm('Are you sure you want to delete this product?')) {
                fetch(`${API_BASE}/products.php?action=delete&id=${productId}`, {
                    method: 'DELETE',
                    credentials: 'same-origin'
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showSuccess('Product deleted!');
                        loadProducts();
                        loadDashboard();
                    } else {
                        showError(data.message);
                    }
                });
            }
        }

        function editCategory(categoryId) {
            currentEditCategory = categoryId;
            document.getElementById('categoryModalTitle').textContent = 'Edit Category';
            document.getElementById('categoryModal').classList.add('show');
        }

        function deleteCategory(categoryId) {
            if (confirm('Are you sure you want to delete this category?')) {
                fetch(`${API_BASE}/categories.php?action=delete&id=${categoryId}`, {
                    method: 'DELETE',
                    credentials: 'same-origin'
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showSuccess('Category deleted!');
                        loadCategoriesList();
                        loadCategories();
                        loadDashboard();
                    } else {
                        showError(data.message);
                    }
                });
            }
        }

        function updateOrderStatus(orderId, status) {
            fetch(`${API_BASE}/orders.php?action=update&id=${orderId}`, {
                method: 'PUT',
                credentials: 'same-origin',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ status: status })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showSuccess('Order status updated!');
                } else {
                    showError(data.message);
                }
            });
        }
    </script>
</body>
</html>
