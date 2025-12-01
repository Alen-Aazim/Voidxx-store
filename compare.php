<?php require 'db.php'; ?>
<?php require 'header.php'; ?>

<?php
$ids = $_GET['compare'] ?? [];
if (!$ids) { echo "<p>No products selected.</p>"; require 'footer.php'; exit; }

$in = implode(',', array_map('intval',$ids));
$stmt = $pdo->query("SELECT * FROM products WHERE id IN ($in)");
$items = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<h1>Compare Products</h1>

<div class="grid">
<?php foreach($items as $p): ?>
  <div class="card">
    <img src="uploads/<?= $p['image'] ?>">
    <h3><?= htmlspecialchars($p['name']) ?></h3>
    <p class="price">₹<?= number_format($p['price']) ?></p>

    <?php if($p['competitor_price']): ?>
      <p class="small">Competitor: ₹<?= number_format($p['competitor_price']) ?></p>
      <p class="small">Savings: ₹<?= number_format($p['competitor_price'] - $p['price']) ?></p>
    <?php endif; ?>
  </div>
<?php endforeach; ?>
</div>

<?php require 'footer.php'; ?>