<?php
/**
 * admin/reviews.php
 * Review management page - view and delete reviews
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/logger.php';

// Require admin role
requireRole('admin');

$success = '';
$error = '';

// Handle review deletion
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $reviewId = (int)$_GET['delete'];

    try {
        // Get review info before deleting
        $stmt = $pdo->prepare("SELECT service_id, rating FROM reviews WHERE id = ?");
        $stmt->execute([$reviewId]);
        $reviewInfo = $stmt->fetch();

        $stmt = $pdo->prepare("DELETE FROM reviews WHERE id = ?");
        $stmt->execute([$reviewId]);

        // Log the deletion
        logAdminActivity($pdo, 'deleted_review', 'review', $reviewId, "Deleted review (Service #{$reviewInfo['service_id']}, Rating: {$reviewInfo['rating']})");

        $success = 'Review deleted successfully.';
    } catch (PDOException $e) {
        $error = 'Failed to delete review.';
    }
}

// Get all reviews with service and user info
$reviews = $pdo->query("
    SELECT
        r.*,
        s.title as service_title,
        u.name as client_name,
        p.name as provider_name
    FROM reviews r
    JOIN services s ON r.service_id = s.id
    JOIN users u ON r.client_id = u.id
    JOIN users p ON s.user_id = p.id
    ORDER BY r.created_at DESC
")->fetchAll();

require_once __DIR__ . '/../includes/header.php';
?>

<div class="container py-4">
    <div class="row mb-4">
        <div class="col-md-6">
            <h1 class="h3 mb-1">Review Management</h1>
            <p class="text-muted">Manage all reviews on ProVenture</p>
        </div>
        <div class="col-md-6 text-end">
            <a href="/cshub/admin/export.php?type=reviews&format=csv" class="btn btn-success me-2">
                <svg width="16" height="16" fill="currentColor" class="me-1">
                    <use href="#icon-download"/>
                </svg>
                Export CSV
            </a>
            <a href="/cshub/admin/dashboard.php" class="btn btn-outline-secondary">← Back to Dashboard</a>
        </div>
    </div>

    <?php if ($success): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <?php echo e($success); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div class="alert alert-danger alert-dismissible fade show">
            <?php echo e($error); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- Reviews Table -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white border-0 py-3">
            <h5 class="mb-0">All Reviews (<?php echo count($reviews); ?>)</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>ID</th>
                            <th>Service</th>
                            <th>Client</th>
                            <th>Provider</th>
                            <th>Rating</th>
                            <th>Review</th>
                            <th>Date</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($reviews) > 0): ?>
                            <?php foreach ($reviews as $review): ?>
                                <tr>
                                    <td><?php echo $review['id']; ?></td>
                                    <td><?php echo e($review['service_title']); ?></td>
                                    <td><?php echo e($review['client_name']); ?></td>
                                    <td><?php echo e($review['provider_name']); ?></td>
                                    <td>
                                        <span class="badge bg-warning text-dark">
                                            <?php echo str_repeat('★', $review['rating']); ?>
                                            (<?php echo $review['rating']; ?>/5)
                                        </span>
                                    </td>
                                    <td>
                                        <?php if ($review['review_text']): ?>
                                            <?php echo e(substr($review['review_text'], 0, 50)); ?>...
                                        <?php else: ?>
                                            <span class="text-muted">No comment</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-muted small"><?php echo date('M d, Y', strtotime($review['created_at'])); ?></td>
                                    <td class="text-center">
                                        <a href="/cshub/admin/reviews.php?delete=<?php echo $review['id']; ?>"
                                           class="btn btn-sm btn-outline-danger"
                                           onclick="return confirm('Are you sure you want to delete this review?');">
                                            Delete
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="8" class="text-center text-muted py-4">
                                    No reviews found. Reviews will appear here once clients start rating services.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- SVG Icons -->
<svg style="display: none;">
    <symbol id="icon-download" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" fill="none">
        <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
        <polyline points="7 10 12 15 17 10"></polyline>
        <line x1="12" y1="15" x2="12" y2="3"></line>
    </symbol>
</svg>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
