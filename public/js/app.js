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
  setTimeout(() => { t.style.opacity = '0'; t.style.transition = 'opacity .25s ease, transform .25s ease'; t.style.transform = 'scale(.85) rotate(2deg)'; }, 2400);
  setTimeout(() => t.remove(), 2700);
}

function buildHeader(me) {
  const path = location.pathname.replace(/\/$/, '') || '/';
  const cat  = new URL(location.href).searchParams.get('cat');
  const isCat = (c) => path === '/products' && cat === c ? 'class="active"' : '';
  const userActions = me?.user
    ? `
      <a href="/orders">Orders</a>
      <a href="/account">${escapeHtml((me.user.full_name || me.user.username).split(' ')[0])}</a>
      <a href="#" id="logoutLink">Logout</a>`
    : `
      <a href="/login">Sign in</a>
      <a href="/admin">Admin</a>`;
  return `
    <div class="bar">FREE SHIPPING OVER ₹2,000 · <a href="/products">SHOP THE NEW DROP →</a></div>
    <header class="site-header">
      <div class="header-row">
        <nav class="nav">
          <a href="/products?cat=Jackets" ${isCat('Jackets')}>Jackets</a>
          <a href="/products?cat=Tees"    ${isCat('Tees')}>Tees</a>
          <a href="/products?cat=Pants"   ${isCat('Pants')}>Pants</a>
          <a href="/products?cat=Shorts"  ${isCat('Shorts')}>Shorts</a>
          <a href="/products" ${path === '/products' && !cat ? 'class="active"' : ''}>All</a>
        </nav>
        <a class="brand" href="/">
          <img src="/images/voidxx-mark.png" alt="Voidxx logo">
          <span class="word">Voidxx</span>
        </a>
        <div class="nav-actions">
          ${userActions}
          <a class="cart-link" href="/cart">Bag<span class="count" id="cartCount">${me?.cartCount || 0}</span></a>
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
            <a class="brand" href="/" style="display:inline-flex;align-items:center;gap:14px;">
              <img src="/images/voidxx-mark.png" alt="Voidxx logo">
              <span class="word">Voidxx</span>
            </a>
            <p style="color: rgba(255,255,255,.7); max-width: 320px; font-size: .92rem; margin-top: 14px; font-weight: 500;">Four essentials. Built well. Worn often. Skip the rest.</p>
            <form class="newsletter" onsubmit="event.preventDefault(); window.toast('You\\'re on the list!', 'success'); this.reset();">
              <input type="email" required placeholder="Email for new drops">
              <button>Join</button>
            </form>
          </div>
          <div>
            <h5>Shop</h5>
            <a href="/products?cat=Jackets">Jackets</a>
            <a href="/products?cat=Tees">Tees</a>
            <a href="/products?cat=Pants">Pants</a>
            <a href="/products?cat=Shorts">Shorts</a>
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
          <span>© ${new Date().getFullYear()} Voidxx</span>
          <span>Made loud.</span>
        </div>
      </div>
    </footer>`;
}

function escapeHtml(str) {
  return String(str ?? '').replace(/[&<>"']/g, m => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[m]));
}

const PLACEHOLDER_IMG = `data:image/svg+xml;utf8,${encodeURIComponent(`
<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 400 500'>
  <rect width='400' height='500' fill='#ed2129'/>
  <text x='200' y='240' font-family='Helvetica, Arial, sans-serif' font-weight='900' font-size='56' fill='#ffffff' stroke='#0a0a0a' stroke-width='2' text-anchor='middle' letter-spacing='-2'>VOIDXX</text>
  <text x='200' y='275' font-family='Helvetica, Arial, sans-serif' font-size='10' letter-spacing='6' fill='rgba(255,255,255,.85)' text-anchor='middle'>NO IMAGE</text>
</svg>`)}`;

function img(src, alt = '') {
  return `<img src="${escapeHtml(src || PLACEHOLDER_IMG)}" alt="${escapeHtml(alt)}" loading="lazy" onerror="this.onerror=null;this.src='${PLACEHOLDER_IMG}'">`;
}

function productCardHtml(p, opts = {}) {
  const save = (p.competitor_price && p.competitor_price > p.price) ? p.competitor_price - p.price : 0;
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
        </div>
      </div>
    </article>`;
}

async function addToCart(productId) {
  try {
    const r = await api('/api/cart/add', { method: 'POST', body: JSON.stringify({ productId }) });
    document.getElementById('cartCount').textContent = r.count;
    toast('Added to bag!', 'success');
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

// expose helpers
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
