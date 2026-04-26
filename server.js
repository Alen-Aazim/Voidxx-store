import express from 'express';
import session from 'express-session';
import bcrypt from 'bcryptjs';
import multer from 'multer';
import { fileURLToPath } from 'url';
import { dirname, join, extname } from 'path';
import { mkdirSync, existsSync } from 'fs';
import db from './db.js';

const __filename = fileURLToPath(import.meta.url);
const __dirname  = dirname(__filename);
const PORT = 5000;

const app = express();
app.disable('x-powered-by');
app.set('trust proxy', 1);
app.use(express.json());
app.use(express.urlencoded({ extended: true }));

mkdirSync(join(__dirname, 'uploads'), { recursive: true });

app.use(session({
  secret: process.env.SESSION_SECRET || 'voidxx-dev-secret-change-me',
  resave: false,
  saveUninitialized: true,
  cookie: { httpOnly: true, sameSite: 'lax', maxAge: 1000 * 60 * 60 * 24 * 30 }
}));

// Disable HTTP caching during development so refreshes always show latest UI
app.use((req, res, next) => {
  if (process.env.NODE_ENV !== 'production') {
    res.set('Cache-Control', 'no-store, no-cache, must-revalidate, proxy-revalidate');
    res.set('Pragma', 'no-cache');
    res.set('Expires', '0');
  }
  next();
});

app.use('/uploads', express.static(join(__dirname, 'uploads'), { fallthrough: true }));
app.use(express.static(join(__dirname, 'public')));

const upload = multer({
  storage: multer.diskStorage({
    destination: (_, __, cb) => cb(null, join(dirname(fileURLToPath(import.meta.url)), 'uploads')),
    filename: (_, file, cb) => {
      const ext = extname(file.originalname || '.jpg').toLowerCase();
      cb(null, `p_${Date.now()}_${Math.round(Math.random()*1e6)}${ext || '.jpg'}`);
    }
  }),
  limits: { fileSize: 5 * 1024 * 1024 }
});

// --- Auth helpers ---
const requireUser  = (req, res, next) => req.session.userId  ? next() : res.status(401).json({ error: 'Sign in required' });
const requireAdmin = (req, res, next) => req.session.adminId ? next() : res.status(401).json({ error: 'Admin sign in required' });

// =========================================================
//  PUBLIC API
// =========================================================
app.get('/api/me', (req, res) => {
  let user = null, admin = null;
  if (req.session.userId) {
    user = db.prepare('SELECT id, username, email, full_name, created_at FROM users WHERE id = ?').get(req.session.userId);
  }
  if (req.session.adminId) {
    admin = db.prepare('SELECT id, username FROM admins WHERE id = ?').get(req.session.adminId);
  }
  res.json({ user, admin, cartCount: cartCountFor(req) });
});

app.get('/api/products', (req, res) => {
  const cat = req.query.cat;
  const rows = cat
    ? db.prepare('SELECT * FROM products WHERE category = ? ORDER BY id DESC').all(cat)
    : db.prepare('SELECT * FROM products ORDER BY id DESC').all();
  res.json(rows);
});

app.get('/api/products/featured', (_, res) => {
  const newest  = db.prepare('SELECT * FROM products ORDER BY created_at DESC LIMIT 4').all();
  const deals   = db.prepare('SELECT * FROM products WHERE competitor_price IS NOT NULL ORDER BY (competitor_price - price) DESC LIMIT 4').all();
  const cats    = db.prepare('SELECT DISTINCT category FROM products ORDER BY category').all().map(r => r.category);
  res.json({ newest, deals, cats });
});

app.get('/api/products/:id', (req, res) => {
  const p = db.prepare('SELECT * FROM products WHERE id = ?').get(req.params.id);
  if (!p) return res.status(404).json({ error: 'Product not found' });
  res.json(p);
});

