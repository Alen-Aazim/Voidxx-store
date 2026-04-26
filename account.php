<?php
require 'db.php';
require_once '_helpers.php';
require_user_login();
$user = current_user($pdo);
$orderCount = (int)$pdo->query("SELECT COUNT(*) AS c FROM orders WHERE user_id=".(int)$user['id'])->fetch()['c'];
$totalSpent = (float)$pdo->query("SELECT COALESCE(SUM(total),0) AS t FROM orders WHERE user_id=".(int)$user['id'])->fetch()['t'];
?>
<?php require 'header.php'; ?>

<div class="page-intro">
  <h1>Hi, <?= htmlspecialchars($user['full_name'] ?: $user['username']) ?>.</h1>
  <p class="muted">Welcome back to your Voidxx account.</p>
</div>

<div class="stat-grid">
  <div class="stat"><div class="num"><?= $orderCount ?></div><div class="lbl">Orders</div></div>
  <div class="stat"><div class="num"><?= fmt_price($totalSpent) ?></div><div class="lbl">Lifetime spend</div></div>
  <div class="stat"><div class="num"><?= htmlspecialchars($user['username']) ?></div><div class="lbl">Username</div></div>
  <div class="stat"><div class="num small" style="font-size:1rem;"><?= htmlspecialchars($user['email']) ?></div><div class="lbl">Email</div></div>
</div>

<div class="card">
  <h2>Quick links</h2>
  <div style="display:flex;gap:10px;flex-wrap:wrap;">
    <a class="btn" href="<?=BASE_URL?>orders.php">View order history</a>
    <a class="btn btn-outline" href="<?=BASE_URL?>products.php">Continue shopping</a>
    <a class="btn btn-ghost" href="<?=BASE_URL?>logout.php">Sign out</a>
  </div>
</div>

<?php require 'footer.php'; ?>
