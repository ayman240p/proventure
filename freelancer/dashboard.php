<?php
/**
 * freelancer/dashboard.php
 * Freelancer dashboard: profile summary + list of their own services with approval status.
 * (Objective 4)
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

requireRole('freelancer');

$stmt = $pdo->prepare("
    SELECT s.id, s.title, s.price, s.availability, s.status, s.created_at, s.portfolio_image,
           c.name AS category_name
    FROM services s
    LEFT JOIN categories c ON s.category_id = c.id
    WHERE s.user_id = ?
    ORDER BY s.created_at DESC
");
$stmt->execute([$_SESSION['user_id']]);
$services = $stmt->fetchAll();

// Count stats
$totalServices = count($services);
$approvedCount = 0;
$pendingCount = 0;
foreach ($services as $s) {
    if ($s['status'] === 'approved') $approvedCount++;
    if ($s['status'] === 'pending') $pendingCount++;
}

// Flash messages
$flashSuccess = $_SESSION['flash_success'] ?? null;
$flashError = $_SESSION['flash_error'] ?? null;
unset($_SESSION['flash_success'], $_SESSION['flash_error']);

require_once __DIR__ . '/../includes/header.php';
?>

<div class="container py-4">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <h1 class="h3 fw-bold mb-1">Freelancer Dashboard</h1>
            <p class="text-muted small mb-0">Manage your micro-services, track admin review status, and update availability.</p>
        </div>
        <div class="d-flex gap-2">
            <a href="/cshub/freelancer/profile.php" class="btn btn-outline-secondary btn-sm rounded-pill px-3">
                <i class="bi bi-person-gear me-1"></i> Edit Profile
            </a>
            <a href="/cshub/freelancer/add-service.php" class="btn btn-primary btn-sm rounded-pill px-3 fw-semibold">
                <i class="bi bi-plus-circle-fill me-1"></i> Add Service
            </a>
        </div>
    </div>

    <!-- Flash Alerts -->
    <?php if ($flashSuccess): ?>
        <div class="alert alert-success alert-dismissible fade show rounded-4 shadow-sm mb-4" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i> <?php echo e($flashSuccess); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <?php if ($flashError): ?>
        <div class="alert alert-danger alert-dismissible fade show rounded-4 shadow-sm mb-4" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2"></i> <?php echo e($flashError); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <!-- Summary Stats -->
    <div class="row g-3 mb-4">
        <div class="col-sm-4">
            <div class="card border-0 shadow-sm rounded-4 p-3 h-100">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted small">Total Services</span>
                        <h3 class="fw-bold mb-0 mt-1"><?php echo $totalServices; ?></h3>
                    </div>
                    <div class="bg-primary bg-opacity-10 text-primary p-3 rounded-circle fs-4">
                        <i class="bi bi-grid-fill"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-4">
            <div class="card border-0 shadow-sm rounded-4 p-3 h-100">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted small">Live (Approved)</span>
                        <h3 class="fw-bold mb-0 mt-1 text-success"><?php echo $approvedCount; ?></h3>
                    </div>
                    <div class="bg-success bg-opacity-10 text-success p-3 rounded-circle fs-4">
                        <i class="bi bi-check-circle-fill"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-4">
            <div class="card border-0 shadow-sm rounded-4 p-3 h-100">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted small">Pending Approval</span>
                        <h3 class="fw-bold mb-0 mt-1 text-warning"><?php echo $pendingCount; ?></h3>
                    </div>
                    <div class="bg-warning bg-opacity-10 text-warning p-3 rounded-circle fs-4">
                        <i class="bi bi-clock-history"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Admin Moderation Notice -->
    <div class="alert alert-light border rounded-4 p-3 mb-4 d-flex align-items-center gap-3">
        <div class="text-primary fs-3"><i class="bi bi-shield-shaded"></i></div>
        <div class="small">
            <strong>Community Moderation Policy:</strong> All new or edited services require administrator verification before going live. Services marked as <strong>Pending Admin Approval</strong> remain private to you until approved, keeping ProVenture safe and spam-free.
        </div>
    </div>

    <!-- Services Table -->
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
        <div class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center">
            <h5 class="fw-bold mb-0">My Listed Services</h5>
            <span class="badge bg-light text-dark"><?php echo count($services); ?> Total</span>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light small">
                    <tr>
                        <th class="ps-3">Service</th>
                        <th>Category</th>
                        <th>Starting Price</th>
                        <th>Approval Status</th>
                        <th>Availability</th>
                        <th class="text-end pe-3">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($services)): ?>
                        <tr>
                            <td colspan="6" class="text-center py-5 text-muted">
                                <div class="py-3">
                                    <i class="bi bi-inbox fs-1 d-block mb-2 text-muted"></i>
                                    <h6>You have not added any services yet.</h6>
                                    <p class="small mb-3">Start offering your skills to local clients today!</p>
                                    <a href="/cshub/freelancer/add-service.php" class="btn btn-primary btn-sm rounded-pill px-4">
                                        <i class="bi bi-plus-circle me-1"></i> Add Your First Service
                                    </a>
                                </div>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($services as $service): ?>
                            <tr>
                                <td class="ps-3">
                                    <div class="d-flex align-items-center gap-2">
                                        <?php if (!empty($service['portfolio_image'])): ?>
                                            <img src="/cshub/uploads/portfolio/<?php echo e($service['portfolio_image']); ?>" alt="Showcase" class="rounded-2 border flex-shrink-0" style="width: 44px; height: 34px; object-fit: cover;">
                                        <?php endif; ?>
                                        <div>
                                            <span class="fw-bold d-block text-dark"><?php echo e($service['title']); ?></span>
                                            <span class="text-muted small">Added on <?php echo date('M d, Y', strtotime($service['created_at'])); ?></span>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-light text-dark border rounded-pill">
                                        <?php echo e($service['category_name'] ?? 'Uncategorized'); ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="fw-bold text-primary"><?php echo formatPrice((float) $service['price']); ?></span>
                                </td>
                                <td>
                                    <?php if ($service['status'] === 'approved'): ?>
                                        <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-3 py-1">
                                            <i class="bi bi-check-circle me-1"></i> Approved (Live)
                                        </span>
                                    <?php elseif ($service['status'] === 'rejected'): ?>
                                        <span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill px-3 py-1">
                                            <i class="bi bi-x-circle me-1"></i> Rejected
                                        </span>
                                    <?php else: ?>
                                        <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle rounded-pill px-3 py-1">
                                            <i class="bi bi-clock-history me-1"></i> Pending Approval
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($service['availability']): ?>
                                        <span class="badge bg-success rounded-pill px-2 py-1 small">Available</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary rounded-pill px-2 py-1 small">Busy</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-end pe-3">
                                    <div class="btn-group btn-group-sm">
                                        <a href="/cshub/service.php?id=<?php echo (int) $service['id']; ?>" class="btn btn-outline-secondary" title="Preview Service">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        <a href="/cshub/freelancer/edit-service.php?id=<?php echo (int) $service['id']; ?>" class="btn btn-outline-secondary" title="Edit Service">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <a href="/cshub/freelancer/toggle-status.php?id=<?php echo (int) $service['id']; ?>" class="btn btn-outline-primary" title="Toggle Available / Busy">
                                            <i class="bi bi-arrow-repeat"></i>
                                        </a>
                                        <a href="/cshub/freelancer/delete-service.php?id=<?php echo (int) $service['id']; ?>" class="btn btn-outline-danger" title="Delete Service" onclick="return confirm('Are you sure you want to delete this service?');">
                                            <i class="bi bi-trash"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
