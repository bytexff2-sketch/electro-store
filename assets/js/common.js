/**
 * Common JavaScript Functions
 * Used across all pages
 */

// Global variable for current page path
const currentPath = window.location.pathname;

// Determine the correct API base path
// This works by analyzing the current URL and calculating relative path
function getAPIBase() {
    const path = window.location.pathname;
    
    // Count how many directory levels we're in
    const parts = path.split('/').filter(p => p.length > 0);
    
    // Remove the filename (last part if it has .php)
    if (parts[parts.length - 1].includes('.php')) {
        parts.pop();
    }
    
    // Determine how many levels up we need to go
    // pages/ and admin/ are 1 level deep from root
    // So if we're in pages/ or admin/, we need to go up one level
    const inSubfolder = path.includes('/pages/') || path.includes('/admin/');
    
    if (inSubfolder) {
        return '../backend/api';
    } else {
        // We're at root, use path from root domain
        // Get the base URL by finding where the app is installed
        const baseUrl = window.location.origin + window.location.pathname.split('/').slice(0, -1).join('/');
        return baseUrl + '/backend/api';
    }
}

const API_BASE = getAPIBase();

// Helper function to get correct relative paths for redirects
function getPagePath(pagePath) {
    const currentPath = window.location.pathname;
    if (currentPath.includes('/pages/') || currentPath.includes('/admin/')) {
        // We're in a subfolder, need to go up one level
        if (pagePath === 'index.php') {
            return '../index.php';
        } else if (pagePath.startsWith('pages/')) {
            return pagePath.substring(6) + '.php';
        } else {
            return pagePath;
        }
    } else {
        // We're at root
        return pagePath;
    }
}

// Helper function to safely parse JSON responses
function safeJsonParse(response) {
    return response.text().then(text => {
        try {
            return JSON.parse(text);
        } catch(e) {
            console.error('JSON Parse Error:', e);
            console.error('Response Text:', text);
            throw new Error('Invalid JSON response from server. Check browser console for details.');
        }
    });
}

// ===== Session Management =====
function checkSession() {
    // Attach X-Session-Id header if client has stored one
    const headers = {};
    const sid = localStorage.getItem('session_id');
    if (sid) headers['X-Session-Id'] = sid;

    return fetch(`${API_BASE}/auth.php?action=check`, { credentials: 'same-origin', headers: headers })
        .then(safeJsonParse)
        .then(data => {
            console.debug('checkSession response:', data);
            console.debug('document.cookie (client):', document.cookie);
            if (data.success) {
                updateUI(data.data);
            } else {
                clearUI();
            }
            return data;
        })
        .catch(error => {
            console.error('Session check error:', error);
            clearUI();
            throw error;
        });
}

function updateUI(user) {
    const authLinks = document.getElementById('authLinks');
    const userLinks = document.getElementById('userLinks');
    const userName = document.getElementById('userName');
    const adminLink = document.getElementById('adminLink');

    if (authLinks) authLinks.style.display = 'none';
    if (userLinks) userLinks.style.display = 'flex';
    if (userName) userName.textContent = user.first_name || user.username;

    // Store user data in sessionStorage for later checks
    try {
        sessionStorage.setItem('user_role', user.role || 'customer');
        sessionStorage.setItem('user_data', JSON.stringify(user));
    } catch(e) {
        console.warn('Could not store user data in sessionStorage');
    }

    if (adminLink && user.role === 'admin') {
        adminLink.style.display = 'block';
        const adminUrl = currentPath.includes('/pages/') || currentPath.includes('/admin/') ? '../admin/dashboard.php' : 'admin/dashboard.php';
        adminLink.href = adminUrl;
    }

    // Update cart count
    loadCartCount();
    loadCategories();
}

function clearUI() {
    const authLinks = document.getElementById('authLinks');
    const userLinks = document.getElementById('userLinks');

    if (authLinks) authLinks.style.display = 'flex';
    if (userLinks) userLinks.style.display = 'none';
    
    localStorage.removeItem('user_data');
    localStorage.removeItem('session_id');
    
    // Clear sessionStorage
    try {
        sessionStorage.removeItem('user_role');
        sessionStorage.removeItem('user_data');
    } catch(e) {
        console.warn('Could not clear sessionStorage');
    }
}

// ===== Logout =====
function logout() {
    fetch(`${API_BASE}/auth.php?action=logout`, {
        method: 'POST',
        credentials: 'same-origin'
    })
    .then(safeJsonParse)
    .then(data => {
        if (data.success) {
            clearUI();
            showSuccess('Logged out successfully');
            setTimeout(() => {
                const homeUrl = currentPath.includes('/pages/') || currentPath.includes('/admin/') ? '../index.php' : './index.php';
                window.location.href = homeUrl;
            }, 1500);
        }
    })
    .catch(error => {
        showError('Logout failed: ' + error.message);
    });
}

