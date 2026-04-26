<?php require 'db.php'; ?>
<?php require 'header.php'; ?>

<div class="page-intro">
  <h1>The Shop</h1>
  <p class="muted">Every piece in our collection. Filter by category to refine.</p>
</div>

<?php
$cats = $pdo->query("SELECT DISTINCT category FROM products ORDER BY category")->fetchAll();
$activeCat = $_GET['cat'] ?? '';
if ($activeCat) {
    $stmt = $pdo->prepare("SELECT * FROM products WHERE category=? ORDER BY id DESC");
    $stmt->execute([$activeCat]);
    $items = $stmt->fetchAll();
} else {
    $items = $pdo->query("SELECT * FROM products ORDER BY id DESC")->fetchAll();
}
?>

<div style="display:flex;gap:8px;flex-wrap:wrap;margin-bottom:22px;">
  <a class="btn btn-sm <?= !$activeCat ? '' : 'btn-ghost' ?>" href="<?=BASE_URL?>products.php">All</a>
  <?php foreach ($cats as $c): ?>
    <a class="btn btn-sm <?= $activeCat === $c['category'] ? '' : 'btn-ghost' ?>"
       href="<?=BASE_URL?>products.php?cat=<?= urlencode($c['category']) ?>">
       <?= htmlspecialchars($c['category']) ?>
    </a>
  <?php endforeach; ?>
</div>

<form method="get" action="compare.php">
<div class="product-grid">
<?php foreach($items as $p): ?>
  <article class="product-card">
    <a class="product-thumb" href="<?=BASE_URL?>product.php?id=<?=$p['id']?>">
      <?php if ($p['competitor_price'] && $p['competitor_price'] > $p['price']): ?>
        <span class="product-tag deal">Save <?= fmt_price($p['competitor_price'] - $p['price']) ?></span>
      <?php endif; ?>
      <img src="<?= product_image_src($p['image']) ?>" alt="<?= htmlspecialchars($p['name']) ?>">
    </a>
    <div class="product-info">
      <span class="cat"><?= htmlspecialchars($p['category']) ?></span>
      <h3><a href="<?=BASE_URL?>product.php?id=<?=$p['id']?>"><?= htmlspecialchars($p['name']) ?></a></h3>
      <div class="price-row">
        <span class="price"><?= fmt_price($p['price']) ?></span>
        <?php if ($p['competitor_price']): ?>
          <span class="price-strike"><?= fmt_price($p['competitor_price']) ?></span>
        <?php endif; ?>
      </div>
      <div class="product-actions">
        <a class="btn btn-sm" href="<?=BASE_URL?>product.php?id=<?=$p['id']?>">View</a>
        <a class="btn btn-sm btn-ghost" href="<?=BASE_URL?>cart.php?action=add&id=<?=$p['id']?>">Add</a>
      </div>
      <label class="compare-row">
        <input type="checkbox" name="compare[]" value="<?=$p['id']?>"> Compare
      </label>
    </div>
  </article>
<?php endforeach; ?>
</div>
<div style="margin-top:24px;text-align:center;">
  <button class="btn btn-outline" type="submit">Compare selected</button>
</div>
</form>

<?php require 'footer.php'; ?>
