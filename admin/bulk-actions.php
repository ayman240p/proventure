<?php
/**
 * admin/bulk-actions.php
 * Handle bulk operations for users and services
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/logger.php';

// Require admin role
requireRole('admin');

$action = $_POST['action'] ?? '';
$type = $_POST['type'] ?? '';
$ids = $_POST['ids'] ?? [];

if (empty($action) || empty($type) || empty($ids)) {
    $_SESSION['bulk_error'] = 'Invalid bulk action parameters.';
    redirect('/cshub/admin/' . $type . '.php');
}

// Convert IDs to integers
$ids = array_map('intval', $ids);
$placeholders = implode(',', array_fill(0, count($ids), '?'));

try {
    switch ($type) {
        case 'users':
            if ($action === 'delete') {
                // Prevent deleting yourself
                if (in_array($_SESSION['user_id'], $ids)) {
                    $_SESSION['bulk_error'] = 'You cannot delete your own account.';
                    redirect('/cshub/admin/users.php');
                }

                // Get user info before deleting for logging
                $stmt = $pdo->prepare("SELECT GROUP_CONCAT(name) as names FROM users WHERE id IN ($placeholders)");
                $stmt->execute($ids);
                $userNames = $stmt->fetch()['names'];

                // Delete users
                $stmt = $pdo->prepare("DELETE FROM users WHERE id IN ($placeholders) AND role != 'admin'");
                $stmt->execute($ids);
                $count = $stmt->rowCount();

                // Log the bulk deletion
                logAdminActivity($pdo, 'bulk_deleted_users', 'user', null, "Bulk deleted $count users: $userNames");

                $_SESSION['bulk_success'] = "$count user(s) deleted successfully.";
            }
            redirect('/cshub/admin/users.php');
            break;

        case 'services':
            if ($action === 'delete') {
                // Get service titles before deleting
                $stmt = $pdo->prepare("SELECT GROUP_CONCAT(title) as titles FROM services WHERE id IN ($placeholders)");
                $stmt->execute($ids);
                $serviceTitles = $stmt->fetch()['titles'];

                // Delete services
                $stmt = $pdo->prepare("DELETE FROM services WHERE id IN ($placeholders)");
                $stmt->execute($ids);
                $count = $stmt->rowCount();

                // Log the bulk deletion
                logAdminActivity($pdo, 'bulk_deleted_services', 'service', null, "Bulk deleted $count services");

                $_SESSION['bulk_success'] = "$count service(s) deleted successfully.";
            } elseif ($action === 'approve') {
                // Set all selected services to approved
                $stmt = $pdo->prepare("UPDATE services SET status = 'approved' WHERE id IN ($placeholders)");
                $stmt->execute($ids);
                $count = $stmt->rowCount();

                logAdminActivity($pdo, 'bulk_approved_services', 'service', null, "Bulk approved $count services");
                $_SESSION['bulk_success'] = "$count service(s) approved successfully and are now live.";
            } elseif ($action === 'reject') {
                // Set all selected services to rejected
                $stmt = $pdo->prepare("UPDATE services SET status = 'rejected' WHERE id IN ($placeholders)");
                $stmt->execute($ids);
                $count = $stmt->rowCount();

                logAdminActivity($pdo, 'bulk_rejected_services', 'service', null, "Bulk rejected $count services");
                $_SESSION['bulk_success'] = "$count service(s) rejected and hidden from public view.";
            }
            redirect('/cshub/admin/services.php');
            break;

        case 'reviews':
            if ($action === 'delete') {
                // Delete reviews
                $stmt = $pdo->prepare("DELETE FROM reviews WHERE id IN ($placeholders)");
                $stmt->execute($ids);
                $count = $stmt->rowCount();

                // Log the bulk deletion
                logAdminActivity($pdo, 'bulk_deleted_reviews', 'review', null, "Bulk deleted $count reviews");

                $_SESSION['bulk_success'] = "$count review(s) deleted successfully.";
            }
            redirect('/cshub/admin/reviews.php');
            break;

        default:
            $_SESSION['bulk_error'] = 'Invalid bulk action type.';
            redirect('/cshub/admin/dashboard.php');
    }
} catch (PDOException $e) {
    $_SESSION['bulk_error'] = 'Bulk action failed. Some items may have dependencies.';
    redirect('/cshub/admin/' . $type . '.php');
}
