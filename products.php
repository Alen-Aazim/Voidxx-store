<?php require 'db.php'; ?>
<?php require 'header.php'; ?>

<h1>Products</h1>

<?php
$stmt = $pdo->query("SELECT * FROM products ORDER BY id DESC");
$items = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="grid">
<?php foreach($items as $p): ?>
  <div class="card">
    <img src="uploads/<?= $p['image'] ?>">
    <h3><?= htmlspecialchars($p['name']) ?></h3>
    <p class="price">₹<?= number_format($p['price']) ?></p>

    <a class="btn" href="product.php?id=<?=$p['id']?>">View</a>
    <a class="btn" href="cart.php?action=add&id=<?=$p['id']?>">Add to Cart</a>

    <form method="get" action="compare.php">
      <input type="checkbox" name="compare[]" value="<?=$p['id']?>"> Compare
    </form>
  </div>
<?php endforeach; ?>
</div>

<?php require 'footer.php'; ?>