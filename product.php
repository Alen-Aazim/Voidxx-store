<?php require 'db.php'; ?>
<?php require 'header.php'; ?>

<?php
$id = intval($_GET['id']);
$stmt = $pdo->prepare("SELECT * FROM products WHERE id=?");
$stmt->execute([$id]);
$p = $stmt->fetch(PDO::FETCH_ASSOC);
?>

<div class="card" style="display:flex;gap:20px">
  <img src="uploads/<?=$p['image']?>" style="width:300px;border-radius:10px">

  <div>
    <h2><?= htmlspecialchars($p['name']) ?></h2>
    <p class="price">₹<?= number_format($p['price']) ?></p>

    <?php if($p['competitor_price']): ?>
      <p class="small">Competitor price: ₹<?= number_format($p['competitor_price']) ?></p>
      <p class="small">
        Savings: ₹<?= number_format($p['competitor_price'] - $p['price']) ?>
      </p>
    <?php endif; ?>

    <a class="btn" href="cart.php?action=add&id=<?=$p['id']?>">Add to Cart</a>
  </div>
</div>

<?php require 'footer.php'; ?>