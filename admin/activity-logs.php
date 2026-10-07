<?php
/**
 * admin/activity-logs.php
 * View admin activity logs
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/logger.php';

// Require admin role
requireRole('admin');

// Get filters
$adminFilter = $_GET['admin'] ?? '';
$actionFilter = $_GET['action'] ?? '';
$dateFilter = $_GET['date'] ?? '';

// Build query
$query = "
    SELECT
        al.*,
        u.name as admin_name,
        u.email as admin_email
    FROM activity_logs al
    JOIN users u ON al.admin_id = u.id
    WHERE 1=1
";
$params = [];

if (!empty($adminFilter) && $adminFilter !== 'all') {
    $query .= " AND al.admin_id = ?";
    $params[] = $adminFilter;
}

if (!empty($actionFilter)) {
    $query .= " AND al.action LIKE ?";
    $params[] = "%$actionFilter%";
}

if (!empty($dateFilter)) {
    $query .= " AND DATE(al.created_at) = ?";
    $params[] = $dateFilter;
}

$query .= " ORDER BY al.created_at DESC LIMIT 100";

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$logs = $stmt->fetchAll();

// Get all admins for filter
$admins = $pdo->query("SELECT id, name FROM users WHERE role = 'admin' ORDER BY name")->fetchAll();

require_once __DIR__ . '/../includes/header.php';
?>

<div class="container py-4">
    <div class="row mb-4">
        <div class="col-md-6">
            <h1 class="h3 mb-1">Activity Logs</h1>
            <p class="text-muted">Track all admin actions on ProVenture</p>
        </div>
        <div class="col-md-6 text-end">
            <a href="/cshub/admin/dashboard.php" class="btn btn-outline-secondary">← Back to Dashboard</a>
        </div>
    </div>

    <!-- Filters -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <form method="get" action="/cshub/admin/activity-logs.php" class="row g-3">
                <div class="col-md-3">
                    <select name="admin" class="form-select">
                        <option value="all">All Admins</option>
                        <?php foreach ($admins as $admin): ?>
                            <option value="<?php echo $admin['id']; ?>" <?php echo $adminFilter == $admin['id'] ? 'selected' : ''; ?>>
                                <?php echo e($admin['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <input type="text" name="action" class="form-control" placeholder="Search action..." value="<?php echo e($actionFilter); ?>">
                </div>
                <div class="col-md-3">
                    <input type="date" name="date" class="form-control" value="<?php echo e($dateFilter); ?>">
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-primary w-100">Filter</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Activity Logs -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white border-0 py-3">
            <h5 class="mb-0">Recent Activity (Last 100 entries)</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Time</th>
                            <th>Admin</th>
                            <th>Action</th>
                            <th>Target</th>
                            <th>Details</th>
                            <th>IP Address</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($logs) > 0): ?>
                            <?php foreach ($logs as $log): ?>
                                <tr>
                                    <td class="text-muted small" style="white-space: nowrap;">
                                        <?php echo date('M d, Y H:i:s', strtotime($log['created_at'])); ?>
                                    </td>
                                    <td>
                                        <strong><?php echo e($log['admin_name']); ?></strong><br>
                                        <small class="text-muted"><?php echo e($log['admin_email']); ?></small>
                                    </td>
                                    <td>
                                        <?php
                                        $badgeClass = 'secondary';
                                        if (strpos($log['action'], 'delete') !== false) $badgeClass = 'danger';
                                        elseif (strpos($log['action'], 'create') !== false || strpos($log['action'], 'add') !== false) $badgeClass = 'success';
                                        elseif (strpos($log['action'], 'update') !== false || strpos($log['action'], 'edit') !== false) $badgeClass = 'warning';
                                        elseif (strpos($log['action'], 'export') !== false) $badgeClass = 'info';
                                        ?>
                                        <span class="badge bg-<?php echo $badgeClass; ?>">
                                            <?php echo e(str_replace('_', ' ', ucfirst($log['action']))); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge bg-info">
                                            <?php echo e(ucfirst($log['target_type'])); ?>
                                            <?php if ($log['target_id']): ?>
                                                #<?php echo $log['target_id']; ?>
                                            <?php endif; ?>
                                        </span>
                                    </td>
                                    <td class="small">
                                        <?php echo e($log['details'] ?? '-'); ?>
                                    </td>
                                    <td class="text-muted small">
                                        <?php echo e($log['ip_address'] ?? '-'); ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="text-center text-muted py-4">
                                    No activity logs found. Actions will appear here once admins perform operations.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Summary Statistics -->
    <div class="row mt-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <?php
                    $totalLogs = $pdo->query("SELECT COUNT(*) FROM activity_logs")->fetchColumn();
                    ?>
                    <h2 class="text-primary"><?php echo $totalLogs; ?></h2>
                    <p class="text-muted mb-0">Total Actions Logged</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <?php
                    $todayLogs = $pdo->query("SELECT COUNT(*) FROM activity_logs WHERE DATE(created_at) = CURDATE()")->fetchColumn();
                    ?>
                    <h2 class="text-success"><?php echo $todayLogs; ?></h2>
                    <p class="text-muted mb-0">Actions Today</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <?php
                    $deletions = $pdo->query("SELECT COUNT(*) FROM activity_logs WHERE action LIKE '%delete%'")->fetchColumn();
                    ?>
                    <h2 class="text-danger"><?php echo $deletions; ?></h2>
                    <p class="text-muted mb-0">Deletions</p>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
