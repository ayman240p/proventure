<?php
/**
 * service.php?id=X
 * Public service detail page: full description, provider info,
 * average rating (Objective 2), review list/form, and WhatsApp button
 * (Objective 3).
 * TODO: build out in the Reviews/Ratings and WhatsApp sprints.
 */

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

$stmt = $pdo->prepare("
    SELECT services.*, categories.name AS category_name,
           users.name AS provider_name, users.phone AS provider_phone
    FROM services
    LEFT JOIN categories ON services.category_id = categories.id
    LEFT JOIN users ON services.user_id = users.id
    WHERE services.id = ?
");
$stmt->execute([$id]);
$service = $stmt->fetch();

if (!$service) {
    require_once __DIR__ . '/includes/header.php';
    echo '<div class="alert alert-warning my-4">' . __('Service not found.') . '</div>';
    echo '<p><a href="/cshub/index.php" class="btn btn-outline-primary">&larr; ' . __('Back to Home') . '</a></p>';
    require_once __DIR__ . '/includes/footer.php';
    exit;
}

// Fetch average rating and count
$ratingStmt = $pdo->prepare("
    SELECT AVG(rating) as avg_rating, COUNT(*) as review_count
    FROM reviews
    WHERE service_id = ?
");
$ratingStmt->execute([$id]);
$ratingData = $ratingStmt->fetch();
$avgRating = $ratingData['avg_rating'] ? round((float)$ratingData['avg_rating'], 1) : 0;
$reviewCount = (int)($ratingData['review_count'] ?? 0);

// Fetch reviews list
$reviewsStmt = $pdo->prepare("
    SELECT r.*, u.name as client_name
    FROM reviews r
    JOIN users u ON r.client_id = u.id
    WHERE r.service_id = ?
    ORDER BY r.created_at DESC
");
$reviewsStmt->execute([$id]);
// Check if service is approved or viewer is authorized (owner or admin)
$isOwner = isLoggedIn() && (int)$service['user_id'] === (int)$_SESSION['user_id'];
$isAdmin = isLoggedIn() && currentRole() === 'admin';

if ($service['status'] !== 'approved' && !$isOwner && !$isAdmin) {
    require_once __DIR__ . '/includes/header.php';
    echo '<div class="container py-5 text-center">';
    echo '<div class="alert alert-warning border-0 rounded-4 shadow-sm p-4 d-inline-block text-start" style="max-width: 540px;">';
    echo '<h5 class="fw-bold"><i class="bi bi-shield-exclamation text-warning me-2"></i>' . __('Service Pending Moderation') . '</h5>';
    echo '<p class="text-muted mb-3">' . __('This service is currently being reviewed by an administrator and is not yet available to the public.') . '</p>';
    echo '<a href="/cshub/index.php" class="btn btn-primary rounded-pill px-4">' . __('Back to Browse Services') . '</a>';
    echo '</div></div>';
    require_once __DIR__ . '/includes/footer.php';
    exit;
}

require_once __DIR__ . '/includes/header.php';
?>

<div class="row justify-content-center my-4">
    <div class="col-lg-8">
        <!-- Moderation Preview Banner for Owner / Admin -->
        <?php if ($service['status'] === 'pending'): ?>
            <div class="alert alert-warning border-0 rounded-4 shadow-sm mb-4 d-flex align-items-center justify-content-between flex-wrap gap-2">
                <div class="d-flex align-items-center gap-2">
                    <i class="bi bi-shield-exclamation fs-4 text-warning"></i>
                    <div>
                        <strong>Preview Mode:</strong> This service is currently <strong>Pending Admin Approval</strong> and is hidden from clients.
                    </div>
                </div>
                <?php if ($isAdmin): ?>
                    <a href="/cshub/admin/services.php?approve=<?php echo $service['id']; ?>" class="btn btn-sm btn-success rounded-pill px-3">
                        <i class="bi bi-check-circle me-1"></i> Approve Service
                    </a>
                <?php endif; ?>
            </div>
        <?php elseif ($service['status'] === 'rejected'): ?>
            <div class="alert alert-danger border-0 rounded-4 shadow-sm mb-4 d-flex align-items-center gap-2">
                <i class="bi bi-x-circle fs-4 text-danger"></i>
                <div>
                    <strong>Moderation Notice:</strong> This service was rejected during administrative review and is not listed publicly.
                </div>
            </div>
        <?php endif; ?>

        <!-- Flash Notification Banners -->
        <?php if (!empty($_SESSION['flash_success'])): ?>
            <div class="alert alert-success alert-dismissible fade show rounded-4 border-0 shadow-sm mb-4">
                <i class="bi bi-check-circle-fill me-2"></i><?php echo e($_SESSION['flash_success']); unset($_SESSION['flash_success']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        <?php if (!empty($_SESSION['flash_error'])): ?>
            <div class="alert alert-danger alert-dismissible fade show rounded-4 border-0 shadow-sm mb-4">
                <i class="bi bi-exclamation-triangle-fill me-2"></i><?php echo e($_SESSION['flash_error']); unset($_SESSION['flash_error']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- Service Main Card -->
        <div class="card shadow-sm border-0 rounded-4 p-4 mb-4">
            <div class="d-flex justify-content-between align-items-start mb-3 flex-wrap gap-2">
                <div class="d-flex align-items-center gap-2 flex-wrap">
                    <span class="badge bg-primary-subtle text-primary rounded-pill px-3 py-1">
                        <?php echo e($service['category_name'] ?? __('Uncategorized')); ?>
                    </span>
                    <?php if (!empty($service['availability'])): ?>
                        <span class="badge bg-success-subtle text-success rounded-pill px-3 py-1 d-inline-flex align-items-center gap-1">
                            <i class="bi bi-check-circle-fill"></i> <?= __('Available') ?>
                        </span>
                    <?php else: ?>
                        <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle rounded-pill px-3 py-1 d-inline-flex align-items-center gap-1">
                            <i class="bi bi-slash-circle"></i> <?= __('Busy / Unavailable') ?>
                        </span>
                    <?php endif; ?>
                </div>

                <button type="button" class="btn btn-sm btn-outline-danger rounded-pill px-3 py-1 d-inline-flex align-items-center gap-1"
                        data-bs-toggle="modal" data-bs-target="#reportServiceModal"
                        data-service-id="<?php echo (int)$service['id']; ?>"
                        data-service-title="<?php echo e($service['title']); ?>"
                        title="<?= __('Report this listing if it contains spam or malicious content') ?>">
                    <i class="bi bi-flag-fill"></i> <span><?= __('Report Listing') ?></span>
                </button>
            </div>

            <h1 class="h2 fw-bold mb-2"><?php echo e($service['title']); ?></h1>
            
            <div class="d-flex align-items-center gap-3 mb-3">
                <span class="fw-bold fs-3 text-primary"><?php echo formatPrice((float) $service['price']); ?></span>
                <?php if ($reviewCount > 0): ?>
                    <span class="d-flex align-items-center gap-1 text-warning fw-bold">
                        <i class="bi bi-star-fill"></i>
                        <span><?php echo number_format($avgRating, 1); ?></span>
                        <span class="text-muted small fw-normal">(<?php echo $reviewCount; ?> reviews)</span>
                    </span>
                <?php endif; ?>
            </div>

            <?php if (empty($service['availability'])): ?>
                <!-- Client Notice: Listing Busy & Unavailable -->
                <div class="busy-listing-notice rounded-4 p-3 mb-4 d-flex align-items-center gap-3 shadow-sm" role="alert">
                    <div class="rounded-circle bg-warning bg-opacity-25 d-flex align-items-center justify-content-center p-3 text-warning-emphasis flex-shrink-0" style="width: 48px; height: 48px;">
                        <i class="bi bi-clock-history fs-3"></i>
                    </div>
                    <div class="flex-grow-1">
                        <div class="d-flex align-items-center gap-2 mb-1 flex-wrap">
                            <h6 class="fw-bold mb-0 text-warning-emphasis"><?= __('Listing Currently Busy & Unavailable') ?></h6>
                            <span class="badge bg-warning text-dark rounded-pill px-2 py-0 small fw-semibold"><?= __('Not Accepting Inquiries') ?></span>
                        </div>
                        <p class="small text-muted mb-0">
                            <?= __('This service provider is currently busy and temporarily unable to take on new projects. Direct WhatsApp inquiries are disabled until their availability is updated. Please check back later or browse other active services.') ?>
                        </p>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Portfolio / Showcase Image Section -->
            <?php 
            $hasShowcaseImage = !empty($service['portfolio_image']) && file_exists(__DIR__ . '/uploads/portfolio/' . $service['portfolio_image']);
            ?>
            <?php if ($hasShowcaseImage): ?>
                <div class="service-showcase-section mb-4">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <span class="fw-bold small text-uppercase tracking-wider text-muted d-flex align-items-center gap-2">
                            <i class="bi bi-image-fill text-primary"></i> <?= __('Work Showcase / Portfolio') ?>
                        </span>
                        <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-3 py-1" data-bs-toggle="modal" data-bs-target="#showcaseImageModal">
                            <i class="bi bi-arrows-fullscreen me-1"></i><?= __('View Full Size') ?>
                        </button>
                    </div>
                    <div class="service-showcase-card">
                        <div class="showcase-media-frame" data-bs-toggle="modal" data-bs-target="#showcaseImageModal" role="button" tabindex="0" title="<?= __('Click to view full image') ?>">
                            <img src="/cshub/uploads/portfolio/<?php echo e($service['portfolio_image']); ?>" 
                                 alt="<?php echo e($service['title']); ?> - Showcase" 
                                 class="showcase-media-img">
                            <div class="showcase-hover-badge">
                                <i class="bi bi-zoom-in"></i>
                                <span><?= __('Click to view full image') ?></span>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <div class="p-3 bg-body-tertiary rounded-3 mb-4">
                <h6 class="fw-bold text-muted small text-uppercase mb-2"><?= __('Description & Deliverables') ?></h6>
                <p class="mb-0" style="white-space: pre-line;"><?php echo e($service['description']); ?></p>
            </div>

            <div class="d-flex align-items-center justify-content-between flex-wrap gap-3 pt-3 border-top">
                <div>
                    <span class="text-muted small d-block"><?= __('Provider:') ?></span>
                    <span class="fw-semibold fs-5"><?php echo e($service['provider_name'] ?? __('Unknown')); ?></span>
                </div>
                <?php if (!empty($service['availability'])): ?>
                    <a href="/cshub/whatsapp.php?service_id=<?php echo (int) $service['id']; ?>" class="btn btn-success btn-lg rounded-pill px-4 shadow-sm">
                        <i class="bi bi-whatsapp me-2"></i><?= __('Contact via WhatsApp') ?>
                    </a>
                <?php else: ?>
                    <div class="d-flex flex-column align-items-sm-end text-sm-end">
                        <button type="button" class="btn btn-secondary btn-lg rounded-pill px-4 shadow-sm btn-whatsapp-disabled" disabled aria-disabled="true" title="<?= __('This listing is currently busy and unavailable') ?>">
                            <i class="bi bi-whatsapp me-2"></i><?= __('Unavailable on WhatsApp') ?>
                        </button>
                        <span class="text-danger small mt-1 fw-semibold d-inline-flex align-items-center gap-1">
                            <i class="bi bi-exclamation-circle-fill"></i> <?= __('Provider is currently busy and unavailable') ?>
                        </span>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Client Reviews Section -->
        <div class="card shadow-sm border-0 rounded-4 p-4">
            <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
                <div>
                    <h3 class="h4 fw-bold mb-1 d-flex align-items-center gap-2">
                        <span><?= __('Client Reviews & Feedback') ?></span>
                        <?php if ($reviewCount > 0): ?>
                            <span class="badge bg-warning text-dark fs-6 rounded-pill">
                                ★ <?php echo number_format($avgRating, 1); ?>
                            </span>
                        <?php endif; ?>
                    </h3>
                    <p class="text-muted small mb-0">
                        <?php echo $reviewCount; ?> <?= __('verified peer review(s)') ?>
                    </p>
                </div>

                <?php if (isLoggedIn() && currentRole() === 'client'): ?>
                    <button class="btn btn-outline-primary btn-sm rounded-pill px-3" type="button" data-bs-toggle="collapse" data-bs-target="#writeReviewCollapse">
                        <i class="bi bi-pencil-fill me-1"></i><?= __('Write a Review') ?>
                    </button>
                <?php endif; ?>
            </div>

            <!-- Client Review Submission Form (Collapsed) -->
            <?php if (isLoggedIn() && currentRole() === 'client'): ?>
                <div class="collapse mb-4" id="writeReviewCollapse">
                    <div class="p-4 rounded-4 bg-body-tertiary border">
                        <h5 class="fw-bold mb-3"><?= __('Leave Your Review') ?></h5>
                        <form method="post" action="/cshub/review.php">
                            <input type="hidden" name="service_id" value="<?php echo (int)$service['id']; ?>">
                            <div class="mb-3">
                                <label class="form-label"><?= __('Your Rating (1 to 5 Stars)') ?></label>
                                <select name="rating" class="form-select w-auto" required>
                                    <option value="5">★★★★★ (5 - Excellent)</option>
                                    <option value="4">★★★★☆ (4 - Very Good)</option>
                                    <option value="3">★★★☆☆ (3 - Average)</option>
                                    <option value="2">★★☆☆☆ (2 - Poor)</option>
                                    <option value="1">★☆☆☆☆ (1 - Very Bad)</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label"><?= __('Your Feedback') ?></label>
                                <textarea name="review_text" rows="3" class="form-control" placeholder="<?= __('Describe your experience with this provider...') ?>" required></textarea>
                            </div>
                            <button type="submit" class="btn btn-primary rounded-pill px-4"><?= __('Submit Review') ?></button>
                        </form>
                    </div>
                </div>
            <?php endif; ?>

            <!-- List of Reviews -->
            <?php if (!empty($reviews)): ?>
                <div class="d-flex flex-column gap-3">
                    <?php foreach ($reviews as $rev): ?>
                        <div class="p-3 rounded-3 bg-body-tertiary border">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <div class="d-flex align-items-center gap-2">
                                    <div class="avatar-mini-initials bg-primary-subtle text-primary">
                                        <?php echo strtoupper(substr($rev['client_name'] ?? 'C', 0, 1)); ?>
                                    </div>
                                    <span class="fw-bold"><?php echo e($rev['client_name'] ?? __('Verified Peer')); ?></span>
                                </div>
                                <div class="text-warning small">
                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                        <i class="bi <?php echo $i <= $rev['rating'] ? 'bi-star-fill' : 'bi-star'; ?>"></i>
                                    <?php endfor; ?>
                                </div>
                            </div>
                            <p class="mb-1 text-secondary"><?php echo e($rev['review_text']); ?></p>
                            <small class="text-muted" style="font-size: 0.75rem;">
                                <?php echo date('M d, Y', strtotime($rev['created_at'])); ?>
                            </small>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <p class="text-muted mb-0"><?= __('No reviews yet for this service. Be the first to try it and leave feedback!') ?></p>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php if ($hasShowcaseImage): ?>
<!-- Fullscreen / Zoom Modal for Showcase Image -->
<div class="modal fade" id="showcaseImageModal" tabindex="-1" aria-labelledby="showcaseImageModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content border-0 rounded-4 shadow-lg overflow-hidden">
            <div class="modal-header border-0 pb-0 pt-3 px-4 d-flex justify-content-between align-items-center">
                <h6 class="modal-title fw-bold mb-0 text-truncate pe-3" id="showcaseImageModalLabel">
                    <i class="bi bi-image text-primary me-2"></i><?php echo e($service['title']); ?>
                </h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-3 p-md-4 text-center">
                <img src="/cshub/uploads/portfolio/<?php echo e($service['portfolio_image']); ?>" 
                     alt="<?php echo e($service['title']); ?>" 
                     class="img-fluid rounded-3 shadow-sm"
                     style="max-height: 85vh; width: auto; object-fit: contain;">
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

