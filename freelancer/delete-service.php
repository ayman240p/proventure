<?php
/**
 * freelancer/delete-service.php?id=X
 * DELETE step of Service CRUD (Objective 4 / Section 14).
 * Enforces ownership before deletion and cleans up portfolio image files.
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

requireRole('freelancer');

$id = (int) ($_GET['id'] ?? 0);

if ($id > 0) {
    // 1. Confirm ownership and retrieve portfolio image
    $stmt = $pdo->prepare("SELECT portfolio_image, title FROM services WHERE id = ? AND user_id = ?");
    $stmt->execute([$id, $_SESSION['user_id']]);
    $service = $stmt->fetch();

    if ($service) {
        // 2. Remove portfolio image from disk if present
        if (!empty($service['portfolio_image'])) {
            $imagePath = __DIR__ . '/../uploads/portfolio/' . $service['portfolio_image'];
            if (file_exists($imagePath)) {
                @unlink($imagePath);
            }
        }

        // 3. Delete from database
        $stmt = $pdo->prepare("DELETE FROM services WHERE id = ? AND user_id = ?");
        $stmt->execute([$id, $_SESSION['user_id']]);

        $_SESSION['flash_success'] = 'Service "' . e($service['title']) . '" deleted successfully.';
    } else {
        $_SESSION['flash_error'] = 'Service not found or you do not have permission to delete it.';
    }
}

redirect('/cshub/freelancer/dashboard.php');
