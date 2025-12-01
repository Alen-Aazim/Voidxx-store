<?php require 'header.php'; ?>

<h1>Contact Us</h1>

<form class="card" method="post">
  <input class="input" name="name" placeholder="Your name" required>
  <input class="input" name="email" placeholder="Email" required>
  <textarea class="input" name="msg" placeholder="Message"></textarea>
  <button class="btn" type="submit">Send</button>
</form>

<?php require 'footer.php'; ?>