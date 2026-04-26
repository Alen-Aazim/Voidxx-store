<?php
require '_auth.php';
require '../db.php';
$items = $pdo->query("SELECT * FROM products ORDER BY id DESC")->fetchAll();
require '_layout.php';
?>

<div class="page-intro" style="display:flex;justify-content:space-between;align-items:end;flex-wrap:wrap;gap:14px;">
  <div>
    <h1>Products</h1>
    <p class="muted">Manage your full catalog.</p>
  </div>
  <a class="btn btn-accent" href="add_product.php">+ Add product</a>
</div>

<table class="table">
  <thead><tr><th></th><th>Name</th><th>Category</th><th>Price</th><th>Competitor</th><th>Stock</th><th></th></tr></thead>
  <tbody>
  <?php foreach($items as $p): ?>
    <tr>
      <td><img src="<?= product_image_src($p['image']) ?>" alt="" style="width:42px;height:52px;object-fit:cover;border-radius:6px;"></td>
      <td><?= htmlspecialchars($p['name']) ?></td>
      <td class="muted small"><?= htmlspecialchars($p['category']) ?></td>
      <td><?= fmt_price($p['price']) ?></td>
      <td class="muted"><?= $p['competitor_price'] ? fmt_price($p['competitor_price']) : '—' ?></td>
      <td><?= (int)$p['stock'] ?></td>
      <td class="right">
        <a class="btn btn-sm btn-ghost" href="edit_product.php?id=<?=$p['id']?>">Edit</a>
        <a class="btn btn-sm btn-ghost" style="color:#a8201a" href="delete_product.php?id=<?=$p['id']?>" onclick="return confirm('Delete this product?')">Delete</a>
      </td>
    </tr>
  <?php endforeach; ?>
  </tbody>
</table>

<?php require '_layout_end.php'; ?>
