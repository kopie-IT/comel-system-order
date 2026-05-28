# Comel Baby Store - Complete Project PRD

## 1. Project Overview

**Project Name**: Comel Baby Store Order System
**Version**: 1.0
**Date**: May 2026
**Type**: Mobile-First E-Commerce Web Application

### 1.1 Purpose
Comel Baby Store is a mobile-first e-commerce web application designed for a baby products store. It enables customers to browse products, manage a cart, place orders, and pay via QR code. Store administrators manage the full catalog, orders, and system settings through a secure admin panel.

### 1.2 Goals
- Provide a mobile-optimized catalog and shopping experience for customers
- Enable easy product and category management for store owners
- Automate order notifications via WhatsApp
- Implement fraud-safe payment verification via QR codes

### 1.3 Tech Stack

| Layer | Technology |
|-------|-----------|
| Backend | PHP 8.x (vanilla, no framework) |
| Database | MySQL / MariaDB |
| Frontend | HTML5, Tailwind CSS (CDN), Vanilla JavaScript |
| Icons | Font Awesome (CDN) |
| Server | Apache with mod_rewrite |

---

## 2. User Roles

### 2.1 Customer (Public User)

**Description**: Any visitor to the store. No registration required. Identified by phone number at checkout.

**Capabilities**:
- Browse product categories
- View products with images, descriptions, and sizes
- Add products to cart
- Checkout with name, phone, and address input
- View order confirmation with payment QR code
- Receive WhatsApp notification on order placement

### 2.2 Admin (Store Owner)

**Description**: Authenticated store owner with full management access.

**Capabilities**:
- Secure login with username and password
- Dashboard with order statistics and revenue
- Full CRUD on categories
- Full CRUD on products with image upload and size management
- View and update order statuses
- Configure system settings (WhatsApp number, QR code, payment instructions)
- View customer order history

---

## 3. Functional Requirements

---

### 3.1 Authentication Module (Admin)

#### 3.1.1 Admin Login
- **Function**: Authenticate admin user with username and password
- **Features**:
  - Username and password form
  - Password verified against bcrypt hash stored in `admins` table
  - On success: create PHP session and redirect to dashboard
  - On failure: show error message, do not reveal which field is wrong
  - Session timeout after inactivity
  - Redirect unauthenticated requests to login page

#### 3.1.2 Admin Logout
- **Function**: Destroy admin session and redirect to login
- **Features**:
  - Destroy PHP session on logout
  - Clear session cookie
  - Redirect to login page

---

### 3.2 Dashboard Module (Admin)

#### 3.2.1 Order Statistics
- **Function**: Display summary metrics for the store
- **Features**:
  - Total orders count
  - Total revenue (sum of completed orders)
  - Orders by status (pending, confirmed, completed, cancelled)
  - Recent orders list (last 10 orders)

#### 3.2.2 Quick Navigation
- **Function**: Provide shortcuts to key admin sections
- **Features**:
  - Links to Products, Categories, Orders, Settings
  - Highlight sections with pending actions (e.g. pending orders count badge)

---

### 3.3 Category Management Module (Admin)

#### 3.3.1 List Categories
- **Function**: Display all product categories
- **Features**:
  - Table view with category name, type, product count
  - Edit and delete action buttons per row
  - Add new category button

#### 3.3.2 Add Category
- **Function**: Create a new product category
- **Features**:
  - Input: category name
  - Input: category type (Pakaian / Produk)
  - Validation: name required, unique
  - On success: redirect to category list with success message

#### 3.3.3 Edit Category
- **Function**: Update an existing category
- **Features**:
  - Pre-filled form with current values
  - Same validation as Add Category
  - On success: redirect to category list with success message

#### 3.3.4 Delete Category
- **Function**: Remove a category
- **Features**:
  - Confirmation prompt before deletion
  - Prevent deletion if category has associated products
  - On success: redirect to category list with success message

---

### 3.4 Product Management Module (Admin)

#### 3.4.1 List Products
- **Function**: Display all products with search and filter
- **Features**:
  - Table/grid view with product image thumbnail, name, category, price, stock
  - Search by product name
  - Filter by category
  - Edit and delete action buttons per product
  - Add new product button

