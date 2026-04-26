<?php
require 'db.php';
require_once '_helpers.php';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $name     = trim($_POST['full_name'] ?? '');
    $pass     = $_POST['password'] ?? '';

    if (strlen($username) < 3) $error = 'Username must be at least 3 characters.';
    elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) $error = 'Please enter a valid email.';
    elseif (strlen($pass) < 6) $error = 'Password must be at least 6 characters.';
    else {
        try {
            $stmt = $pdo->prepare("INSERT INTO users (username, email, password_hash, full_name) VALUES (?, ?, ?, ?)");
            $stmt->execute([$username, $email, password_hash($pass, PASSWORD_DEFAULT), $name]);
            $_SESSION['user_id'] = $pdo->lastInsertId();
            flash_set('success', 'Welcome to Voidxx, '.htmlspecialchars($username).'!');
            header("Location: ".BASE_URL."account.php"); exit;
        } catch (PDOException $e) {
            $error = 'That username or email is already in use.';
        }
    }
}
?>
<?php require 'header.php'; ?>

<div class="card card-narrow">
  <h2>Create your account</h2>
  <p class="muted small">It takes about 30 seconds.</p>
  <?php if ($error): ?><div class="error-text"><?= htmlspecialchars($error) ?></div><?php endif; ?>
  <form method="post">
    <div class="field">
      <label class="label">Full name</label>
      <input class="input" name="full_name" placeholder="Jane Doe">
    </div>
    <div class="field">
      <label class="label">Username</label>
      <input class="input" name="username" required value="<?= htmlspecialchars($_POST['username'] ?? '') ?>">
    </div>
    <div class="field">
      <label class="label">Email</label>
      <input class="input" type="email" name="email" required value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
    </div>
    <div class="field">
      <label class="label">Password</label>
      <input class="input" type="password" name="password" required minlength="6">
    </div>
    <button class="btn btn-accent btn-block" type="submit">Create account</button>
  </form>
  <p class="muted-note">Already have an account? <a href="<?=BASE_URL?>login.php"><strong>Sign in</strong></a>.</p>
</div>

<?php require 'footer.php'; ?>
