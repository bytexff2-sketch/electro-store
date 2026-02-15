<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Product Details - Electronics Store</title>
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
                <li><a href="cart.php" class="nav-link">🛒 Cart (<span id="cartCount">0</span>)</a></li>
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

    <!-- Product Detail Section -->
    <section style="padding: 3rem 0; min-height: 50vh;">
        <div class="container" style="max-width: 1000px;">
            <div id="productDetail" style="background: white; padding: 2rem; border-radius: 8px;">
                <div class="loading"><div class="spinner"></div></div>
            </div>
        </div>
    </section>

    <!-- Related Products Section -->
    <section style="padding: 3rem 0; background: var(--light-color);">
        <div class="container">
            <h2>Related Products</h2>
            <div class="products-grid" id="relatedProducts"></div>
        </div>
    </section>

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
        const urlParams = new URLSearchParams(window.location.search);
        const productId = urlParams.get('id');

        document.addEventListener('DOMContentLoaded', () => {
            if (!productId) {
                document.getElementById('productDetail').innerHTML = `
                    <p class="text-center text-danger">Product not found</p>
                `;
                return;
            }
            loadProductDetail();
        });

        function loadProductDetail() {
            fetch(`${API_BASE}/products.php?action=get&id=${productId}`, { credentials: 'same-origin' })
                .then(response => response.text())
                .then(text => {
                    try {
                        const data = JSON.parse(text);
                        if (data.success) {
                            displayProductDetail(data.data);
                            loadRelatedProducts(data.data.category_id);
                        } else {
                            document.getElementById('productDetail').innerHTML = `
                                <p class="text-center text-danger">${data.message}</p>
                            `;
                        }
                    } catch(e) {
                        console.error('JSON Parse Error:', e);
                        console.error('Response:', text);
                        document.getElementById('productDetail').innerHTML = `
                            <p class="text-center text-danger">Error loading product details. Check browser console.</p>
                        `;
                    }
                })
                .catch(error => {
                    console.error('Fetch error:', error);
                    document.getElementById('productDetail').innerHTML = `
                        <p class="text-center text-danger">Error: ${error.message}</p>
                    `;
                });
        }

        function displayProductDetail(product) {
            // Fix image URL by stripping leading slash
            let imageUrl;
            if (product.image_url) {
                // Remove leading slash if present
                imageUrl = product.image_url.startsWith('/') 
                    ? `../${product.image_url.substring(1)}`
                    : `../${product.image_url}`;
            } else {
                imageUrl = '../assets/images/products/iphone15pro-2.jpg';
            }
            
            // Convert stock_quantity to number (database returns it as string)
            const stockQty = parseInt(product.stock_quantity, 10) || 0;
            
            let stockStatus = '<span class="text-success">In Stock</span>';
            
            if (stockQty === 0) {
                stockStatus = '<span class="text-danger">Out of Stock</span>';
            } else if (stockQty < 5) {
                stockStatus = '<span class="text-warning">Limited Stock</span>';
            }

            let reviewsHtml = '';
            if (product.reviews && product.reviews.length > 0) {
                reviewsHtml = '<h3>Customer Reviews</h3>';
                product.reviews.forEach(review => {
                    const stars = '⭐'.repeat(review.rating);
                    reviewsHtml += `
                        <div style="padding: 1rem; border-top: 1px solid var(--border-color);">
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem;">
                                <strong>${review.first_name} ${review.last_name}</strong>
                                <span>${stars}</span>
                            </div>
                            <p>${review.review_text || 'No comment'}</p>
                        </div>
                    `;
                });
            } else {
                reviewsHtml = '<p class="text-muted">No reviews yet</p>';
            }

            const html = `
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem; margin-bottom: 3rem;">
                    <!-- Product Image -->
                    <div>
                        <img src="${imageUrl}" alt="${product.product_name}" style="width: 100%; border-radius: 8px; object-fit: cover;" onerror="this.src='../assets/images/products/placeholder.png'">
                    </div>

                    <!-- Product Info -->
                    <div>
                        <h1>${product.product_name}</h1>
                        <p style="color: #666; margin-bottom: 1rem;">${product.category_name || 'Electronics'}</p>
                        
                        <div style="font-size: 1.2rem; color: var(--primary-color); font-weight: bold; margin-bottom: 1rem;">
                            ${formatCurrency(product.price)}
                        </div>

                        <div style="margin-bottom: 1rem;">
                            <p><strong>Stock Status:</strong> ${stockStatus}</p>
                            <p><strong>Available:</strong> ${stockQty} units</p>
                            <p><strong>SKU:</strong> ${product.sku}</p>
                        </div>

                        <div style="margin-bottom: 2rem;">
                            <label for="quantity" style="display: block; margin-bottom: 0.5rem;"><strong>Quantity:</strong></label>
                            <div style="display: flex; align-items: center; gap: 1rem;">
                                <button type="button" class="btn btn-small" id="decreaseBtn">-</button>
                                <input type="number" id="quantity" value="1" min="1" max="${stockQty}" style="width: 60px; padding: 0.5rem; text-align: center; border: 2px solid var(--border-color); border-radius: 4px;">
                                <button type="button" class="btn btn-small" id="increaseBtn">+</button>
                            </div>
                        </div>

                        <button class="btn btn-primary btn-block" id="addToCartBtn" ${stockQty === 0 ? 'disabled' : ''}>
                            🛒 Add to Cart
                        </button>

                        <div style="margin-top: 2rem; padding: 1rem; background: var(--light-color); border-radius: 4px;">
                            <h3>Product Description</h3>
                            <p style="margin-top: 1rem;">${product.description}</p>
                        </div>
                    </div>
                </div>

                <div style="background: var(--light-color); padding: 2rem; border-radius: 8px;">
                    ${reviewsHtml}
                </div>
            `;

            document.getElementById('productDetail').innerHTML = html;

            // Setup event listeners
            setupProductDetail();
        }

        function setupProductDetail() {
            const quantity = document.getElementById('quantity');
            const decreaseBtn = document.getElementById('decreaseBtn');
            const increaseBtn = document.getElementById('increaseBtn');
            const addToCartBtn = document.getElementById('addToCartBtn');

            decreaseBtn.addEventListener('click', () => {
                if (quantity.value > 1) {
                    quantity.value = parseInt(quantity.value) - 1;
                }
            });

            increaseBtn.addEventListener('click', () => {
                if (quantity.value < quantity.max) {
                    quantity.value = parseInt(quantity.value) + 1;
                }
            });

            addToCartBtn.addEventListener('click', () => {
                addToCart(productId, parseInt(quantity.value));
            });
        }

        function loadRelatedProducts(categoryId) {
            if (!categoryId) return;

            fetch(`${API_BASE}/products.php?action=by-category&id=${categoryId}`, { credentials: 'same-origin' })
                .then(response => response.text())
                .then(text => {
                    try {
                        const data = JSON.parse(text);
                        const container = document.getElementById('relatedProducts');
                        container.innerHTML = '';

                        if (data.success && data.data.products.length > 0) {
                            data.data.products.slice(0, 4).forEach(product => {
                                const card = createProductCard(product);
                                container.appendChild(card);
                            });
                        }
                    } catch(e) {
                        console.error('JSON Parse Error in related products:', e);
                        console.error('Response:', text);
                    }
                });
        }
    </script>
</body>
</html>