// ===== Cart Functions =====
function loadCartCount() {
    if (!isLoggedIn()) {
        updateCartCount(0);
        return;
    }
    fetch(`${API_BASE}/cart.php?action=view`, { credentials: 'same-origin' })
        .then(safeJsonParse)
        .then(data => {
            if (data.success) {
                updateCartCount(data.data.item_count);
            }
        })
        .catch(error => console.error('Cart count error:', error));
}

function updateCartCount(count) {
    const cartCount = document.getElementById('cartCount');
    if (cartCount) {
        cartCount.textContent = count;
    }
}

function addToCart(productId, quantity = 1) {
    if (!isLoggedIn()) {
        showError('Please login to add items to cart');
        setTimeout(() => {
            const loginUrl = currentPath.includes('/pages/') || currentPath.includes('/admin/') ? 'login.php' : './pages/login.php';
            window.location.href = loginUrl;
        }, 1500);
        return;
    }
    

    fetch(`${API_BASE}/cart.php?action=add`, {
        method: 'POST',
        credentials: 'same-origin',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            product_id: productId,
            quantity: quantity
        })
    })
    .then(safeJsonParse)
    .then(data => {
        if (data.success) {
            showSuccess('Product added to cart!');
            loadCartCount();
        } else {
            showError(data.message);
        }
    })
    .catch(error => {
        showError('Error adding to cart: ' + error.message);
    });
}

// ===== Category Functions =====
function loadCategories() {
    fetch(`${API_BASE}/categories.php?action=list`, { credentials: 'same-origin' })
        .then(safeJsonParse)
        .then(data => {
            if (data.success) {
                populateCategories(data.data);
            } else {
                console.error('Categories error:', data.message);
            }
        })
        .catch(error => console.error('Categories fetch error:', error));
}

function populateCategories(categories) {
    // Dropdown in navbar
    const dropdown = document.getElementById('categoryDropdown');
    if (dropdown) {
        dropdown.innerHTML = '';
        categories.forEach(cat => {
            const link = document.createElement('a');
            const prodUrl = currentPath.includes('/pages/') || currentPath.includes('/admin/') 
                ? `products.php?category=${cat.category_id}` 
                : `pages/products.php?category=${cat.category_id}`;
            link.href = prodUrl;
            link.textContent = `${cat.category_name} (${cat.product_count})`;
            link.style.cursor = 'pointer';
            link.addEventListener('click', (e) => {
                e.preventDefault();
                window.location.href = prodUrl;
            });
            dropdown.appendChild(link);
        });
    }

    // Search filter (clear existing to avoid duplicates)
    const searchCategory = document.getElementById('searchCategory');
    if (searchCategory) {
        // Only add categories that don't already exist
        const isFirstLoad = searchCategory.children.length <= 1;
        if (isFirstLoad) {
            categories.forEach(cat => {
                const option = document.createElement('option');
                option.value = cat.category_id;
                option.textContent = cat.category_name;
                searchCategory.appendChild(option);
            });
        }
    }

    // Categories grid on home page
    const categoriesGrid = document.getElementById('categoriesGrid');
    if (categoriesGrid) {
        categoriesGrid.innerHTML = '';
        categories.forEach(cat => {
            const card = document.createElement('div');
            card.className = 'category-card';
            card.innerHTML = `
                <div class="category-icon">📦</div>
                <div class="category-name">${cat.category_name}</div>
                <div class="category-count">${cat.product_count} products</div>
            `;
            card.style.cursor = 'pointer';
            card.addEventListener('click', () => {
                const url = currentPath.includes('/pages/') || currentPath.includes('/admin/') 
                    ? `products.php?category=${cat.category_id}` 
                    : `pages/products.php?category=${cat.category_id}`;
                window.location.href = url;
            });
            categoriesGrid.appendChild(card);
        });
    }
}

