<?php require 'db.php'; ?>
<?php require 'header.php'; ?>

<?php
$id = intval($_GET['id'] ?? 0);
$stmt = $pdo->prepare("SELECT * FROM products WHERE id=?");
$stmt->execute([$id]);
$p = $stmt->fetch();
if (!$p) {
    echo '<div class="card"><h2>Product not found</h2><a class="btn" href="'.BASE_URL.'products.php">Back to shop</a></div>';
    require 'footer.php'; exit;
}
?>

<div class="product-detail">
  <div class="thumb">
    <img src="<?= product_image_src($p['image']) ?>" alt="<?= htmlspecialchars($p['name']) ?>">
  </div>
  <div>
    <span class="cat muted small" style="letter-spacing:.14em;text-transform:uppercase;"><?= htmlspecialchars($p['category']) ?></span>
    <h1><?= htmlspecialchars($p['name']) ?></h1>
    <div class="price-row" style="margin:6px 0 18px;">
      <span class="price" style="font-size:1.6rem;"><?= fmt_price($p['price']) ?></span>
      <?php if ($p['competitor_price']): ?>
        <span class="price-strike"><?= fmt_price($p['competitor_price']) ?></span>
        <span class="savings">Save <?= fmt_price($p['competitor_price'] - $p['price']) ?></span>
      <?php endif; ?>
    </div>
    <p class="muted"><?= htmlspecialchars($p['description']) ?></p>
    <p class="small muted">In stock: <?= (int)$p['stock'] ?> units</p>

    <div style="display:flex;gap:10px;margin-top:18px;">
      <a class="btn btn-accent" href="<?=BASE_URL?>cart.php?action=add&id=<?=$p['id']?>">Add to cart</a>
      <a class="btn btn-outline" href="<?=BASE_URL?>products.php">Keep shopping</a>
    </div>
  </div>
</div>

<?php require 'footer.php'; ?>
