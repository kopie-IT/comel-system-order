# Comel Baby Store - Product Requirements Document

## 1. Overview

**Comel Baby Store** is a mobile-first e-commerce web application for a baby products store. The system allows customers to browse products by category, add items to cart, place orders, and make payments via QR code. Administrators can manage products, categories, orders, and payment settings through a secure admin panel.

### 1.1 Goals
- Provide a mobile-optimized catalog experience for customers
- Enable easy product management for store owners
- Automate order notifications via WhatsApp
- Implement fraud-safe payment verification via QR codes

### 1.2 Tech Stack
- **Backend**: PHP 8.x (vanilla, no framework)
- **Database**: MySQL/MariaDB
- **Frontend**: HTML5, Tailwind CSS (CDN), Vanilla JavaScript
- **Icons**: Font Awesome (CDN)
- **Server**: Apache with mod_rewrite

---

## 2. User Roles

### 2.1 Customer (Public User)
- Browse product categories
- View products with images, descriptions, and sizes
- Add products to cart
- Checkout with address/phone input
- Account identified by phone number
- View order confirmation with payment QR code
- Receive WhatsApp notification on order placement

### 2.2 Admin (Store Owner)
- Secure login with username/password
- Dashboard with order stats
- Manage categories (create, edit, delete)
- Manage products with images, sizes, pricing, quantities
- Manage orders (view, update status)
- Configure settings (WhatsApp number, QR code image, payment instructions)
- View customer order history

---

## 3. Functional Requirements

### 3.1 Product Management (Admin)

| Feature | Description |
|---------|-------------|
| Add Product | Upload image, set name, description, category |
| Category Assignment | Products assigned to categories: Pakaian (clothing) or Produk (products) |
| Size Management | For clothing: add multiple sizes per product (S, M, L, XL, or age-based) |
| Pricing | Set price per piece; for sized items, different prices per size |
| Quantity | Track stock quantity per size (or per product for non-sized items) |
| Edit/Delete | Full CRUD on products and categories |

### 3.2 Category System

Two main category types:
1. **Pakaian** (Clothing) - Pyjamas, play sets, etc. Requires size selection
2. **Produk** (Products) - Botol susu, swaddle, kain bedung, etc. Flat pricing, no sizes
3. Botol susu falls under Produk category

### 3.3 Customer Journey

1. User lands on homepage → sees category list
2. Selects category → sees product grid
3. Clicks product → sees detail with image, description, sizes (if applicable), price
4. Selects size (if applicable) → adds to cart
5. Reviews cart → proceeds to checkout
6. Enters name, phone number, address
7. System identifies/creates customer account by phone number
8. Places order → WhatsApp notification sent to admin
9. Order confirmation page shows QR code with payment instructions
10. Customer makes payment via QR, ensuring name matches to prevent fraud

### 3.4 Cart System
- Session-based cart (no login required)
- Add/remove items
- Update quantities
- Size-specific line items
- Cart persists during session

### 3.5 Order Management
- Order statuses: pending, confirmed, completed, cancelled
- Admin can update order status
- Order history viewable in admin panel
- Each order records customer details and items

### 3.6 WhatsApp Integration
- On new order: system sends message to admin via `wa.me` link
- Message includes: customer name, phone, order items, total, address
- Admin receives notification to process the order

### 3.7 Payment System
- Payment method configured in admin settings
- Admin uploads QR code image
- Admin sets payment instructions text
- On order confirmation, customer sees QR code + payment instructions
- Instructions remind customer to use the exact name as registered

---

## 4. Database Design

### Tables

| Table | Purpose |
|-------|---------|
| `admins` | Admin authentication |
| `categories` | Product categories |
| `products` | Product master data |
| `product_sizes` | Size-specific pricing and stock |
| `customers` | Customer accounts (by phone) |
| `orders` | Order headers |
| `order_items` | Order line items |
| `settings` | System configuration |

### Entity Relationships
- Category 1→N Products
- Product 1→N Product Sizes (for clothing items)
- Customer 1→N Orders
- Order 1→N Order Items
- Order Items → Product (via product_id) and optional Product Size (via size_id)

---

## 5. User Interface Requirements

### 5.1 Public Pages
- **Homepage**: Category cards, clean mobile layout
- **Category Page**: Product grid with images and names
- **Product Detail**: Image, name, description, size selector (if applicable), price, add-to-cart button
- **Cart Page**: Item list, quantities, total, checkout button
- **Checkout Page**: Form (name, phone, address), order summary, place order button
- **Order Confirmation**: Order number, QR code, payment instructions

### 5.2 Admin Pages
- **Login Page**: Simple username/password form
- **Dashboard**: Order count, total revenue, recent orders
- **Categories**: List, add, edit, delete
- **Products**: List with search/filter, add, edit, delete
- **Product Form**: Image upload, category select, dynamic size rows
- **Orders**: List all orders with status filter, view detail, update status
- **Settings**: WhatsApp number, QR code upload, payment instructions text

### 5.3 Design Principles
- Mobile-first responsive design (320px and up)
- Touch-friendly buttons and inputs
- Clean, minimal, baby-store aesthetic
- Fast loading, optimized images

---

## 6. Security Requirements
- Admin passwords hashed with bcrypt
- Admin session management with PHP sessions
- SQL injection prevention via prepared statements
- XSS prevention via htmlspecialchars on output
- File upload validation (images only, size limits)
- No sensitive data exposure in URLs

---

## 7. Non-Functional Requirements
- Support all modern mobile browsers (Chrome, Safari, Samsung Internet)
- Page load under 3 seconds on 4G
- Graceful degradation on older browsers
- Mobile viewport optimized (touch targets ≥ 44px)
- Offline-friendly error states

---

## 8. Future Enhancements (v2)
- Customer order history page
- Admin can send WhatsApp broadcast to customers
- Multiple payment method support
- Order tracking with status timeline
- Product search functionality
- Image gallery (multiple images per product)
- Discount/coupon system
- Email notifications

---

## 9. Glossary

| Term | Definition |
|------|------------|
| Pakaian | Clothing category (pyjamas, play sets, etc.) |
| Produk | Product category (bottles, swaddles, etc.) |
| Botol Susu | Baby bottle (product) |
| Swaddle | Baby wrapping cloth (product) |
| Kain Bedung | Swaddling cloth (product) |
| Size | Variant like S/M/L or age-based sizing |
| QR | Quick Response code for payment scan |