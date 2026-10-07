<?php
/**
 * search.php
 * Handles the search/filter form submitted from index.php.
 * TODO (Objective 1 / Section 15): build the full parameterized query
 * combining keyword, category, and price range filters.
 */

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';

$q = trim($_GET['q'] ?? '');
$category = $_GET['category'] ?? '';
$minPrice = $_GET['min_price'] ?? '';
$maxPrice = $_GET['max_price'] ?? '';

$where = ["services.status = 'approved'"];
$params = [];

if (!empty($q)) {
    $where[] = "(services.title LIKE ? OR services.description LIKE ? OR categories.name LIKE ?)";
    $params[] = "%{$q}%";
    $params[] = "%{$q}%";
    $params[] = "%{$q}%";
}

if (!empty($category)) {
    $where[] = "services.category_id = ?";
    $params[] = $category;
}

if (!empty($minPrice)) {
    $where[] = "services.price >= ?";
    $params[] = (float)$minPrice;
}

if (!empty($maxPrice)) {
    $where[] = "services.price <= ?";
    $params[] = (float)$maxPrice;
}

$sql = "SELECT services.*, categories.name AS category_name, users.name AS provider_name
        FROM services
        LEFT JOIN categories ON services.category_id = categories.id
        LEFT JOIN users ON services.user_id = users.id
        WHERE " . implode(' AND ', $where) . "
        ORDER BY services.availability DESC, services.created_at DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$services = $stmt->fetchAll();

require_once __DIR__ . '/includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <h1 class="h3 fw-bold mb-1"><?= __('Search Results') ?></h1>
        <?php if (!empty($q)): ?>
            <p class="text-muted mb-0"><?= __('Matching results for:') ?> "<strong><?php echo e($q); ?></strong>" (<?php echo count($services); ?> <?= __('found') ?>)</p>
        <?php else: ?>
            <p class="text-muted mb-0"><?php echo count($services); ?> <?= __('services available') ?></p>
        <?php endif; ?>
    </div>
    <a href="/cshub/index.php" class="btn btn-outline-secondary rounded-pill px-3">
        <i class="bi bi-arrow-left me-1"></i><?= __('Back to Home') ?>
    </a>
</div>

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

<div class="row g-4 mb-4">
    <?php if (empty($services)): ?>
        <div class="col-12">
            <div class="alert alert-info rounded-4 p-4 text-center">
                <i class="bi bi-search fs-1 text-primary d-block mb-2"></i>
                <h5 class="fw-bold"><?= __('No services listed yet') ?></h5>
                <p class="text-muted mb-3"><?= __('Try different keywords or browse all categories.') ?></p>
                <a href="/cshub/index.php" class="btn btn-primary rounded-pill px-4"><?= __('Browse All Services') ?></a>
            </div>
        </div>
    <?php endif; ?>

    <?php foreach ($services as $service): ?>
        <div class="col-md-4">
            <div class="card h-100 shadow-sm service-card border-0 rounded-4">
                <div class="card-body p-4 d-flex flex-column">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <span class="badge bg-light text-dark border rounded-pill px-3 py-1 small fw-semibold">
                            <?php echo e($service['category_name'] ?? __('Uncategorized')); ?>
                        </span>
                        <?php if (!empty($service['availability'])): ?>
                            <span class="badge bg-success-subtle text-success rounded-pill px-2 py-1 small">
                                <i class="bi bi-check-circle-fill me-1"></i><?= __('Available') ?>
                            </span>
                        <?php else: ?>
                            <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle rounded-pill px-2 py-1 small">
                                <i class="bi bi-slash-circle me-1"></i><?= __('Busy / Unavailable') ?>
                            </span>
                        <?php endif; ?>
                    </div>
                    <h5 class="card-title fw-bold mb-2"><?php echo e($service['title']); ?></h5>
                    <p class="text-muted small mb-3 flex-grow-1"><?php echo e(mb_strimwidth($service['description'] ?? '', 0, 95, '...')); ?></p>
                    <div class="d-flex align-items-center justify-content-between pt-3 border-top mt-auto">
                        <div>
                            <span class="small text-muted d-block" style="font-size:0.72rem;"><?= __('Provider:') ?> <?php echo e($service['provider_name'] ?? __('Unknown')); ?></span>
                            <span class="fw-bold text-primary fs-5"><?php echo formatPrice((float) $service['price']); ?></span>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <button type="button" class="btn btn-sm btn-outline-danger rounded-circle p-0 d-flex align-items-center justify-content-center"
                                    style="width: 32px; height: 32px;"
                                    data-bs-toggle="modal" data-bs-target="#reportServiceModal"
                                    data-service-id="<?php echo (int) $service['id']; ?>"
                                    data-service-title="<?php echo e($service['title']); ?>"
                                    title="<?= __('Report this listing') ?>">
                                <i class="bi bi-flag-fill" style="font-size: 0.75rem;"></i>
                            </button>
                            <a href="/cshub/service.php?id=<?php echo (int) $service['id']; ?>" class="btn btn-sm <?php echo !empty($service['availability']) ? 'btn-outline-primary' : 'btn-outline-secondary'; ?> rounded-pill px-3">
                                <?= __('View Details') ?>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
