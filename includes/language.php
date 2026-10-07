<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (isset($_GET['lang']) && in_array($_GET['lang'], ['en', 'ms'])) {
    $_SESSION['lang'] = $_GET['lang'];
}

$current_lang = $_SESSION['lang'] ?? 'en';

$lang_file = __DIR__ . '/../lang/' . $current_lang . '.php';
if (file_exists($lang_file)) {
    global $lang;
    $lang = require $lang_file;
} else {
    global $lang;
    $lang = [];
}

if (!function_exists('__')) {
    function __($key) {
        global $lang;
        return $lang[$key] ?? $key;
    }
}
