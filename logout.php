<?php
require_once 'config.php';
require_once '_helpers.php';
unset($_SESSION['user_id']);
flash_set('info', 'You have been signed out.');
header("Location: ".BASE_URL); exit;
