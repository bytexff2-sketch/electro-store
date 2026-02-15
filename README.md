# ElectroHub - Electronics Store E-Commerce Application

## Project Overview

ElectroHub is a complete, fully functional e-commerce platform for selling electronics online. It's built using modern web technologies and follows best practices for security, database design, and responsive web development.

**Key Features:**
- User registration and login with secure authentication
- Customer shopping functionality with product catalog
- Shopping cart and checkout system
- Order management for customers
- Admin dashboard for inventory management
- Role-based access control (Customer vs Admin)
- Responsive design for all devices
- RESTful API backend
- MySQL database with 3NF normalization

---

## System Architecture

### Technology Stack

**Frontend:**
- HTML5
- CSS3 (Vanilla, no frameworks)
- JavaScript (Vanilla, no frameworks)
- Fetch API for AJAX communications

**Backend:**
- PHP 7.4+
- MySQL 5.7+

**Database:**
- MySQL with normalized schema (3NF)

### Project Structure
```
electronics-store/
├── index.php                    # Home page
├── backend/
│   ├── config.php              # Database configuration
│   ├── utils.php               # Security and utility functions
│   ├── database/
│   │   └── electronics_store.sql  # Database schema
│   └── api/
│       ├── auth.php            # Authentication endpoints
│       ├── products.php        # Product management
│       ├── categories.php      # Category management
│       ├── cart.php            # Shopping cart
│       └── orders.php          # Order management
├── pages/
│   ├── login.php               # Login page
│   ├── register.php            # Registration page
│   ├── products.php            # Products listing
│   ├── cart.php                # Shopping cart
│   ├── orders.php              # Order history
│   └── product-detail.php      # Product details
├── admin/
│   └── dashboard.php           # Admin dashboard
├── assets/
│   ├── css/
│   │   ├── style.css           # Main styles
│   │   └── responsive.css      # Mobile responsive styles
│   ├── js/
│   │   ├── common.js           # Shared JavaScript
│   │   └── index.js            # Homepage functionality
│   └── images/                 # Product images
└── README.md                   # This file
```

---

## Database Design

### Tables (7 tables in 3NF)

#### 1. **users**
- Primary key: `user_id`
- Contains user profile information and role assignments
- Roles: 'customer' or 'admin'

#### 2. **categories**
- Primary key: `category_id`
- Stores product categories (Laptops, Phones, Headphones, etc.)

#### 3. **products**
- Primary key: `product_id`
- Foreign key: `category_id` (categories)
- Stores all product information with pricing and inventory

#### 4. **orders**
- Primary key: `order_id`
- Foreign key: `user_id` (users)
- Captures customer orders with delivery and payment info
- Status tracking: pending, confirmed, shipped, delivered, cancelled

#### 5. **order_items**
- Primary key: `order_item_id`
- Composite foreign keys: `order_id`, `product_id`
- Line items for each order with quantity and pricing

#### 6. **shopping_cart**
- Primary key: `cart_id`
- Foreign keys: `user_id`, `product_id`
- Temporary storage for items before checkout

#### 7. **reviews**
- Primary key: `review_id`
- Foreign keys: `product_id`, `user_id`
- Customer reviews and ratings (1-5 stars)

### Database Normalization

All tables follow **Third Normal Form (3NF)**:
- ✓ No duplicate groups of columns
- ✓ Proper use of foreign keys for relationships
- ✓ No transitive dependencies
- ✓ All non-key attributes depend on the primary key

---

## Installation & Setup

### Prerequisites
- PHP 7.4 or higher
- MySQL 5.7 or higher
- Web server (Apache/Nginx)
- Git (optional)

### Step 1: Setup Web Server

1. Clone or download the project to your web root:
   - Windows: `C:\xampp\htdocs\electronics-store`
   - Linux: `/var/www/html/electronics-store`

2. Ensure project is accessible at `http://localhost/electronics-store`

### Step 2: Create Database

1. Open phpMyAdmin or MySQL client
2. Run the SQL file:
   ```sql
   source backend/database/electronics_store.sql;
   ```
   Or copy the entire contents of `electronics_store.sql` and execute

3. Verify database created:
   ```sql
   USE electronics_store;
   SHOW TABLES;
   ```

### Step 3: Configure Database Connection

Edit `backend/config.php`:
```php
define('DB_HOST', 'localhost');    // Your MySQL host
define('DB_USER', 'root');         // Your MySQL username
define('DB_PASS', '');             // Your MySQL password
define('DB_NAME', 'electronics_store');
```

### Step 4: Set File Permissions

Ensure web server can write to:
```bash
chmod 755 assets/images
chmod 666 backend/config.php
```

### Step 5: Access the Application

Open browser and navigate to:
```
http://localhost/electronics-store
```

---

## User Guide

### Customer Features

#### 1. **Registration & Login**
- Navigate to `/pages/register.php`
- Fill in required information (First Name, Last Name, Email, Username, Password)
- Password must be: min 8 chars, uppercase, lowercase, number
- Login with username and password

