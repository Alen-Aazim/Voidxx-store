// Admin shared layout
function adminSidebar() {
  const path = location.pathname;
  const link = (href, label) => `<a href="${href}" class="${path === href ? 'active' : ''}">${label}</a>`;
  return `
    <aside class="admin-side">
      <h5>Overview</h5>
      ${link('/admin/dashboard', 'Dashboard')}
      <h5>Catalog</h5>
      ${link('/admin/products', 'Products')}
      <h5>Customers</h5>
      ${link('/admin/users', 'Customers')}
      <h5>Orders</h5>
      ${link('/admin/orders', 'All orders')}
      ${link('/admin/add-order', '+ Add order to user')}
      <h5>Account</h5>
      <a href="#" id="adminLogout">Sign out</a>
    </aside>`;
}

document.addEventListener('app-ready', (e) => {
  if (!e.detail?.admin) {
    if (location.pathname !== '/admin/login') return location.href = '/admin/login';
  }
  const slot = document.getElementById('admin-side-slot');
  if (slot) slot.outerHTML = adminSidebar();

  document.addEventListener('click', async (ev) => {
    if (ev.target.id === 'adminLogout') {
      ev.preventDefault();
      await api('/api/admin/logout', { method: 'POST' });
      location.href = '/admin/login';
    }
  });
});
