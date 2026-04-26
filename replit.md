# Voidxx — A Clothing Store

A polished PHP e-commerce demo with a public storefront, customer accounts with full order history, and an admin back-office that can manage products, customers, and orders.

## Stack

- **Language:** PHP 8.2 (no Composer dependencies; only standard PDO extensions)
- **Database:** SQLite (file: `data/store.sqlite`) via PDO
  - Originally targeted MySQL. Switched to SQLite so the app runs out of the box on Replit. All SQL is portable.
- **Server (dev & prod):** PHP built-in web server, bound to `0.0.0.0:5000`
- **Frontend:** Server-rendered PHP templates, custom CSS, Inter + Playfair Display from Google Fonts.

## Features

### Storefront (public)
- Hero homepage with new arrivals and best-deal sections
- Shop page with category filter
- Product detail page with competitor pricing & savings
- Side-by-side product comparison
- Sessions-backed shopping cart (add / increment / decrement / remove / clear)
- Contact form

### User accounts
- Register, login, logout
- `account.php` — profile + lifetime stats
- `orders.php` — full order history showing status, items, totals
- Checkout from cart creates an order assigned to the signed-in user

### Admin portal (`/admin/`)
- Separate admin login (session key isolated from customer sessions)
- Sidebar layout for: Dashboard, Products, Users, Orders
- **Product CRUD** — add, edit (with image upload), delete
- **Customer list** — see every registered user with order count and lifetime spend
- **Order management** — view every order with line items; change status (Processing / Shipped / Delivered / Cancelled)
- **Add order to a user** — admin can compose a multi-item order on behalf of any customer; the order shows up in that user's history with a "Placed by admin" badge

## Default Credentials

| Role     | URL                  | Username | Password   |
|----------|----------------------|----------|------------|
| Admin    | `/admin/login.php`   | `Voidxx` | `admin123` |
| Customer | `/login.php`         | `demo`   | `demo1234` |

Created automatically on first run by `db.php`.

## Project Layout

```
.
├── index.php              # Home (hero + featured)
├── products.php           # Shop with category filter
├── product.php            # Product detail
├── compare.php            # Side-by-side compare
├── cart.php               # Cart + checkout
├── contact.php
├── login.php / register.php / logout.php / account.php / orders.php
├── header.php / footer.php
├── _helpers.php           # Sessions, flash, current_user(), formatting
├── config.php             # Constants (DB + BASE_URL)
├── db.php                 # SQLite connection + auto-migrate + seed
├── styles.css             # Full design system
├── assets/placeholder.svg # Fallback product image
├── uploads/               # Admin-uploaded product images
├── data/store.sqlite      # SQLite DB (auto-created)
└── admin/
    ├── _auth.php          # Admin session guard
    ├── _layout.php        # Sidebar shell wrapper
    ├── _layout_end.php
    ├── login.php / logout.php
    ├── dashboard.php      # Stats + recent orders
    ├── products.php / add_product.php / edit_product.php / delete_product.php
    ├── users.php          # Customer list
    ├── orders.php         # All orders + status updates
    └── add_order.php      # Compose an order for a user
```

## Database Schema (SQLite)

- `products`(id, name, description, price, competitor_price, image, category, stock, created_at)
- `admins`(id, username, password_hash, created_at)
- `users`(id, username, email, password_hash, full_name, created_at)
- `orders`(id, user_id, total, status, note, placed_by, created_at)
- `order_items`(id, order_id, product_id, product_name, unit_price, quantity)

`db.php` uses `CREATE TABLE IF NOT EXISTS` and seeds only when tables are empty, so it is idempotent.

## Workflow & Deployment

- Workflow `Start application` runs `php -S 0.0.0.0:5000 -t .` on port 5000 (webview).
- Deployment configured as `vm` with the same command — VM was chosen because the app keeps state on the local disk (`data/store.sqlite` and `uploads/`).