// =========================================================
//  AUTH (customer)
// =========================================================
app.post('/api/auth/register', (req, res) => {
  const { username, email, password, full_name } = req.body;
  if (!username || username.length < 3) return res.status(400).json({ error: 'Username must be at least 3 characters.' });
  if (!email || !/.+@.+\..+/.test(email)) return res.status(400).json({ error: 'Please enter a valid email.' });
  if (!password || password.length < 6)   return res.status(400).json({ error: 'Password must be at least 6 characters.' });
  try {
    const info = db.prepare('INSERT INTO users (username, email, password_hash, full_name) VALUES (?, ?, ?, ?)')
      .run(username.trim(), email.trim().toLowerCase(), bcrypt.hashSync(password, 10), (full_name || '').trim());
    req.session.userId = info.lastInsertRowid;
    res.json({ ok: true });
  } catch (e) {
    res.status(400).json({ error: 'That username or email is already in use.' });
  }
});

app.post('/api/auth/login', (req, res) => {
  const { username, password } = req.body;
  const u = db.prepare('SELECT * FROM users WHERE username = ? OR email = ?').get(username, (username || '').toLowerCase());
  if (!u || !bcrypt.compareSync(password || '', u.password_hash)) {
    return res.status(401).json({ error: 'Invalid username/email or password.' });
  }
  req.session.userId = u.id;
  res.json({ ok: true });
});

app.post('/api/auth/logout', (req, res) => {
  delete req.session.userId;
  res.json({ ok: true });
});

// =========================================================
//  CART (server-session)
// =========================================================
function getCart(req) {
  if (!req.session.cart) req.session.cart = {};
  return req.session.cart;
}
function cartCountFor(req) {
  const c = req.session.cart || {};
  return Object.values(c).reduce((a, b) => a + b, 0);
}
function expandCart(req) {
  const cart = getCart(req);
  const ids = Object.keys(cart).map(Number);
  if (!ids.length) return { items: [], total: 0, count: 0 };
  const placeholders = ids.map(() => '?').join(',');
  const rows = db.prepare(`SELECT * FROM products WHERE id IN (${placeholders})`).all(...ids);
  let total = 0;
  const items = rows.map(p => {
    const qty = cart[p.id]; const line = qty * p.price; total += line;
    return { product: p, qty, line };
  });
  return { items, total, count: cartCountFor(req) };
}

app.get('/api/cart', (req, res) => res.json(expandCart(req)));

app.post('/api/cart/add', (req, res) => {
  const id = Number(req.body.productId);
  const p = db.prepare('SELECT id FROM products WHERE id = ?').get(id);
  if (!p) return res.status(404).json({ error: 'Product not found' });
  const cart = getCart(req);
  cart[id] = (cart[id] || 0) + 1;
  res.json(expandCart(req));
});

app.post('/api/cart/update', (req, res) => {
  const id = Number(req.body.productId);
  const qty = Math.max(0, Number(req.body.qty) || 0);
  const cart = getCart(req);
  if (qty === 0) delete cart[id]; else cart[id] = qty;
  res.json(expandCart(req));
});

app.post('/api/cart/remove', (req, res) => {
  const id = Number(req.body.productId);
  const cart = getCart(req);
  delete cart[id];
  res.json(expandCart(req));
});

app.post('/api/cart/clear', (req, res) => {
  req.session.cart = {};
  res.json(expandCart(req));
});

// =========================================================
//  ORDERS
// =========================================================
app.post('/api/orders/checkout', requireUser, (req, res) => {
  const cart = getCart(req);
  const ids = Object.keys(cart).map(Number);
  if (!ids.length) return res.status(400).json({ error: 'Your cart is empty.' });
  const placeholders = ids.map(() => '?').join(',');
  const rows = db.prepare(`SELECT * FROM products WHERE id IN (${placeholders})`).all(...ids);
  let total = 0;
  rows.forEach(r => { total += r.price * cart[r.id]; });

  const tx = db.transaction(() => {
    const ord = db.prepare("INSERT INTO orders (user_id, total, status, placed_by) VALUES (?, ?, 'Processing', 'user')")
      .run(req.session.userId, total);
    const itemStmt = db.prepare('INSERT INTO order_items (order_id, product_id, product_name, unit_price, quantity) VALUES (?, ?, ?, ?, ?)');
    rows.forEach(r => itemStmt.run(ord.lastInsertRowid, r.id, r.name, r.price, cart[r.id]));
    return ord.lastInsertRowid;
  });
  const orderId = tx();
  req.session.cart = {};
  res.json({ ok: true, orderId });
});

