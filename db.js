import Database from 'better-sqlite3';
import bcrypt from 'bcryptjs';
import { mkdirSync } from 'fs';
import { dirname } from 'path';

const DB_PATH = './data/store.sqlite';
mkdirSync(dirname(DB_PATH), { recursive: true });

const db = new Database(DB_PATH);
db.pragma('journal_mode = WAL');
db.pragma('foreign_keys = ON');

db.exec(`
  CREATE TABLE IF NOT EXISTS products (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name TEXT NOT NULL,
    description TEXT,
    price REAL NOT NULL,
    competitor_price REAL,
    image TEXT,
    category TEXT DEFAULT 'Tees',
    stock INTEGER DEFAULT 50,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
  );

  CREATE TABLE IF NOT EXISTS admins (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    username TEXT UNIQUE NOT NULL,
    password_hash TEXT NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
  );

  CREATE TABLE IF NOT EXISTS users (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    username TEXT UNIQUE NOT NULL,
    email TEXT UNIQUE NOT NULL,
    password_hash TEXT NOT NULL,
    full_name TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
  );

  CREATE TABLE IF NOT EXISTS orders (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id INTEGER NOT NULL,
    total REAL NOT NULL,
    status TEXT DEFAULT 'Processing',
    note TEXT,
    placed_by TEXT DEFAULT 'user',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
  );

  CREATE TABLE IF NOT EXISTS order_items (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    order_id INTEGER NOT NULL,
    product_id INTEGER,
    product_name TEXT NOT NULL,
    unit_price REAL NOT NULL,
    quantity INTEGER NOT NULL,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE
  );
`);

export const CATEGORIES = ['Jackets', 'Tees', 'Pants', 'Shorts'];

if (db.prepare('SELECT COUNT(*) AS c FROM admins').get().c === 0) {
  db.prepare('INSERT INTO admins (username, password_hash) VALUES (?, ?)')
    .run('Voidxx', bcrypt.hashSync('admin123', 10));
}

if (db.prepare('SELECT COUNT(*) AS c FROM users').get().c === 0) {
  db.prepare('INSERT INTO users (username, email, password_hash, full_name) VALUES (?, ?, ?, ?)')
    .run('demo', 'demo@voidxx.test', bcrypt.hashSync('demo1234', 10), 'Demo Customer');
}

if (db.prepare('SELECT COUNT(*) AS c FROM products').get().c === 0) {
  const seed = db.prepare(
    'INSERT INTO products (name, description, price, competitor_price, image, category, stock) VALUES (?, ?, ?, ?, ?, ?, ?)'
  );
  const items = [
    // ---------- JACKETS ----------
    ['Heavyweight Bomber', 'Boxy fit bomber in heavy poly-cotton with ribbed cuffs and hem.', 3499, 3999, 'https://images.unsplash.com/photo-1591047139829-d91aecb6caea?w=900&q=80', 'Jackets', 30],
    ['Selvedge Trucker', 'Classic trucker silhouette cut from 14oz Japanese selvedge denim.', 4299, 4999, 'https://images.unsplash.com/photo-1551488831-00ddcb6c6bd3?w=900&q=80', 'Jackets', 22],
    ['Wool Overshirt', 'Mid-weight wool overshirt with horn buttons. Layer all season.', 3899, 4499, 'https://images.unsplash.com/photo-1539533018447-63fcce2678e3?w=900&q=80', 'Jackets', 18],

    // ---------- TEES ----------
    ['Heavy Box Tee — Black', '240gsm combed cotton, boxy cut, garment dyed for a soft hand-feel.', 899, 1099, 'https://images.unsplash.com/photo-1583743814966-8936f5b7be1a?w=900&q=80', 'Tees', 120],
    ['Heavy Box Tee — White', '240gsm combed cotton, boxy cut, garment dyed for a soft hand-feel.', 899, 1099, 'https://images.unsplash.com/photo-1521572163474-6864f9cf17ab?w=900&q=80', 'Tees', 120],
    ['Long Sleeve Tee — Cream', 'Heavyweight long-sleeve tee with ribbed cuffs and a relaxed body.', 1299, 1499, 'https://images.unsplash.com/photo-1618354691373-d851c5c3a990?w=900&q=80', 'Tees', 70],

    // ---------- PANTS ----------
    ['Wide-Leg Trouser', 'Drape-y wide-leg trouser in stretch tencel twill. Pleated front.', 2499, 2899, 'https://images.unsplash.com/photo-1473966968600-fa801b869a1a?w=900&q=80', 'Pants', 40],
    ['Carpenter Pant — Stone', 'Workwear-inspired carpenter pant with hammer loop and reinforced knees.', 2799, 3199, 'https://images.unsplash.com/photo-1542272604-787c3835535d?w=900&q=80', 'Pants', 32],
    ['Pleated Trouser — Charcoal', 'Tailored pleated trouser in mid-weight wool blend. Office-ready.', 2299, 2699, 'https://images.unsplash.com/photo-1605518216938-7c31b7b14ad0?w=900&q=80', 'Pants', 28],

    // ---------- SHORTS ----------
    ['Heavy Sweat Short', '380gsm brushed-back fleece sweat short with elastic drawcord waist.', 1499, 1799, 'https://images.unsplash.com/photo-1591195853828-11db59a44f6b?w=900&q=80', 'Shorts', 80],
    ['Cargo Short — Olive', 'Mid-thigh cargo short with bellowed pockets and a relaxed leg.', 1799, 2099, 'https://images.unsplash.com/photo-1604176354204-9268737828e4?w=900&q=80', 'Shorts', 60],
    ['Nylon Track Short — Black', 'Featherweight ripstop nylon track short with mesh liner.', 1299, 1499, 'https://images.unsplash.com/photo-1622445275576-721325763afe?w=900&q=80', 'Shorts', 70]
  ];
  const insertMany = db.transaction(() => items.forEach(it => seed.run(...it)));
  insertMany();
}

export default db;
