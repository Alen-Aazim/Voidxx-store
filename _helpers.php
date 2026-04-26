<?php
if (session_status() === PHP_SESSION_NONE) session_start();

function current_user($pdo) {
    if (!isset($_SESSION['user_id'])) return null;
    $stmt = $pdo->prepare("SELECT id, username, email, full_name, created_at FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    return $stmt->fetch() ?: null;
}

function require_user_login() {
    if (!isset($_SESSION['user_id'])) {
        header("Location: " . BASE_URL . "login.php");
        exit;
    }
}

function flash_set($type, $msg) {
    $_SESSION['flash'] = ['type' => $type, 'msg' => $msg];
}

function flash_pop() {
    if (!isset($_SESSION['flash'])) return null;
    $f = $_SESSION['flash'];
    unset($_SESSION['flash']);
    return $f;
}

function product_image_src($image) {
    $base = rtrim(BASE_URL, '/');
    if (!$image) return $base . "/assets/placeholder.svg";
    $path = __DIR__ . "/uploads/" . $image;
    if (is_file($path)) return $base . "/uploads/" . rawurlencode($image);
    return $base . "/assets/placeholder.svg";
}

function cart_count() {
    if (!isset($_SESSION['cart'])) return 0;
    return array_sum($_SESSION['cart']);
}

function fmt_price($amount) {
    return '₹' . number_format((float)$amount);
}
?>
