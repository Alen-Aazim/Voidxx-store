import Database from 'better-sqlite3';
import bcrypt from 'bcryptjs';
import { mkdirSync, existsSync } from 'fs';
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
    category TEXT DEFAULT 'General',
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

const adminCount = db.prepare('SELECT COUNT(*) AS c FROM admins').get().c;
if (adminCount === 0) {
  db.prepare('INSERT INTO admins (username, password_hash) VALUES (?, ?)').run(
    'Voidxx',
    bcrypt.hashSync('admin123', 10)
  );
}

const userCount = db.prepare('SELECT COUNT(*) AS c FROM users').get().c;
if (userCount === 0) {
  db.prepare('INSERT INTO users (username, email, password_hash, full_name) VALUES (?, ?, ?, ?)').run(
    'demo',
    'demo@voidxx.test',
    bcrypt.hashSync('demo1234', 10),
    'Demo Customer'
  );
}

const productCount = db.prepare('SELECT COUNT(*) AS c FROM products').get().c;
if (productCount === 0) {
  const seed = db.prepare(
    'INSERT INTO products (name, description, price, competitor_price, image, category, stock) VALUES (?, ?, ?, ?, ?, ?, ?)'
  );
  const items = [
    ['Midnight Denim Jacket', 'Premium slim-fit denim with structured shoulders. Built to outlast trends.', 2999, 3299, 'https://images.unsplash.com/photo-1591047139829-d91aecb6caea?w=900&q=80', 'Outerwear', 25],
    ['Crimson Cotton Tee', 'Heavyweight 240gsm combed cotton. Soft hand-feel, zero shrink.', 699, 799, 'https://images.unsplash.com/photo-1583743814966-8936f5b7be1a?w=900&q=80', 'T-Shirts', 80],
    ['Onyx Tapered Chinos', 'A modern tapered cut in stretch twill. Comfortable from desk to dinner.', 1499, 1699, 'https://images.unsplash.com/photo-1473966968600-fa801b869a1a?w=900&q=80', 'Bottoms', 40],
    ['Ivory Oxford Shirt', 'Crisp Oxford weave with mother-of-pearl buttons. Office to weekend.', 1299, 1499, 'https://images.unsplash.com/photo-1602810318383-e386cc2a3ccf?w=900&q=80', 'Shirts', 35],
    ['Charcoal Hoodie', 'Brushed-back fleece, double-lined hood, kangaroo pocket.', 1899, 2199, 'https://images.unsplash.com/photo-1556821840-3a63f95609a7?w=900&q=80', 'Outerwear', 60],
    ['Sand Linen Trousers', 'Breathable European linen, drawstring waist. Made for warm days.', 1799, 1999, 'https://images.unsplash.com/photo-1542272604-787c3835535d?w=900&q=80', 'Bottoms', 22],
    ['Olive Cargo Pants', 'Utility-inspired cargos with reinforced stitching and six pockets.', 1999, 2299, 'https://images.unsplash.com/photo-1605518216938-7c31b7b14ad0?w=900&q=80', 'Bottoms', 30],
    ['White Minimal Sneakers', 'Full-grain leather, vulcanized sole, hand-stitched details.', 2499, 2899, 'https://images.unsplash.com/photo-1542291026-7eec264c27ff?w=900&q=80', 'Footwear', 18],
    ['Camel Wool Overcoat', 'Long-line single-breasted overcoat in Italian wool blend.', 5999, 6999, 'https://images.unsplash.com/photo-1539533018447-63fcce2678e3?w=900&q=80', 'Outerwear', 12],
    ['Black Leather Belt', 'Hand-finished full-grain leather with brushed nickel buckle.', 999, 1199, 'https://images.unsplash.com/photo-1624222247344-550fb60583dc?w=900&q=80', 'Accessories', 50]
  ];
  const insertMany = db.transaction(() => items.forEach(it => seed.run(...it)));
  insertMany();
}

export default db;
