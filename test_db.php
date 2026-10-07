<?php
/**
 * test_db.php
 * Quick database connection and setup verification script
 */

require_once __DIR__ . '/config/database.php';

echo "<!DOCTYPE html>\n<html>\n<head>\n";
echo "<title>ProVenture Database Test</title>\n";
echo "<style>body { font-family: Arial, sans-serif; padding: 20px; background: #f5f5f5; }";
echo ".success { color: green; } .error { color: red; } .info { color: blue; }";
echo "pre { background: #fff; padding: 10px; border-radius: 5px; }</style>\n";
echo "</head>\n<body>\n";
echo "<h1>ProVenture Database Connection Test</h1>\n";

try {
    echo "<p class='success'>✓ Database connection successful!</p>\n";

    // Check if tables exist
    $tables = ['users', 'categories', 'services', 'reviews'];
    echo "<h2>Table Status:</h2>\n";

    foreach ($tables as $table) {
        $stmt = $pdo->query("SHOW TABLES LIKE '$table'");
        if ($stmt->rowCount() > 0) {
            echo "<p class='success'>✓ Table '$table' exists</p>\n";

            // Count records
            $countStmt = $pdo->query("SELECT COUNT(*) as count FROM $table");
            $count = $countStmt->fetch()['count'];
            echo "<p class='info'>   → Records: $count</p>\n";
        } else {
            echo "<p class='error'>✗ Table '$table' does not exist</p>\n";
        }
    }

    // Check categories
    echo "<h2>Categories:</h2>\n";
    $stmt = $pdo->query("SELECT id, name FROM categories ORDER BY id");
    $categories = $stmt->fetchAll();
    if (count($categories) > 0) {
        echo "<pre>";
        foreach ($categories as $cat) {
            echo "{$cat['id']}. {$cat['name']}\n";
        }
        echo "</pre>";
    } else {
        echo "<p class='error'>No categories found. Run the schema.sql file in phpMyAdmin.</p>\n";
    }

    // Check users
    echo "<h2>Users:</h2>\n";
    $stmt = $pdo->query("SELECT id, name, email, role FROM users ORDER BY id");
    $users = $stmt->fetchAll();
    if (count($users) > 0) {
        echo "<pre>";
        foreach ($users as $user) {
            echo "{$user['id']}. {$user['name']} ({$user['email']}) - {$user['role']}\n";
        }
        echo "</pre>";
    } else {
        echo "<p class='info'>No users yet. Try registering through the registration page.</p>\n";
    }

    echo "<hr>\n";
    echo "<p><a href='/cshub/register.php'>→ Go to Registration</a></p>\n";
    echo "<p><a href='/cshub/login.php'>→ Go to Login</a></p>\n";
    echo "<p><a href='/cshub/index.php'>→ Go to Homepage</a></p>\n";

} catch (PDOException $e) {
    echo "<p class='error'>✗ Database error: " . htmlspecialchars($e->getMessage()) . "</p>\n";
    echo "<h3>Troubleshooting:</h3>\n";
    echo "<ol>\n";
    echo "<li>Make sure XAMPP is running (Apache + MySQL)</li>\n";
    echo "<li>Open phpMyAdmin: <a href='http://localhost/phpmyadmin' target='_blank'>http://localhost/phpmyadmin</a></li>\n";
    echo "<li>Import the database schema from: <code>C:\\xampp\\htdocs\\cshub\\database\\schema.sql</code></li>\n";
    echo "</ol>\n";
}

echo "</body>\n</html>";
?>
