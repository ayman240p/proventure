<?php
/**
 * client/dashboard.php
 * Client Account Dashboard: Profile overview, submitted reviews history,
 * recommended micro-services, and account settings.
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

requireRole('client');

$clientId = (int) $_SESSION['user_id'];
$success = '';
$error = '';

// 1. Handle Profile Update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'update_profile') {
    $name = trim($_POST['name'] ?? '');
    $phone = trim($_POST['phone'] ?? '');

    if (empty($name)) {
        $error = 'Your full name is required.';
    } elseif (empty($phone)) {
        $error = 'WhatsApp/Contact phone number is required.';
    } else {
        try {
            $stmt = $pdo->prepare("UPDATE users SET name = ?, phone = ? WHERE id = ?");
            $stmt->execute([$name, $phone, $clientId]);
            $_SESSION['user_name'] = $name;
            $success = 'Profile updated successfully!';
        } catch (PDOException $e) {
            $error = 'Failed to update profile. Please try again.';
        }
    }
}

// 2. Handle Password Change
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'change_password') {
    $currentPassword = $_POST['current_password'] ?? '';
    $newPassword = $_POST['new_password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';

    if (empty($currentPassword) || empty($newPassword) || empty($confirmPassword)) {
        $error = 'All password fields are required.';
    } elseif ($newPassword !== $confirmPassword) {
        $error = 'New passwords do not match.';
    } elseif (strlen($newPassword) < 6) {
        $error = 'New password must be at least 6 characters long.';
    } else {
        $stmt = $pdo->prepare("SELECT password FROM users WHERE id = ?");
        $stmt->execute([$clientId]);
        $user = $stmt->fetch();

        if ($user && password_verify($currentPassword, $user['password'])) {
            $hashed = password_hash($newPassword, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
            $stmt->execute([$hashed, $clientId]);
            $success = 'Password changed successfully!';
        } else {
            $error = 'Incorrect current password.';
        }
    }
}

// 3. Handle Review Deletion
if (isset($_GET['delete_review']) && is_numeric($_GET['delete_review'])) {
    $reviewId = (int) $_GET['delete_review'];
    try {
        $stmt = $pdo->prepare("DELETE FROM reviews WHERE id = ? AND client_id = ?");
        $stmt->execute([$reviewId, $clientId]);
        if ($stmt->rowCount() > 0) {
            $success = 'Review deleted successfully.';
        }
    } catch (PDOException $e) {
        $error = 'Failed to delete review.';
    }
}

// 4. Fetch Client Profile Information
$stmt = $pdo->prepare("SELECT name, email, phone, role, created_at FROM users WHERE id = ?");
$stmt->execute([$clientId]);
$clientUser = $stmt->fetch(PDO::FETCH_ASSOC);

// 5. Fetch Client's Submitted Reviews
$stmt = $pdo->prepare("
    SELECT r.id, r.rating, r.review_text, r.created_at,
           s.id AS service_id, s.title AS service_title, s.price,
           u.name AS provider_name, u.phone AS provider_phone,
           c.name AS category_name
    FROM reviews r
    JOIN services s ON r.service_id = s.id
    JOIN users u ON s.user_id = u.id
    LEFT JOIN categories c ON s.category_id = c.id
    WHERE r.client_id = ?
    ORDER BY r.created_at DESC
");
$stmt->execute([$clientId]);
$clientReviews = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Stats
$totalReviews = count($clientReviews);
$avgRatingGiven = 0;
if ($totalReviews > 0) {
    $sum = 0;
    foreach ($clientReviews as $rev) {
        $sum += (int) $rev['rating'];
    }
    $avgRatingGiven = round($sum / $totalReviews, 1);
}

// 6. Fetch Recommended Services (Top rated / approved)
$recommendedServices = $pdo->query("
    SELECT s.id, s.title, s.price, s.description, s.availability,
           c.name AS category_name, u.name AS provider_name, u.phone AS provider_phone,
           COALESCE(AVG(r.rating), 0) AS avg_rating, COUNT(r.id) AS review_count
    FROM services s
    JOIN users u ON s.user_id = u.id
    LEFT JOIN categories c ON s.category_id = c.id
    LEFT JOIN reviews r ON s.id = r.service_id
    WHERE s.status = 'approved' AND s.availability = 1
    GROUP BY s.id
    ORDER BY avg_rating DESC, s.created_at DESC
    LIMIT 4
")->fetchAll(PDO::FETCH_ASSOC);

require_once __DIR__ . '/../includes/header.php';
?>

<div class="container py-4">
    <!-- Welcome Banner Header -->
    <div class="card border-0 rounded-4 shadow-sm p-4 mb-4 client-welcome-card">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
            <div class="d-flex align-items-center gap-3">
                <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center shadow-sm" style="width: 60px; height: 60px; font-size: 1.6rem;">
                    <i class="bi bi-person-circle"></i>
                </div>
                <div>
                    <div class="d-flex align-items-center gap-2">
                        <h2 class="h4 fw-bold mb-0"><?php echo e($clientUser['name']); ?></h2>
                        <span class="badge bg-primary-subtle text-primary rounded-pill px-3 py-1 small">Client Account</span>
                    </div>
                    <div class="text-muted small mt-1">
                        <i class="bi bi-envelope me-1"></i> <?php echo e($clientUser['email']); ?> &middot;
                        <i class="bi bi-whatsapp me-1 text-success"></i> <?php echo e($clientUser['phone']); ?> &middot;
                        <i class="bi bi-calendar3 me-1"></i> Member since <?php echo date('M Y', strtotime($clientUser['created_at'])); ?>
                    </div>
                </div>
            </div>
            <div class="d-flex gap-2">
                <a href="/cshub/index.php" class="btn btn-primary btn-sm rounded-pill px-3 fw-semibold">
                    <i class="bi bi-search me-1"></i> Browse Services
                </a>
            </div>
        </div>
    </div>

    <!-- Alert Messages -->
    <?php if ($success): ?>
        <div class="alert alert-success alert-dismissible fade show rounded-4 shadow-sm mb-4" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i> <?php echo e($success); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div class="alert alert-danger alert-dismissible fade show rounded-4 shadow-sm mb-4" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2"></i> <?php echo e($error); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <!-- Metric Overview Cards -->
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-4 p-3 h-100">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted small">Reviews Submitted</span>
                        <h3 class="fw-bold mb-0 mt-1"><?php echo $totalReviews; ?></h3>
                        <span class="text-muted small">Feedback left for local providers</span>
                    </div>
                    <div class="bg-warning bg-opacity-10 text-warning p-3 rounded-circle fs-4">
                        <i class="bi bi-star-fill"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-4 p-3 h-100">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted small">Avg Rating Given</span>
                        <h3 class="fw-bold mb-0 mt-1 text-primary"><?php echo $avgRatingGiven > 0 ? $avgRatingGiven . ' / 5.0' : 'N/A'; ?></h3>
                        <span class="text-muted small">Community star rating score</span>
                    </div>
                    <div class="bg-primary bg-opacity-10 text-primary p-3 rounded-circle fs-4">
                        <i class="bi bi-award-fill"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-4 p-3 h-100">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted small">Available Services</span>
                        <h3 class="fw-bold mb-0 mt-1 text-success">
                            <?php echo count($recommendedServices); ?>+
                        </h3>
                        <span class="text-muted small">Ready for instant WhatsApp booking</span>
                    </div>
                    <div class="bg-success bg-opacity-10 text-success p-3 rounded-circle fs-4">
                        <i class="bi bi-briefcase-fill"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Navigation Tabs -->
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4">
        <div class="card-header border-bottom p-3" style="background: transparent;">
            <ul class="nav nav-pills card-header-pills gap-2" id="clientTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active rounded-pill px-4 fw-semibold" id="reviews-tab" data-bs-toggle="tab" data-bs-target="#reviewsPane" type="button" role="tab">
                        <i class="bi bi-chat-square-quote-fill me-1"></i> My Reviews (<?php echo $totalReviews; ?>)
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link rounded-pill px-4 fw-semibold" id="recommended-tab" data-bs-toggle="tab" data-bs-target="#recommendedPane" type="button" role="tab">
                        <i class="bi bi-stars me-1"></i> Recommended Services
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link rounded-pill px-4 fw-semibold" id="profile-tab" data-bs-toggle="tab" data-bs-target="#profilePane" type="button" role="tab">
                        <i class="bi bi-gear-fill me-1"></i> Account Settings
                    </button>
                </li>
            </ul>
        </div>

        <div class="card-body p-4">
            <div class="tab-content" id="clientTabsContent">
                
                <!-- Tab 1: My Reviews History -->
                <div class="tab-pane fade show active" id="reviewsPane" role="tabpanel">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="fw-bold mb-0">Your Review History</h5>
                        <span class="text-muted small">Ratings &amp; feedback you've left for freelancers</span>
                    </div>

                    <?php if (empty($clientReviews)): ?>
                        <div class="text-center py-5 text-muted">
                            <div class="py-3">
                                <i class="bi bi-chat-square-text fs-1 d-block mb-2 text-muted"></i>
                                <h6 class="fw-bold">You have not submitted any reviews yet</h6>
                                <p class="small mb-3 text-muted">Once you connect with a freelancer and complete a service, you can leave a star rating and written review on their service page.</p>
                                <a href="/cshub/index.php" class="btn btn-primary btn-sm rounded-pill px-4">
                                    <i class="bi bi-search me-1"></i> Explore Services to Review
                                </a>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="row g-3">
                            <?php foreach ($clientReviews as $review): ?>
                                <div class="col-12">
                                    <div class="card border rounded-4 p-3 shadow-none client-subcard">
                                        <div class="d-flex justify-content-between align-items-start flex-wrap gap-2 mb-2">
                                            <div>
                                                <a href="/cshub/service.php?id=<?php echo (int) $review['service_id']; ?>" class="fw-bold text-decoration-none client-service-title fs-6 d-block">
                                                    <?php echo e($review['service_title']); ?>
                                                </a>
                                                <div class="small text-muted mt-1">
                                                    <span class="badge border me-2 client-category-badge"><?php echo e($review['category_name'] ?? 'General'); ?></span>
                                                    Provider: <strong><?php echo e($review['provider_name']); ?></strong> &middot;
                                                    <?php echo formatPrice((float) $review['price']); ?>
                                                </div>
                                            </div>
                                            <div class="d-flex align-items-center gap-2">
                                                <!-- Stars -->
                                                <div class="text-warning">
                                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                                        <i class="bi bi-star-fill <?php echo $i <= (int)$review['rating'] ? 'text-warning' : 'text-muted opacity-25'; ?>"></i>
                                                    <?php endfor; ?>
                                                </div>
                                                <span class="small text-muted ps-1"><?php echo date('M d, Y', strtotime($review['created_at'])); ?></span>
                                            </div>
                                        </div>

                                        <!-- Review Text -->
                                        <div class="p-3 rounded-3 border mb-2 small client-quote-box">
                                            "<?php echo nl2br(e($review['review_text'])); ?>"
                                        </div>

                                        <!-- Actions -->
                                        <div class="d-flex justify-content-between align-items-center pt-2">
                                            <?php if (!empty($review['provider_phone'])): ?>
                                                <a href="https://wa.me/<?php echo preg_replace('/\D/', '', $review['provider_phone']); ?>?text=Hi%20<?php echo urlencode($review['provider_name']); ?>%2C%20contacting%20you%20again%20via%20ProVenture" target="_blank" class="btn btn-sm btn-outline-success rounded-pill px-3">
                                                    <i class="bi bi-whatsapp me-1"></i> Contact Provider Again
                                                </a>
                                            <?php else: ?>
                                                <span></span>
                                            <?php endif; ?>

                                            <div class="d-flex gap-2">
                                                <a href="/cshub/service.php?id=<?php echo (int) $review['service_id']; ?>" class="btn btn-sm btn-outline-primary rounded-pill px-3">
                                                    <i class="bi bi-arrow-right me-1"></i> View Service Page
                                                </a>
                                                <a href="/cshub/client/dashboard.php?delete_review=<?php echo (int) $review['id']; ?>" class="btn btn-sm btn-outline-danger rounded-pill px-2" title="Delete Review" onclick="return confirm('Are you sure you want to delete this review?');">
                                                    <i class="bi bi-trash"></i>
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Tab 2: Recommended Services -->
                <div class="tab-pane fade" id="recommendedPane" role="tabpanel">
                    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
                        <div>
                            <h5 class="fw-bold mb-1">Top-Rated Micro-Services</h5>
                            <p class="text-muted small mb-0">High-rated community freelancers currently available for hire</p>
                        </div>
                        <a href="/cshub/index.php" class="btn btn-outline-primary btn-sm rounded-pill px-3">
                            View All Categories <i class="bi bi-arrow-right"></i>
                        </a>
                    </div>

                    <div class="row g-3">
                        <?php foreach ($recommendedServices as $rec): ?>
                            <div class="col-md-6">
                                <div class="card h-100 border rounded-4 p-3 shadow-none client-subcard d-flex flex-column">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <span class="badge border rounded-pill client-category-badge">
                                            <?php echo e($rec['category_name'] ?? 'Service'); ?>
                                        </span>
                                        <div class="small text-warning">
                                            <i class="bi bi-star-fill"></i>
                                            <span class="fw-bold"><?php echo round((float)$rec['avg_rating'], 1); ?></span>
                                            <span class="text-muted small">(<?php echo (int)$rec['review_count']; ?>)</span>
                                        </div>
                                    </div>
                                    <h6 class="fw-bold mb-2">
                                        <a href="/cshub/service.php?id=<?php echo (int)$rec['id']; ?>" class="text-decoration-none client-service-title">
                                            <?php echo e($rec['title']); ?>
                                        </a>
                                    </h6>
                                    <p class="text-muted small flex-grow-1 mb-3">
                                        <?php echo e(mb_strimwidth($rec['description'], 0, 90, '...')); ?>
                                    </p>
                                    <div class="d-flex justify-content-between align-items-center pt-2 border-top mt-auto">
                                        <div>
                                            <span class="small text-muted d-block" style="font-size:0.75rem;">By <?php echo e($rec['provider_name']); ?></span>
                                            <span class="fw-bold text-primary fs-5"><?php echo formatPrice((float)$rec['price']); ?></span>
                                        </div>
                                        <div class="d-flex gap-2">
                                            <?php if (!empty($rec['provider_phone'])): ?>
                                                <a href="https://wa.me/<?php echo preg_replace('/\D/', '', $rec['provider_phone']); ?>?text=Hi%2C%20I%20found%20your%20service%20on%20ProVenture" target="_blank" class="btn btn-sm btn-success rounded-pill px-3">
                                                    <i class="bi bi-whatsapp"></i>
                                                </a>
                                            <?php endif; ?>
                                            <a href="/cshub/service.php?id=<?php echo (int)$rec['id']; ?>" class="btn btn-sm btn-outline-primary rounded-pill px-3">
                                                Details
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Tab 3: Profile & Password Settings -->
                <div class="tab-pane fade" id="profilePane" role="tabpanel">
                    <div class="row g-4">
                        <!-- Profile Details Form -->
                        <div class="col-lg-6">
                            <div class="card border rounded-4 p-4 shadow-none client-subcard h-100">
                                <h6 class="fw-bold mb-3"><i class="bi bi-person-fill me-1 text-primary"></i> Personal Details</h6>
                                <form method="post" action="/cshub/client/dashboard.php">
                                    <input type="hidden" name="action" value="update_profile">
                                    <div class="mb-3">
                                        <label class="form-label small fw-bold">Full Name</label>
                                        <input type="text" name="name" class="form-control rounded-3" value="<?php echo e($clientUser['name']); ?>" required>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label small fw-bold">Email Address</label>
                                        <input type="email" class="form-control rounded-3" value="<?php echo e($clientUser['email']); ?>" disabled>
                                        <div class="form-text small">Email cannot be changed directly.</div>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label small fw-bold">WhatsApp / Contact Phone</label>
                                        <input type="text" name="phone" class="form-control rounded-3" value="<?php echo e($clientUser['phone']); ?>" required>
                                        <div class="form-text small">Used for coordinating with freelancers.</div>
                                    </div>
                                    <button type="submit" class="btn btn-primary rounded-pill px-4 fw-semibold">
                                        <i class="bi bi-check2 me-1"></i> Save Profile Details
                                    </button>
                                </form>
                            </div>
                        </div>

                        <!-- Change Password Form -->
                        <div class="col-lg-6">
                            <div class="card border rounded-4 p-4 shadow-none client-subcard h-100">
                                <h6 class="fw-bold mb-3"><i class="bi bi-key-fill me-1 text-primary"></i> Security &amp; Password</h6>
                                <form method="post" action="/cshub/client/dashboard.php">
                                    <input type="hidden" name="action" value="change_password">
                                    <div class="mb-3">
                                        <label class="form-label small fw-bold">Current Password</label>
                                        <input type="password" name="current_password" class="form-control rounded-3" placeholder="Enter current password" required>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label small fw-bold">New Password</label>
                                        <input type="password" name="new_password" class="form-control rounded-3" placeholder="Minimum 6 characters" minlength="6" required>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label small fw-bold">Confirm New Password</label>
                                        <input type="password" name="confirm_password" class="form-control rounded-3" placeholder="Re-type new password" minlength="6" required>
                                    </div>
                                    <button type="submit" class="btn btn-outline-primary rounded-pill px-4 fw-semibold">
                                        <i class="bi bi-shield-lock me-1"></i> Update Password
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
