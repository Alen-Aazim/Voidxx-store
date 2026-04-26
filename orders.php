<?php
require 'db.php';
require_once '_helpers.php';
require_user_login();
$user = current_user($pdo);

$stmt = $pdo->prepare("SELECT * FROM orders WHERE user_id=? ORDER BY created_at DESC");
$stmt->execute([$user['id']]);
$orders = $stmt->fetchAll();

$itemStmt = $pdo->prepare("SELECT * FROM order_items WHERE order_id=?");
?>
<?php require 'header.php'; ?>

<div class="page-intro">
  <h1>Your Orders</h1>
  <p class="muted">A complete history of everything you've ordered with Voidxx.</p>
</div>

<?php if (!$orders): ?>
  <div class="card" style="text-align:center;padding:60px 30px;">
    <h2>No orders yet</h2>
    <p class="muted">Once you place your first order, it'll show up here.</p>
    <a class="btn btn-accent" href="<?=BASE_URL?>products.php">Start shopping</a>
  </div>
<?php else: ?>
  <?php foreach ($orders as $o): ?>
    <?php $itemStmt->execute([$o['id']]); $items = $itemStmt->fetchAll(); ?>
    <div class="order-card">
      <div class="order-head">
        <div>
          <h3 style="margin:0 0 4px;">Order #<?= $o['id'] ?></h3>
          <div class="order-meta">
            <span><?= date('M j, Y · H:i', strtotime($o['created_at'])) ?></span>
            <span><?= count($items) ?> item<?= count($items) === 1 ? '' : 's' ?></span>
            <?php if ($o['placed_by'] === 'admin'): ?>
              <span class="badge badge-info">Placed by admin</span>
            <?php endif; ?>
          </div>
        </div>
        <div style="text-align:right;">
          <span class="badge badge-<?= strtolower($o['status']) ?>"><?= htmlspecialchars($o['status']) ?></span>
          <div style="margin-top:6px;font-weight:700;font-size:1.15rem;"><?= fmt_price($o['total']) ?></div>
        </div>
      </div>
      <div class="order-items">
      <?php foreach ($items as $it): ?>
        <div class="order-item">
          <span class="name"><?= htmlspecialchars($it['product_name']) ?></span>
          <span class="qty muted">×<?= $it['quantity'] ?></span>
          <span class="each muted"><?= fmt_price($it['unit_price']) ?> ea.</span>
          <span class="line"><?= fmt_price($it['unit_price'] * $it['quantity']) ?></span>
        </div>
      <?php endforeach; ?>
      </div>
      <?php if ($o['note']): ?>
        <p class="muted small" style="margin-top:12px;border-top:1px dashed var(--line);padding-top:10px;">
          <strong>Note:</strong> <?= htmlspecialchars($o['note']) ?>
        </p>
      <?php endif; ?>
    </div>
  <?php endforeach; ?>
<?php endif; ?>

<?php require 'footer.php'; ?>
