<?php
/**
 * admin/services.php
 * Service management page - view, approve, reject, edit, delete services
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/logger.php';

// Require admin role
requireRole('admin');

$success = $_SESSION['bulk_success'] ?? '';
$error = $_SESSION['bulk_error'] ?? '';
unset($_SESSION['bulk_success'], $_SESSION['bulk_error']);

// 1. Handle service approval
if (isset($_GET['approve']) && is_numeric($_GET['approve'])) {
    $serviceId = (int)$_GET['approve'];
    try {
        $stmt = $pdo->prepare("SELECT title FROM services WHERE id = ?");
        $stmt->execute([$serviceId]);
        $serviceInfo = $stmt->fetch();

        if ($serviceInfo) {
            $stmt = $pdo->prepare("UPDATE services SET status = 'approved' WHERE id = ?");
            $stmt->execute([$serviceId]);

            logAdminActivity($pdo, 'approved_service', 'service', $serviceId, "Approved service: {$serviceInfo['title']}");
            $success = 'Service "' . e($serviceInfo['title']) . '" approved successfully! It is now live on the public directory.';
        }
    } catch (PDOException $e) {
        $error = 'Failed to approve service.';
    }
}

// 2. Handle service rejection
if (isset($_GET['reject']) && is_numeric($_GET['reject'])) {
    $serviceId = (int)$_GET['reject'];
    try {
        $stmt = $pdo->prepare("SELECT title FROM services WHERE id = ?");
        $stmt->execute([$serviceId]);
        $serviceInfo = $stmt->fetch();

        if ($serviceInfo) {
            $stmt = $pdo->prepare("UPDATE services SET status = 'rejected' WHERE id = ?");
            $stmt->execute([$serviceId]);

            logAdminActivity($pdo, 'rejected_service', 'service', $serviceId, "Rejected service: {$serviceInfo['title']}");
            $success = 'Service "' . e($serviceInfo['title']) . '" has been rejected and hidden from public view.';
        }
    } catch (PDOException $e) {
        $error = 'Failed to reject service.';
    }
}

// 3. Handle service deletion
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $serviceId = (int)$_GET['delete'];
    try {
        $stmt = $pdo->prepare("SELECT title, portfolio_image FROM services WHERE id = ?");
        $stmt->execute([$serviceId]);
        $serviceInfo = $stmt->fetch();

        if ($serviceInfo) {
            if (!empty($serviceInfo['portfolio_image'])) {
                $imgPath = __DIR__ . '/../uploads/portfolio/' . $serviceInfo['portfolio_image'];
                if (file_exists($imgPath)) @unlink($imgPath);
            }

            $stmt = $pdo->prepare("DELETE FROM services WHERE id = ?");
            $stmt->execute([$serviceId]);

            logAdminActivity($pdo, 'deleted_service', 'service', $serviceId, "Deleted service: {$serviceInfo['title']}");
            $success = 'Service deleted successfully.';
        }
    } catch (PDOException $e) {
        $error = 'Failed to delete service.';
    }
}

// 4. Handle availability toggle
if (isset($_GET['toggle']) && is_numeric($_GET['toggle'])) {
    $serviceId = (int)$_GET['toggle'];
    try {
        $stmt = $pdo->prepare("UPDATE services SET availability = NOT availability WHERE id = ?");
        $stmt->execute([$serviceId]);

        logAdminActivity($pdo, 'toggled_service_availability', 'service', $serviceId, 'Admin toggled service availability');
        $success = 'Service availability updated.';
    } catch (PDOException $e) {
        $error = 'Failed to update service availability.';
    }
}

// Get all services with search and filter
$search = $_GET['search'] ?? '';
$categoryFilter = $_GET['category'] ?? '';
$availabilityFilter = $_GET['availability'] ?? '';
$statusFilter = $_GET['status'] ?? '';

$query = "
    SELECT s.*, u.name as provider_name, c.name as category_name,
           (SELECT COUNT(*) FROM service_reports WHERE service_id = s.id AND status = 'pending') as pending_reports_count
    FROM services s
    JOIN users u ON s.user_id = u.id
    LEFT JOIN categories c ON s.category_id = c.id
    WHERE 1=1
";
$params = [];

if (!empty($search)) {
    $query .= " AND (s.title LIKE ? OR s.description LIKE ? OR u.name LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

if (!empty($categoryFilter) && $categoryFilter !== 'all') {
    $query .= " AND s.category_id = ?";
    $params[] = $categoryFilter;
}

if ($availabilityFilter !== '' && $availabilityFilter !== 'all') {
    $query .= " AND s.availability = ?";
    $params[] = $availabilityFilter;
}

if (!empty($statusFilter) && $statusFilter !== 'all') {
    $query .= " AND s.status = ?";
    $params[] = $statusFilter;
}

$query .= " ORDER BY (s.status = 'pending') DESC, s.created_at DESC";

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$services = $stmt->fetchAll();

// Get counts for quick stats
$pendingCount = $pdo->query("SELECT COUNT(*) FROM services WHERE status = 'pending'")->fetchColumn();
$categories = $pdo->query("SELECT id, name FROM categories ORDER BY name")->fetchAll();

require_once __DIR__ . '/../includes/header.php';
?>

<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-md-5">
            <h1 class="h3 mb-1 fw-bold">Service Moderation &amp; Management</h1>
            <p class="text-muted">Review, approve, reject, or delete community micro-services.</p>
        </div>
        <div class="col-md-7 text-end d-flex align-items-center justify-content-md-end gap-2 flex-wrap">
            <a href="/cshub/admin/reports.php" class="btn btn-outline-danger btn-sm rounded-pill px-3">
                <i class="bi bi-flag-fill me-1"></i> Reports Center
            </a>
            <a href="/cshub/admin/export.php?type=services&format=csv" class="btn btn-success btn-sm rounded-pill px-3">
                <i class="bi bi-download me-1"></i> Export CSV
            </a>
            <a href="/cshub/admin/dashboard.php" class="btn btn-outline-secondary btn-sm rounded-pill px-3">
                <i class="bi bi-arrow-left me-1"></i> Dashboard
            </a>
        </div>
    </div>

    <!-- Pending Review Alert if any exist -->
    <?php if ($pendingCount > 0): ?>
        <div class="alert alert-warning border-0 rounded-4 shadow-sm mb-4 d-flex align-items-center justify-content-between flex-wrap gap-2">
            <div class="d-flex align-items-center gap-3">
                <span class="badge bg-warning text-dark fs-6 rounded-pill px-3 py-2">
                    <i class="bi bi-clock-history me-1"></i> <?php echo $pendingCount; ?> Pending
                </span>
                <div>
                    <strong class="d-block">Services Awaiting Admin Moderation</strong>
                    <span class="small text-muted">Review new freelancer submissions below to ensure safety before they appear on the public directory.</span>
                </div>
            </div>
            <a href="/cshub/admin/services.php?status=pending" class="btn btn-dark btn-sm rounded-pill px-3">
                Filter Pending Only
            </a>
        </div>
    <?php endif; ?>

    <?php if ($success): ?>
        <div class="alert alert-success alert-dismissible fade show rounded-4 shadow-sm">
            <i class="bi bi-check-circle-fill me-2"></i> <?php echo e($success); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div class="alert alert-danger alert-dismissible fade show rounded-4 shadow-sm">
            <i class="bi bi-exclamation-triangle-fill me-2"></i> <?php echo e($error); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- Search and Filter Bar -->
    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-body p-3">
            <form method="get" action="/cshub/admin/services.php" class="row g-2 align-items-center">
                <div class="col-md-3">
                    <input type="text" name="search" class="form-control rounded-3" placeholder="Search by title, provider..." value="<?php echo e($search); ?>">
                </div>
                <div class="col-md-3">
                    <select name="status" class="form-select rounded-3">
                        <option value="all">All Moderation Status</option>
                        <option value="pending" <?php echo $statusFilter === 'pending' ? 'selected' : ''; ?>>⏳ Pending Approval (<?php echo $pendingCount; ?>)</option>
                        <option value="approved" <?php echo $statusFilter === 'approved' ? 'selected' : ''; ?>>✅ Approved</option>
                        <option value="rejected" <?php echo $statusFilter === 'rejected' ? 'selected' : ''; ?>>❌ Rejected</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="category" class="form-select rounded-3">
                        <option value="all">All Categories</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?php echo $cat['id']; ?>" <?php echo $categoryFilter == $cat['id'] ? 'selected' : ''; ?>>
                                <?php echo e($cat['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="availability" class="form-select rounded-3">
                        <option value="all">All Availability</option>
                        <option value="1" <?php echo $availabilityFilter === '1' ? 'selected' : ''; ?>>Available</option>
                        <option value="0" <?php echo $availabilityFilter === '0' ? 'selected' : ''; ?>>Busy</option>
                    </select>
                </div>
                <div class="col-md-2 d-flex gap-1">
                    <button type="submit" class="btn btn-primary rounded-3 w-100 fw-semibold">Filter</button>
                    <a href="/cshub/admin/services.php" class="btn btn-outline-secondary rounded-3" title="Clear Filters">
                        <i class="bi bi-x-lg"></i>
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Bulk Action Form & Services Table -->
    <form method="post" action="/cshub/admin/bulk-actions.php" id="bulkForm">
        <input type="hidden" name="type" value="services">

        <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
            <div class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
                <div class="d-flex align-items-center gap-3">
                    <h5 class="mb-0 fw-bold">All Services (<?php echo count($services); ?>)</h5>
                    <div id="bulkControls" class="d-none align-items-center gap-2">
                        <span class="small text-muted" id="selectedCount">0 selected</span>
                        <button type="submit" name="action" value="approve" class="btn btn-sm btn-success rounded-pill px-3">
                            <i class="bi bi-check-circle me-1"></i> Bulk Approve
                        </button>
                        <button type="submit" name="action" value="reject" class="btn btn-sm btn-warning rounded-pill px-3">
                            <i class="bi bi-x-circle me-1"></i> Bulk Reject
                        </button>
                        <button type="submit" name="action" value="delete" class="btn btn-sm btn-danger rounded-pill px-3" onclick="return confirm('Delete selected services?');">
                            <i class="bi bi-trash me-1"></i> Bulk Delete
                        </button>
                    </div>
                </div>
                <div>
                    <span class="badge bg-light text-dark">Default order: Pending first</span>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light small">
                        <tr>
                            <th style="width: 40px;" class="ps-3">
                                <input type="checkbox" id="selectAll" class="form-check-input">
                            </th>
                            <th>Service Title</th>
                            <th>Provider</th>
                            <th>Category</th>
                            <th>Price</th>
                            <th>Moderation</th>
                            <th>Availability</th>
                            <th>Submitted</th>
                            <th class="text-center pe-3">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($services) > 0): ?>
                            <?php foreach ($services as $service): ?>
                                <tr class="<?php echo $service['status'] === 'pending' ? 'table-warning bg-opacity-25' : ''; ?>">
                                    <td class="ps-3">
                                        <input type="checkbox" name="ids[]" value="<?php echo $service['id']; ?>" class="form-check-input row-checkbox">
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            <?php if (!empty($service['portfolio_image'])): ?>
                                                <img src="/cshub/uploads/portfolio/<?php echo e($service['portfolio_image']); ?>" alt="Image" class="rounded-2 border" style="width: 42px; height: 32px; object-fit: cover;">
                                            <?php endif; ?>
                                            <div>
                                                <a href="/cshub/service.php?id=<?php echo $service['id']; ?>" target="_blank" class="fw-bold text-dark text-decoration-none d-block">
                                                    <?php echo e($service['title']); ?>
                                                </a>
                                                <small class="text-muted"><?php echo e(mb_strimwidth($service['description'], 0, 50, '...')); ?></small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="small fw-semibold"><?php echo e($service['provider_name']); ?></span>
                                    </td>
                                    <td>
                                        <span class="badge bg-light text-dark border">
                                            <?php echo e($service['category_name'] ?? 'Uncategorized'); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="fw-bold text-primary"><?php echo formatPrice((float)$service['price']); ?></span>
                                    </td>
                                    <td>
                                        <?php if ($service['status'] === 'approved'): ?>
                                            <span class="badge bg-success rounded-pill px-3 py-1">
                                                <i class="bi bi-check-circle me-1"></i> Approved
                                            </span>
                                        <?php elseif ($service['status'] === 'rejected'): ?>
                                            <span class="badge bg-danger rounded-pill px-3 py-1">
                                                <i class="bi bi-x-circle me-1"></i> Rejected
                                            </span>
                                        <?php else: ?>
                                            <span class="badge bg-warning text-dark rounded-pill px-3 py-1">
                                                <i class="bi bi-clock-history me-1"></i> Pending
                                            </span>
                                        <?php endif; ?>
                                        <?php if (!empty($service['pending_reports_count']) && $service['pending_reports_count'] > 0): ?>
                                            <div class="mt-1">
                                                <a href="/cshub/admin/reports.php?status=pending" class="badge bg-danger text-white rounded-pill px-2 py-1 text-decoration-none small" title="View community reports">
                                                    <i class="bi bi-flag-fill me-1"></i> <?php echo $service['pending_reports_count']; ?> Report(s)
                                                </a>
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($service['availability']): ?>
                                            <span class="badge bg-success-subtle text-success rounded-pill px-2 py-1 small">Available</span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary-subtle text-secondary rounded-pill px-2 py-1 small">Busy</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-muted small">
                                        <?php echo date('M d, Y', strtotime($service['created_at'])); ?>
                                    </td>
                                    <td class="text-center pe-3">
                                        <div class="btn-group btn-group-sm">
                                            <!-- Approve Button -->
                                            <?php if ($service['status'] !== 'approved'): ?>
                                                <a href="/cshub/admin/services.php?approve=<?php echo $service['id']; ?>" class="btn btn-success" title="Approve service">
                                                    <i class="bi bi-check-lg"></i> Approve
                                                </a>
                                            <?php endif; ?>

                                            <!-- Reject Button -->
                                            <?php if ($service['status'] !== 'rejected'): ?>
                                                <a href="/cshub/admin/services.php?reject=<?php echo $service['id']; ?>" class="btn btn-warning text-dark" title="Reject service">
                                                    <i class="bi bi-x-lg"></i> Reject
                                                </a>
                                            <?php endif; ?>

                                            <!-- Toggle Availability -->
                                            <a href="/cshub/admin/services.php?toggle=<?php echo $service['id']; ?>" class="btn btn-outline-secondary" title="Toggle availability">
                                                <i class="bi bi-arrow-repeat"></i>
                                            </a>

                                            <!-- Delete -->
                                            <a href="/cshub/admin/services.php?delete=<?php echo $service['id']; ?>" class="btn btn-outline-danger" title="Delete" onclick="return confirm('Delete this service permanently?');">
                                                <i class="bi bi-trash"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="9" class="text-center text-muted py-5">
                                    No services found matching your filter criteria.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </form>
</div>

<script>
// Select All & Bulk Action controls
document.addEventListener('DOMContentLoaded', function() {
    var selectAll = document.getElementById('selectAll');
    var checkboxes = document.querySelectorAll('.row-checkbox');
    var bulkControls = document.getElementById('bulkControls');
    var selectedCount = document.getElementById('selectedCount');

    function updateBulkBar() {
        var count = 0;
        checkboxes.forEach(function(cb) { if (cb.checked) count++; });
        if (count > 0) {
            bulkControls.classList.remove('d-none');
            bulkControls.classList.add('d-flex');
            selectedCount.textContent = count + ' selected';
        } else {
            bulkControls.classList.add('d-none');
            bulkControls.classList.remove('d-flex');
        }
    }

    if (selectAll) {
        selectAll.addEventListener('change', function() {
            checkboxes.forEach(function(cb) { cb.checked = selectAll.checked; });
            updateBulkBar();
        });
    }

    checkboxes.forEach(function(cb) {
        cb.addEventListener('change', function() {
            if (!cb.checked && selectAll) selectAll.checked = false;
            updateBulkBar();
        });
    });
});
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
