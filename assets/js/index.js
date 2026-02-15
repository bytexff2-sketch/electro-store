/**
 * Index Page JavaScript
 * Handles home page functionality
 */

let currentPage = 1;

document.addEventListener('DOMContentLoaded', () => {
    loadFeaturedProducts();
    setupSearch();
});

/**
 * Load and display featured products
 */
function loadFeaturedProducts() {
    const container = document.getElementById('featuredProducts');
    
    if (!container) return;

    container.innerHTML = '<div class="loading"><div class="spinner"></div></div>';

    fetch(`${API_BASE}/products.php?action=list&page=1`, { credentials: 'same-origin' })
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.text();
        })
        .then(text => {
            container.innerHTML = '';
            try {
                const data = JSON.parse(text);
                if (data.success && data.data.products.length > 0) {
                    data.data.products.forEach(product => {
                        const card = createProductCard(product);
                        container.appendChild(card);
                    });
                } else {
                    container.innerHTML = '<p class="text-center">No products available</p>';
                }
            } catch(e) {
                console.error('JSON Parse error:', e);
                console.error('Response text:', text);
                container.innerHTML = `<p class="text-center text-danger">Error loading products: Invalid response from server</p>`;
            }
        })
        .catch(error => {
            container.innerHTML = `<p class="text-center text-danger">Error loading products: ${error.message}</p>`;
            console.error('Fetch error:', error);
        });
}

/**
 * Setup search functionality
 */
function setupSearch() {
    const searchBtn = document.getElementById('searchBtn');
    const searchInput = document.getElementById('searchInput');
    const minPrice = document.getElementById('minPrice');
    const maxPrice = document.getElementById('maxPrice');
    const searchCategory = document.getElementById('searchCategory');

    if (searchBtn) {
        searchBtn.addEventListener('click', performSearch);
    }

    if (searchInput) {
        searchInput.addEventListener('keypress', (e) => {
            if (e.key === 'Enter') {
                performSearch();
            }
        });
    }
}

/**
 * Perform search
 */
function performSearch() {
    const searchInput = document.getElementById('searchInput');
    const minPrice = document.getElementById('minPrice');
    const maxPrice = document.getElementById('maxPrice');
    const searchCategory = document.getElementById('searchCategory');

    let url = `${API_BASE}/products.php?action=search`;

    if (searchInput && searchInput.value) {
        url += `&q=${encodeURIComponent(searchInput.value)}`;
    }
    if (minPrice && minPrice.value) {
        url += `&min_price=${minPrice.value}`;
    }
    if (maxPrice && maxPrice.value) {
        url += `&max_price=${maxPrice.value}`;
    }
    if (searchCategory && searchCategory.value) {
        url += `&category=${searchCategory.value}`;
    }

    fetch(url, { credentials: 'same-origin' })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                displaySearchResults(data.data.products);
            } else {
                showError(data.message);
            }
        })
        .catch(error => {
            showError('Search failed: ' + error.message);
        });
}

/**
 * Display search results
 */
function displaySearchResults(products) {
    const container = document.getElementById('featuredProducts');
    
    if (!container) return;

    container.innerHTML = '';

    if (products.length === 0) {
        container.innerHTML = '<p class="text-center">No products found</p>';
        return;
    }

    products.forEach(product => {
        const card = createProductCard(product);
        container.appendChild(card);
    });

    showSuccess(`Found ${products.length} product(s)`);
}