#### 3.4.2 Add Product
- **Function**: Create a new product listing
- **Features**:
  - Input: product name (required)
  - Input: description (textarea)
  - Input: category (dropdown from categories table)
  - Input: product image upload (JPEG/PNG/WebP, max 2MB)
  - Input: base price (for non-sized products)
  - Input: stock quantity (for non-sized products)
  - Dynamic size rows (for Pakaian category):
    - Add/remove size rows dynamically via JavaScript
    - Each row: size label (S/M/L/XL or age-based), price, stock quantity
  - Validation: name required, image required, valid price format
  - Image stored in `/public/uploads/products/`
  - On success: redirect to product list with success message

#### 3.4.3 Edit Product
- **Function**: Update an existing product
- **Features**:
  - Pre-filled form with current product data
  - Option to replace or keep existing image
  - Add/remove size rows for sized products
  - Update existing size pricing and stock
  - Same validation as Add Product
  - On success: redirect to product list with success message

#### 3.4.4 Delete Product
- **Function**: Remove a product listing
- **Features**:
  - Confirmation prompt before deletion
  - Delete associated product sizes from `product_sizes` table
  - Delete product image file from server
  - Prevent deletion if product is referenced in existing orders
  - On success: redirect to product list with success message

#### 3.4.5 Size Management (Sub-feature of Product)
- **Function**: Manage size variants for clothing products
- **Features**:
  - Sizes only applicable to Pakaian (clothing) category products
  - Each size has: label (e.g. S, M, L, XL, 0-3M, 3-6M), price, stock quantity
  - Multiple sizes per product
  - Sizes stored in `product_sizes` table linked to product

---

### 3.5 Customer-Facing Catalog Module

#### 3.5.1 Homepage
- **Function**: Entry point showing product categories
- **Features**:
  - Display all active categories as cards
  - Each card shows category name and icon/image
  - Mobile-first grid layout
  - Tap category card to navigate to category product listing

#### 3.5.2 Category Page
- **Function**: Display all products within a selected category
- **Features**:
  - Page title shows category name
  - Product grid with product image, name, and starting price
  - Tap product card to navigate to product detail page
  - Empty state message if no products in category

#### 3.5.3 Product Detail Page
- **Function**: Show full product information and allow add-to-cart
- **Features**:
  - Product image (full width on mobile)
  - Product name and description
  - Price display:
    - For non-sized products: single price
    - For sized products: price updates based on selected size
  - Size selector (radio buttons or dropdown) — only for Pakaian products
  - Stock availability indicator
  - Quantity selector (increment/decrement, min 1, max available stock)
  - Add to Cart button
  - Validation: size must be selected before adding to cart (for sized products)
  - Success feedback on add to cart (toast notification or cart count update)

---

### 3.6 Cart Module

#### 3.6.1 Cart Storage
- **Function**: Persist cart items during customer session
- **Features**:
  - Session-based storage (PHP `$_SESSION`)
  - No login required
  - Cart survives page navigation within session
  - Cart cleared after successful order placement

#### 3.6.2 View Cart
- **Function**: Display all items currently in cart
- **Features**:
  - List of cart items with: product image, name, size (if applicable), unit price, quantity, line total
  - Quantity update controls (increment/decrement per item)
  - Remove item button per line
  - Order subtotal
  - Proceed to Checkout button
  - Empty cart state with link back to homepage

#### 3.6.3 Update Cart
- **Function**: Modify item quantities in cart
- **Features**:
  - Increase or decrease quantity per item
  - Minimum quantity: 1
  - Maximum quantity: available stock for that product/size
  - Line total updates dynamically
  - Cart total updates dynamically

#### 3.6.4 Remove from Cart
- **Function**: Delete a line item from cart
- **Features**:
  - Remove individual item from cart
  - Cart total recalculates
  - If cart becomes empty, show empty cart state

---

### 3.7 Checkout Module

#### 3.7.1 Checkout Form
- **Function**: Collect customer details before placing order
- **Features**:
  - Input: full name (required)
  - Input: phone number (required, used as customer identifier)
  - Input: delivery address (required, textarea)
  - Order summary panel showing items, quantities, and total
  - Place Order button
  - Validation: all fields required, phone number format check

