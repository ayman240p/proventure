<?php
/**
 * admin/reports.php
 * Community Moderation & Reports Management Dashboard
 * Allows administrators to review listings flagged by users for spam or malicious content.
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/logger.php';

// Require admin role
requireRole('admin');

$success = '';
$error = '';

// 1. Handle "Dismiss Report" action
if (isset($_GET['action']) && $_GET['action'] === 'dismiss' && isset($_GET['id']) && is_numeric($_GET['id'])) {
    $reportId = (int)$_GET['id'];
    try {
        $stmt = $pdo->prepare("
            SELECT r.*, s.title as service_title 
            FROM service_reports r 
            LEFT JOIN services s ON r.service_id = s.id 
            WHERE r.id = ?
        ");
        $stmt->execute([$reportId]);
        $report = $stmt->fetch();

        if ($report) {
            $update = $pdo->prepare("UPDATE service_reports SET status = 'dismissed' WHERE id = ?");
            $update->execute([$reportId]);

            logAdminActivity($pdo, 'dismissed_report', 'service', (int)$report['service_id'], "Dismissed report #{$reportId} on '{$report['service_title']}'");
            $success = "Report #{$reportId} dismissed. The service remains active.";
        }
    } catch (PDOException $e) {
        $error = 'Failed to dismiss report.';
    }
}

// 2. Handle "Suspend/Reject Service" action (Action Taken)
if (isset($_GET['action']) && $_GET['action'] === 'suspend' && isset($_GET['id']) && is_numeric($_GET['id'])) {
    $reportId = (int)$_GET['id'];
    try {
        $stmt = $pdo->prepare("
            SELECT r.*, s.title as service_title 
            FROM service_reports r 
            LEFT JOIN services s ON r.service_id = s.id 
            WHERE r.id = ?
        ");
        $stmt->execute([$reportId]);
        $report = $stmt->fetch();

        if ($report && $report['service_id']) {
            // Set service status to rejected
            $rejectService = $pdo->prepare("UPDATE services SET status = 'rejected' WHERE id = ?");
            $rejectService->execute([(int)$report['service_id']]);

            // Update all pending reports for this service to action_taken
            $updateReports = $pdo->prepare("UPDATE service_reports SET status = 'action_taken' WHERE service_id = ?");
            $updateReports->execute([(int)$report['service_id']]);

            logAdminActivity($pdo, 'rejected_service_report', 'service', (int)$report['service_id'], "Suspended reported service '{$report['service_title']}' (Reason: {$report['reason']})");
            $success = "Service '{$report['service_title']}' has been suspended/rejected and hidden from public search!";
        }
    } catch (PDOException $e) {
        $error = 'Failed to suspend service.';
    }
}

// 3. Handle Filters
$statusFilter = $_GET['status'] ?? 'pending';
$reasonFilter = $_GET['reason'] ?? 'all';

$where = [];
$params = [];

if ($statusFilter !== 'all') {
    $where[] = "r.status = ?";
    $params[] = $statusFilter;
}

if ($reasonFilter !== 'all') {
    $where[] = "r.reason = ?";
    $params[] = $reasonFilter;
}

$whereClause = count($where) > 0 ? "WHERE " . implode(" AND ", $where) : "";

// 4. Fetch Stats
$statsQuery = "
    SELECT 
        COUNT(*) as total_reports,
        SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending_reports,
        SUM(CASE WHEN status = 'action_taken' THEN 1 ELSE 0 END) as action_reports,
        SUM(CASE WHEN status = 'dismissed' THEN 1 ELSE 0 END) as dismissed_reports
    FROM service_reports
";
$stats = $pdo->query($statsQuery)->fetch();

// 5. Fetch Reports list with service and user details
$reportsQuery = "
    SELECT 
        r.*,
        s.title as service_title,
        s.status as service_status,
        s.price as service_price,
        p.name as provider_name,
        p.email as provider_email,
        u.role as reporter_role
    FROM service_reports r
    LEFT JOIN services s ON r.service_id = s.id
    LEFT JOIN users p ON s.user_id = p.id
    LEFT JOIN users u ON r.reporter_id = u.id
    $whereClause
    ORDER BY (r.status = 'pending') DESC, r.created_at DESC
";
$stmt = $pdo->prepare($reportsQuery);
$stmt->execute($params);
$reports = $stmt->fetchAll();

require_once __DIR__ . '/../includes/header.php';
?>

<div class="container-fluid py-4">
    <!-- Breadcrumb & Header -->
    <div class="row mb-4 align-items-center">
        <div class="col-md-6">
            <h1 class="h3 fw-bold mb-1">
                <i class="bi bi-shield-exclamation text-danger me-2"></i>Community Moderation &amp; Reports
            </h1>
            <p class="text-muted small mb-0">
                Investigate and take action on live listings reported by the community for spam or malicious activity.
            </p>
        </div>
        <div class="col-md-6 text-md-end mt-3 mt-md-0 d-flex gap-2 justify-content-md-end">
            <a href="/cshub/admin/services.php" class="btn btn-outline-secondary rounded-pill btn-sm px-3">
                <i class="bi bi-grid me-1"></i> Manage Services
            </a>
            <a href="/cshub/admin/settings.php" class="btn btn-outline-primary rounded-pill btn-sm px-3">
                <i class="bi bi-sliders me-1"></i> Moderation Settings
            </a>
        </div>
    </div>

    <!-- Feedback Alerts -->
    <?php if ($success): ?>
        <div class="alert alert-success alert-dismissible fade show rounded-4 border-0 shadow-sm mb-4">
            <i class="bi bi-check-circle-fill me-2"></i><?php echo e($success); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div class="alert alert-danger alert-dismissible fade show rounded-4 border-0 shadow-sm mb-4">
            <i class="bi bi-exclamation-triangle-fill me-2"></i><?php echo e($error); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- Summary Statistics Cards -->
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-4 p-3 <?php echo ($stats['pending_reports'] > 0) ? 'bg-danger bg-opacity-10 border-danger border' : ''; ?>">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <span class="text-muted small d-block">Pending Investigation</span>
                        <h3 class="fw-bold mb-0 text-danger"><?php echo (int)$stats['pending_reports']; ?></h3>
                    </div>
                    <div class="rounded-circle bg-danger text-white p-3 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                        <i class="bi bi-clock-history fs-5"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-4 p-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <span class="text-muted small d-block">Suspended / Action Taken</span>
                        <h3 class="fw-bold mb-0 text-warning"><?php echo (int)$stats['action_reports']; ?></h3>
                    </div>
                    <div class="rounded-circle bg-warning text-dark p-3 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                        <i class="bi bi-shield-slash fs-5"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-4 p-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <span class="text-muted small d-block">Dismissed (False Alarm)</span>
                        <h3 class="fw-bold mb-0 text-secondary"><?php echo (int)$stats['dismissed_reports']; ?></h3>
                    </div>
                    <div class="rounded-circle bg-secondary bg-opacity-10 text-secondary p-3 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                        <i class="bi bi-check2-all fs-5"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-4 p-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <span class="text-muted small d-block">Total Reports Logged</span>
                        <h3 class="fw-bold mb-0 text-primary"><?php echo (int)$stats['total_reports']; ?></h3>
                    </div>
                    <div class="rounded-circle bg-primary bg-opacity-10 text-primary p-3 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                        <i class="bi bi-flag-fill fs-5"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter Bar Card -->
    <div class="card border-0 shadow-sm rounded-4 p-3 mb-4">
        <form method="get" action="/cshub/admin/reports.php" class="row g-2 align-items-center">
            <div class="col-md-4">
                <label class="form-label small text-muted mb-1">Status Filter</label>
                <select name="status" class="form-select form-select-sm rounded-pill" onchange="this.form.submit()">
                    <option value="pending" <?php echo $statusFilter === 'pending' ? 'selected' : ''; ?>>⏳ Pending Review Only (<?php echo (int)$stats['pending_reports']; ?>)</option>
                    <option value="action_taken" <?php echo $statusFilter === 'action_taken' ? 'selected' : ''; ?>>🛑 Action Taken / Suspended (<?php echo (int)$stats['action_reports']; ?>)</option>
                    <option value="dismissed" <?php echo $statusFilter === 'dismissed' ? 'selected' : ''; ?>>✓ Dismissed / Safe (<?php echo (int)$stats['dismissed_reports']; ?>)</option>
                    <option value="all" <?php echo $statusFilter === 'all' ? 'selected' : ''; ?>>All Reports (<?php echo (int)$stats['total_reports']; ?>)</option>
                </select>
            </div>

            <div class="col-md-4">
                <label class="form-label small text-muted mb-1">Reason Filter</label>
                <select name="reason" class="form-select form-select-sm rounded-pill" onchange="this.form.submit()">
                    <option value="all" <?php echo $reasonFilter === 'all' ? 'selected' : ''; ?>>All Violation Reasons</option>
                    <option value="spam" <?php echo $reasonFilter === 'spam' ? 'selected' : ''; ?>>Spam or Scam</option>
                    <option value="malicious" <?php echo $reasonFilter === 'malicious' ? 'selected' : ''; ?>>Malicious Content / Fraud</option>
                    <option value="misleading" <?php echo $reasonFilter === 'misleading' ? 'selected' : ''; ?>>Misleading Information</option>
                    <option value="inappropriate" <?php echo $reasonFilter === 'inappropriate' ? 'selected' : ''; ?>>Inappropriate / Harmful</option>
                    <option value="other" <?php echo $reasonFilter === 'other' ? 'selected' : ''; ?>>Other Violation</option>
                </select>
            </div>

            <div class="col-md-4 text-md-end mt-md-4">
                <a href="/cshub/admin/reports.php" class="btn btn-outline-secondary btn-sm rounded-pill px-3">
                    <i class="bi bi-arrow-counterclockwise me-1"></i> Reset Filters
                </a>
            </div>
        </form>
    </div>

    <!-- Reports Table -->
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
        <div class="card-header bg-white border-0 py-3">
            <h5 class="mb-0 fw-bold">Reported Listings (<?php echo count($reports); ?>)</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4">Reported Service</th>
                            <th>Provider</th>
                            <th>Reason</th>
                            <th>Reported By</th>
                            <th>Details</th>
                            <th>Date</th>
                            <th>Status</th>
                            <th class="pe-4 text-end">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($reports) > 0): ?>
                            <?php foreach ($reports as $r): ?>
                                <tr class="<?php echo $r['status'] === 'pending' ? 'table-danger bg-opacity-25' : ''; ?>">
                                    <!-- Service Title & Link -->
                                    <td class="ps-4" style="max-width: 260px;">
                                        <?php if (!empty($r['service_title'])): ?>
                                            <a href="/cshub/service.php?id=<?php echo (int)$r['service_id']; ?>" target="_blank" class="fw-bold text-dark text-decoration-none d-block text-truncate">
                                                <?php echo e($r['service_title']); ?> <i class="bi bi-box-arrow-up-right small text-muted"></i>
                                            </a>
                                            <div class="small">
                                                <span class="badge <?php echo $r['service_status'] === 'approved' ? 'bg-success' : ($r['service_status'] === 'rejected' ? 'bg-danger' : 'bg-warning text-dark'); ?>">
                                                    <?php echo ucfirst($r['service_status']); ?>
                                                </span>
                                                <span class="text-muted ms-1"><?php echo formatPrice((float)$r['service_price']); ?></span>
                                            </div>
                                        <?php else: ?>
                                            <span class="text-muted fst-italic">Deleted Service (#<?php echo (int)$r['service_id']; ?>)</span>
                                        <?php endif; ?>
                                    </td>

                                    <!-- Provider Info -->
                                    <td>
                                        <div class="small fw-semibold"><?php echo e($r['provider_name'] ?? 'Unknown'); ?></div>
                                        <div class="text-muted small"><?php echo e($r['provider_email'] ?? '-'); ?></div>
                                    </td>

                                    <!-- Reason Badge -->
                                    <td>
                                        <?php if ($r['reason'] === 'malicious'): ?>
                                            <span class="badge bg-danger rounded-pill px-3 py-1">
                                                <i class="bi bi-shield-x me-1"></i> Malicious
                                            </span>
                                        <?php elseif ($r['reason'] === 'spam'): ?>
                                            <span class="badge bg-warning text-dark rounded-pill px-3 py-1">
                                                <i class="bi bi-exclamation-triangle me-1"></i> Spam
                                            </span>
                                        <?php elseif ($r['reason'] === 'misleading'): ?>
                                            <span class="badge bg-info text-dark rounded-pill px-3 py-1">
                                                <i class="bi bi-question-circle me-1"></i> Misleading
                                            </span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary rounded-pill px-3 py-1">
                                                <?php echo ucfirst($r['reason']); ?>
                                            </span>
                                        <?php endif; ?>
                                    </td>

                                    <!-- Reporter Info -->
                                    <td>
                                        <div class="small fw-semibold"><?php echo e($r['reporter_name'] ?? 'Guest'); ?></div>
                                        <small class="text-muted d-block"><?php echo e($r['reporter_email'] ?? 'No email'); ?></small>
                                    </td>

                                    <!-- Details Snippet -->
                                    <td style="max-width: 220px;">
                                        <span class="small text-muted d-inline-block text-truncate" style="max-width: 220px;" title="<?php echo e($r['details']); ?>">
                                            <?php echo !empty($r['details']) ? e($r['details']) : '<em class="text-muted">No additional details</em>'; ?>
                                        </span>
                                    </td>

                                    <!-- Date -->
                                    <td class="text-muted small">
                                        <?php echo date('M d, H:i', strtotime($r['created_at'])); ?>
                                    </td>

                                    <!-- Status -->
                                    <td>
                                        <?php if ($r['status'] === 'pending'): ?>
                                            <span class="badge bg-danger text-white rounded-pill">
                                                <i class="bi bi-clock me-1"></i> Pending
                                            </span>
                                        <?php elseif ($r['status'] === 'action_taken'): ?>
                                            <span class="badge bg-warning text-dark rounded-pill">
                                                <i class="bi bi-slash-circle me-1"></i> Action Taken
                                            </span>
                                        <?php else: ?>
                                            <span class="badge bg-success-subtle text-success rounded-pill">
                                                <i class="bi bi-check2 me-1"></i> Dismissed
                                            </span>
                                        <?php endif; ?>
                                    </td>

                                    <!-- Actions -->
                                    <td class="pe-4 text-end">
                                        <div class="btn-group btn-group-sm">
                                            <?php if ($r['status'] === 'pending' && !empty($r['service_title']) && $r['service_status'] !== 'rejected'): ?>
                                                <a href="/cshub/admin/reports.php?action=suspend&id=<?php echo (int)$r['id']; ?>" 
                                                   class="btn btn-danger" 
                                                   title="Suspend &amp; Reject Service"
                                                   onclick="return confirm('Suspend this service immediately? It will be removed from public view.');">
                                                    <i class="bi bi-shield-slash me-1"></i> Suspend
                                                </a>
                                            <?php endif; ?>

                                            <?php if ($r['status'] === 'pending'): ?>
                                                <a href="/cshub/admin/reports.php?action=dismiss&id=<?php echo (int)$r['id']; ?>" 
                                                   class="btn btn-outline-secondary" 
                                                   title="Dismiss as False Alarm"
                                                   onclick="return confirm('Dismiss this report?');">
                                                    <i class="bi bi-x-circle"></i>
                                                </a>
                                            <?php endif; ?>

                                            <?php if (!empty($r['service_id'])): ?>
                                                <a href="/cshub/service.php?id=<?php echo (int)$r['service_id']; ?>" 
                                                   target="_blank" 
                                                   class="btn btn-outline-primary" 
                                                   title="Preview Service">
                                                    <i class="bi bi-eye"></i>
                                                </a>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="8" class="text-center py-5 text-muted">
                                    <i class="bi bi-shield-check fs-1 text-success d-block mb-2"></i>
                                    <h6 class="fw-bold">No Reports Found</h6>
                                    <p class="small text-muted mb-0">No service reports match the current filter criteria.</p>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
