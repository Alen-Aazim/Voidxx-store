<?php
require_once "../db.php";
require_once "../_helpers.php";
$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $u = $_POST['username'] ?? '';
    $p = $_POST['password'] ?? '';
    $stmt = $pdo->prepare("SELECT * FROM admins WHERE username=?");
    $stmt->execute([$u]);
    $admin = $stmt->fetch();
    if ($admin && password_verify($p, $admin['password_hash'])) {
        $_SESSION['admin_id'] = $admin['id'];
        flash_set('success', 'Signed in as admin.');
        header("Location: dashboard.php"); exit;
    }
    $error = "Invalid login";
}
require '../header.php';
?>

<div class="card card-narrow" style="background:linear-gradient(135deg,#0e0e10,#2a2a2e);color:#fff;border-color:#222;">
  <h2 style="color:#fff;">Admin Portal</h2>
  <p class="small" style="color:#bbb;">Restricted area. Authorised personnel only.</p>
  <?php if ($error): ?><div class="error-text"><?= htmlspecialchars($error) ?></div><?php endif; ?>
  <form method="post">
    <div class="field">
      <label class="label" style="color:#ddd;">Username</label>
      <input class="input" name="username" required autofocus>
    </div>
    <div class="field">
      <label class="label" style="color:#ddd;">Password</label>
      <input class="input" type="password" name="password" required>
    </div>
    <button class="btn btn-accent btn-block" type="submit">Sign in</button>
  </form>
  <p class="small" style="color:#999;margin-top:12px;">Default: <code>Voidxx</code> / <code>admin123</code></p>
</div>

<?php require '../footer.php'; ?>
