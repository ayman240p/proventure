<?php
/**
 * config/database.php
 * PDO connection to the cshub MySQL database (via XAMPP).
 * Suggested Implementation Decision: credentials are hardcoded for local
 * XAMPP development (root / no password). For a real deployment these
 * should be moved to environment variables or a non-web-accessible file.
 */

$DB_HOST = "127.0.0.1";
$DB_NAME = "cshub";
$DB_USER = "root";
$DB_PASS = "";
$DB_CHARSET = "utf8mb4";

$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

$ports = [3307, 3306];
$pdo = null;
$lastException = null;

foreach ($ports as $port) {
    try {
        $dsn = "mysql:host={$DB_HOST};port={$port};dbname={$DB_NAME};charset={$DB_CHARSET}";
        $pdo = new PDO($dsn, $DB_USER, $DB_PASS, $options);
        break;
    } catch (PDOException $e) {
        $lastException = $e;
    }
}

if (!$pdo) {
    die("Database connection failed: " . htmlspecialchars($lastException ? $lastException->getMessage() : "Could not connect to database"));
}
