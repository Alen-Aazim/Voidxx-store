<?php require 'db.php'; ?>
<?php require 'header.php'; ?>

<h1>Welcome to Voidxx — A Clothing Store</h1>
<p class="small">Premium fashion. Better prices. Only at Voidxx.</p>

<?php
$stmt = $pdo->query("SELECT * FROM products ORDER BY created_at DESC LIMIT 6");
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<h2>Featured Products</h2>

<div class="grid">
<?php foreach($products as $p): ?>
  <div class="card">
    <img src="uploads/<?= $p['image'] ?>" />
    <h3><?= htmlspecialchars($p['name']) ?></h3>
    <p class="price">₹<?= number_format($p['price']) ?></p>
    <a class="btn" href="product.php?id=<?=$p['id']?>">View</a>
  </div>
<?php endforeach; ?>
</div>

<?php require 'footer.php'; ?>