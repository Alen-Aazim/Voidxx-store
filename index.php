<?php require 'db.php'; ?>
<?php require 'header.php'; ?>

<section class="hero">
  <div class="hero-content">
    <div class="eyebrow">New Season — Spring '26</div>
    <h1>Premium fashion.<br>Better prices.</h1>
    <p>Wardrobe essentials and standout pieces, made from materials that last and priced honestly. Shop the new collection.</p>
    <div class="hero-cta">
      <a class="btn btn-accent" href="<?=BASE_URL?>products.php">Shop the collection</a>
      <a class="btn btn-outline" href="<?=BASE_URL?>compare.php">Compare prices</a>
    </div>
  </div>
  <div class="hero-art">V</div>
</section>

<?php
$featured = $pdo->query("SELECT * FROM products ORDER BY created_at DESC LIMIT 4")->fetchAll();
$best = $pdo->query("SELECT * FROM products WHERE competitor_price IS NOT NULL ORDER BY (competitor_price - price) DESC LIMIT 4")->fetchAll();
?>

<div class="section-head">
  <h2>New Arrivals</h2>
  <a class="muted" href="<?=BASE_URL?>products.php">View all →</a>
</div>
<div class="product-grid">
<?php foreach ($featured as $p): ?>
  <article class="product-card">
    <a class="product-thumb" href="<?=BASE_URL?>product.php?id=<?=$p['id']?>">
      <span class="product-tag">New</span>
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
    </div>
  </article>
<?php endforeach; ?>
</div>

<div class="section-head">
  <h2>Best Deals</h2>
  <a class="muted" href="<?=BASE_URL?>compare.php">Compare all →</a>
</div>
<div class="product-grid">
<?php foreach ($best as $p): $save = $p['competitor_price'] - $p['price']; ?>
  <article class="product-card">
    <a class="product-thumb" href="<?=BASE_URL?>product.php?id=<?=$p['id']?>">
      <span class="product-tag deal">Save <?= fmt_price($save) ?></span>
      <img src="<?= product_image_src($p['image']) ?>" alt="<?= htmlspecialchars($p['name']) ?>">
    </a>
    <div class="product-info">
      <span class="cat"><?= htmlspecialchars($p['category']) ?></span>
      <h3><a href="<?=BASE_URL?>product.php?id=<?=$p['id']?>"><?= htmlspecialchars($p['name']) ?></a></h3>
      <div class="price-row">
        <span class="price"><?= fmt_price($p['price']) ?></span>
        <span class="price-strike"><?= fmt_price($p['competitor_price']) ?></span>
        <span class="savings">−<?= fmt_price($save) ?></span>
      </div>
      <div class="product-actions">
        <a class="btn btn-sm" href="<?=BASE_URL?>product.php?id=<?=$p['id']?>">View</a>
        <a class="btn btn-sm btn-ghost" href="<?=BASE_URL?>cart.php?action=add&id=<?=$p['id']?>">Add</a>
      </div>
    </div>
  </article>
<?php endforeach; ?>
</div>

<?php require 'footer.php'; ?>
