<?php
/**
 * freelancer/toggle-status.php?id=X
 * Flips a service's availability between Available and Busy (Section 19).
 * Enforces ownership before updating.
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

requireRole('freelancer');

$id = (int) ($_GET['id'] ?? 0);

if ($id > 0) {
    // 1. Confirm ownership
    $stmt = $pdo->prepare("SELECT availability, title FROM services WHERE id = ? AND user_id = ?");
    $stmt->execute([$id, $_SESSION['user_id']]);
    $service = $stmt->fetch();

    if ($service) {
        $newAvailability = $service['availability'] ? 0 : 1;
        $stmt = $pdo->prepare("UPDATE services SET availability = ? WHERE id = ? AND user_id = ?");
        $stmt->execute([$newAvailability, $id, $_SESSION['user_id']]);

        $statusLabel = $newAvailability ? 'Available' : 'Busy';
        $_SESSION['flash_success'] = 'Service "' . e($service['title']) . '" is now marked as ' . $statusLabel . '.';
    } else {
        $_SESSION['flash_error'] = 'Service not found or you do not have permission to update it.';
    }
}

redirect('/cshub/freelancer/dashboard.php');
