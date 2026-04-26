<?php
require_once "../config.php";
require_once "../_helpers.php";
unset($_SESSION['admin_id']);
flash_set('info', 'Admin session ended.');
header("Location: login.php"); exit;
