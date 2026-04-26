<?php require 'db.php'; ?>
<?php
require_once '_helpers.php';

$action = $_GET['action'] ?? null;
$id = intval($_GET['id'] ?? 0);
if (!isset($_SESSION['cart'])) $_SESSION['cart'] = [];

if ($action === 'add' && $id) {
    $stmt = $pdo->prepare("SELECT id FROM products WHERE id=?");
    $stmt->execute([$id]);
    if ($stmt->fetch()) {
        $_SESSION['cart'][$id] = ($_SESSION['cart'][$id] ?? 0) + 1;
        flash_set('success', 'Added to cart.');
    }
    header("Location: ".BASE_URL."cart.php"); exit;
}
if ($action === 'remove' && $id) {
    unset($_SESSION['cart'][$id]);
    flash_set('info', 'Item removed.');
    header("Location: ".BASE_URL."cart.php"); exit;
}
if ($action === 'inc' && $id) {
    $_SESSION['cart'][$id] = ($_SESSION['cart'][$id] ?? 0) + 1;
    header("Location: ".BASE_URL."cart.php"); exit;
}
if ($action === 'dec' && $id) {
    if (isset($_SESSION['cart'][$id])) {
        $_SESSION['cart'][$id]--;
        if ($_SESSION['cart'][$id] <= 0) unset($_SESSION['cart'][$id]);
    }
    header("Location: ".BASE_URL."cart.php"); exit;
}
if ($action === 'clear') {
    $_SESSION['cart'] = [];
    flash_set('info', 'Cart cleared.');
    header("Location: ".BASE_URL."cart.php"); exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'checkout') {
    if (!isset($_SESSION['user_id'])) {
        flash_set('error', 'Please sign in to place an order.');
        header("Location: ".BASE_URL."login.php?next=cart.php"); exit;
    }
    if (!$_SESSION['cart']) {
        flash_set('error', 'Your cart is empty.');
        header("Location: ".BASE_URL."cart.php"); exit;
    }
    $ids = array_keys($_SESSION['cart']);
    $in = implode(',', array_map('intval', $ids));
    $rows = $pdo->query("SELECT * FROM products WHERE id IN ($in)")->fetchAll();

    $total = 0;
    foreach ($rows as $r) $total += $r['price'] * $_SESSION['cart'][$r['id']];

    $pdo->beginTransaction();
    $stmt = $pdo->prepare("INSERT INTO orders (user_id, total, status, placed_by) VALUES (?, ?, 'Processing', 'user')");
    $stmt->execute([$_SESSION['user_id'], $total]);
    $orderId = $pdo->lastInsertId();
    $itemStmt = $pdo->prepare("INSERT INTO order_items (order_id, product_id, product_name, unit_price, quantity) VALUES (?, ?, ?, ?, ?)");
    foreach ($rows as $r) {
        $itemStmt->execute([$orderId, $r['id'], $r['name'], $r['price'], $_SESSION['cart'][$r['id']]]);
    }
    $pdo->commit();
    $_SESSION['cart'] = [];
    flash_set('success', 'Order #'.$orderId.' placed successfully!');
    header("Location: ".BASE_URL."orders.php"); exit;
}

$cart = $_SESSION['cart'];
$items = [];
$total = 0;
if ($cart) {
    $ids = implode(',', array_map('intval', array_keys($cart)));
    $items = $pdo->query("SELECT * FROM products WHERE id IN ($ids)")->fetchAll();
}
?>
<?php require 'header.php'; ?>

<div class="page-intro">
  <h1>Your Cart</h1>
  <p class="muted"><?= cart_count() ?> item<?= cart_count() === 1 ? '' : 's' ?> in your bag.</p>
</div>

<?php if (!$items): ?>
  <div class="card" style="text-align:center;padding:60px 30px;">
    <h2 style="margin-bottom:10px;">Your bag is empty</h2>
    <p class="muted">Browse the shop and add a few favourites.</p>
    <a class="btn btn-accent" href="<?=BASE_URL?>products.php">Start shopping</a>
  </div>
<?php else: ?>
  <table class="table">
    <thead>
      <tr><th>Product</th><th>Price</th><th>Qty</th><th class="right">Subtotal</th><th></th></tr>
    </thead>
    <tbody>
    <?php foreach ($items as $p): $qty = $cart[$p['id']]; $line = $qty * $p['price']; $total += $line; ?>
      <tr>
        <td>
          <div style="display:flex;align-items:center;gap:12px;">
            <img src="<?= product_image_src($p['image']) ?>" alt="" style="width:60px;height:75px;object-fit:cover;border-radius:8px;">
            <div>
              <div style="font-weight:600;"><?= htmlspecialchars($p['name']) ?></div>
              <div class="muted small"><?= htmlspecialchars($p['category']) ?></div>
            </div>
          </div>
        </td>
        <td><?= fmt_price($p['price']) ?></td>
        <td>
          <a class="btn btn-sm btn-ghost" href="<?=BASE_URL?>cart.php?action=dec&id=<?=$p['id']?>">−</a>
          <strong style="margin:0 8px;"><?= $qty ?></strong>
          <a class="btn btn-sm btn-ghost" href="<?=BASE_URL?>cart.php?action=inc&id=<?=$p['id']?>">+</a>
        </td>
        <td class="right"><?= fmt_price($line) ?></td>
        <td class="right"><a class="muted small" href="<?=BASE_URL?>cart.php?action=remove&id=<?=$p['id']?>">Remove</a></td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>

  <div style="display:flex;justify-content:space-between;align-items:center;margin-top:22px;flex-wrap:wrap;gap:14px;">
    <a class="muted small" href="<?=BASE_URL?>cart.php?action=clear">Clear cart</a>
    <div style="display:flex;align-items:center;gap:18px;">
      <div><span class="muted small">Total</span> <strong style="font-size:1.3rem;margin-left:8px;"><?= fmt_price($total) ?></strong></div>
      <form method="post">
        <input type="hidden" name="action" value="checkout">
        <button class="btn btn-accent" type="submit">
          <?= isset($_SESSION['user_id']) ? 'Place order' : 'Sign in to checkout' ?>
        </button>
      </form>
    </div>
  </div>
<?php endif; ?>

<?php require 'footer.php'; ?>
