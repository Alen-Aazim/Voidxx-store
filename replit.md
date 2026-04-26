# Voidxx — Premium Clothing Store

A Node.js + Express e-commerce site with a hand-rolled premium UI in plain HTML/CSS/JavaScript. SQLite via better-sqlite3 for persistence.

## Run

```
npm start          # starts node server.js on 0.0.0.0:5000
```

The "Start application" workflow runs `node server.js`.

## Stack

- **Runtime:** Node.js 20 (ES modules — `"type": "module"` in package.json)
- **Server:** Express 5 with `express-session`, `multer` (image uploads), `bcryptjs` (password hashing)
- **Database:** SQLite (better-sqlite3) — file at `data/store.sqlite`
- **Frontend:** Static HTML files in `/public`, vanilla CSS in `/public/css/styles.css`, vanilla JS in `/public/js/`. No build step.
- **Fonts:** Google Fonts — Fraunces (display serif) + Inter (UI sans)

## Project layout

```
server.js                 # Express app, all routes & APIs
db.js                     # SQLite init + schema + seed
package.json              # type: module, start: node server.js
data/store.sqlite         # SQLite database file (gitignored)
uploads/                  # User-uploaded product images (gitignored)
public/
  css/styles.css          # Full design system
  js/app.js               # Shared header/footer/cart logic for every page
  js/admin.js             # Admin sidebar + auth guard
  index.html              # Home (hero + new + deals + features + editorial split)
  products.html           # Catalog with category filter chips
  product.html            # PDP (uses ?id=)
  cart.html               # Bag with qty controls + checkout
  login.html, register.html, account.html, orders.html
  contact.html, 404.html
  admin/login.html         # Separate admin sign-in
  admin/dashboard.html     # Stats + recent orders
  admin/products.html      # Catalog CRUD with image upload
  admin/users.html         # Customer list with order count + lifetime spend
  admin/orders.html        # All orders + status dropdown
  admin/add-order.html     # Compose an order on behalf of a user
```

Every page uses two slots — `<div id="header-slot"></div>` and `<div id="footer-slot"></div>`. `app.js` fetches `/api/me`, injects the header/footer, and dispatches an `app-ready` CustomEvent so per-page scripts can render once auth state is known.

## Default credentials

- **Customer:** `demo` / `demo1234`
- **Admin:** `Voidxx` / `admin123`

The seed runs only when the relevant table is empty, so you can safely re-run.

## Database schema

- `products` — id, name, description, price, competitor_price, image, category, stock, created_at
- `admins` — id, username, password_hash
- `users` — id, username (unique), email (unique), password_hash, full_name, created_at
- `orders` — id, user_id (FK), total, status, note, placed_by ('user' | 'admin'), created_at
- `order_items` — id, order_id (FK), product_id, product_name, unit_price, quantity

## API surface

- **Public:** `/api/me`, `/api/products`, `/api/products/featured`, `/api/products/:id`
- **Auth:** `/api/auth/register`, `/api/auth/login`, `/api/auth/logout`
- **Cart (session):** `/api/cart`, `/api/cart/add`, `/api/cart/update`, `/api/cart/remove`, `/api/cart/clear`
- **Orders:** `POST /api/orders/checkout`, `GET /api/orders` (current user)
- **Admin auth:** `/api/admin/login`, `/api/admin/logout`
- **Admin:** `/api/admin/stats`, `/api/admin/users`, `/api/admin/orders` (GET/POST/PATCH), `/api/admin/products` (POST/PATCH/DELETE)

## Deployment

Deployment target is `vm` running `node server.js` (chosen for persistent in-process sessions and the on-disk SQLite file).

## Notes

- Customer and admin sessions are independent fields on the same session (`req.session.userId` vs `req.session.adminId`), so an admin signed-in won't be confused with a customer.
- Orders placed by an admin on behalf of a user are flagged with `placed_by = 'admin'` and surface a "Placed by admin" badge on the customer's order history.
- Product images: store either an absolute URL (for stock photos) or a `/uploads/...` path (for admin-uploaded images). The frontend has an SVG fallback if a URL fails to load.
- Dev cache is disabled via response headers when `NODE_ENV !== 'production'`.
