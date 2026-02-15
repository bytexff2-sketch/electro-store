<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shopping Cart - Electronics Store</title>
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
                <li><a href="cart.php" class="nav-link active">🛒 Cart</a></li>
                <li id="authLinks" style="display: flex;">
                    <a href="login.php" class="nav-link">Login</a>
                </li>
                <li id="userLinks" style="display: none;">
                    <a href="orders.php" class="nav-link">Orders</a>
                    <a href="#" id="logoutBtn" class="nav-link">Logout</a>
                </li>
            </ul>
        </div>
    </nav>

    <!-- Cart Section -->
    <section class="shopping-cart">
        <div class="container" style="max-width: 1000px;">
            <h2>Shopping Cart</h2>

            <div id="cartContainer" style="display: flex; gap: 2rem; margin: 2rem 0;">
                <!-- Cart Items -->
                <div id="cartItems" style="flex: 2;">
                    <div class="loading" style="text-align: center; padding: 3rem;">
                        <div class="spinner"></div>
                        <p>Loading cart...</p>
                    </div>
                </div>

                <!-- Cart Summary -->
                <div id="cartSummary" style="flex: 1; padding: 1.5rem; background: var(--light-color); border-radius: 8px; height: fit-content; display: none;">
                    <h3>Order Summary</h3>
                    <div class="mt-2">
                        <div style="display: flex; justify-content: space-between; margin-bottom: 1rem;">
                            <span>Subtotal:</span>
                            <span id="subtotal">$0.00</span>
                        </div>
                        <div style="display: flex; justify-content: space-between; margin-bottom: 1rem;">
                            <span>Tax (8%):</span>
                            <span id="tax">$0.00</span>
                        </div>
                        <hr style="border: none; border-top: 2px solid #ddd; margin: 1rem 0;">
                        <div style="display: flex; justify-content: space-between; font-size: 1.3rem; font-weight: bold; color: var(--primary-color); margin-bottom: 2rem;">
                            <span>Total:</span>
                            <span id="total">$0.00</span>
                        </div>
                        <button id="checkoutBtn" class="btn btn-primary btn-block">Proceed to Checkout</button>
                        <a href="products.php" class="btn btn-secondary btn-block" style="margin-top: 1rem;">Continue Shopping</a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Checkout Modal -->
    <div id="checkoutModal" class="modal">
        <div class="modal-content" style="max-width: 600px;">
            <span class="close">&times;</span>
            <h2>Checkout</h2>
            
            <form id="checkoutForm">
                <div class="form-group">
                    <label for="deliveryAddress">Delivery Address</label>
                    <textarea id="deliveryAddress" name="deliveryAddress" required style="min-height: 80px;"></textarea>
                    <div class="form-error error-deliveryAddress"></div>
                </div>

                <div class="form-group">
                    <label for="paymentMethod">Payment Method</label>
                    <select id="paymentMethod" name="paymentMethod" required>
                        <option value="Credit Card">Credit Card</option>
                        <option value="Debit Card">Debit Card</option>
                        <option value="PayPal">PayPal</option>
                        <option value="Bank Transfer">Bank Transfer</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="notes">Additional Notes (Optional)</label>
                    <textarea id="notes" name="notes" style="min-height: 60px;"></textarea>
                </div>

                <button type="submit" class="btn btn-primary btn-block">Place Order</button>
            </form>
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
            // First, check if user is already in sessionStorage (from login)
            const storedUserId = sessionStorage.getItem('user_id');
            const storedRole = sessionStorage.getItem('user_role');
            
            console.log('Cart page - checking login status');
            console.log('Stored user_id:', storedUserId);
            console.log('Stored role:', storedRole);
            
            // If we have stored credentials, they're logged in
            if (storedUserId && storedRole) {
                console.log('User already logged in (from sessionStorage)');
                loadCart();
                setupCheckout();
                return;
            }
            
            // Otherwise, verify with server
            console.log('Checking session with server...');
            const headers = {};
            try { 
                const sid = localStorage.getItem('session_id'); 
                if (sid) headers['X-Session-Id'] = sid; 
            } catch(e) {}
            
            fetch(`${API_BASE}/auth.php?action=check`, { 
                credentials: 'same-origin', 
                headers: headers 
            })
                .then(response => response.json())
                .then(data => {
                    console.log('Server session check response:', data);
                    if (data && data.success && data.data && data.data.user_id) {
                        // User is logged in - load cart
                        console.log('User verified by server, loading cart');
                        loadCart();
                        setupCheckout();
                    } else {
                        // User not logged in
                        console.log('User not logged in');
                        showLoginRequiredMessage();
                    }
                })
                .catch((error) => {
                    console.log('Server check failed:', error);
                    showLoginRequiredMessage();
                });
        });

        function showLoginRequiredMessage() {
            // Hide cart items and summary
            document.getElementById('cartItems').innerHTML = '';
            document.getElementById('cartSummary').style.display = 'none';
            
            // Show login message in full width
            document.getElementById('cartContainer').innerHTML = `
                <div style="width: 100%; background: #fff3cd; border: 2px solid #ffc107; border-radius: 8px; padding: 3rem; text-align: center;">
                    <h3 style="color: #856404; margin-bottom: 1rem;">🔐 Login Required</h3>
                    <p style="font-size: 1.1rem; color: #856404; margin-bottom: 2rem;">
                        You need to log in first to view and manage your shopping cart.
                    </p>
                    <a href="login.php" class="btn btn-primry" style="margin-right: 1rem;">Go to Login</a>
                    <a href="../index.php" class="btn btn-secondry" style ="margin-top:2px;">Back to Home</a>
                </div>
            `;
        }

        function loadCart() {
            // Show summary when logged in
            document.getElementById('cartSummary').style.display = 'block';
            
            const container = document.getElementById('cartItems');
            container.innerHTML = '<div class="loading" style="text-align: center; padding: 2rem;"><div class="spinner"></div><p>Loading cart...</p></div>';

            const headers = {};
            try { const sid = localStorage.getItem('session_id'); if (sid) headers['X-Session-Id'] = sid; } catch(e) {}
            fetch(`${API_BASE}/cart.php?action=view`, { credentials: 'same-origin', headers: headers })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        displayCart(data.data);
                    } else {
                        container.innerHTML = `<p class="text-center text-danger">${data.message}</p>`;
                    }
                })
                .catch(error => {
                    container.innerHTML = `<p class="text-center text-danger">Error: ${error.message}</p>`;
                });
        }

        function displayCart(cartData) {
            const container = document.getElementById('cartItems');
            
            if (cartData.item_count === 0) {
                container.innerHTML = `
                    <div class="alert alert-info text-center">
                        <p>Your cart is empty. <a href="products.php">Continue shopping</a></p>
                    </div>
                `;
                document.getElementById('checkoutBtn').disabled = true;
                updateSummary(0, 0, 0);
                return;
            }

            let html = '<table class="table"><thead><tr><th>Product</th><th>Price</th><th>Quantity</th><th>Subtotal</th><th></th></tr></thead><tbody>';

            cartData.items.forEach(item => {
                // Ensure numeric types for calculations
                const quantity = parseInt(item.quantity, 10) || 1;
                const price = parseFloat(item.price) || 0;
                const stockQty = parseInt(item.stock_quantity, 10) || 0;
                
                // Fix image URL by stripping leading slash
                let itemImageUrl = item.image_url;
                if (itemImageUrl && itemImageUrl.startsWith('/')) {
                    itemImageUrl = `../${itemImageUrl.substring(1)}`;
                } else if (!itemImageUrl) {
                    itemImageUrl = '../assets/images/placeholder.png';
                } else {
                    itemImageUrl = `../${itemImageUrl}`;
                }
                
                html += `
                    <tr>
                        <td class="product-image-cell">
                            <div style="display: flex; align-items: center; gap: 1rem;">
                                <img src="${itemImageUrl}" alt="${item.product_name}" onerror="this.src='../assets/images/placeholder.png'">
                                <div>
                                    <div style="font-weight: 600;">${item.product_name}</div>
                                    <small>${stockQty} in stock</small>
                                </div>
                            </div>
                        </td>
                        <td>$${price.toFixed(2)}</td>
                        <td>
                            <div style="display: flex; align-items: center; gap: 0.5rem;">
                                <button type="button" class="btn btn-small" onclick="updateQuantity(${item.cart_id}, ${quantity - 1})">-</button>
                                <input type="number" value="${quantity}" style="width: 50px; text-align: center; border: 1px solid var(--border-color); padding: 0.25rem;" readonly>
                                <button type="button" class="btn btn-small" onclick="updateQuantity(${item.cart_id}, ${quantity + 1})">+</button>
                            </div>
                        </td>
                        <td>$${(parseFloat(item.subtotal) || 0).toFixed(2)}</td>
                        <td>
                            <button type="button" class="btn btn-danger btn-small" onclick="removeFromCart(${item.cart_id})">Remove</button>
                        </td>
                    </tr>
                `;
            });

            html += '</tbody></table>';
            container.innerHTML = html;

            updateSummary(cartData.subtotal, cartData.tax, cartData.total);
        }

        function updateSummary(subtotal, tax, total) {
            document.getElementById('subtotal').textContent = formatCurrency(subtotal);
            document.getElementById('tax').textContent = formatCurrency(tax);
            document.getElementById('total').textContent = formatCurrency(total);
        }

        function updateQuantity(cartId, newQuantity) {
            if (newQuantity < 1) {
                removeFromCart(cartId);
                return;
            }

            const headers = { 'Content-Type': 'application/json' };
            try { const sid = localStorage.getItem('session_id'); if (sid) headers['X-Session-Id'] = sid; } catch(e) {}
            fetch(`${API_BASE}/cart.php?action=update&id=${cartId}`, {
                method: 'PUT',
                credentials: 'same-origin',
                headers: headers,
                body: JSON.stringify({ quantity: newQuantity })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    loadCart();
                } else {
                    showError(data.message);
                }
            });
        }

        function removeFromCart(cartId) {
            if (confirm('Remove this item from cart?')) {
                const headers = {};
                try { const sid = localStorage.getItem('session_id'); if (sid) headers['X-Session-Id'] = sid; } catch(e) {}
                fetch(`${API_BASE}/cart.php?action=remove&id=${cartId}`, {
                    method: 'DELETE',
                    credentials: 'same-origin',
                    headers: headers
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        loadCart();
                        loadCartCount();
                    } else {
                        showError(data.message);
                    }
                });
            }
        }

        function setupCheckout() {
            const checkoutBtn = document.getElementById('checkoutBtn');
            const checkoutForm = document.getElementById('checkoutForm');
            const checkoutModal = document.getElementById('checkoutModal');
            const closeBtn = document.querySelector('.modal .close');

            checkoutBtn.addEventListener('click', () => {
                checkoutModal.classList.add('show');
            });

            // Close modal when X button is clicked
            if (closeBtn) {
                closeBtn.addEventListener('click', () => {
                    checkoutModal.classList.remove('show');
                });
            }

            // Close modal when clicking outside the content
            checkoutModal.addEventListener('click', (e) => {
                if (e.target === checkoutModal) {
                    checkoutModal.classList.remove('show');
                }
            });

            checkoutForm.addEventListener('submit', (e) => {
                e.preventDefault();

                const deliveryAddress = document.getElementById('deliveryAddress').value.trim();
                const paymentMethod = document.getElementById('paymentMethod').value;
                const notes = document.getElementById('notes').value.trim();

                if (!deliveryAddress) {
                    showError('Please enter a delivery address');
                    return;
                }

                const headers = { 'Content-Type': 'application/json' };
                try { const sid = localStorage.getItem('session_id'); if (sid) headers['X-Session-Id'] = sid; } catch(e) {}
                fetch(`${API_BASE}/orders.php?action=create`, {
                    method: 'POST',
                    credentials: 'same-origin',
                    headers: headers,
                    body: JSON.stringify({
                        delivery_address: deliveryAddress,
                        payment_method: paymentMethod,
                        notes: notes
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showSuccess('Order placed successfully! Order ID: ' + data.data.order_id);
                        checkoutModal.classList.remove('show');
                        setTimeout(() => {
                            window.location.href = 'orders.php';
                        }, 2000);
                    } else {
                        showError(data.message);
                    }
                });
            });
        }
    </script>
</body>
</html>