#### 2. **Browse Products**
- Home page shows featured products
- Click "Shop" to see all products
- Use search and filter options
- Click product to view details

#### 3. **Shopping Cart**
- Click "Add to Cart" from product card or detail page
- Manage quantities in cart page
- Review order summary
- Proceed to checkout

#### 4. **Checkout & Orders**
- Enter delivery address and payment method
- Place order
- View order history in "Orders" section
- Track order status

### Admin Features

#### Access Admin Dashboard
- Login as admin (username: `admin`)
- Click "Admin" link in navigation or go to `/admin/dashboard.php`

#### Product Management
- View all products with inventory
- Add new products to catalog
- Edit product details (name, price, stock, description)
- Delete products (soft delete - marks as inactive)

#### Category Management
- Manage product categories
- Add, edit, or delete categories
- See product count per category

#### Order Management
- View all customer orders
- Update order status (pending → shipped → delivered)
- Monitor pending orders

---

## API Endpoints

All API endpoints return JSON and require proper authentication for protected routes.

### Authentication API (`/backend/api/auth.php`)

#### Register
```
POST /backend/api/auth.php?action=register
{
  "username": "john_doe",
  "email": "john@example.com",
  "password": "SecurePass123",
  "first_name": "John",
  "last_name": "Doe"
}
```

#### Login
```
POST /backend/api/auth.php?action=login
{
  "username": "john_doe",
  "password": "SecurePass123"
}
```

#### Logout
```
POST /backend/api/auth.php?action=logout
```

#### Check Session
```
GET /backend/api/auth.php?action=check
```

### Products API (`/backend/api/products.php`)

#### List Products
```
GET /backend/api/products.php?action=list&page=1
```

#### Get Single Product
```
GET /backend/api/products.php?action=get&id=1
```

#### Search Products
```
GET /backend/api/products.php?action=search&q=laptop&min_price=100&max_price=2000&category=1
```

#### Create Product (Admin)
```
POST /backend/api/products.php?action=create
{
  "product_name": "New Product",
  "category_id": 1,
  "price": 299.99,
  "stock_quantity": 10,
  "sku": "PROD-001",
  "description": "Product description"
}
```

#### Update Product (Admin)
```
PUT /backend/api/products.php?action=update&id=1
{
  "price": 249.99,
  "stock_quantity": 15
}
```

#### Delete Product (Admin)
```
DELETE /backend/api/products.php?action=delete&id=1
```

### Cart API (`/backend/api/cart.php`)

#### View Cart
```
GET /backend/api/cart.php?action=view
```

#### Add to Cart
```
POST /backend/api/cart.php?action=add
{
  "product_id": 1,
  "quantity": 2
}
```

#### Update Cart Item
```
PUT /backend/api/cart.php?action=update&id=1
{
  "quantity": 3
}
```

#### Remove from Cart
```
DELETE /backend/api/cart.php?action=remove&id=1
```

### Orders API (`/backend/api/orders.php`)

#### Create Order
```
POST /backend/api/orders.php?action=create
{
  "delivery_address": "123 Main St, City, State 12345",
  "payment_method": "Credit Card",
  "notes": "Optional delivery notes"
}
```

#### List User Orders
```
GET /backend/api/orders.php?action=list&page=1
```

#### Get Order Details
```
GET /backend/api/orders.php?action=get&id=1
```

#### Get All Orders (Admin)
```
GET /backend/api/orders.php?action=all&page=1
```

#### Update Order Status (Admin)
```
PUT /backend/api/orders.php?action=update&id=1
{
  "status": "shipped"
}
```

### Categories API (`/backend/api/categories.php`)

#### List Categories
```
GET /backend/api/categories.php?action=list
```

#### Create Category (Admin)
```
POST /backend/api/categories.php?action=create
{
  "category_name": "Tablets",
  "description": "Tablet devices"
}
```

---

## Security Features

### 1. **Password Security**
- Passwords hashed using bcrypt (PASSWORD_BCRYPT)
- Cost factor of 10 for secure hashing
- Password validation enforced (min 8 chars, uppercase, lowercase, number)

### 2. **Input Sanitization**
- All user inputs sanitized with `htmlspecialchars()`
- SQL injection prevented with prepared statements (MySQLi parameterized queries)
- XSS protection through output encoding

### 3. **Session Management**
- Server-side sessions with PHP `$_SESSION`
- User roles stored in session for authorization
- Session timeout: 1 hour

### 4. **Role-Based Access Control**
- Routes check for admin role before allowing modifications
- API functions verify user authentication
- Different views based on user role

### 5. **Database Security**
- Foreign key constraints enforce referential integrity
- Soft deletes for audit trail
- Proper data validation at database level

---

## Testing with Demo Data

### Available Demo Accounts

**Admin Account:**
- Username: `admin`
- Password: `password` (Note: Use actual password from database)

**Customer Account:**
- Username: `customer1`
- Password: `password` (Note: Use actual password from database)

### Test Data
- 6 product categories
- 10 sample products with various prices
- Sample orders for testing order management

---

## Features Overview