#### 3.7.2 Customer Account Handling
- **Function**: Identify or create customer record by phone number
- **Features**:
  - On order placement, check `customers` table for existing phone number
  - If found: link order to existing customer record
  - If not found: create new customer record with name and phone
  - Customer name updated to latest provided name on each order

#### 3.7.3 Order Placement
- **Function**: Create order record and trigger notifications
- **Features**:
  - Insert record into `orders` table with status `pending`
  - Insert line items into `order_items` table
  - Decrement stock in `products` or `product_sizes` table
  - Generate unique order number
  - Trigger WhatsApp notification to admin
  - Clear cart session
  - Redirect to Order Confirmation page

---

### 3.8 Order Confirmation Module

#### 3.8.1 Confirmation Page
- **Function**: Show order summary and payment instructions after successful order
- **Features**:
  - Display order number
  - Display ordered items, quantities, and total
  - Display QR code image (from settings)
  - Display payment instructions text (from settings)
  - Reminder to use exact registered name for payment transfer
  - Link back to homepage

---

### 3.9 Order Management Module (Admin)

#### 3.9.1 List Orders
- **Function**: Display all orders with filtering
- **Features**:
  - Table view: order number, customer name, phone, total, status, date
  - Filter by status (pending, confirmed, completed, cancelled)
  - Sort by date (newest first by default)
  - Click order row to view order detail

#### 3.9.2 View Order Detail
- **Function**: Show full details of a single order
- **Features**:
  - Order number, date, status
  - Customer name, phone, address
  - Line items: product name, size, quantity, unit price, line total
  - Order total
  - Status update control

#### 3.9.3 Update Order Status
- **Function**: Change the status of an order
- **Features**:
  - Dropdown to select new status: pending, confirmed, completed, cancelled
  - Save button to apply status change
  - Status change recorded with timestamp
  - On success: show success message

---

### 3.10 WhatsApp Notification Module

#### 3.10.1 New Order Notification
- **Function**: Notify admin of new order via WhatsApp
- **Features**:
  - Triggered automatically on successful order placement
  - Constructs `wa.me` URL with pre-filled message
  - Message includes:
    - Customer name
    - Customer phone number
    - Delivery address
    - List of ordered items with sizes and quantities
    - Order total
    - Order number
  - Admin WhatsApp number sourced from `settings` table
  - Opens WhatsApp link in new tab/window on confirmation page

---

### 3.11 Settings Module (Admin)

#### 3.11.1 View/Edit Settings
- **Function**: Configure system-wide settings
- **Features**:
  - Input: Admin WhatsApp number (used for order notifications)
  - Input: QR code image upload (JPEG/PNG, max 2MB) for payment
  - Input: Payment instructions text (textarea)
  - Save button
  - Current QR code image preview
  - Validation: WhatsApp number format, image type and size
  - Settings stored in `settings` table as key-value pairs

---

## 4. Database Design

### 4.1 Table: `admins`

| Column | Type | Description |
|--------|------|-------------|
| id | INT PK AUTO_INCREMENT | Admin ID |
| username | VARCHAR(100) UNIQUE | Login username |
| password | VARCHAR(255) | bcrypt hashed password |
| created_at | TIMESTAMP | Record creation time |

### 4.2 Table: `categories`

| Column | Type | Description |
|--------|------|-------------|
| id | INT PK AUTO_INCREMENT | Category ID |
| name | VARCHAR(100) | Category display name |
| type | ENUM('pakaian','produk') | Category type |
| created_at | TIMESTAMP | Record creation time |

### 4.3 Table: `products`

| Column | Type | Description |
|--------|------|-------------|
| id | INT PK AUTO_INCREMENT | Product ID |
| category_id | INT FK | References categories.id |
| name | VARCHAR(255) | Product name |
| description | TEXT | Product description |
| image | VARCHAR(255) | Image file path |
| price | DECIMAL(10,2) | Base price (non-sized products) |
| stock | INT | Stock quantity (non-sized products) |
| created_at | TIMESTAMP | Record creation time |
| updated_at | TIMESTAMP | Last update time |

### 4.4 Table: `product_sizes`

