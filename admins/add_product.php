<?php
require "_auth.php";
require "../db.php";

if ($_POST) {
    $name = $_POST['name'];
    $desc = $_POST['description'];
    $price = $_POST['price'];
    $cp = $_POST['competitor_price'];

    $file = $_FILES['image']['name'];
    $newName = uniqid().".jpg";
    move_uploaded_file($_FILES['image']['tmp_name'], "../uploads/".$newName);

    $stmt = $pdo->prepare("INSERT INTO products (name, description, price, competitor_price, image)
                           VALUES (?,?,?,?,?)");
    $stmt->execute([$name,$desc,$price,$cp,$newName]);

    header("Location: products.php");
    exit;
}
require "../header.php";
?>

<h1>Add Product</h1>

<form class="card" method="post" enctype="multipart/form-data">
  <input class="input" name="name" placeholder="Name">
  <textarea class="input" name="description"></textarea>
  <input class="input" name="price" placeholder="Price">
  <input class="input" name="competitor_price" placeholder="Competitor Price">
  <input type="file" name="image">
  <button class="btn">Save</button>
</form>

<?php require "../footer.php"; ?>