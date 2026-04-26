<?php
require '_auth.php';
require '../db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userId = (int)$_POST['user_id'];
    $note   = trim($_POST['note'] ?? '');
    $status = $_POST['status'] ?? 'Processing';
    $items  = $_POST['items'] ?? [];

    $clean = [];
    foreach ($items as $row) {
        $pid = (int)($row['product_id'] ?? 0);
        $qty = (int)($row['qty'] ?? 0);
        if ($pid > 0 && $qty > 0) $clean[] = ['pid' => $pid, 'qty' => $qty];
    }
    if (!$userId || !$clean) {
        flash_set('error', 'Please pick a customer and at least one product.');
        header("Location: add_order.php"); exit;
    }

    $ids = implode(',', array_map(fn($r) => $r['pid'], $clean));
    $rows = $pdo->query("SELECT id, name, price FROM products WHERE id IN ($ids)")->fetchAll();
    $byId = [];
    foreach ($rows as $r) $byId[$r['id']] = $r;

    $total = 0;
    foreach ($clean as $c) {
        if (isset($byId[$c['pid']])) $total += $byId[$c['pid']]['price'] * $c['qty'];
    }

    $pdo->beginTransaction();
    $stmt = $pdo->prepare("INSERT INTO orders (user_id, total, status, note, placed_by) VALUES (?, ?, ?, ?, 'admin')");
    $stmt->execute([$userId, $total, $status, $note]);
    $orderId = $pdo->lastInsertId();
    $itemStmt = $pdo->prepare("INSERT INTO order_items (order_id, product_id, product_name, unit_price, quantity) VALUES (?, ?, ?, ?, ?)");
    foreach ($clean as $c) {
        if (!isset($byId[$c['pid']])) continue;
        $p = $byId[$c['pid']];
        $itemStmt->execute([$orderId, $p['id'], $p['name'], $p['price'], $c['qty']]);
    }
    $pdo->commit();

    flash_set('success', 'Order #'.$orderId.' added to customer\'s account.');
    header("Location: orders.php"); exit;
}

$users    = $pdo->query("SELECT id, username, full_name, email FROM users ORDER BY username")->fetchAll();
$products = $pdo->query("SELECT id, name, price, category FROM products ORDER BY name")->fetchAll();
$preselectUser = (int)($_GET['user_id'] ?? 0);

require '_layout.php';
?>

<div class="page-intro">
  <h1>Add order to a customer</h1>
  <p class="muted">Place an order on behalf of a user. It will appear in their order history immediately.</p>
</div>

<?php if (!$users): ?>
  <div class="card">
    <p class="muted">No registered customers yet. Ask someone to <a href="<?=BASE_URL?>register.php">register</a> first.</p>
  </div>
<?php else: ?>

<form class="card" method="post" id="orderForm">
  <div class="compose-grid">
    <div>
      <div class="field">
        <label class="label">Customer</label>
        <select class="select" name="user_id" required>
          <option value="">— Select customer —</option>
          <?php foreach ($users as $u): ?>
            <option value="<?= $u['id'] ?>" <?= $preselectUser === (int)$u['id'] ? 'selected' : '' ?>>
              <?= htmlspecialchars($u['username']) ?> · <?= htmlspecialchars($u['email']) ?>
              <?= $u['full_name'] ? ' ('.htmlspecialchars($u['full_name']).')' : '' ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>

      <label class="label">Items</label>
      <div id="lines">
        <div class="line-row">
          <select class="select" name="items[0][product_id]" required>
            <option value="">— Choose product —</option>
            <?php foreach ($products as $p): ?>
              <option value="<?= $p['id'] ?>" data-price="<?= $p['price'] ?>">
                <?= htmlspecialchars($p['name']) ?> — <?= fmt_price($p['price']) ?>
              </option>
            <?php endforeach; ?>
          </select>
          <input class="input" type="number" min="1" value="1" name="items[0][qty]">
          <button type="button" class="btn btn-sm btn-ghost" onclick="this.parentNode.remove()">×</button>
        </div>
      </div>
      <button type="button" class="btn btn-sm btn-ghost" onclick="addLine()">+ Add another item</button>
    </div>

    <div>
      <div class="field">
        <label class="label">Status</label>
        <select class="select" name="status">
          <option>Processing</option>
          <option>Shipped</option>
          <option>Delivered</option>
          <option>Cancelled</option>
        </select>
      </div>
      <div class="field">
        <label class="label">Internal note (optional)</label>
        <textarea class="textarea" name="note" placeholder="Manual order entry, in-store purchase, etc."></textarea>
      </div>
      <div class="card" style="background:#f6f5f1;border-style:dashed;">
        <strong>Summary</strong>
        <p class="small muted" style="margin:8px 0 0;">Totals are calculated server-side from current product prices when the order is saved.</p>
      </div>
    </div>
  </div>

  <div style="margin-top:18px;display:flex;gap:10px;">
    <button class="btn btn-accent" type="submit">Save order to customer</button>
    <a class="btn btn-ghost" href="users.php">Cancel</a>
  </div>
</form>

<script>
let lineIdx = 1;
const productOptions = <?= json_encode(array_map(fn($p) => ['id'=>$p['id'],'name'=>$p['name'],'price'=>$p['price']], $products)) ?>;
function addLine() {
  const i = lineIdx++;
  const opts = productOptions.map(p => `<option value="${p.id}">${p.name} — ₹${Number(p.price).toLocaleString('en-IN')}</option>`).join('');
  const div = document.createElement('div');
  div.className = 'line-row';
  div.innerHTML = `
    <select class="select" name="items[${i}][product_id]" required>
      <option value="">— Choose product —</option>${opts}
    </select>
    <input class="input" type="number" min="1" value="1" name="items[${i}][qty]">
    <button type="button" class="btn btn-sm btn-ghost" onclick="this.parentNode.remove()">×</button>`;
  document.getElementById('lines').appendChild(div);
}
</script>

<?php endif; ?>

<?php require '_layout_end.php'; ?>
