<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/_helpers.php';
$isAdminArea = strpos($_SERVER['REQUEST_URI'], '/admin/') === 0;
$cssBase = $isAdminArea ? '../' : '';
$user = isset($pdo) ? current_user($pdo) : null;
$flash = flash_pop();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Voidxx — Premium Clothing Store</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Playfair+Display:wght@500;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="<?= $cssBase ?>styles.css">
<meta name="viewport" content="width=device-width, initial-scale=1">
</head>
<body>

<header class="site-header">
  <div class="container header-inner">
    <a class="logo" href="<?=BASE_URL?>">VOIDXX<span class="logo-dot">.</span></a>
    <nav class="nav">
      <a href="<?=BASE_URL?>">Home</a>
      <a href="<?=BASE_URL?>products.php">Shop</a>
      <a href="<?=BASE_URL?>compare.php">Compare</a>
      <a href="<?=BASE_URL?>contact.php">Contact</a>
    </nav>
    <div class="nav-actions">
      <?php if ($user): ?>
        <a class="nav-link" href="<?=BASE_URL?>orders.php">My Orders</a>
        <a class="nav-link" href="<?=BASE_URL?>account.php">Hi, <?= htmlspecialchars(explode(' ', $user['full_name'] ?? $user['username'])[0]) ?></a>
        <a class="nav-link muted" href="<?=BASE_URL?>logout.php">Logout</a>
      <?php else: ?>
        <a class="nav-link" href="<?=BASE_URL?>login.php">Sign in</a>
        <a class="nav-link muted" href="<?=BASE_URL?>admin/login.php">Admin</a>
      <?php endif; ?>
      <a class="cart-pill" href="<?=BASE_URL?>cart.php">
        <span class="cart-icon">🛍</span>
        <span><?= cart_count() ?></span>
      </a>
    </div>
  </div>
</header>

<?php if ($flash): ?>
<div class="flash flash-<?= htmlspecialchars($flash['type']) ?>">
  <div class="container"><?= htmlspecialchars($flash['msg']) ?></div>
</div>
<?php endif; ?>

<main class="container main-content">
