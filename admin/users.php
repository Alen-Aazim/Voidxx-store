<?php
require '_auth.php';
require '../db.php';

$users = $pdo->query("
  SELECT u.*,
    (SELECT COUNT(*) FROM orders WHERE user_id = u.id) AS order_count,
    (SELECT COALESCE(SUM(total),0) FROM orders WHERE user_id = u.id) AS lifetime
  FROM users u
  ORDER BY u.created_at DESC
")->fetchAll();

require '_layout.php';
?>

<div class="page-intro">
  <h1>Customers</h1>
  <p class="muted">Everyone who's signed up to Voidxx.</p>
</div>

<?php if (!$users): ?>
  <div class="card"><p class="muted">No customers yet.</p></div>
<?php else: ?>
<table class="table">
  <thead><tr><th>Username</th><th>Name</th><th>Email</th><th>Orders</th><th>Lifetime</th><th>Joined</th><th></th></tr></thead>
  <tbody>
  <?php foreach ($users as $u): ?>
    <tr>
      <td><strong><?= htmlspecialchars($u['username']) ?></strong></td>
      <td><?= htmlspecialchars($u['full_name'] ?: '—') ?></td>
      <td class="muted small"><?= htmlspecialchars($u['email']) ?></td>
      <td><?= $u['order_count'] ?></td>
      <td><?= fmt_price($u['lifetime']) ?></td>
      <td class="muted small"><?= date('M j, Y', strtotime($u['created_at'])) ?></td>
      <td class="right">
        <a class="btn btn-sm btn-ghost" href="add_order.php?user_id=<?= $u['id'] ?>">+ Add order</a>
      </td>
    </tr>
  <?php endforeach; ?>
  </tbody>
</table>
<?php endif; ?>

<?php require '_layout_end.php'; ?>
