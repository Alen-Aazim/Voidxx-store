<?php
if (session_status() === PHP_SESSION_NONE) session_start();
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Voidxx — A Clothing Store</title>
<link rel="stylesheet" href="<?= (strpos($_SERVER['REQUEST_URI'], '/admin/')===0 ? '../' : '') ?>styles.css">
<meta name="viewport" content="width=device-width, initial-scale=1">
</head>
<body>

<header class="site-header">
  <div class="container">
    <a class="logo" href="<?=BASE_URL?>">Voidxx</a>
    <nav class="nav">
      <a href="<?=BASE_URL?>">Home</a>
      <a href="<?=BASE_URL?>products.php">Products</a>
      <a href="<?=BASE_URL?>compare.php">Compare</a>
      <a href="<?=BASE_URL?>contact.php">Contact</a>
      <a href="<?=BASE_URL?>cart.php">Cart (<?= isset($_SESSION['cart']) ? array_sum($_SESSION['cart']) : 0 ?>)</a>
      <a href="<?=BASE_URL?>admin/login.php">Admin</a>
    </nav>
  </div>
</header>

<main class="container">