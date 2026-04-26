<?php require "_auth.php"; ?>
<?php require "../db.php"; ?>
<?php require "../header.php"; ?>

<h1>Manage Products</h1>
<a class="btn" href="add_product.php">Add New Product</a>

<?php
$stmt = $pdo->query("SELECT * FROM products ORDER BY id DESC");
$items = $stmt->fetchAll();
?>

<table class="table">
<tr><th>ID</th><th>Name</th><th>Price</th><th>Competitor</th><th>Image</th><th>Action</th></tr>

<?php foreach($items as $p): ?>
<tr>
  <td><?=$p['id']?></td>
  <td><?=$p['name']?></td>
  <td><?=$p['price']?></td>
  <td><?=$p['competitor_price']?></td>
  <td><?=$p['image']?></td>
  <td>
    <a href="edit_product.php?id=<?=$p['id']?>">Edit</a> |
    <a href="delete_product.php?id=<?=$p['id']?>">Delete</a>
  </td>
</tr>
<?php endforeach; ?>
</table>

<?php require "../footer.php"; ?>