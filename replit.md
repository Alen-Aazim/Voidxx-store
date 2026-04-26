# Voidxx — A Clothing Store

A small PHP e-commerce demo with a public storefront and an admin area for managing products.

## Stack

- **Language:** PHP 8.2 (no Composer dependencies required; only standard PDO extensions)
- **Database:** SQLite (file: `data/store.sqlite`) via PDO
  - The original repo targeted MySQL. It was switched to SQLite so the app runs out of the box on Replit without an external database server. All SQL used by the app is portable between the two.
- **Server (dev & prod):** PHP built-in web server, bound to `0.0.0.0:5000`
- **Frontend:** Server-rendered PHP templates + a single `styles.css`

## Project Layout

- `index.php`, `products.php`, `product.php`, `cart.php`, `compare.php`, `contact.php` — public pages
- `header.php`, `footer.php` — shared layout partials
- `admin/` — admin area (login, dashboard, product CRUD). Originally named `admins/`; renamed to match the `admin/` URLs used by the navigation and `header.php` style-path logic.
- `db.php` — opens the SQLite connection and seeds the database with the schema, a default admin, and three sample products on first run
- `config.php` — kept for backwards compatibility (DB constants, `BASE_URL`)
- `uploads/` — destination for admin-uploaded product images (created on setup; empty by default — sample products reference image filenames that are not bundled, hence broken thumbnails until you add real images via the admin)
- `data/` — SQLite database file lives here

## Default Admin Credentials

- URL: `/admin/login.php`
- Username: `Voidxx`
- Password: `admin123`

(Created on first run by `db.php`.)

## Workflow

A single workflow `Start application` runs `php -S 0.0.0.0:5000 -t .` and is bound to port 5000 (webview).

## Deployment

Configured as a `vm` deployment using the same PHP built-in server command. VM was chosen instead of autoscale because the app keeps state on the local disk (`data/store.sqlite` and `uploads/`).
