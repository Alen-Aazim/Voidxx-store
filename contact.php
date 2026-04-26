<?php require 'db.php'; ?>
<?php
require_once 'config.php';
require_once '_helpers.php';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    flash_set('success', 'Thanks for reaching out — we\'ll be in touch shortly.');
    header("Location: ".BASE_URL."contact.php"); exit;
}
?>
<?php require 'header.php'; ?>

<div class="page-intro">
  <h1>Contact</h1>
  <p class="muted">Questions about an order, a fit, or just want to say hi? We read every message.</p>
</div>

<form class="card card-narrow" method="post">
  <div class="field">
    <label class="label">Your name</label>
    <input class="input" name="name" placeholder="Jane Doe" required>
  </div>
  <div class="field">
    <label class="label">Email</label>
    <input class="input" type="email" name="email" placeholder="you@example.com" required>
  </div>
  <div class="field">
    <label class="label">Message</label>
    <textarea class="textarea" name="msg" placeholder="How can we help?" required></textarea>
  </div>
  <button class="btn btn-accent btn-block" type="submit">Send message</button>
</form>

<?php require 'footer.php'; ?>
