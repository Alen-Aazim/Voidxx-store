</main>

<footer class="site-footer">
  <div class="container footer-grid">
    <div>
      <div class="logo logo-footer">VOIDXX<span class="logo-dot">.</span></div>
      <p class="muted small">Premium fashion. Better prices.<br>Crafted for those who notice the details.</p>
    </div>
    <div>
      <h4>Shop</h4>
      <a href="<?=BASE_URL?>products.php">All Products</a>
      <a href="<?=BASE_URL?>compare.php">Compare</a>
      <a href="<?=BASE_URL?>cart.php">Cart</a>
    </div>
    <div>
      <h4>Account</h4>
      <?php if (isset($_SESSION['user_id'])): ?>
        <a href="<?=BASE_URL?>account.php">My Account</a>
        <a href="<?=BASE_URL?>orders.php">My Orders</a>
        <a href="<?=BASE_URL?>logout.php">Logout</a>
      <?php else: ?>
        <a href="<?=BASE_URL?>login.php">Sign in</a>
        <a href="<?=BASE_URL?>register.php">Create account</a>
      <?php endif; ?>
    </div>
    <div>
      <h4>Help</h4>
      <a href="<?=BASE_URL?>contact.php">Contact</a>
      <a href="<?=BASE_URL?>admin/login.php">Admin Portal</a>
    </div>
  </div>
  <div class="footer-bottom container">
    <span class="muted small">© <?=date('Y')?> Voidxx. All rights reserved.</span>
    <span class="muted small">Made with care.</span>
  </div>
</footer>

</body>
</html>
