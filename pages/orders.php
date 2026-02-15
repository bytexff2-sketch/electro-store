<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Orders - Electronics Store</title>
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
                <li><a href="products.php" class="nav-link">Shop</a></li>
                <li><a href="cart.php" class="nav-link">Cart</a></li>
                <li id="authLinks" style="display: flex;">
                    <a href="login.php" class="nav-link">Login</a>
                </li>
                <li id="userLinks" style="display: none;">
                    <a href="orders.php" class="nav-link active">Orders</a>
                    <a href="#" id="logoutBtn" class="nav-link">Logout</a>
                </li>
            </ul>
        </div>
    </nav>

    <!-- Orders Section -->
    <section style="padding: 3rem 0;">
        <div class="container">
            <h2>My Orders</h2>

            <div id="ordersContainer" style="margin-top: 2rem;">
                <div class="loading"><div class="spinner"></div></div>
            </div>
        </div>
    </section>

    <!-- Order Detail Modal -->
    <div id="orderDetailModal" class="modal">
        <div class="modal-content" style="max-width: 700px; max-height: 80vh; overflow-y: auto;">
            <span class="close">&times;</span>
            <h2>Order Details</h2>
            <div id="orderDetailContent"></div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
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
        document.addEventListener('DOMContentLoaded', () => {
            // Defensive: ensure checkSession exists and returns a Promise
            if (typeof checkSession === 'function') {
                const p = checkSession();
                if (p && typeof p.then === 'function') {
                    p.then(() => {
                        if (!isLoggedIn()) {
                            document.getElementById('ordersContainer').innerHTML = `
                                <div class="alert alert-warning">
                                    <p>Please <a href="login.php">login</a> to view your orders.</p>
                                </div>
                            `;
                            return;
                        }
                        loadOrders();
                    }).catch(() => {
                        document.getElementById('ordersContainer').innerHTML = `
                            <div class="alert alert-warning">
                                <p>Please <a href="login.php">login</a> to view your orders.</p>
                            </div>
                        `;
                    });
                } else {
                    document.getElementById('ordersContainer').innerHTML = `
                        <div class="alert alert-warning">
                            <p>Please <a href="login.php">login</a> to view your orders.</p>
                        </div>
                    `;
                }
            } else {
                fetch(`${API_BASE}/auth.php?action=check`, { credentials: 'same-origin' })
                    .then(response => {
                        if (response.status === 401) throw new Error('unauth');
                        return response.json();
                    })
                    .then(data => {
                        if (data && data.success) {
                            loadOrders();
                        } else {
                            document.getElementById('ordersContainer').innerHTML = `
                                <div class="alert alert-warning">
                                    <p>Please <a href="login.php">login</a> to view your orders.</p>
                                </div>
                            `;
                        }
                    })
                    .catch(() => {
                        document.getElementById('ordersContainer').innerHTML = `
                            <div class="alert alert-warning">
                                <p>Please <a href="login.php">login</a> to view your orders.</p>
                            </div>
                        `;
                    });
            }
        });

        function loadOrders(page = 1) {
            const container = document.getElementById('ordersContainer');
            container.innerHTML = '<div class="loading"><div class="spinner"></div></div>';

            const headers = {};
            try { const sid = localStorage.getItem('session_id'); if (sid) headers['X-Session-Id'] = sid; } catch(e) {}
            fetch(`${API_BASE}/orders.php?action=list&page=${page}`, { credentials: 'same-origin', headers: headers })
                .then(response => response.json())
                .then(data => {
                    container.innerHTML = '';
                    
                    if (data.success && data.data.orders.length > 0) {
                        displayOrders(data.data.orders);
                        
                        const paginationContainer = document.createElement('div');
                        const pagination = createPagination(
                            data.data.pagination.current_page,
                            data.data.pagination.total_pages,
                            loadOrders
                        );
                        paginationContainer.appendChild(pagination);
                        container.appendChild(paginationContainer);
                    } else {
                        container.innerHTML = `
                            <div class="alert alert-info">
                                <p>No orders found. <a href="products.php">Start shopping</a></p>
                            </div>
                        `;
                    }
                })
                .catch(error => {
                    container.innerHTML = `<p class="text-center text-danger">Error: ${error.message}</p>`;
                });
        }

        function displayOrders(orders) {
            const container = document.getElementById('ordersContainer');
            
            let html = '<table class="table"><thead><tr><th>Order ID</th><th>Date</th><th>Items</th><th>Total</th><th>Status</th><th></th></tr></thead><tbody>';

            orders.forEach(order => {
                const date = new Date(order.order_date).toLocaleDateString();
                const statusColor = getStatusColor(order.status);
                
                html += `
                    <tr>
                        <td>#${order.order_id}</td>
                        <td>${date}</td>
                        <td>${order.item_count}</td>
                        <td>${formatCurrency(order.total_amount)}</td>
                        <td><span style="display: inline-block; padding: 0.25rem 0.75rem; border-radius: 20px; background: ${statusColor}; color: white;">${order.status}</span></td>
                        <td>
                            <button class="btn btn-small btn-secondary" onclick="viewOrderDetails(${order.order_id})">View</button>
                        </td>
                    </tr>
                `;
            });

            html += '</tbody></table>';
            container.innerHTML = html;
        }

        function viewOrderDetails(orderId) {
            const headers = {};
            try { const sid = localStorage.getItem('session_id'); if (sid) headers['X-Session-Id'] = sid; } catch(e) {}
            fetch(`${API_BASE}/orders.php?action=get&id=${orderId}`, { credentials: 'same-origin', headers: headers })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        displayOrderDetails(data.data);
                        document.getElementById('orderDetailModal').classList.add('show');
                    } else {
                        showError(data.message);
                    }
                });
        }

        function displayOrderDetails(order) {
            const date = new Date(order.order_date).toLocaleDateString();
            const statusColor = getStatusColor(order.status);
            
            let itemsHtml = '';
            order.items.forEach(item => {
                itemsHtml += `
                    <tr>
                        <td>${item.product_name}</td>
                        <td>${item.quantity}</td>
                        <td>${formatCurrency(item.unit_price)}</td>
                        <td>${formatCurrency(item.subtotal)}</td>
                    </tr>
                `;
            });

            const html = `
                <div style="margin-top: 1.5rem;">
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 2rem;">
                        <div>
                            <p><strong>Order ID:</strong> #${order.order_id}</p>
                            <p><strong>Date:</strong> ${date}</p>
                            <p><strong>Status:</strong> <span style="display: inline-block; padding: 0.25rem 0.75rem; border-radius: 20px; background: ${statusColor}; color: white;">${order.status}</span></p>
                        </div>
                        <div>
                            <p><strong>Payment:</strong> ${order.payment_method}</p>
                            <p><strong>Delivery Address:</strong> ${order.delivery_address}</p>
                        </div>
                    </div>

                    <h3>Order Items</h3>
                    <table class="table" style="margin-bottom: 2rem;">
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th>Quantity</th>
                                <th>Price</th>
                                <th>Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            ${itemsHtml}
                        </tbody>
                    </table>

                    <div style="text-align: right; padding: 1rem; background: var(--light-color); border-radius: 4px;">
                        <p style="margin-bottom: 0.5rem;"><strong>Total Amount:</strong> ${formatCurrency(order.total_amount)}</p>
                    </div>

                    ${order.notes ? `<div style="margin-top: 1rem;"><p><strong>Notes:</strong> ${order.notes}</p></div>` : ''}
                </div>
            `;

            document.getElementById('orderDetailContent').innerHTML = html;
        }

        function getStatusColor(status) {
            const colors = {
                'pending': '#ffc107',
                'confirmed': '#17a2b8',
                'shipped': '#007bff',
                'delivered': '#28a745',
                'cancelled': '#dc3545'
            };
            return colors[status] || '#6c757d';
        }
    </script>
</body>
</html>
