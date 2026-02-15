<?php
/**
 * Insert Sample Data
 * Adds demo users and products for testing
 */

header('Content-Type: application/json');

require_once 'config.php';

$response = [
    'success' => false,
    'inserted' => [],
    'errors' => []
];

try {
    // Check if users already exist
    $res = $mysqli->query("SELECT COUNT(*) as count FROM users");
    if (!$res) {
        throw new Exception('Failed to check users table: ' . $mysqli->error);
    }
    $existing = $res->fetch_assoc();
    
    if ($existing['count'] > 0) {
        $response['message'] = 'Sample data already exists. Skipping insertion.';
        $response['existing_users'] = $existing['count'];
        $response['success'] = true;
        echo json_encode($response);
        exit;
    }

    // Password: admin123 and customer123 (hashed)
    $admin_password = password_hash('admin123', PASSWORD_BCRYPT, ['cost' => 10]);
    $customer_password = password_hash('customer123', PASSWORD_BCRYPT, ['cost' => 10]);

    // Insert admin user
    $stmt = $mysqli->prepare("INSERT INTO users (username, email, password_hash, first_name, last_name, role, is_active) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $username = 'admin';
    $email = 'admin@electrohub.com';
    $fname = 'Admin';
    $lname = 'User';
    $role = 'admin';
    $active = 1;
    $stmt->bind_param("ssssssi", 
        $username,
        $email,
        $admin_password,
        $fname,
        $lname,
        $role,
        $active
    );
    
    if (!$stmt->execute()) {
        throw new Exception("Failed to insert admin user: " . $stmt->error);
    }
    $response['inserted'][] = 'Admin user (admin / admin123)';
    $stmt->close();

    // Insert customer user
    $stmt = $mysqli->prepare("INSERT INTO users (username, email, password_hash, first_name, last_name, role, is_active) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $username = 'customer';
    $email = 'customer@example.com';
    $fname = 'Customer';
    $lname = 'User';
    $role = 'customer';
    $active = 1;
    $stmt->bind_param("ssssssi",
        $username,
        $email,
        $customer_password,
        $fname,
        $lname,
        $role,
        $active
    );
    
    if (!$stmt->execute()) {
        throw new Exception("Failed to insert customer user: " . $stmt->error);
    }
    $response['inserted'][] = 'Customer user (customer / customer123)';
    $stmt->close();

    // Insert test user
    $stmt = $mysqli->prepare("INSERT INTO users (username, email, password_hash, first_name, last_name, role, is_active) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $username = 'testuser';
    $email = 'testuser@example.com';
    $fname = 'Test';
    $lname = 'User';
    $role = 'customer';
    $active = 1;
    $stmt->bind_param("ssssssi",
        $username,
        $email,
        $customer_password,
        $fname,
        $lname,
        $role,
        $active
    );
    
    if (!$stmt->execute()) {
        throw new Exception("Failed to insert test user: " . $stmt->error);
    }
    $response['inserted'][] = 'Test user (testuser / customer123)';
    $stmt->close();

    // Check if categories exist, if not insert them
    $cat_res = $mysqli->query("SELECT COUNT(*) as count FROM categories");
    if (!$cat_res) {
        throw new Exception('Failed to check categories table: ' . $mysqli->error);
    }
    $cat_check = $cat_res->fetch_assoc();
    
    if ($cat_check['count'] === 0) {
        $categories = [
            ['Laptops', 'High-performance laptops for work and gaming'],
            ['Smartphones', 'Latest smartphones with advanced features'],
            ['Tablets', 'Portable tablets for entertainment and productivity'],
            ['Headphones', 'Premium audio devices and headphones'],
            ['Cameras', 'Digital cameras and photography equipment'],
            ['Accessories', 'Phone and computer accessories']
        ];

        $stmt = $mysqli->prepare("INSERT INTO categories (category_name, description) VALUES (?, ?)");
        
        foreach ($categories as $cat) {
            $stmt->bind_param("ss", $cat[0], $cat[1]);
            if (!$stmt->execute()) {
                throw new Exception("Failed to insert category: " . $stmt->error);
            }
        }
        $response['inserted'][] = count($categories) . ' product categories';
        $stmt->close();
    }

    // Check if products exist, if not insert sample products
    $prod_res = $mysqli->query("SELECT COUNT(*) as count FROM products");
    if (!$prod_res) {
        throw new Exception('Failed to check products table: ' . $mysqli->error);
    }
    $prod_check = $prod_res->fetch_assoc();
    
    if ($prod_check['count'] === 0) {
        $products = [
            // Laptops (5)
            [1, 'Dell XPS 13 FHD', 'Compact and powerful laptop with Intel i7, 16GB RAM, 512GB SSD. Perfect for professionals on the go with stunning FHD display', 1299.99, 15, 'DELL-XPS13-001', '/assets/images/products/dell-xps13-1.jpg'],
            [1, 'Dell XPS 13 4K', 'Premium variant with 4K OLED display, Intel i9, 32GB RAM, 1TB SSD. Ultimate clarity and performance', 1899.99, 8, 'DELL-XPS13-002', '/assets/images/products/dell-xps13-2.jpg'],
            [1, 'Dell XPS 13 Plus', 'Next-gen design with Intel i5, 8GB RAM, 256GB SSD. Lightweight student and casual use option', 799.99, 20, 'DELL-XPS13-003', '/assets/images/products/dell-xps13-3.jpg'],
            [1, 'Dell XPS 13 Business', 'Business edition with Intel i7, 16GB RAM, Windows Pro, security features', 1449.99, 12, 'DELL-XPS13-004', '/assets/images/products/dell-xps13-4.jpg'],
            [1, 'Dell XPS 13 Creator', 'Optimized for content creators with RTX GPU, color-accurate display, 32GB RAM', 2099.99, 5, 'DELL-XPS13-005', '/assets/images/products/dell-xps13-5.jpg'],
            
            // MacBooks (3)
            [1, 'MacBook Pro 14 M3', 'Pro-grade laptop with Apple M3 chip, 8-core GPU, 16GB RAM, 512GB SSD. Great for development', 1799.99, 12, 'APPLE-MBP14-001', '/assets/images/products/macbook-pro14-1.jpg'],
            [1, 'MacBook Pro 14 M3 Max', 'Maximum performance with M3 Max chip, 36GB RAM, 1TB SSD, up to 30-hour battery life', 2499.99, 8, 'APPLE-MBP14-002', '/assets/images/products/macbook-pro14-2.jpg'],
            [1, 'MacBook Pro 14 M3 Ultra', 'Ultimate powerhouse with M3 Ultra chip, 64GB RAM, 2TB SSD. For professional video and 3D work', 3299.99, 3, 'APPLE-MBP14-003', '/assets/images/products/macbook-pro14-3.jpg'],
            
            // iPhones (5)
            [2, 'iPhone 15 Pro Max', 'Largest Pro model with 6.7" display, 1TB storage option, titanium design, advanced camera system', 1199.99, 18, 'APPLE-IP15P-001', '/assets/images/products/iphone15pro-1.jpg'],
            [2, 'iPhone 15 Pro Standard', 'Latest Apple iPhone with A17 Pro chip, 128GB storage, titanium design, ProMotion display', 999.99, 25, 'APPLE-IP15P-002', '/assets/images/products/iphone15pro-2.jpg'],
            [2, 'iPhone 15 Pro 256GB', '256GB storage variant with enhanced camera capabilities and video recording features', 1099.99, 20, 'APPLE-IP15P-003', '/assets/images/products/iphone15pro-3.jpg'],
            [2, 'iPhone 15 Pro 512GB', '512GB storage for power users and content creators who need maximum space', 1299.99, 15, 'APPLE-IP15P-004', '/assets/images/products/iphone15pro-4.jpg'],
            [2, 'iPhone 15 Pro Max 1TB', '1TB variant of the premium iPhone 15 Pro Max for professionals', 1399.99, 8, 'APPLE-IP15P-005', '/assets/images/products/iphone15pro-5.jpg'],
            
            // Samsung Galaxy S24 (4)
            [2, 'Samsung Galaxy S24 FE', 'Affordable flagship killer with Snapdragon 8 Gen 3, 128GB storage, great camera', 699.99, 30, 'SAMSUNG-GS24-001', '/assets/images/products/galaxy-s24-1.jpg'],
            [2, 'Samsung Galaxy S24+', 'Plus model with 6.7" display, 256GB storage, enhanced battery capacity, fast charging', 899.99, 25, 'SAMSUNG-GS24-002', '/assets/images/products/galaxy-s24-2.jpg'],
            [2, 'Samsung Galaxy S24 Ultra', 'Premium ultra model with stylus, titanium frame, 512GB storage, professional camera suite', 1299.99, 12, 'SAMSUNG-GS24-003', '/assets/images/products/galaxy-s24-3.jpg'],
            [2, 'Samsung Galaxy S24 Edge', 'Latest variant with edge-to-edge display, minimal bezels, exclusive color options', 999.99, 18, 'SAMSUNG-GS24-004', '/assets/images/products/galaxy-s24-4.jpg'],
            
            // iPad (3)
            [3, 'iPad Pro 12.9 256GB', 'Apple iPad Pro with M2 chip, 256GB storage, Liquid Retina display, ideal for creative professionals', 1099.99, 12, 'APPLE-IPP129-001', '/assets/images/products/ipad-pro-1.jpg'],
            [3, 'iPad Pro 12.9 512GB', '512GB high-capacity storage variant for power users and designers', 1299.99, 8, 'APPLE-IPP129-002', '/assets/images/products/ipad-pro-2.jpg'],
            [3, 'iPad Pro 12.9 1TB', '1TB premium variant with maximum storage and performance for video editors and creators', 1599.99, 5, 'APPLE-IPP129-003', '/assets/images/products/ipad-pro-3.jpg'],
            
            // Sony Headphones (3)
            [4, 'Sony WH-1000XM5 Black', 'Premium noise-cancelling wireless headphones with 30-hour battery, industry-leading ANC technology', 399.99, 20, 'SONY-WH1000-001', '/assets/images/products/sony-wh1000xm5-1.jpg'],
            [4, 'Sony WH-1000XM5 Silver', 'Same premium features in elegant silver finish with free carrying case', 399.99, 15, 'SONY-WH1000-002', '/assets/images/products/sony-wh1000xm5-2.jpg'],
            [4, 'Sony WH-1000XM5 Blue', 'Premium variant in midnight blue with studio-quality sound and multipoint connection', 399.99, 12, 'SONY-WH1000-003', '/assets/images/products/sony-wh1000xm5-3.jpg'],
            
            // AirPods (3)
            [4, 'Apple AirPods Pro Max', 'Premium over-ear wireless headphones with spatial audio and dynamic head tracking', 549.99, 10, 'APPLE-APP-MAX', '/assets/images/products/airpods-pro-1.jpg'],
            [4, 'Apple AirPods Pro 2', 'Premium wireless earbuds with active noise cancellation and transparency mode', 249.99, 40, 'APPLE-APP-001', '/assets/images/products/airpods-pro-2.jpg'],
            [4, 'Apple AirPods Air', 'Versatile wireless earbuds with adaptive audio for any environment', 179.99, 35, 'APPLE-APP-AIR', '/assets/images/products/airpods-pro-3.jpg'],
            
            // Cameras (3)
            [5, 'Canon EOS R6 Body Only', 'Professional mirrorless camera with 20MP sensor and 4K video, body only', 2499.99, 8, 'CANON-R6-001', '/assets/images/products/canon-r6-1.jpg'],
            [5, 'Canon EOS R6 with 24-70mm', 'Canon EOS R6 professional package with premium zoom lens included', 3299.99, 5, 'CANON-R6-002', '/assets/images/products/canon-r6-2.jpg'],
            [5, 'Canon EOS R6 Mark II', 'Latest R6 variant with improved autofocus, higher fps shooting, 8K capabilities', 3999.99, 3, 'CANON-R6-003', '/assets/images/products/canon-r6-3.jpg'],
            
            // Accessories (4)
            [6, 'USB-C Cable 2m Nylon', 'High-quality nylon-braided USB-C charging and data cable, 100W power delivery', 19.99, 100, 'GENERIC-USBC-001', '/assets/images/products/usbc-cable-1.jpg'],
            [6, 'USB-C Cable 3m Kevlar', 'Extra-long 3m USB-C cable with Kevlar reinforcement for maximum durability', 24.99, 80, 'GENERIC-USBC-002', '/assets/images/products/usbc-cable-2.jpg'],
            [6, 'Tempered Glass Protector 9H', 'Premium 9H hardness tempered glass screen protector for all smartphones', 9.99, 150, 'GENERIC-PROT-001', '/assets/images/products/protector-1.jpg'],
            [6, 'Privacy Screen Protector', 'Anti-spy tempered glass protector with privacy filter, 9H hardness', 14.99, 120, 'GENERIC-PROT-002', '/assets/images/products/protector-2.jpg']
        ];

        $stmt = $mysqli->prepare("INSERT INTO products (category_id, product_name, description, price, stock_quantity, sku, image_url) VALUES (?, ?, ?, ?, ?, ?, ?)");
        if (!$stmt) {
            throw new Exception('Failed to prepare product insert: ' . $mysqli->error);
        }

        foreach ($products as $prod) {
            $category_id = (int)$prod[0];
            $product_name = $prod[1];
            $description = $prod[2];
            $price = (float)$prod[3];
            $stock_quantity = (int)$prod[4];
            $sku = $prod[5];
            $image_url = $prod[6];

            $stmt->bind_param("issdiis", $category_id, $product_name, $description, $price, $stock_quantity, $sku, $image_url);
            if (!$stmt->execute()) {
                throw new Exception("Failed to insert product: " . $stmt->error);
            }
        }
        $response['inserted'][] = count($products) . ' sample products (33 products with images)';
        $stmt->close();
    }

    $response['success'] = true;
    $response['message'] = 'Sample data inserted successfully!';
    $response['test_credentials'] = [
        'admin' => ['username' => 'admin', 'password' => 'admin123'],
        'customer' => ['username' => 'customer1', 'password' => 'password']
    ];

} catch (Exception $e) {
    $response['errors'][] = $e->getMessage();
    $response['success'] = false;
}

echo json_encode($response, JSON_PRETTY_PRINT);
$mysqli->close();
?>
