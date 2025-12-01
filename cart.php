<?php require 'db.php'; ?>
<?php require 'header.php'; ?>

<?php
$cart = $_SESSION['cart'] ?? [];
$items = [];
$total = 0;

if ($cart) {
    $ids = implode(',', array_keys($cart));
    $stmt = $pdo->query("SELECT * FROM products WHERE id IN ($ids)");
    $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>

<h1>Your Cart</h1>

<?php if(!$items): ?>
<p>Your cart is empty.</p>
<?php else: ?>
<table class="table">
<tr><th>Product</th><th>Qty</th><th>Total</th><th></th></tr>

<?php foreach($items as $p): ?>
<?php
$qty = $cart[$p['id']];
$line = $qty * $p['price'];
$total += $line;
?>
<tr>
  <td><?= htmlspecialchars($p['name']) ?></td>
  <td><?= $qty ?></td>
  <td>₹<?= number_format($line) ?></td>
  <td><a href="cart.php?action=remove&id=<?=$p['id']?>">Remove</a></td>
</tr>
<?php endforeach; ?>

</table>

<h3>Total: ₹<?= number_format($total) ?></h3>
<?php endif; ?>

<?php require 'footer.php'; ?>