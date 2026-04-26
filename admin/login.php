<?php
require_once "../db.php";
session_start();

$error = "";

if ($_POST) {
    $u = $_POST['username'];
    $p = $_POST['password'];

    $stmt = $pdo->prepare("SELECT * FROM admins WHERE username=?");
    $stmt->execute([$u]);
    $admin = $stmt->fetch();

    if ($admin && password_verify($p, $admin['password_hash'])) {
        $_SESSION['admin_id'] = $admin['id'];
        header("Location: dashboard.php");
        exit;
    } else {
        $error = "Invalid login";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
<title>Voidxx Admin Login</title>
<link rel="stylesheet" href="../styles.css">
</head>
<body>
<div class="container" style="max-width:400px;margin-top:40px">
  <div class="card">
    <h2>Voidxx Admin</h2>
    <p style="color:red"><?= $error ?></p>
    <form method="post">
      <input class="input" name="username" placeholder="Username">
      <input class="input" name="password" type="password" placeholder="Password">
      <button class="btn">Login</button>
    </form>
  </div>
</div>
</body>
</html>