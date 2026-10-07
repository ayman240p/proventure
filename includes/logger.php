<?php
/**
 * includes/logger.php
 * Activity logging functions for admin actions
 */

/**
 * Log an admin action to the activity_logs table
 *
 * @param PDO $pdo Database connection
 * @param string $action Action performed (e.g., 'deleted_user', 'updated_service')
 * @param string $targetType Type of target (user, service, category, review)
 * @param int|null $targetId ID of the affected item
 * @param string|null $details Additional details about the action
 */
function logAdminActivity(PDO $pdo, string $action, string $targetType, ?int $targetId = null, ?string $details = null): void
{
    // Only log if user is logged in and is an admin
    if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
        return;
    }

    try {
        $ipAddress = $_SERVER['REMOTE_ADDR'] ?? null;

        $stmt = $pdo->prepare(
            "INSERT INTO activity_logs (admin_id, action, target_type, target_id, details, ip_address)
             VALUES (?, ?, ?, ?, ?, ?)"
        );

        $stmt->execute([
            $_SESSION['user_id'],
            $action,
            $targetType,
            $targetId,
            $details,
            $ipAddress
        ]);
    } catch (PDOException $e) {
        // Silent fail - don't break the main operation if logging fails
        // In production, you might want to log this error somewhere
        error_log("Failed to log admin activity: " . $e->getMessage());
    }
}

/**
 * Get recent admin activity logs
 *
 * @param PDO $pdo Database connection
 * @param int $limit Number of logs to retrieve
 * @return array Array of activity log records
 */
function getRecentActivityLogs(PDO $pdo, int $limit = 50): array
{
    $stmt = $pdo->prepare("
        SELECT
            al.*,
            u.name as admin_name,
            u.email as admin_email
        FROM activity_logs al
        JOIN users u ON al.admin_id = u.id
        ORDER BY al.created_at DESC
        LIMIT ?
    ");

    $stmt->execute([$limit]);
    return $stmt->fetchAll();
}

/**
 * Get activity logs for a specific admin
 *
 * @param PDO $pdo Database connection
 * @param int $adminId Admin user ID
 * @param int $limit Number of logs to retrieve
 * @return array Array of activity log records
 */
function getAdminActivityLogs(PDO $pdo, int $adminId, int $limit = 50): array
{
    $stmt = $pdo->prepare("
        SELECT *
        FROM activity_logs
        WHERE admin_id = ?
        ORDER BY created_at DESC
        LIMIT ?
    ");

    $stmt->execute([$adminId, $limit]);
    return $stmt->fetchAll();
}
