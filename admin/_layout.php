<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../_helpers.php';
require __DIR__ . '/../header.php';
$current = basename($_SERVER['PHP_SELF']);
?>
<div class="admin-shell">
  <aside class="admin-side">
    <h5>Overview</h5>
    <a href="dashboard.php" class="<?= $current === 'dashboard.php' ? 'active' : '' ?>">Dashboard</a>
    <h5>Catalog</h5>
    <a href="products.php"   class="<?= in_array($current, ['products.php','add_product.php','edit_product.php']) ? 'active' : '' ?>">Products</a>
    <a href="add_product.php" class="<?= $current === 'add_product.php' ? 'active' : '' ?>">+ New product</a>
    <h5>Customers</h5>
    <a href="users.php"      class="<?= $current === 'users.php' ? 'active' : '' ?>">Users</a>
    <h5>Orders</h5>
    <a href="orders.php"     class="<?= $current === 'orders.php' ? 'active' : '' ?>">All orders</a>
    <a href="add_order.php"  class="<?= $current === 'add_order.php' ? 'active' : '' ?>">+ Add order to user</a>
    <h5>Account</h5>
    <a href="logout.php">Sign out</a>
  </aside>
  <section class="admin-main">