app.get('/api/orders', requireUser, (req, res) => {
  const orders = db.prepare('SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC').all(req.session.userId);
  const itemStmt = db.prepare('SELECT * FROM order_items WHERE order_id = ?');
  res.json(orders.map(o => ({ ...o, items: itemStmt.all(o.id) })));
});

// =========================================================
//  ADMIN AUTH
// =========================================================
app.post('/api/admin/login', (req, res) => {
  const { username, password } = req.body;
  const a = db.prepare('SELECT * FROM admins WHERE username = ?').get(username);
  if (!a || !bcrypt.compareSync(password || '', a.password_hash)) {
    return res.status(401).json({ error: 'Invalid credentials.' });
  }
  req.session.adminId = a.id;
  res.json({ ok: true });
});

app.post('/api/admin/logout', (req, res) => {
  delete req.session.adminId;
  res.json({ ok: true });
});

// =========================================================
//  ADMIN — products
// =========================================================
app.post('/api/admin/products', requireAdmin, upload.single('image'), (req, res) => {
  const { name, description, price, competitor_price, category, stock, image_url } = req.body;
  const image = req.file ? `/uploads/${req.file.filename}` : (image_url || null);
  const info = db.prepare(
    'INSERT INTO products (name, description, price, competitor_price, image, category, stock) VALUES (?, ?, ?, ?, ?, ?, ?)'
  ).run(
    name?.trim() || 'Untitled',
    description || '',
    Number(price) || 0,
    competitor_price ? Number(competitor_price) : null,
    image,
    category?.trim() || 'General',
    Number(stock) || 0
  );
  res.json({ ok: true, id: info.lastInsertRowid });
});

app.patch('/api/admin/products/:id', requireAdmin, upload.single('image'), (req, res) => {
  const id = Number(req.params.id);
  const existing = db.prepare('SELECT * FROM products WHERE id = ?').get(id);
  if (!existing) return res.status(404).json({ error: 'Not found' });
  const { name, description, price, competitor_price, category, stock, image_url } = req.body;
  const image = req.file ? `/uploads/${req.file.filename}` : (image_url || existing.image);
  db.prepare(
    'UPDATE products SET name = ?, description = ?, price = ?, competitor_price = ?, image = ?, category = ?, stock = ? WHERE id = ?'
  ).run(
    name?.trim() || existing.name,
    description ?? existing.description,
    Number(price) || existing.price,
    competitor_price !== undefined && competitor_price !== '' ? Number(competitor_price) : null,
    image,
    category?.trim() || existing.category,
    stock !== undefined ? Number(stock) : existing.stock,
    id
  );
  res.json({ ok: true });
});

app.delete('/api/admin/products/:id', requireAdmin, (req, res) => {
  db.prepare('DELETE FROM products WHERE id = ?').run(Number(req.params.id));
  res.json({ ok: true });
});

// =========================================================
//  ADMIN — users / orders
// =========================================================
app.get('/api/admin/users', requireAdmin, (_, res) => {
  const rows = db.prepare(`
    SELECT u.*,
      (SELECT COUNT(*) FROM orders WHERE user_id = u.id) AS order_count,
      (SELECT COALESCE(SUM(total), 0) FROM orders WHERE user_id = u.id) AS lifetime
    FROM users u
    ORDER BY u.created_at DESC
  `).all();
  res.json(rows);
});

app.get('/api/admin/orders', requireAdmin, (_, res) => {
  const orders = db.prepare(`
    SELECT o.*, u.username, u.email, u.full_name FROM orders o
    JOIN users u ON u.id = o.user_id
    ORDER BY o.created_at DESC
  `).all();
  const itemStmt = db.prepare('SELECT * FROM order_items WHERE order_id = ?');
  res.json(orders.map(o => ({ ...o, items: itemStmt.all(o.id) })));
});

