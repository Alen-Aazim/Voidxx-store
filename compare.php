<?php require 'db.php'; ?>
<?php require 'header.php'; ?>

<?php
$ids = $_GET['compare'] ?? [];
if (!$ids) {
    echo '<div class="page-intro"><h1>Compare</h1><p class="muted">Select products from the shop to compare them side-by-side.</p></div>';
    echo '<a class="btn" href="'.BASE_URL.'products.php">Browse products</a>';
    require 'footer.php'; exit;
}
$ids = array_map('intval', (array)$ids);
$in = implode(',', $ids);
$items = $pdo->query("SELECT * FROM products WHERE id IN ($in)")->fetchAll();
?>

<div class="page-intro">
  <h1>Compare Products</h1>
  <p class="muted">See how Voidxx prices stack up against the competition.</p>
</div>

<div class="product-grid">
<?php foreach($items as $p): ?>
  <article class="product-card">
    <div class="product-thumb">
      <img src="<?= product_image_src($p['image']) ?>" alt="<?= htmlspecialchars($p['name']) ?>">
    </div>
    <div class="product-info">
      <span class="cat"><?= htmlspecialchars($p['category']) ?></span>
      <h3><?= htmlspecialchars($p['name']) ?></h3>
      <div class="price-row">
        <span class="price"><?= fmt_price($p['price']) ?></span>
      </div>
      <?php if ($p['competitor_price']): ?>
        <p class="small muted" style="margin:8px 0 4px;">Competitor: <strong><?= fmt_price($p['competitor_price']) ?></strong></p>
        <p class="small" style="color:var(--accent);font-weight:600;">You save <?= fmt_price($p['competitor_price'] - $p['price']) ?></p>
      <?php endif; ?>
      <a class="btn btn-sm" href="<?=BASE_URL?>product.php?id=<?=$p['id']?>">View product</a>
    </div>
  </article>
<?php endforeach; ?>
</div>

<?php require 'footer.php'; ?>
