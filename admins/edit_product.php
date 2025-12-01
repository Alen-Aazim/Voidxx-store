<?php
require "_auth.php";
require "../db.php";

$id = $_GET['id'];
$stmt = $pdo->prepare("SELECT * FROM products WHERE id=?");
$stmt->execute([$id]);
$p = $stmt->fetch();

if ($_POST) {
    $name = $_POST['name'];
    $desc = $_POST['description'];
    $price = $_POST['price'];
    $cp = $_POST['competitor_price'];
    $image = $p['image'];

    if (!empty($_FILES['image']['name'])) {
        $new = uniqid().".jpg";
        move_uploaded_file($_FILES['image']['tmp_name'], "../uploads/".$new);
        $image = $new;
    }

    $stmt = $pdo->prepare("UPDATE products SET name=?,description=?,price=?,competitor_price=?,image=? WHERE id=?");
    $stmt->execute([$name,$desc,$price,$cp,$image,$id]);

    header("Location: products.php");
    exit;
}

require "../header.php";
?>

<h1>Edit Product</h1>

<form class="card" method="post" enctype="multipart/form-data">
  <input class="input" name="name" value="<?=$p['name']?>">
  <textarea class="input" name="description"><?=$p['description']?></textarea>
  <input class="input" name="price" value="<?=$p['price']?>">
  <input class="input" name="competitor_price" value="<?=$p['competitor_price']?>">
  <img src="../uploads/<?=$p['image']?>" width="150"><br>
  <input type="file" name="image">
  <button class="btn">Update</button>
</form>

<?php require "../footer.php"; ?>