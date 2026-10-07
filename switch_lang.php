<?php
session_start();
if (isset($_GET['lang']) && in_array($_GET['lang'], ['en', 'ms'])) {
    $_SESSION['lang'] = $_GET['lang'];
}
$redirect = $_SERVER['HTTP_REFERER'] ?? '/cshub/index.php';
header("Location: " . $redirect);
exit;
