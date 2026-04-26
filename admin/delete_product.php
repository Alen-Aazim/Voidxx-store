<?php
require '_auth.php';
require '../db.php';
require_once '../_helpers.php';
$id = intval($_GET['id'] ?? 0);
$stmt = $pdo->prepare("DELETE FROM products WHERE id=?");
$stmt->execute([$id]);
flash_set('info', 'Product deleted.');
header("Location: products.php"); exit;
