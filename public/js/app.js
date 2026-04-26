// Shared JS: header/footer injection, session, toast, cart count
const fmt = (n) => '₹' + Number(n || 0).toLocaleString('en-IN');

async function api(path, opts = {}) {
  const res = await fetch(path, {
    headers: { 'Content-Type': 'application/json', ...(opts.headers || {}) },
    credentials: 'same-origin',
    ...opts
  });
  if (!res.ok) {
    let msg = 'Something went wrong';
    try { const j = await res.json(); msg = j.error || msg; } catch (_) {}
    throw new Error(msg);
  }
  if (res.status === 204) return null;
  const ct = res.headers.get('content-type') || '';
  return ct.includes('application/json') ? res.json() : res.text();
}

function toast(msg, type = '') {
  let wrap = document.querySelector('.toast-wrap');
  if (!wrap) { wrap = document.createElement('div'); wrap.className = 'toast-wrap'; document.body.appendChild(wrap); }
  const t = document.createElement('div');
  t.className = 'toast ' + type;
  t.textContent = msg;
  wrap.appendChild(t);
  setTimeout(() => { t.style.opacity = '0'; t.style.transition = 'opacity .25s ease'; }, 2400);
  setTimeout(() => t.remove(), 2700);
}

function buildHeader(me) {
  const path = location.pathname.replace(/\/$/, '') || '/';
  const isActive = (p) => path === p ? 'class="active"' : '';
  const userActions = me?.user
    ? `
      <a href="/orders">Orders</a>
      <a href="/account">Hi, ${escapeHtml((me.user.full_name || me.user.username).split(' ')[0])}</a>
      <a href="#" id="logoutLink">Logout</a>`
    : `
      <a href="/login">Sign in</a>
      <a href="/register">Register</a>
      <a href="/admin">Admin</a>`;
  return `
    <div class="bar">Free shipping on orders over ₹2,000 · <a href="/products">Shop the new collection →</a></div>
    <header class="site-header">
      <div class="header-row">
        <nav class="nav">
          <a href="/products"  ${isActive('/products')}>Shop</a>
          <a href="/products?cat=Outerwear">Outerwear</a>
          <a href="/products?cat=Footwear">Footwear</a>
          <a href="/contact"   ${isActive('/contact')}>Contact</a>
        </nav>
        <a class="brand" href="/">VOID<span>X</span>X</a>
        <div class="nav-actions">
          ${userActions}
          <a class="cart-link" href="/cart">Cart<span class="count" id="cartCount">${me?.cartCount || 0}</span></a>
        </div>
      </div>
    </header>`;
}

function buildFooter() {
  return `
    <footer class="site-footer">
      <div class="container">
        <div class="footer-top">
          <div>
            <div class="brand">VOID<span>X</span>X</div>
            <p style="color: rgba(246,243,236,.7); max-width: 320px;">Considered, premium pieces designed to outlast the trends.</p>
            <form class="newsletter" onsubmit="event.preventDefault(); window.toast('Thanks — you\\'re on the list.', 'success'); this.reset();">
              <input type="email" required placeholder="Email for new drops">
              <button>Join</button>
            </form>
          </div>
          <div>
            <h5>Shop</h5>
            <a href="/products">All products</a>
            <a href="/products?cat=Outerwear">Outerwear</a>
            <a href="/products?cat=Footwear">Footwear</a>
            <a href="/products?cat=Accessories">Accessories</a>
          </div>
          <div>
            <h5>Account</h5>
            <a href="/login">Sign in</a>
            <a href="/register">Create account</a>
            <a href="/orders">My orders</a>
            <a href="/admin">Admin portal</a>
          </div>
          <div>
            <h5>Help</h5>
            <a href="/contact">Contact</a>
            <a href="#">Shipping</a>
            <a href="#">Returns</a>
            <a href="#">Size guide</a>
          </div>
        </div>
        <div class="footer-bottom">
          <span>© ${new Date().getFullYear()} Voidxx. All rights reserved.</span>
          <span>Crafted with care.</span>
        </div>
      </div>
    </footer>`;
}

function escapeHtml(str) {
  return String(str ?? '').replace(/[&<>"']/g, m => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[m]));
}

const PLACEHOLDER_IMG = `data:image/svg+xml;utf8,${encodeURIComponent(`
<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 400 500'>
  <defs>
    <linearGradient id='g' x1='0' y1='0' x2='1' y2='1'>
      <stop offset='0' stop-color='#1c1410'/>
      <stop offset='1' stop-color='#3d2818'/>
    </linearGradient>
  </defs>
  <rect width='400' height='500' fill='url(#g)'/>
  <text x='200' y='240' font-family='Georgia, serif' font-size='52' fill='rgba(255,255,255,.9)' text-anchor='middle'>VOIDXX</text>
  <text x='200' y='275' font-family='Inter, sans-serif' font-size='12' letter-spacing='6' fill='rgba(184,137,90,.9)' text-anchor='middle'>PREMIUM CLOTHING</text>
</svg>`)}`;

function img(src, alt = '') {
  return `<img src="${escapeHtml(src || PLACEHOLDER_IMG)}" alt="${escapeHtml(alt)}" loading="lazy" onerror="this.onerror=null;this.src='${PLACEHOLDER_IMG}'">`;
}

function productCardHtml(p, opts = {}) {
  const save = (p.competitor_price && p.competitor_price > p.price)
    ? p.competitor_price - p.price : 0;
  const tag = save > 0
    ? `<div class="tag deal">Save ${fmt(save)}</div>`
    : (opts.newTag ? `<div class="tag">New</div>` : '');
  return `
    <article class="product">
      <a class="thumb" href="/product?id=${p.id}">
        ${tag}
        ${img(p.image, p.name)}
        <button class="quick-add" data-add="${p.id}">+ Quick add</button>
      </a>
      <div class="meta">
        <span class="cat">${escapeHtml(p.category)}</span>
        <a class="name" href="/product?id=${p.id}">${escapeHtml(p.name)}</a>
        <div class="price-row">
          <span class="price">${fmt(p.price)}</span>
          ${p.competitor_price ? `<span class="strike">${fmt(p.competitor_price)}</span>` : ''}
          ${save > 0 ? `<span class="save">−${fmt(save)}</span>` : ''}
        </div>
      </div>
    </article>`;
}

async function addToCart(productId) {
  try {
    const r = await api('/api/cart/add', { method: 'POST', body: JSON.stringify({ productId }) });
    document.getElementById('cartCount').textContent = r.count;
    toast('Added to cart', 'success');
  } catch (e) {
    toast(e.message, 'error');
  }
}

document.addEventListener('click', async (e) => {
  const addBtn = e.target.closest('[data-add]');
  if (addBtn) {
    e.preventDefault();
    addToCart(Number(addBtn.dataset.add));
  }
  if (e.target.id === 'logoutLink') {
    e.preventDefault();
    await api('/api/auth/logout', { method: 'POST' });
    location.href = '/';
  }
});

// Init: inject header/footer everywhere
window.toast = toast;
window.api = api;
window.fmt = fmt;
window.img = img;
window.escapeHtml = escapeHtml;
window.productCardHtml = productCardHtml;

document.addEventListener('DOMContentLoaded', async () => {
  const me = await api('/api/me').catch(() => ({}));
  window.__me = me;
  const headerSlot = document.getElementById('header-slot');
  const footerSlot = document.getElementById('footer-slot');
  if (headerSlot) headerSlot.outerHTML = buildHeader(me);
  if (footerSlot) footerSlot.outerHTML = buildFooter();
  document.dispatchEvent(new CustomEvent('app-ready', { detail: me }));
});
