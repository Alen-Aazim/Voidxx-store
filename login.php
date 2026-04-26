<?php
require 'db.php';
require_once '_helpers.php';

$error = '';
$next = $_GET['next'] ?? 'account.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $u = trim($_POST['username'] ?? '');
    $p = $_POST['password'] ?? '';
    $stmt = $pdo->prepare("SELECT * FROM users WHERE username=? OR email=?");
    $stmt->execute([$u, $u]);
    $user = $stmt->fetch();
    if ($user && password_verify($p, $user['password_hash'])) {
        $_SESSION['user_id'] = $user['id'];
        flash_set('success', 'Welcome back, '.htmlspecialchars($user['username']).'.');
        header("Location: ".BASE_URL.$next); exit;
    } else {
        $error = 'Invalid username/email or password.';
    }
}
?>
<?php require 'header.php'; ?>

<div class="auth-split">
  <div class="card">
    <h2>Sign in</h2>
    <p class="muted small">Welcome back. Sign in to view your orders and check out faster.</p>
    <?php if ($error): ?><div class="error-text"><?= $error ?></div><?php endif; ?>
    <form method="post">
      <div class="field">
        <label class="label">Username or email</label>
        <input class="input" name="username" required autofocus>
      </div>
      <div class="field">
        <label class="label">Password</label>
        <input class="input" type="password" name="password" required>
      </div>
      <button class="btn btn-accent btn-block" type="submit">Sign in</button>
    </form>
    <p class="muted-note">Don't have an account? <a href="<?=BASE_URL?>register.php"><strong>Create one</strong></a>.</p>
    <p class="muted-note small">Demo: <code>demo</code> / <code>demo1234</code></p>
  </div>

  <div class="card" style="background:linear-gradient(135deg,#111,#2a2a2e);color:#fff;border-color:#222;">
    <h2 style="color:#fff;">Members get more.</h2>
    <ul style="padding-left:18px;line-height:2;color:#ddd;">
      <li>Order history at your fingertips</li>
      <li>Faster checkout</li>
      <li>Early access to drops</li>
      <li>Personalised recommendations</li>
    </ul>
    <a class="btn btn-outline" style="color:#fff;border-color:rgba(255,255,255,.4);" href="<?=BASE_URL?>register.php">Create account →</a>
  </div>
</div>

<?php require 'footer.php'; ?>