| Column | Type | Description |
|--------|------|-------------|
| id | INT PK AUTO_INCREMENT | Size ID |
| product_id | INT FK | References products.id |
| size_label | VARCHAR(50) | Size name (S, M, L, 0-3M, etc.) |
| price | DECIMAL(10,2) | Price for this size |
| stock | INT | Stock for this size |

### 4.5 Table: `customers`

| Column | Type | Description |
|--------|------|-------------|
| id | INT PK AUTO_INCREMENT | Customer ID |
| name | VARCHAR(255) | Customer full name |
| phone | VARCHAR(20) UNIQUE | Phone number (identifier) |
| created_at | TIMESTAMP | First order date |

### 4.6 Table: `orders`

| Column | Type | Description |
|--------|------|-------------|
| id | INT PK AUTO_INCREMENT | Order ID |
| order_number | VARCHAR(50) UNIQUE | Human-readable order number |
| customer_id | INT FK | References customers.id |
| address | TEXT | Delivery address |
| total | DECIMAL(10,2) | Order total amount |
| status | ENUM('pending','confirmed','completed','cancelled') | Order status |
| created_at | TIMESTAMP | Order placement time |
| updated_at | TIMESTAMP | Last status update time |

### 4.7 Table: `order_items`

| Column | Type | Description |
|--------|------|-------------|
| id | INT PK AUTO_INCREMENT | Item ID |
| order_id | INT FK | References orders.id |
| product_id | INT FK | References products.id |
| size_id | INT FK NULL | References product_sizes.id (nullable) |
| product_name | VARCHAR(255) | Snapshot of product name at order time |
| size_label | VARCHAR(50) NULL | Snapshot of size label at order time |
| unit_price | DECIMAL(10,2) | Snapshot of price at order time |
| quantity | INT | Quantity ordered |
| subtotal | DECIMAL(10,2) | unit_price * quantity |

### 4.8 Table: `settings`

| Column | Type | Description |
|--------|------|-------------|
| id | INT PK AUTO_INCREMENT | Setting ID |
| key | VARCHAR(100) UNIQUE | Setting key name |
| value | TEXT | Setting value |
| updated_at | TIMESTAMP | Last update time |

**Default settings keys**:
- `whatsapp_number` — Admin WhatsApp number
- `qr_code_image` — Path to QR code image file
- `payment_instructions` — Payment instruction text

---

## 5. User Interface Requirements

### 5.1 Public Pages

#### 5.1.1 Homepage (`/`)
- Category cards in a responsive grid (2 columns on mobile)
- Each card: category name, optional icon
- Header with store name and cart icon with item count badge
- Clean, minimal baby-store aesthetic (soft colors, rounded corners)

#### 5.1.2 Category Page (`/category/{id}`)
- Page heading with category name
- Product grid (2 columns on mobile, 3 on tablet)
- Each product card: image, name, starting price
- Back button to homepage

#### 5.1.3 Product Detail Page (`/product/{id}`)
- Full-width product image
- Product name (heading)
- Description text
- Size selector (radio group) — Pakaian products only
- Price display (updates on size selection)
- Stock indicator
- Quantity selector (+/- buttons)
- Add to Cart button (full width, prominent)
- Back button to category

#### 5.1.4 Cart Page (`/cart`)
- Cart item list with image, name, size, price, quantity controls, remove button
- Order total
- Checkout button (full width)
- Empty cart state with "Continue Shopping" link

#### 5.1.5 Checkout Page (`/checkout`)
- Form: Name, Phone, Address
- Order summary (read-only item list and total)
- Place Order button
- Back to Cart link

#### 5.1.6 Order Confirmation Page (`/order/confirmation/{order_number}`)
- Order number display
- Thank you message
- Ordered items summary
- QR code image
- Payment instructions
- Name-matching fraud prevention reminder
- Back to Home button

### 5.2 Admin Pages

#### 5.2.1 Login Page (`/admin/login`)
- Centered card layout
- Username and password fields
- Login button
- Error message display

#### 5.2.2 Dashboard (`/admin/dashboard`)
- Stats cards: Total Orders, Total Revenue, Pending Orders
- Recent Orders table (last 10)
- Navigation sidebar or top nav