// ===== Product Display =====
function createProductCard(product) {
    const card = document.createElement('div');
    card.className = 'product-card';
    
    // Fix image URL by stripping leading slash and constructing correct relative path
    let imageUrl;
    if (product.image_url) {
        // Remove leading slash if present
        let imagePath = product.image_url.startsWith('/') 
            ? product.image_url.substring(1) 
            : product.image_url;
        
        // Check if we're in a subfolder (pages/ or admin/)
        const inSubfolder = currentPath.includes('/pages/') || currentPath.includes('/admin/');
        
        // If in subfolder, add ../ prefix; otherwise use as-is
        imageUrl = inSubfolder ? `../${imagePath}` : imagePath;
    } else {
        const inSubfolder = currentPath.includes('/pages/') || currentPath.includes('/admin/');
        imageUrl = inSubfolder 
            ? '../assets/images/products/placeholder.png'
            : 'assets/images/products/placeholder.png';
    }
    
    // Convert stock_quantity to number (database returns it as string)
    const stockQty = parseInt(product.stock_quantity, 10) || 0;
    
    let stockStatus = '<span class="text-success">In Stock</span>';
    let stockBadge = '';
    
    if (stockQty === 0) {
        stockStatus = '<span class="text-danger">Out of Stock</span>';
        stockBadge = '<div class="product-badge">Out of Stock</div>';
    } else if (stockQty < 5) {
        stockBadge = '<div class="product-badge">Limited</div>';
    }

    const rating = product.rating || 0;
    const ratingStars = '⭐'.repeat(Math.floor(rating));

    const inSubfolder = currentPath.includes('/pages/') || currentPath.includes('/admin/');
    const fallbackImage = inSubfolder 
        ? '../assets/images/products/placeholder.png' 
        : 'assets/images/products/placeholder.png';

    card.innerHTML = `
        <div class="product-image">
            ${stockBadge}
            <img src="${imageUrl}" alt="${product.product_name}" onerror="this.src='${fallbackImage}'">
        </div>
        <div class="product-info">
            <div class="product-category">${product.category_name || 'Electronics'}</div>
            <div class="product-name">${product.product_name}</div>
            <div class="product-description">${product.description.substring(0, 80)}...</div>
            ${ratingStars ? `<div class="product-rating">${ratingStars}</div>` : ''}
            <div class="product-price">$${parseFloat(product.price).toFixed(2)}</div>
            <div class="product-stock">${stockStatus}</div>
            <div class="product-actions">
                <button class="btn btn-secondary btn-small" onclick="viewProduct(${product.product_id})">
                    View Details
                </button>
                <button class="btn btn-primary btn-small" onclick="addToCart(${product.product_id})" ${stockQty === 0 ? 'disabled' : ''}>
                    🛒 Add
                </button>
            </div>
        </div>
    `;
    
    return card;
}

function viewProduct(productId) {
    const detailUrl = currentPath.includes('/pages/') || currentPath.includes('/admin/') ? `product-detail.php?id=${productId}` : `pages/product-detail.php?id=${productId}`;
    window.location.href = detailUrl;
}

// ===== Modal Functions =====
function showSuccess(message) {
    const modal = document.getElementById('successModal');
    const messageEl = document.getElementById('successMessage');
    
    if (messageEl) messageEl.textContent = message;
    if (modal) modal.classList.add('show');
    
    setTimeout(() => {
        if (modal) modal.classList.remove('show');
    }, 3000);
}

function showError(message) {
    const modal = document.getElementById('errorModal');
    const messageEl = document.getElementById('errorMessage');
    
    if (messageEl) messageEl.textContent = message;
    if (modal) modal.classList.add('show');
    
    setTimeout(() => {
        if (modal) modal.classList.remove('show');
    }, 4000);
}

// ===== Authentication Check =====
function isLoggedIn() {
    // Check if user_id or username is in sessionStorage (primary method)
    const userId = sessionStorage.getItem('user_id');
    const username = sessionStorage.getItem('username');
    if (userId || username) {
        return true;
    }
    
    // Fallback: check if session exists by checking for auth links visibility
    const authLinks = document.getElementById('authLinks');
    if (!authLinks)
        return false;
    const style = window.getComputedStyle(authLinks);
    return style.display === 'none';
}

function isAdmin() {
    try {
        const role = sessionStorage.getItem('user_role');
        console.debug('isAdmin check - sessionStorage role:', role);
        return role === 'admin';
    } catch(e) {
        console.warn('Could not check sessionStorage:', e);
        // If sessionStorage is not available, default to false
        return false;
    }
}

// ===== Form Validation =====
function validateEmail(email) {
    const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return re.test(email);
}

function validatePassword(password) {
    // Min 8 chars, uppercase, lowercase, number
    return password.length >= 8 && 
           /[A-Z]/.test(password) && 
           /[a-z]/.test(password) && 
           /[0-9]/.test(password);
}

function validateForm(fields) {
    const errors = {};

    for (const [key, value] of Object.entries(fields)) {
        if (value.required && !value.value) {
            errors[key] = `${key} is required`;
        } else if (value.type === 'email' && value.value && !validateEmail(value.value)) {
            errors[key] = 'Invalid email format';
        } else if (value.type === 'password' && value.value && !validatePassword(value.value)) {
            errors[key] = 'Password must be at least 8 characters with uppercase, lowercase, and number';
        }
    }

    return errors;
}

function displayFormErrors(errors, formId) {
    // Clear previous errors
    document.querySelectorAll('.form-error').forEach(el => {
        el.classList.remove('show');
        el.textContent = '';
    });

    // Display new errors
    Object.entries(errors).forEach(([field, message]) => {
        const errorEl = document.querySelector(`#${formId} .error-${field}`);
        if (errorEl) {
            errorEl.textContent = message;
            errorEl.classList.add('show');
        }
    });
}

