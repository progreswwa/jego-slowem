<?php
session_start();

// Destroy session
$_SESSION = [];
session_destroy();

// Redirect to login
header('Location: /admin/index.php');
exit;
