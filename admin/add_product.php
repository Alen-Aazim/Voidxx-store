<?php
require '_auth.php';
require '../db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $desc = $_POST['description'] ?? '';
    $price = (float)$_POST['price'];
    $cp    = $_POST['competitor_price'] !== '' ? (float)$_POST['competitor_price'] : null;
    $cat   = trim($_POST['category']) ?: 'General';
    $stock = (int)($_POST['stock'] ?? 0);

    $newName = null;
    if (!empty($_FILES['image']['name']) && is_uploaded_file($_FILES['image']['tmp_name'])) {
        $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION) ?: 'jpg';
        $newName = uniqid('p_').'.'.strtolower($ext);
        if (!is_dir('../uploads')) mkdir('../uploads', 0775, true);
        move_uploaded_file($_FILES['image']['tmp_name'], "../uploads/".$newName);
    }

    $stmt = $pdo->prepare("INSERT INTO products (name, description, price, competitor_price, image, category, stock) VALUES (?,?,?,?,?,?,?)");
    $stmt->execute([$name, $desc, $price, $cp, $newName, $cat, $stock]);
    flash_set('success', 'Product added.');
    header("Location: products.php"); exit;
}

require '_layout.php';
?>

<div class="page-intro"><h1>Add product</h1><p class="muted">Add a new piece to the catalog.</p></div>

<form class="card" method="post" enctype="multipart/form-data" style="max-width:680px;">
  <div class="field">
    <label class="label">Name</label>
    <input class="input" name="name" required>
  </div>
  <div class="field">
    <label class="label">Description</label>
    <textarea class="textarea" name="description"></textarea>
  </div>
  <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;">
    <div class="field">
      <label class="label">Price (₹)</label>
      <input class="input" name="price" type="number" step="0.01" required>
    </div>
    <div class="field">
      <label class="label">Competitor price (₹)</label>
      <input class="input" name="competitor_price" type="number" step="0.01">
    </div>
    <div class="field">
      <label class="label">Category</label>
      <input class="input" name="category" placeholder="e.g. Outerwear">
    </div>
    <div class="field">
      <label class="label">Stock</label>
      <input class="input" name="stock" type="number" value="50">
    </div>
  </div>
  <div class="field">
    <label class="label">Image</label>
    <input class="input" type="file" name="image" accept="image/*">
  </div>
  <button class="btn btn-accent" type="submit">Save product</button>
  <a class="btn btn-ghost" href="products.php">Cancel</a>
</form>

<?php require '_layout_end.php'; ?>
