<?php
/**
 * create_admin.php
 * One-time script to create the admin account with proper password hashing
 * Run this once: http://localhost/cshub/create_admin.php
 * Then delete this file for security
 */

require_once __DIR__ . '/config/database.php';

echo "<!DOCTYPE html>\n<html>\n<head>\n";
echo "<title>Create Admin Account</title>\n";
echo "<style>body { font-family: Arial, sans-serif; padding: 40px; background: #f5f5f5; max-width: 600px; margin: 0 auto; }";
echo ".success { color: green; background: #d4edda; padding: 15px; border-radius: 5px; margin: 10px 0; }";
echo ".error { color: red; background: #f8d7da; padding: 15px; border-radius: 5px; margin: 10px 0; }";
echo ".info { color: blue; background: #d1ecf1; padding: 15px; border-radius: 5px; margin: 10px 0; }";
echo "code { background: #fff; padding: 2px 6px; border-radius: 3px; }</style>\n";
echo "</head>\n<body>\n";
echo "<h1>Create Admin Account</h1>\n";

try {
    // Step 1: Modify the role column to include 'admin'
    echo "<h2>Step 1: Adding Admin Role...</h2>\n";

    try {
        $pdo->exec("ALTER TABLE users MODIFY COLUMN role ENUM('client', 'freelancer', 'admin') NOT NULL");
        echo "<p class='success'>✓ Admin role added to users table successfully!</p>\n";
    } catch (PDOException $e) {
        // If error is about already having admin in enum, that's fine
        if (strpos($e->getMessage(), 'Duplicate') !== false || strpos($e->getMessage(), '1265') !== false) {
            echo "<p class='info'>ℹ Admin role already exists in the table.</p>\n";
        } else {
            throw $e;
        }
    }

    // Step 2: Check if admin already exists
    echo "<h2>Step 2: Creating Admin Account...</h2>\n";

    $checkStmt = $pdo->prepare("SELECT id, email FROM users WHERE email = ? OR role = 'admin'");
    $checkStmt->execute(['admin@cshub.com']);
    $existing = $checkStmt->fetch();

    if ($existing) {
        echo "<p class='info'>ℹ Admin account already exists!</p>\n";
        echo "<p>Email: <code>{$existing['email']}</code></p>\n";
        echo "<p class='error'>⚠ If you forgot the password, you'll need to reset it manually in the database.</p>\n";
    } else {
        // Create admin account
        $adminPassword = 'admin123'; // Default password
        $hashedPassword = password_hash($adminPassword, PASSWORD_DEFAULT);

        $stmt = $pdo->prepare(
            "INSERT INTO users (name, email, password, phone, role)
             VALUES (?, ?, ?, ?, ?)"
        );

        $stmt->execute([
            'Administrator',
            'admin@cshub.com',
            $hashedPassword,
            '60123456789',
            'admin'
        ]);

        echo "<p class='success'>✓ Admin account created successfully!</p>\n";
        echo "<div class='info'>\n";
        echo "<h3>Admin Login Credentials:</h3>\n";
        echo "<p><strong>Email:</strong> <code>admin@cshub.com</code></p>\n";
        echo "<p><strong>Password:</strong> <code>admin123</code></p>\n";
        echo "<p><strong>⚠ Important:</strong> Change this password after your first login!</p>\n";
        echo "</div>\n";
    }

    echo "<hr>\n";
    echo "<h2>Next Steps:</h2>\n";
    echo "<ol>\n";
    echo "<li><strong>Delete this file</strong> for security: <code>C:\\xampp\\htdocs\\cshub\\create_admin.php</code></li>\n";
    echo "<li>Login at: <a href='/cshub/login.php'>http://localhost/cshub/login.php</a></li>\n";
    echo "<li>Use credentials: <code>admin@cshub.com</code> / <code>admin123</code></li>\n";
    echo "</ol>\n";

} catch (PDOException $e) {
    echo "<p class='error'>✗ Error: " . htmlspecialchars($e->getMessage()) . "</p>\n";
    echo "<p>Make sure MySQL is running and the database exists.</p>\n";
}

echo "</body>\n</html>";
?>
