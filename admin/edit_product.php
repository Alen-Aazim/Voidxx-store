<?php
require '_auth.php';
require '../db.php';

$id = intval($_GET['id'] ?? 0);
$stmt = $pdo->prepare("SELECT * FROM products WHERE id=?");
$stmt->execute([$id]);
$p = $stmt->fetch();
if (!$p) { flash_set('error','Product not found.'); header("Location: products.php"); exit; }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $desc = $_POST['description'] ?? '';
    $price = (float)$_POST['price'];
    $cp    = $_POST['competitor_price'] !== '' ? (float)$_POST['competitor_price'] : null;
    $cat   = trim($_POST['category']) ?: 'General';
    $stock = (int)($_POST['stock'] ?? 0);
    $image = $p['image'];

    if (!empty($_FILES['image']['name']) && is_uploaded_file($_FILES['image']['tmp_name'])) {
        $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION) ?: 'jpg';
        $image = uniqid('p_').'.'.strtolower($ext);
        if (!is_dir('../uploads')) mkdir('../uploads', 0775, true);
        move_uploaded_file($_FILES['image']['tmp_name'], "../uploads/".$image);
    }

    $stmt = $pdo->prepare("UPDATE products SET name=?, description=?, price=?, competitor_price=?, image=?, category=?, stock=? WHERE id=?");
    $stmt->execute([$name,$desc,$price,$cp,$image,$cat,$stock,$id]);
    flash_set('success', 'Product updated.');
    header("Location: products.php"); exit;
}

require '_layout.php';
?>

<div class="page-intro"><h1>Edit product</h1></div>

<form class="card" method="post" enctype="multipart/form-data" style="max-width:680px;">
  <div class="field">
    <label class="label">Name</label>
    <input class="input" name="name" value="<?= htmlspecialchars($p['name']) ?>" required>
  </div>
  <div class="field">
    <label class="label">Description</label>
    <textarea class="textarea" name="description"><?= htmlspecialchars($p['description']) ?></textarea>
  </div>
  <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;">
    <div class="field">
      <label class="label">Price (₹)</label>
      <input class="input" name="price" type="number" step="0.01" value="<?= htmlspecialchars($p['price']) ?>" required>
    </div>
    <div class="field">
      <label class="label">Competitor price (₹)</label>
      <input class="input" name="competitor_price" type="number" step="0.01" value="<?= htmlspecialchars($p['competitor_price']) ?>">
    </div>
    <div class="field">
      <label class="label">Category</label>
      <input class="input" name="category" value="<?= htmlspecialchars($p['category']) ?>">
    </div>
    <div class="field">
      <label class="label">Stock</label>
      <input class="input" name="stock" type="number" value="<?= (int)$p['stock'] ?>">
    </div>
  </div>
  <div class="field">
    <label class="label">Image</label>
    <div style="display:flex;gap:14px;align-items:center;">
      <img src="<?= product_image_src($p['image']) ?>" style="width:90px;height:110px;object-fit:cover;border-radius:8px;border:1px solid var(--line);">
      <input class="input" type="file" name="image" accept="image/*">
    </div>
  </div>
  <button class="btn btn-accent" type="submit">Save changes</button>
  <a class="btn btn-ghost" href="products.php">Cancel</a>
</form>

<?php require '_layout_end.php'; ?>