app.post('/api/admin/orders', requireAdmin, (req, res) => {
  const { user_id, status, note, items } = req.body;
  if (!user_id || !Array.isArray(items) || !items.length) {
    return res.status(400).json({ error: 'Choose a customer and at least one item.' });
  }
  const ids = items.map(i => Number(i.product_id)).filter(Boolean);
  if (!ids.length) return res.status(400).json({ error: 'Pick valid products.' });
  const placeholders = ids.map(() => '?').join(',');
  const products = db.prepare(`SELECT * FROM products WHERE id IN (${placeholders})`).all(...ids);
  const byId = Object.fromEntries(products.map(p => [p.id, p]));
  let total = 0;
  const cleaned = items.map(i => ({
    pid: Number(i.product_id), qty: Math.max(1, Number(i.qty) || 1)
  })).filter(i => byId[i.pid]);
  if (!cleaned.length) return res.status(400).json({ error: 'Pick valid products.' });
  cleaned.forEach(c => { total += byId[c.pid].price * c.qty; });

  const tx = db.transaction(() => {
    const ord = db.prepare("INSERT INTO orders (user_id, total, status, note, placed_by) VALUES (?, ?, ?, ?, 'admin')")
      .run(Number(user_id), total, status || 'Processing', note || null);
    const itemStmt = db.prepare('INSERT INTO order_items (order_id, product_id, product_name, unit_price, quantity) VALUES (?, ?, ?, ?, ?)');
    cleaned.forEach(c => {
      const p = byId[c.pid];
      itemStmt.run(ord.lastInsertRowid, p.id, p.name, p.price, c.qty);
    });
    return ord.lastInsertRowid;
  });
  res.json({ ok: true, orderId: tx() });
});

app.patch('/api/admin/orders/:id', requireAdmin, (req, res) => {
  const { status } = req.body;
  db.prepare('UPDATE orders SET status = ? WHERE id = ?').run(status, Number(req.params.id));
  res.json({ ok: true });
});

app.get('/api/admin/stats', requireAdmin, (_, res) => {
  res.json({
    products: db.prepare('SELECT COUNT(*) AS c FROM products').get().c,
    users:    db.prepare('SELECT COUNT(*) AS c FROM users').get().c,
    orders:   db.prepare('SELECT COUNT(*) AS c FROM orders').get().c,
    revenue:  db.prepare('SELECT COALESCE(SUM(total), 0) AS t FROM orders').get().t,
    recent:   db.prepare(`
      SELECT o.*, u.username FROM orders o
      JOIN users u ON u.id = o.user_id
      ORDER BY o.created_at DESC LIMIT 6
    `).all()
  });
});

// =========================================================
//  HTML routes — clean URLs
// =========================================================
const sendPage = (rel) => (req, res) => res.sendFile(join(__dirname, 'public', rel));

app.get('/',          sendPage('index.html'));
app.get('/products',  sendPage('products.html'));
app.get('/product',   sendPage('product.html'));
app.get('/cart',      sendPage('cart.html'));
app.get('/contact',   sendPage('contact.html'));
app.get('/login',     sendPage('login.html'));
app.get('/register',  sendPage('register.html'));
app.get('/account',   sendPage('account.html'));
app.get('/orders',    sendPage('orders.html'));

app.get('/admin',         (_, res) => res.redirect('/admin/login'));
app.get('/admin/login',     sendPage('admin/login.html'));
app.get('/admin/dashboard', sendPage('admin/dashboard.html'));
app.get('/admin/products',  sendPage('admin/products.html'));
app.get('/admin/users',     sendPage('admin/users.html'));
app.get('/admin/orders',    sendPage('admin/orders.html'));
app.get('/admin/add-order', sendPage('admin/add-order.html'));

// 404
app.use((req, res) => {
  if (req.path.startsWith('/api/')) return res.status(404).json({ error: 'Not found' });
  res.status(404).sendFile(join(__dirname, 'public', '404.html'));
});

app.listen(PORT, '0.0.0.0', () => {
  console.log(`Voidxx running at http://0.0.0.0:${PORT}`);
});
