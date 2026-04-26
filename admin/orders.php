<?php
require '_auth.php';
require '../db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['order_id'], $_POST['status'])) {
    $stmt = $pdo->prepare("UPDATE orders SET status=? WHERE id=?");
    $stmt->execute([$_POST['status'], (int)$_POST['order_id']]);
    flash_set('success', 'Order status updated.');
    header("Location: orders.php"); exit;
}

$orders = $pdo->query("
  SELECT o.*, u.username FROM orders o
  JOIN users u ON u.id = o.user_id
  ORDER BY o.created_at DESC
")->fetchAll();

$itemStmt = $pdo->prepare("SELECT * FROM order_items WHERE order_id=?");
$statuses = ['Processing','Shipped','Delivered','Cancelled'];
require '_layout.php';
?>

<div class="page-intro" style="display:flex;justify-content:space-between;align-items:end;flex-wrap:wrap;gap:14px;">
  <div>
    <h1>All Orders</h1>
    <p class="muted">Every order placed through the store.</p>
  </div>
  <a class="btn btn-accent" href="add_order.php">+ Add order to user</a>
</div>

<?php if (!$orders): ?>
  <div class="card"><p class="muted">No orders yet. Use "Add order to user" to create one for a customer.</p></div>
<?php else: ?>
  <?php foreach ($orders as $o): $itemStmt->execute([$o['id']]); $items = $itemStmt->fetchAll(); ?>
    <div class="order-card">
      <div class="order-head">
        <div>
          <h3 style="margin:0 0 4px;">Order #<?= $o['id'] ?> · <?= htmlspecialchars($o['username']) ?></h3>
          <div class="order-meta">
            <span><?= date('M j, Y · H:i', strtotime($o['created_at'])) ?></span>
            <span><?= count($items) ?> items</span>
            <span class="badge badge-info">via <?= htmlspecialchars($o['placed_by']) ?></span>
          </div>
        </div>
        <div style="text-align:right;">
          <form method="post" style="display:flex;gap:6px;align-items:center;">
            <input type="hidden" name="order_id" value="<?= $o['id'] ?>">
            <select class="select" name="status" style="width:auto;">
              <?php foreach ($statuses as $s): ?>
                <option value="<?= $s ?>" <?= $o['status'] === $s ? 'selected' : '' ?>><?= $s ?></option>
              <?php endforeach; ?>
            </select>
            <button class="btn btn-sm" type="submit">Update</button>
          </form>
          <div style="margin-top:8px;font-weight:700;font-size:1.15rem;"><?= fmt_price($o['total']) ?></div>
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
    </div>
  <?php endforeach; ?>
<?php endif; ?>

<?php require '_layout_end.php'; ?>