#### 5.2.3 Categories Page (`/admin/categories`)
- Categories table with Name, Type, Product Count, Actions
- Add Category button
- Inline or modal form for add/edit

#### 5.2.4 Products Page (`/admin/products`)
- Products table/grid with Image, Name, Category, Price, Stock, Actions
- Search input and category filter dropdown
- Add Product button

#### 5.2.5 Product Form (`/admin/products/add` and `/admin/products/edit/{id}`)
- Image upload with preview
- Name, Description fields
- Category dropdown
- Price and Stock fields (non-sized)
- Dynamic size rows section (shown for Pakaian category):
  - Add Size Row button
  - Each row: Size Label, Price, Stock, Remove Row button
- Save and Cancel buttons

#### 5.2.6 Orders Page (`/admin/orders`)
- Orders table: Order Number, Customer, Phone, Total, Status, Date, Actions
- Status filter tabs or dropdown
- Click row or View button to see order detail

#### 5.2.7 Order Detail Page (`/admin/orders/{id}`)
- Order metadata (number, date, customer info, address)
- Items table (product, size, qty, unit price, subtotal)
- Order total
- Status update dropdown + Save button

#### 5.2.8 Settings Page (`/admin/settings`)
- WhatsApp number input
- QR code image upload with current image preview
- Payment instructions textarea
- Save Settings button

---

## 6. Security Requirements

| Requirement | Implementation |
|-------------|---------------|
| Admin password storage | bcrypt hashing via `password_hash()` |
| Admin session management | PHP sessions with session regeneration on login |
| SQL injection prevention | PDO prepared statements throughout |
| XSS prevention | `htmlspecialchars()` on all output |
| File upload validation | Check MIME type, extension whitelist, max file size |
| CSRF protection | CSRF token on all admin forms |
| Sensitive data in URLs | Order numbers only, no customer PII in URLs |
| Directory traversal | Validate and sanitize all file paths |
| Admin route protection | Auth check middleware on all `/admin/*` routes |

---

## 7. Non-Functional Requirements

| Requirement | Target |
|-------------|--------|
| Mobile browser support | Chrome, Safari, Samsung Internet (latest 2 versions) |
| Page load time | Under 3 seconds on 4G |
| Touch targets | Minimum 44x44px for all interactive elements |
| Viewport | Optimized for 320px and up |
| Graceful degradation | Functional on older browsers without JS enhancements |
| Image optimization | Compress uploaded images, serve appropriately sized images |
| Error states | User-friendly error pages (404, 500) |

---

## 8. File & Folder Structure

```
comel-order-system/
├── public/                  # Web root (Apache DocumentRoot)
│   ├── index.php            # Front controller / router
│   ├── .htaccess            # mod_rewrite rules
│   ├── assets/
│   │   ├── css/             # Custom CSS (if any beyond Tailwind)
│   │   └── js/              # Custom JavaScript files
│   └── uploads/
│       ├── products/        # Product images
│       └── qr/              # QR code images
├── src/
│   ├── config/
│   │   └── database.php     # PDO connection setup
│   ├── controllers/
│   │   ├── HomeController.php
│   │   ├── CategoryController.php
│   │   ├── ProductController.php
│   │   ├── CartController.php
│   │   ├── CheckoutController.php
│   │   ├── OrderController.php
│   │   └── admin/
│   │       ├── AuthController.php
│   │       ├── DashboardController.php
│   │       ├── CategoryController.php
│   │       ├── ProductController.php
│   │       ├── OrderController.php
│   │       └── SettingsController.php
│   ├── models/
│   │   ├── Admin.php
│   │   ├── Category.php
│   │   ├── Product.php
│   │   ├── ProductSize.php
│   │   ├── Customer.php
│   │   ├── Order.php
│   │   ├── OrderItem.php
│   │   └── Setting.php
│   └── views/
│       ├── layouts/
│       │   ├── public.php   # Public layout (header, footer)
│       │   └── admin.php    # Admin layout (sidebar, header)
│       ├── home/
│       ├── category/
│       ├── product/
│       ├── cart/
│       ├── checkout/
│       ├── order/
│       └── admin/
│           ├── auth/
│           ├── dashboard/
│           ├── categories/
│           ├── products/
│           ├── orders/
│           └── settings/
├── database/
│   └── schema.sql           # Database schema and seed data
└── docs/
    ├── prd.md               # Original PRD
    └── project-prd.md       # This document
```

