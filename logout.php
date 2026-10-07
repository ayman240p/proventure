<?php
/**
 * logout.php
 * Destroys the current session and returns the user to the homepage.
 */

require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';

$_SESSION = [];
session_destroy();

redirect('/cshub/index.php');
