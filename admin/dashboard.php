<?php
require '_auth.php';
require '../db.php';

$pCount = (int)$pdo->query("SELECT COUNT(*) AS c FROM products")->fetch()['c'];
$uCount = (int)$pdo->query("SELECT COUNT(*) AS c FROM users")->fetch()['c'];
$oCount = (int)$pdo->query("SELECT COUNT(*) AS c FROM orders")->fetch()['c'];
$rev    = (float)$pdo->query("SELECT COALESCE(SUM(total),0) AS t FROM orders")->fetch()['t'];

$recent = $pdo->query("
  SELECT o.*, u.username FROM orders o
  JOIN users u ON u.id = o.user_id
  ORDER BY o.created_at DESC LIMIT 6
")->fetchAll();

require '_layout.php';
?>

<div class="page-intro">
  <h1>Dashboard</h1>
  <p class="muted">Live snapshot of your store.</p>
</div>

<div class="stat-grid">
  <div class="stat"><div class="num"><?= $pCount ?></div><div class="lbl">Products</div></div>
  <div class="stat"><div class="num"><?= $uCount ?></div><div class="lbl">Customers</div></div>
  <div class="stat"><div class="num"><?= $oCount ?></div><div class="lbl">Orders</div></div>
  <div class="stat"><div class="num"><?= fmt_price($rev) ?></div><div class="lbl">Revenue</div></div>
</div>

<div class="section-head"><h2>Recent orders</h2><a class="muted" href="orders.php">View all →</a></div>

<?php if (!$recent): ?>
  <div class="card"><p class="muted">No orders yet.</p></div>
<?php else: ?>
<table class="table">
  <thead><tr><th>#</th><th>Customer</th><th>Total</th><th>Status</th><th>Placed</th><th>When</th></tr></thead>
  <tbody>
  <?php foreach ($recent as $o): ?>
    <tr>
      <td>#<?= $o['id'] ?></td>
      <td><?= htmlspecialchars($o['username']) ?></td>
      <td><?= fmt_price($o['total']) ?></td>
      <td><span class="badge badge-<?= strtolower($o['status']) ?>"><?= htmlspecialchars($o['status']) ?></span></td>
      <td><span class="badge badge-info"><?= htmlspecialchars($o['placed_by']) ?></span></td>
      <td class="muted small"><?= date('M j, H:i', strtotime($o['created_at'])) ?></td>
    </tr>
  <?php endforeach; ?>
  </tbody>
</table>
<?php endif; ?>

<?php require '_layout_end.php'; ?>