---

## 9. API / Route Map

### 9.1 Public Routes

| Method | Route | Controller | Function |
|--------|-------|-----------|----------|
| GET | `/` | HomeController | Show homepage with categories |
| GET | `/category/{id}` | CategoryController | Show products in category |
| GET | `/product/{id}` | ProductController | Show product detail |
| GET | `/cart` | CartController | Show cart |
| POST | `/cart/add` | CartController | Add item to cart |
| POST | `/cart/update` | CartController | Update item quantity |
| POST | `/cart/remove` | CartController | Remove item from cart |
| GET | `/checkout` | CheckoutController | Show checkout form |
| POST | `/checkout` | CheckoutController | Process order placement |
| GET | `/order/confirmation/{order_number}` | OrderController | Show order confirmation |

### 9.2 Admin Routes

| Method | Route | Controller | Function |
|--------|-------|-----------|----------|
| GET | `/admin/login` | AuthController | Show login form |
| POST | `/admin/login` | AuthController | Process login |
| GET | `/admin/logout` | AuthController | Logout and redirect |
| GET | `/admin/dashboard` | DashboardController | Show dashboard |
| GET | `/admin/categories` | CategoryController | List categories |
| GET | `/admin/categories/add` | CategoryController | Show add form |
| POST | `/admin/categories/add` | CategoryController | Create category |
| GET | `/admin/categories/edit/{id}` | CategoryController | Show edit form |
| POST | `/admin/categories/edit/{id}` | CategoryController | Update category |
| POST | `/admin/categories/delete/{id}` | CategoryController | Delete category |
| GET | `/admin/products` | ProductController | List products |
| GET | `/admin/products/add` | ProductController | Show add form |
| POST | `/admin/products/add` | ProductController | Create product |
| GET | `/admin/products/edit/{id}` | ProductController | Show edit form |
| POST | `/admin/products/edit/{id}` | ProductController | Update product |
| POST | `/admin/products/delete/{id}` | ProductController | Delete product |
| GET | `/admin/orders` | OrderController | List orders |
| GET | `/admin/orders/{id}` | OrderController | View order detail |
| POST | `/admin/orders/{id}/status` | OrderController | Update order status |
| GET | `/admin/settings` | SettingsController | Show settings form |
| POST | `/admin/settings` | SettingsController | Save settings |

---

## 10. Future Enhancements (v2)

| Feature | Description |
|---------|-------------|
| Customer Order History | Customers can view past orders by phone number lookup |
| WhatsApp Broadcast | Admin can send promotional messages to all customers |
| Multiple Payment Methods | Support bank transfer, e-wallet options alongside QR |
| Order Tracking Timeline | Visual status timeline on order confirmation page |
| Product Search | Full-text search across product names and descriptions |
| Image Gallery | Multiple images per product with swipe carousel |
| Discount / Coupon System | Promo codes with percentage or fixed amount discounts |
| Email Notifications | Order confirmation and status update emails |
| Low Stock Alerts | Admin notification when product stock falls below threshold |
| Sales Reports | Revenue reports by date range, category, product |

---

## 11. Glossary

| Term | Definition |
|------|-----------|
| Pakaian | Clothing category (pyjamas, play sets, etc.) requiring size selection |
| Produk | Product category (bottles, swaddles, etc.) with flat pricing, no sizes |
| Botol Susu | Baby bottle — falls under Produk category |
| Swaddle | Baby wrapping cloth — falls under Produk category |
| Kain Bedung | Swaddling cloth — falls under Produk category |
| Size | Variant label such as S, M, L, XL or age-based (0-3M, 3-6M, etc.) |
| QR | Quick Response code image used for payment scanning |
| wa.me | WhatsApp click-to-chat URL format used for notifications |
| PDO | PHP Data Objects — database abstraction layer used for prepared statements |
| CSRF | Cross-Site Request Forgery — attack vector mitigated by form tokens |