### ✅ Implemented Features
- [x] User registration with validation
- [x] Secure login system
- [x] User logout
- [x] Product catalog with search
- [x] Product filtering by category and price
- [x] Shopping cart with quantity management
- [x] Checkout and order creation
- [x] Order history and tracking
- [x] Admin dashboard
- [x] Product management (Add, Edit, Delete)
- [x] Category management
- [x] Order status tracking
- [x] Responsive design (Mobile, tablet, desktop)
- [x] Input validation
- [x] Password hashing
- [x] Prepared statements (SQL injection prevention)
- [x] AJAX/Fetch API integration
- [x] RESTful API backend
- [x] 3NF database design

---

## Performance Considerations

### Database Optimization
- Proper indexing on frequently queried columns
- Foreign key relationships for data integrity
- LIMIT clauses for pagination
- Efficient query structure

### Caching
- CSS and JavaScript minified recommendations
- Browser caching headers
- Session management for faster repeated requests

### Scalability Features
- Pagination for large product lists
- API-based architecture allows load balancing
- Database normalized for efficient data storage
- Stateless API design (except for sessions)

---

## Troubleshooting

### Database Connection Issues
**Error:** "Database connection failed"
- Check MySQL is running
- Verify credentials in `backend/config.php`
- Ensure database name is correct

### Login Issues
**Error:** "Invalid username or password"
- Verify user account exists in database
- Check password is correct (passwords are case-sensitive)
- Ensure user account is active (is_active = 1)

### 404 Errors
- Ensure project is in correct web root
- Check file names match exactly (case-sensitive on Linux)
- Verify .htaccess or web server routing if using subdirectories

### Session Issues
**Error:** "Not logged in" when should be logged in
- Clear browser cookies
- Check PHP session settings
- Verify sessions directory is writable

---

## File Permissions

Recommended permissions:
```bash
# PHP files
chmod 644 *.php backend/**/*.php pages/**/*.php admin/**/*.php

# Directories
chmod 755 backend backend/api backend/database
chmod 755 pages admin
chmod 755 assets assets/css assets/js assets/images

# Writable directories (if needed for uploads)
chmod 777 assets/images
```

---

## Development Notes

### Code Style
- Use prepared statements for all database queries
- Sanitize all user inputs
- Always validate data before processing
- Add error handling with try-catch blocks
- Use meaningful variable names

### Adding New Features
1. Add database schema changes to `electronics_store.sql`
2. Create API endpoints in `backend/api/`
3. Create frontend HTML pages in `pages/`
4. Add JavaScript functionality in `assets/js/`
5. Test thoroughly with both admin and customer roles

---

## 🚀 Deployment Guide

This project is ready to deploy to the cloud!

### Quick Deployment (Choose One):

**Railway** (Recommended - Easier)
1. Go to https://railway.app
2. Connect your GitHub account
3. Deploy this repository
4. Visit `/setup.php` to initialize the database
5. Done! 🎉

**Render**
1. Go to https://render.com
2. Connect your GitHub account
3. Deploy from blueprint
4. Visit `/setup.php` to initialize the database
5. Done! 🎉

### Detailed Instructions

See the complete deployment guides:
- 📖 **[DEPLOYMENT_GUIDE.md](DEPLOYMENT_GUIDE.md)** - Full step-by-step for both platforms
- ⚡ **[QUICK_START.md](QUICK_START.md)** - 60-second quick reference

### Environment Configuration

The app uses **environment variables** for configuration:

```bash
# Database
DB_HOST=your_host
DB_USER=your_user
DB_PASS=your_password
DB_NAME=electronics_store
DB_PORT=3306

# Application
APP_URL=https://your-domain.com
```

These are automatically detected from the hosting platform (Railway/Render).

---

## Pre-Deployment Checklist

Before deploying to production:
- [ ] Update admin password in setup/seed data
- [ ] Test all functionality locally
- [ ] Review database credentials (use platform secrets, not hardcoded)
- [ ] Ensure HTTPS is enforced
- [ ] Set proper file permissions
- [ ] Enable database backups on platform
- [ ] Test responsive design on mobile
- [ ] Configure error logging
- [ ] Remove setup.php after initialization
- [ ] Monitor error logs after deployment

---

## Future Enhancements

Possible features to add:
- Email notifications for orders
- Payment gateway integration
- Product reviews and ratings
- Wishlist functionality
- User profile management
- Email verification
- Password reset via email
- Advanced analytics/reports
- Multiple language support
- Two-factor authentication

---

## Support & Documentation

For more information:
- MySQL Documentation: https://dev.mysql.com/doc/
- PHP Documentation: https://www.php.net/docs.php
- MDN Web Docs: https://developer.mozilla.org/
- REST API Best Practices: https://restfulapi.net/

---

## License

This project is provided as-is for educational purposes.

---

## Author Notes

This e-commerce platform demonstrates:
- Full-stack web development
- Secure authentication and authorization
- Database design and normalization
- RESTful API design
- Responsive web design
- Input validation and sanitization
- Proper separation of concerns

**Created:** February 2026

---

**Enjoy your ElectroHub shopping experience!** 🛒⚡
