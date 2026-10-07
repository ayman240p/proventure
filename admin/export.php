<?php
/**
 * admin/export.php
 * Export data to CSV/Excel format
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/logger.php';

// Require admin role
requireRole('admin');

$type = $_GET['type'] ?? '';
$format = $_GET['format'] ?? 'csv';

if (empty($type) || !in_array($type, ['users', 'services', 'reviews', 'categories'])) {
    die('Invalid export type');
}

// Log the export activity
logAdminActivity($pdo, 'exported_data', $type, null, "Exported $type data as $format");

// Set headers for download
$filename = "cshub_{$type}_" . date('Y-m-d_His') . ".$format";
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=' . $filename);

// Create output stream
$output = fopen('php://output', 'w');

// Add BOM for UTF-8 (helps Excel display special characters correctly)
fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

// Export based on type
switch ($type) {
    case 'users':
        // Headers
        fputcsv($output, ['ID', 'Name', 'Email', 'Phone', 'Role', 'Created At']);

        // Data
        $stmt = $pdo->query("SELECT id, name, email, phone, role, created_at FROM users ORDER BY id");
        while ($row = $stmt->fetch(PDO::FETCH_NUM)) {
            fputcsv($output, $row);
        }
        break;

    case 'services':
        // Headers
        fputcsv($output, ['ID', 'Title', 'Description', 'Provider Name', 'Provider Email', 'Category', 'Price (RM)', 'Availability', 'Created At']);

        // Data
        $stmt = $pdo->query("
            SELECT
                s.id,
                s.title,
                s.description,
                u.name as provider_name,
                u.email as provider_email,
                c.name as category_name,
                s.price,
                IF(s.availability = 1, 'Available', 'Busy') as availability,
                s.created_at
            FROM services s
            JOIN users u ON s.user_id = u.id
            LEFT JOIN categories c ON s.category_id = c.id
            ORDER BY s.id
        ");
        while ($row = $stmt->fetch(PDO::FETCH_NUM)) {
            fputcsv($output, $row);
        }
        break;

    case 'reviews':
        // Headers
        fputcsv($output, ['ID', 'Service Title', 'Service Provider', 'Client Name', 'Client Email', 'Rating', 'Review Text', 'Created At']);

        // Data
        $stmt = $pdo->query("
            SELECT
                r.id,
                s.title as service_title,
                p.name as provider_name,
                c.name as client_name,
                c.email as client_email,
                r.rating,
                r.review_text,
                r.created_at
            FROM reviews r
            JOIN services s ON r.service_id = s.id
            JOIN users p ON s.user_id = p.id
            JOIN users c ON r.client_id = c.id
            ORDER BY r.id
        ");
        while ($row = $stmt->fetch(PDO::FETCH_NUM)) {
            fputcsv($output, $row);
        }
        break;

    case 'categories':
        // Headers
        fputcsv($output, ['ID', 'Name', 'Description', 'Service Count', 'Created At']);

        // Data
        $stmt = $pdo->query("
            SELECT
                c.id,
                c.name,
                c.description,
                COUNT(s.id) as service_count,
                c.created_at
            FROM categories c
            LEFT JOIN services s ON c.id = s.category_id
            GROUP BY c.id
            ORDER BY c.id
        ");
        while ($row = $stmt->fetch(PDO::FETCH_NUM)) {
            fputcsv($output, $row);
        }
        break;
}

fclose($output);
exit;