// ===== Hamburger Menu =====
function setupHamburger() {
    const hamburger = document.getElementById('hamburger');
    const navMenu = document.getElementById('navMenu');
    const navLinks = document.querySelectorAll('.nav-link');

    if (hamburger) {
        hamburger.addEventListener('click', () => {
            navMenu.classList.toggle('active');
        });

        // Close menu when link is clicked
        navLinks.forEach(link => {
            link.addEventListener('click', () => {
                navMenu.classList.remove('active');
            });
        });
    }
}

// ===== Modal Close Handlers =====
function setupModals() {
    const closeButtons = document.querySelectorAll('.close');
    const modals = document.querySelectorAll('.modal');

    closeButtons.forEach(btn => {
        btn.addEventListener('click', (e) => {
            e.target.closest('.modal').classList.remove('show');
        });
    });

    modals.forEach(modal => {
        modal.addEventListener('click', (e) => {
            if (e.target === modal) {
                modal.classList.remove('show');
            }
        });
    });
}

// ===== Logout Button Handler =====
function setupLogout() {
    const logoutBtn = document.getElementById('logoutBtn');
    if (logoutBtn) {
        logoutBtn.addEventListener('click', (e) => {
            e.preventDefault();
            if (confirm('Are you sure you want to logout?')) {
                logout();
            }
        });
    }
}

// ===== Format Currency =====
function formatCurrency(amount) {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD'
    }).format(amount);
}

// ===== Pagination =====
function createPagination(currentPage, totalPages, onPageChange) {
    const container = document.createElement('div');
    container.className = 'pagination';

    if (currentPage > 1) {
        const prevBtn = document.createElement('a');
        prevBtn.textContent = '← Previous';
        prevBtn.href = '#';
        prevBtn.addEventListener('click', (e) => {
            e.preventDefault();
            onPageChange(currentPage - 1);
        });
        container.appendChild(prevBtn);
    }

    for (let i = 1; i <= totalPages; i++) {
        if (i === currentPage) {
            const span = document.createElement('span');
            span.className = 'active';
            span.textContent = i;
            container.appendChild(span);
        } else {
            const link = document.createElement('a');
            link.textContent = i;
            link.href = '#';
            link.addEventListener('click', (e) => {
                e.preventDefault();
                onPageChange(i);
            });
            container.appendChild(link);
        }
    }

    if (currentPage < totalPages) {
        const nextBtn = document.createElement('a');
        nextBtn.textContent = 'Next →';
        nextBtn.href = '#';
        nextBtn.addEventListener('click', (e) => {
            e.preventDefault();
            onPageChange(currentPage + 1);
        });
        container.appendChild(nextBtn);
    }

    return container;
}

// ===== Initialize on Page Load =====
document.addEventListener('DOMContentLoaded', () => {
    // Normalize nav links (fix relative paths across folders)
    try {
        normalizeNavbarLinks();
    } catch (e) {
        console.error('normalizeNavbarLinks error', e);
    }

    checkSession();
    setupHamburger();
    setupModals();
    setupLogout();
    // Always load categories for navigation/search even if user not logged in
    try { loadCategories(); } catch (e) { console.error('loadCategories failed:', e); }
});

// Normalize navigation links so they work correctly from root, /pages/ and /admin/
function normalizeNavbarLinks() {
    const path = window.location.pathname;
    const inSubfolder = path.includes('/pages/') || path.includes('/admin/');

    const nav = document.querySelector('.nav-menu') || document.getElementById('navMenu');
    if (!nav) return;

    const anchors = nav.querySelectorAll('a.nav-link');
    anchors.forEach(a => {
        const text = (a.textContent || '').toLowerCase();

        if (text.includes('home') || text.includes('store')) {
            a.href = inSubfolder ? '../index.php' : 'index.php';
        } else if (text.includes('shop') || (a.getAttribute('href') || '').includes('products.php')) {
            a.href = inSubfolder ? 'products.php' : 'pages/products.php';
        } else if (text.includes('cart') || (a.getAttribute('href') || '').includes('cart.php')) {
            a.href = inSubfolder ? 'cart.php' : 'pages/cart.php';
        } else if (text.includes('login')) {
            a.href = inSubfolder ? 'login.php' : 'pages/login.php';
        } else if (text.includes('register')) {
            a.href = inSubfolder ? 'register.php' : 'pages/register.php';
        } else if (text.includes('orders')) {
            a.href = inSubfolder ? 'orders.php' : 'pages/orders.php';
        }
    });
}

// Global error handler to log uncaught errors
window.addEventListener('error', function (e) {
    console.error('Uncaught error:', e.message, 'at', e.filename + ':' + e.lineno);
});
