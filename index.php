<?php
/**
 * index.php
 * Landing page for non-logged-in users
 * Service browsing page for logged-in users
 */

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';

// If user is logged in, show the browse services page
if (isLoggedIn()) {
    // Get all categories for filter
    $categories = $pdo->query("SELECT id, name FROM categories ORDER BY name")->fetchAll();

    // Get services with basic filtering
    $services = $pdo->query("
        SELECT services.id, services.title, services.price, services.availability,
               categories.name AS category_name,
               users.name AS provider_name
        FROM services
        LEFT JOIN categories ON services.category_id = categories.id
        LEFT JOIN users ON services.user_id = users.id
        WHERE services.status = 'approved'
        ORDER BY services.created_at DESC
        LIMIT 20
    ")->fetchAll();

    require_once __DIR__ . '/includes/header.php';
    ?>

    <h1 class="h3 mb-4"><?= __('Browse Local Micro-Services') ?></h1>

    <form method="get" action="/cshub/search.php" class="row g-2 mb-4">
        <div class="col-md-4">
            <input type="text" name="q" class="form-control" placeholder="<?= __('Search services...') ?>">
        </div>
        <div class="col-md-3">
            <select name="category" class="form-select">
                <option value=""><?= __('All Categories') ?></option>
                <?php foreach ($categories as $cat): ?>
                    <option value="<?php echo $cat['id']; ?>"><?php echo e($cat['name']); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-2">
            <input type="number" name="min_price" class="form-control" placeholder="<?= __('Min Price') ?>">
        </div>
        <div class="col-md-2">
            <input type="number" name="max_price" class="form-control" placeholder="<?= __('Max Price') ?>">
        </div>
        <div class="col-md-1">
            <button type="submit" class="btn btn-primary w-100"><?= __('Search') ?></button>
        </div>
    </form>

    <div class="row g-3">
        <?php if (empty($services)): ?>
            <div class="col-12">
                <div class="alert alert-info">
                    <h5><?= __('No services listed yet') ?></h5>
                    <p class="mb-0"><?= __('Be the first to offer your services on ProVenture!') ?></p>
                </div>
            </div>
        <?php endif; ?>

        <?php foreach ($services as $service): ?>
            <div class="col-md-4">
                <div class="card h-100 shadow-sm service-card">
                    <div class="card-body">
                        <h5 class="card-title"><?php echo e($service['title']); ?></h5>
                        <p class="text-muted mb-1"><?php echo e($service['category_name'] ?? __('Uncategorized')); ?></p>
                        <p class="fw-bold text-primary"><?php echo formatPrice((float) $service['price']); ?></p>
                        <p class="small mb-2"><?= __('Provider:') ?> <?php echo e($service['provider_name'] ?? __('Unknown')); ?></p>
                        <p class="mb-3">
                            <?php if ($service['availability']): ?>
                                <span class="badge bg-success"><?= __('Available') ?></span>
                            <?php else: ?>
                                <span class="badge bg-secondary"><?= __('Busy') ?></span>
                            <?php endif; ?>
                        </p>
                        <a href="/cshub/service.php?id=<?php echo (int) $service['id']; ?>" class="btn btn-sm btn-outline-primary"><?= __('View Details') ?></a>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <?php require_once __DIR__ . '/includes/footer.php'; ?>

<?php
    exit; // Stop here for logged-in users
}

// For non-logged-in users, show the landing page
$stats = $pdo->query("
    SELECT
        (SELECT COUNT(*) FROM services WHERE availability = 1 AND status = 'approved') as active_services,
        (SELECT COUNT(*) FROM users WHERE role = 'freelancer') as total_freelancers,
        (SELECT COUNT(*) FROM categories) as total_categories
")->fetch();

// Fetch featured services for guest preview
$featuredServices = $pdo->query("
    SELECT services.id, services.title, services.description, services.price, services.availability,
           categories.name AS category_name,
           users.name AS provider_name
    FROM services
    LEFT JOIN categories ON services.category_id = categories.id
    LEFT JOIN users ON services.user_id = users.id
    WHERE services.availability = 1 AND services.status = 'approved'
    ORDER BY services.created_at DESC
    LIMIT 3
")->fetchAll();

// Popular categories with service count
$categories = $pdo->query("
    SELECT c.id, c.name, c.description, COUNT(s.id) as service_count
    FROM categories c
    LEFT JOIN services s ON c.id = s.category_id AND s.status = 'approved'
    GROUP BY c.id, c.name, c.description
    ORDER BY service_count DESC, c.name ASC
    LIMIT 6
")->fetchAll();

$categoryIcons = [
    'Academic Tutoring' => ['icon' => 'bi-mortarboard', 'emoji' => '📚', 'color' => '#3b82f6', 'bg' => 'rgba(59, 130, 246, 0.1)'],
    'Graphic Design'    => ['icon' => 'bi-palette', 'emoji' => '🎨', 'color' => '#8b5cf6', 'bg' => 'rgba(139, 92, 246, 0.1)'],
    'Gadget Repair'     => ['icon' => 'bi-tools', 'emoji' => '🔧', 'color' => '#f97316', 'bg' => 'rgba(249, 115, 22, 0.1)'],
    'Delivery / Runner' => ['icon' => 'bi-bicycle', 'emoji' => '🚚', 'color' => '#10b981', 'bg' => 'rgba(16, 185, 129, 0.1)'],
    'Photography'       => ['icon' => 'bi-camera', 'emoji' => '📸', 'color' => '#ec4899', 'bg' => 'rgba(236, 72, 153, 0.1)'],
    'Computer Services' => ['icon' => 'bi-laptop', 'emoji' => '💻', 'color' => '#06b6d4', 'bg' => 'rgba(6, 182, 212, 0.1)'],
    'Other'             => ['icon' => 'bi-stars', 'emoji' => '✨', 'color' => '#128c7e', 'bg' => 'rgba(18, 140, 126, 0.1)']
];

$mainContainerClass = 'landing-page-wrap p-0';
require_once __DIR__ . '/includes/header.php';
?>

<!-- 1. Hero Section -->
<section class="landing-hero position-relative">
    <div class="hero-ambient-glow hero-ambient-glow-1"></div>
    <div class="hero-ambient-glow hero-ambient-glow-2"></div>

    <div class="container position-relative z-1 py-lg-5 py-4">
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
        <div class="row align-items-center gy-5">
            <!-- Hero Left Column: Copy & Search -->
            <div class="col-lg-6">
                <!-- Pill Badge -->
                <div class="hero-badge mb-3">
                    <span class="badge-pulse-dot"></span>
                    <span class="small fw-semibold"><?= __('The #1 Campus & Local Community Talent Hub') ?></span>
                </div>

                <!-- Main Headline -->
                <h1 class="hero-title display-4 fw-bold mb-3">
                    <?= __('Connect with Local Talent,') ?>
                    <span class="gradient-text"><?= __('Instantly') ?></span>
                </h1>

                <!-- Subheadline -->
                <p class="hero-lead lead mb-4">
                    <?= __('hero_desc') ?>
                </p>

                <!-- Search Box Bar -->
                <div class="hero-search-card shadow-lg mb-4">
                    <form method="get" action="/cshub/search.php" class="d-flex flex-column flex-sm-row gap-2">
                        <div class="input-group search-input-group flex-grow-1">
                            <span class="input-group-text bg-transparent border-0 text-muted ps-3 pe-2">
                                <i class="bi bi-search fs-5"></i>
                            </span>
                            <input type="text" name="q" class="form-control border-0 hero-search-input" placeholder="<?= __('Search services (e.g. Calculus, Logo Design, Phone Repair)...') ?>" aria-label="<?= __('Search') ?>">
                        </div>
                        <button type="submit" class="btn btn-primary hero-search-btn text-nowrap px-4 py-2">
                            <span><?= __('Search') ?></span>
                            <i class="bi bi-arrow-right ms-1"></i>
                        </button>
                    </form>

                    <!-- Trending Search Tags -->
                    <div class="hero-trending d-flex flex-wrap align-items-center gap-2 pt-2 px-1 border-top border-light-subtle">
                        <span class="small text-muted fw-semibold d-flex align-items-center">
                            <i class="bi bi-fire text-danger me-1"></i><?= __('Popular:') ?>
                        </span>
                        <a href="/cshub/search.php?q=Tutoring" class="badge-pill-link"><?= __('Tutoring') ?></a>
                        <a href="/cshub/search.php?q=Design" class="badge-pill-link"><?= __('Graphic Design') ?></a>
                        <a href="/cshub/search.php?q=Repair" class="badge-pill-link"><?= __('Gadget Repair') ?></a>
                        <a href="/cshub/search.php?q=Runner" class="badge-pill-link"><?= __('Runner') ?></a>
                    </div>
                </div>

                <!-- CTA Action Buttons -->
                <div class="d-flex gap-3 flex-wrap align-items-center mb-4">
                    <a href="/cshub/register.php" class="btn btn-primary btn-lg hero-cta-btn px-4 shadow-sm">
                        <i class="bi bi-person-plus-fill me-2"></i><?= __('Get Started Free') ?>
                    </a>
                    <a href="/cshub/login.php" class="btn btn-outline-secondary btn-lg px-4 hero-login-btn">
                        <i class="bi bi-box-arrow-in-right me-2"></i><?= __('Login') ?>
                    </a>
                </div>

                <!-- Trust Points -->
                <div class="hero-guarantees d-flex flex-wrap gap-4 text-muted small">
                    <span class="d-flex align-items-center gap-2">
                        <i class="bi bi-patch-check-fill text-success fs-6"></i>
                        <span><?= __('100% Free to Join') ?></span>
                    </span>
                    <span class="d-flex align-items-center gap-2">
                        <i class="bi bi-whatsapp text-success fs-6"></i>
                        <span><?= __('Direct WhatsApp Chat') ?></span>
                    </span>
                    <span class="d-flex align-items-center gap-2">
                        <i class="bi bi-shield-fill-check text-primary fs-6"></i>
                        <span><?= __('Verified Peers') ?></span>
                    </span>
                </div>
            </div>

            <!-- Hero Right Column: Interactive Glassmorphic Talent Showcase Matrix -->
            <div class="col-lg-6">
                <div class="hero-showcase-matrix position-relative mx-auto">
                    <!-- Ambient Glow Behind Cards -->
                    <div class="showcase-glow"></div>

                    <!-- Main Showcase Card -->
                    <div class="talent-card-main glass-card shadow-lg">
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <div class="d-flex align-items-center gap-3">
                                <div class="avatar-circle-gradient">
                                    <span>AK</span>
                                </div>
                                <div>
                                    <h6 class="mb-0 fw-bold d-flex align-items-center gap-1">
                                        Ahmad Kamil
                                        <i class="bi bi-patch-check-fill text-primary" title="<?= __('Verified Student / Provider') ?>"></i>
                                    </h6>
                                    <span class="text-muted small">CS Undergrad • Peer Tutor</span>
                                </div>
                            </div>
                            <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1 rounded-pill small">
                                <span class="badge-pulse-dot me-1"></span><?= __('Available') ?>
                            </span>
                        </div>

                        <div class="service-preview-box p-3 rounded-4 mb-3">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <span class="badge bg-primary-subtle text-primary fw-semibold px-2 py-1 rounded-pill">
                                    <i class="bi bi-mortarboard me-1"></i><?= __('Academic Tutoring') ?>
                                </span>
                                <span class="fw-bold text-primary fs-5">RM 25.00<small class="text-muted fw-normal" style="font-size:0.75rem;">/hr</small></span>
                            </div>
                            <h6 class="fw-bold mb-1">Calculus & Python Programming 1-on-1 Coaching</h6>
                            <p class="text-muted small mb-0 line-clamp-2">Patient explanations, past-year exam revisions, and assignment troubleshooting on campus or online.</p>
                        </div>

                        <div class="d-flex align-items-center justify-content-between pt-1">
                            <div class="d-flex align-items-center gap-1 text-warning small fw-bold">
                                <i class="bi bi-star-fill"></i>
                                <i class="bi bi-star-fill"></i>
                                <i class="bi bi-star-fill"></i>
                                <i class="bi bi-star-fill"></i>
                                <i class="bi bi-star-fill"></i>
                                <span class="text-secondary ms-1 fw-medium">(48 reviews)</span>
                            </div>
                            <a href="/cshub/register.php" class="btn btn-success btn-sm rounded-pill px-3 py-1 fw-semibold d-flex align-items-center gap-1">
                                <i class="bi bi-whatsapp"></i>
                                <span><?= __('Chat on WhatsApp') ?></span>
                            </a>
                        </div>
                    </div>

                    <!-- Floating Pill 1: Response Time (Top Right) -->
                    <div class="floating-pill floating-pill-top glass-card shadow-sm">
                        <div class="floating-icon-box bg-warning-subtle text-warning">
                            <i class="bi bi-lightning-charge-fill"></i>
                        </div>
                        <div>
                            <div class="fw-bold small lh-1 mb-1"><?= __('Response time') ?></div>
                            <div class="text-muted" style="font-size: 0.75rem;">&lt; 15 mins</div>
                        </div>
                    </div>

                    <!-- Floating Pill 2: 0% Commission (Bottom Left) -->
                    <div class="floating-pill floating-pill-bottom glass-card shadow-sm">
                        <div class="floating-icon-box bg-success-subtle text-success">
                            <i class="bi bi-cash-stack"></i>
                        </div>
                        <div>
                            <div class="fw-bold small lh-1 mb-1"><?= __('Keep 100%') ?></div>
                            <div class="text-muted" style="font-size: 0.75rem;"><?= __('No middleman cuts') ?></div>
                        </div>
                    </div>

                    <!-- Floating Pill 3: Community Trust (Bottom Right) -->
                    <div class="floating-pill floating-pill-accent glass-card shadow-sm d-none d-sm-flex">
                        <div class="floating-avatar-stack me-2">
                            <span class="avatar-mini bg-primary text-white">S</span>
                            <span class="avatar-mini bg-success text-white">F</span>
                            <span class="avatar-mini bg-warning text-dark">R</span>
                        </div>
                        <div class="text-start">
                            <div class="fw-bold small lh-1 mb-1">150+ Community Peers</div>
                            <div class="text-muted" style="font-size: 0.72rem;">Active this semester</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- 2. Elevated Floating Stats Bar -->
<section class="stats-section position-relative z-2">
    <div class="container">
        <div class="stats-bar-card shadow-lg">
            <div class="row g-4 text-center text-md-start align-items-center">
                <div class="col-6 col-lg-3">
                    <div class="stat-card-inner d-flex align-items-center gap-3">
                        <div class="stat-icon-wrapper bg-primary-subtle text-primary">
                            <i class="bi bi-briefcase-fill"></i>
                        </div>
                        <div>
                            <h3 class="stat-number mb-0"><?php echo max((int)($stats['active_services'] ?? 0), 15); ?>+</h3>
                            <p class="stat-label mb-0 text-muted small"><?= __('Active Services') ?></p>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-lg-3">
                    <div class="stat-card-inner d-flex align-items-center gap-3">
                        <div class="stat-icon-wrapper bg-success-subtle text-success">
                            <i class="bi bi-people-fill"></i>
                        </div>
                        <div>
                            <h3 class="stat-number mb-0"><?php echo max((int)($stats['total_freelancers'] ?? 0), 10); ?>+</h3>
                            <p class="stat-label mb-0 text-muted small"><?= __('Skilled Providers') ?></p>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-lg-3">
                    <div class="stat-card-inner d-flex align-items-center gap-3">
                        <div class="stat-icon-wrapper bg-info-subtle text-info">
                            <i class="bi bi-grid-3x3-gap-fill"></i>
                        </div>
                        <div>
                            <h3 class="stat-number mb-0"><?php echo max((int)($stats['total_categories'] ?? 0), 7); ?>+</h3>
                            <p class="stat-label mb-0 text-muted small"><?= __('Diverse Categories') ?></p>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-lg-3">
                    <div class="stat-card-inner d-flex align-items-center gap-3">
                        <div class="stat-icon-wrapper bg-warning-subtle text-warning">
                            <i class="bi bi-whatsapp"></i>
                        </div>
                        <div>
                            <h3 class="stat-number mb-0">100%</h3>
                            <p class="stat-label mb-0 text-muted small"><?= __('Direct WhatsApp Inquiries') ?></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- 3. Featured Local Services Section -->
<section class="py-5 section-featured">
    <div class="container py-4">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-end mb-4">
            <div>
                <span class="badge bg-primary-subtle text-primary rounded-pill px-3 py-1 fw-semibold mb-2">
                    <i class="bi bi-stars me-1"></i><?= __('Featured Local Services') ?>
                </span>
                <h2 class="h1 fw-bold mb-1"><?= __('Explore top-rated skills and micro-services available today') ?></h2>
                <p class="text-muted mb-0"><?= __('Direct contact with verified community talent') ?></p>
            </div>
            <div class="mt-3 mt-md-0">
                <a href="/cshub/register.php" class="btn btn-outline-primary rounded-pill px-4">
                    <?= __('Explore All Services') ?> <i class="bi bi-arrow-right ms-1"></i>
                </a>
            </div>
        </div>

        <div class="row g-4">
            <?php if (!empty($featuredServices)): ?>
                <?php foreach ($featuredServices as $service): ?>
                    <div class="col-md-4">
                        <div class="service-card-modern h-100">
                            <div class="service-card-top p-4">
                                <div class="d-flex justify-content-between align-items-start mb-3">
                                    <span class="badge bg-light text-dark border px-3 py-1 rounded-pill small fw-semibold">
                                        <?php echo e($service['category_name'] ?? __('Uncategorized')); ?>
                                    </span>
                                    <span class="badge bg-success-subtle text-success rounded-pill px-2 py-1 small">
                                        <i class="bi bi-check-circle-fill me-1"></i><?= __('Available Now') ?>
                                    </span>
                                </div>
                                <h5 class="fw-bold mb-2 service-card-title"><?php echo e($service['title']); ?></h5>
                                <p class="text-muted small mb-0 service-card-desc"><?php echo e(mb_strimwidth($service['description'] ?? '', 0, 95, '...')); ?></p>
                            </div>

                            <div class="service-card-bottom p-4 border-top mt-auto">
                                <div class="d-flex align-items-center justify-content-between mb-3">
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="avatar-mini-initials">
                                            <?php echo strtoupper(substr($service['provider_name'] ?? 'U', 0, 1)); ?>
                                        </div>
                                        <div>
                                            <span class="small fw-semibold d-block text-truncate" style="max-width: 120px;"><?php echo e($service['provider_name'] ?? __('Unknown')); ?></span>
                                            <span class="text-muted" style="font-size: 0.72rem;"><?= __('Verified Peers') ?></span>
                                        </div>
                                    </div>
                                    <div class="text-end">
                                        <span class="text-muted small d-block" style="font-size: 0.72rem;"><?= __('Starting from') ?></span>
                                        <span class="fw-bold text-primary fs-5"><?php echo formatPrice((float)$service['price']); ?></span>
                                    </div>
                                </div>

                                <div class="d-flex align-items-center gap-2">
                                    <button type="button" class="btn btn-outline-danger rounded-pill p-2 d-flex align-items-center justify-content-center flex-shrink-0"
                                            style="width: 40px; height: 40px;"
                                            data-bs-toggle="modal" data-bs-target="#reportServiceModal"
                                            data-service-id="<?php echo (int)$service['id']; ?>"
                                            data-service-title="<?php echo e($service['title']); ?>"
                                            title="<?= __('Report this listing') ?>">
                                        <i class="bi bi-flag-fill"></i>
                                    </button>
                                    <a href="/cshub/service.php?id=<?php echo (int)$service['id']; ?>" class="btn btn-primary flex-grow-1 rounded-pill">
                                        <i class="bi bi-eye me-1"></i><?= __('View Details') ?>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <!-- Curated Showcase Cards if DB is completely fresh -->
                <div class="col-md-4">
                    <div class="service-card-modern h-100">
                        <div class="service-card-top p-4">
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-3 py-1 rounded-pill small fw-semibold">
                                    <?= __('Academic Tutoring') ?>
                                </span>
                                <span class="badge bg-success-subtle text-success rounded-pill px-2 py-1 small">
                                    <i class="bi bi-check-circle-fill me-1"></i><?= __('Available Now') ?>
                                </span>
                            </div>
                            <h5 class="fw-bold mb-2">Calculus & Discrete Mathematics 1-on-1</h5>
                            <p class="text-muted small mb-0">Help with lecture problem sets, exam revisions, and core computer science mathematics formulas.</p>
                        </div>
                        <div class="service-card-bottom p-4 border-top mt-auto">
                            <div class="d-flex align-items-center justify-content-between mb-3">
                                <div class="d-flex align-items-center gap-2">
                                    <div class="avatar-mini-initials">A</div>
                                    <div>
                                        <span class="small fw-semibold d-block">Adam F.</span>
                                        <span class="text-muted" style="font-size: 0.72rem;">Dean's List Peer</span>
                                    </div>
                                </div>
                                <div class="text-end">
                                    <span class="text-muted small d-block" style="font-size: 0.72rem;"><?= __('Starting from') ?></span>
                                    <span class="fw-bold text-primary fs-5">RM 25.00</span>
                                </div>
                            </div>
                            <a href="/cshub/register.php" class="btn btn-primary w-100 rounded-pill">
                                <i class="bi bi-eye me-1"></i><?= __('View Details') ?>
                            </a>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="service-card-modern h-100">
                        <div class="service-card-top p-4">
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <span class="badge bg-purple-subtle text-purple border border-purple-subtle px-3 py-1 rounded-pill small fw-semibold">
                                    <?= __('Graphic Design') ?>
                                </span>
                                <span class="badge bg-success-subtle text-success rounded-pill px-2 py-1 small">
                                    <i class="bi bi-check-circle-fill me-1"></i><?= __('Available Now') ?>
                                </span>
                            </div>
                            <h5 class="fw-bold mb-2">Poster Design, Slides & Event Branding</h5>
                            <p class="text-muted small mb-0">Custom high-res digital flyers, Instagram carousel packs, club event banners, and pitch decks.</p>
                        </div>
                        <div class="service-card-bottom p-4 border-top mt-auto">
                            <div class="d-flex align-items-center justify-content-between mb-3">
                                <div class="d-flex align-items-center gap-2">
                                    <div class="avatar-mini-initials bg-warning-subtle text-warning">N</div>
                                    <div>
                                        <span class="small fw-semibold d-block">Nurul H.</span>
                                        <span class="text-muted" style="font-size: 0.72rem;">Design Freelancer</span>
                                    </div>
                                </div>
                                <div class="text-end">
                                    <span class="text-muted small d-block" style="font-size: 0.72rem;"><?= __('Starting from') ?></span>
                                    <span class="fw-bold text-primary fs-5">RM 35.00</span>
                                </div>
                            </div>
                            <a href="/cshub/register.php" class="btn btn-primary w-100 rounded-pill">
                                <i class="bi bi-eye me-1"></i><?= __('View Details') ?>
                            </a>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="service-card-modern h-100">
                        <div class="service-card-top p-4">
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <span class="badge bg-orange-subtle text-orange border border-orange-subtle px-3 py-1 rounded-pill small fw-semibold">
                                    <?= __('Gadget Repair') ?>
                                </span>
                                <span class="badge bg-success-subtle text-success rounded-pill px-2 py-1 small">
                                    <i class="bi bi-check-circle-fill me-1"></i><?= __('Available Now') ?>
                                </span>
                            </div>
                            <h5 class="fw-bold mb-2">Laptop Reformatting, SSD Upgrade & Thermal Paste</h5>
                            <p class="text-muted small mb-0">Fast diagnosis and servicing for Windows and Mac notebooks. Free preliminary check via WhatsApp.</p>
                        </div>
                        <div class="service-card-bottom p-4 border-top mt-auto">
                            <div class="d-flex align-items-center justify-content-between mb-3">
                                <div class="d-flex align-items-center gap-2">
                                    <div class="avatar-mini-initials bg-info-subtle text-info">K</div>
                                    <div>
                                        <span class="small fw-semibold d-block">Kevin T.</span>
                                        <span class="text-muted" style="font-size: 0.72rem;">Hardware Tech</span>
                                    </div>
                                </div>
                                <div class="text-end">
                                    <span class="text-muted small d-block" style="font-size: 0.72rem;"><?= __('Starting from') ?></span>
                                    <span class="fw-bold text-primary fs-5">RM 45.00</span>
                                </div>
                            </div>
                            <a href="/cshub/register.php" class="btn btn-primary w-100 rounded-pill">
                                <i class="bi bi-eye me-1"></i><?= __('View Details') ?>
                            </a>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- 4. Popular Categories Section -->
<section class="py-5 section-categories section-light-bg">
    <div class="container py-4">
        <div class="text-center max-w-700 mx-auto mb-5">
            <span class="badge bg-primary-subtle text-primary rounded-pill px-3 py-1 fw-semibold mb-2">
                <i class="bi bi-grid-fill me-1"></i><?= __('Popular Categories') ?>
            </span>
            <h2 class="h1 fw-bold mb-2"><?= __('Browse by Skill & Specialty') ?></h2>
            <p class="lead text-muted"><?= __('Explore the services available on ProVenture') ?></p>
        </div>

        <div class="row g-4">
            <?php foreach ($categories as $cat):
                $catMeta = $categoryIcons[$cat['name']] ?? ['icon' => 'bi-stars', 'emoji' => '📌', 'color' => '#128C7E', 'bg' => 'rgba(18, 140, 126, 0.1)'];
            ?>
                <div class="col-md-6 col-lg-4">
                    <a href="/cshub/search.php?category=<?php echo (int)$cat['id']; ?>" class="category-card-modern">
                        <div class="category-icon-box" style="background-color: <?php echo $catMeta['bg']; ?>; color: <?php echo $catMeta['color']; ?>;">
                            <span class="fs-3"><?php echo $catMeta['emoji']; ?></span>
                        </div>
                        <div class="flex-grow-1">
                            <h5 class="fw-bold mb-1 category-title"><?php echo e($cat['name']); ?></h5>
                            <p class="text-muted small mb-2 line-clamp-1"><?php echo e($cat['description']); ?></p>
                            <span class="badge bg-primary-subtle text-primary rounded-pill small fw-semibold">
                                <?php echo (int)($cat['service_count'] ?? 0); ?> <?= __('Active Services') ?>
                            </span>
                        </div>
                        <div class="category-arrow">
                            <i class="bi bi-arrow-right-circle-fill"></i>
                        </div>
                    </a>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="text-center mt-5">
            <a href="/cshub/register.php" class="btn btn-primary btn-lg rounded-pill px-5 shadow-sm">
                <?= __('View All Services') ?> <i class="bi bi-arrow-right ms-2"></i>
            </a>
        </div>
    </div>
</section>

<!-- 5. Why Choose ProVenture Section -->
<section class="py-5 section-features">
    <div class="container py-4">
        <div class="text-center max-w-700 mx-auto mb-5">
            <span class="badge bg-primary-subtle text-primary rounded-pill px-3 py-1 fw-semibold mb-2">
                <i class="bi bi-shield-check me-1"></i><?= __('Why Choose ProVenture?') ?>
            </span>
            <h2 class="h1 fw-bold mb-2"><?= __('Designed for Speed, Trust, and Community') ?></h2>
            <p class="lead text-muted"><?= __('Everything you need to find or offer local micro-services with zero hassle') ?></p>
        </div>

        <div class="row g-4">
            <div class="col-md-6 col-lg-3">
                <div class="feature-card-modern">
                    <div class="feature-icon-circle bg-success-subtle text-success">
                        <i class="bi bi-whatsapp"></i>
                    </div>
                    <h5 class="fw-bold mb-2"><?= __('Instant WhatsApp Connection') ?></h5>
                    <p class="text-muted small mb-0"><?= __('Connect in one click without complicated in-app messaging or waiting delays.') ?></p>
                </div>
            </div>

            <div class="col-md-6 col-lg-3">
                <div class="feature-card-modern">
                    <div class="feature-icon-circle bg-primary-subtle text-primary">
                        <i class="bi bi-cash-stack"></i>
                    </div>
                    <h5 class="fw-bold mb-2"><?= __('Zero Hidden Fees') ?></h5>
                    <p class="text-muted small mb-0"><?= __('Freelancers keep 100% of their earnings. Clients pay transparent, direct rates.') ?></p>
                </div>
            </div>

            <div class="col-md-6 col-lg-3">
                <div class="feature-card-modern">
                    <div class="feature-icon-circle bg-info-subtle text-info">
                        <i class="bi bi-geo-alt-fill"></i>
                    </div>
                    <h5 class="fw-bold mb-2"><?= __('Hyper-Local Community') ?></h5>
                    <p class="text-muted small mb-0"><?= __('Find reliable campus peers and neighbors just minutes away for quick turnarounds.') ?></p>
                </div>
            </div>

            <div class="col-md-6 col-lg-3">
                <div class="feature-card-modern">
                    <div class="feature-icon-circle bg-warning-subtle text-warning">
                        <i class="bi bi-star-fill"></i>
                    </div>
                    <h5 class="fw-bold mb-2"><?= __('Verified Peer Reviews') ?></h5>
                    <p class="text-muted small mb-0"><?= __('Make informed decisions with authentic reviews and ratings from real clients.') ?></p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- 6. How It Works Section -->
<section class="py-5 section-how-it-works section-light-bg">
    <div class="container py-4">
        <div class="text-center max-w-700 mx-auto mb-5">
            <span class="badge bg-primary-subtle text-primary rounded-pill px-3 py-1 fw-semibold mb-2">
                <i class="bi bi-signpost-2-fill me-1"></i><?= __('How It Works') ?>
            </span>
            <h2 class="h1 fw-bold mb-2"><?= __('Simple, Transparent & Fast') ?></h2>
            <p class="lead text-muted"><?= __('Get started in three simple, frictionless steps') ?></p>
        </div>

        <div class="row g-4 position-relative">
            <div class="col-md-4">
                <div class="step-card-modern text-center p-4">
                    <div class="step-badge-gradient mb-3">1</div>
                    <h4 class="fw-bold mb-2"><?= __('Discover or Post') ?></h4>
                    <p class="text-muted small mb-0"><?= __('Search local services or register as a freelancer to list your skills in 2 minutes.') ?></p>
                </div>
            </div>

            <div class="col-md-4">
                <div class="step-card-modern text-center p-4">
                    <div class="step-badge-gradient mb-3">2</div>
                    <h4 class="fw-bold mb-2"><?= __('Chat on WhatsApp') ?></h4>
                    <p class="text-muted small mb-0"><?= __('Tap to discuss your requirements, budget, and delivery directly with the provider.') ?></p>
                </div>
            </div>

            <div class="col-md-4">
                <div class="step-card-modern text-center p-4">
                    <div class="step-badge-gradient mb-3">3</div>
                    <h4 class="fw-bold mb-2"><?= __('Complete & Review') ?></h4>
                    <p class="text-muted small mb-0"><?= __('Get top-quality work delivered, pay directly, and leave feedback for the community.') ?></p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- 7. Community Testimonials Section -->
<section class="py-5 section-testimonials">
    <div class="container py-4">
        <div class="text-center max-w-700 mx-auto mb-5">
            <span class="badge bg-warning-subtle text-warning rounded-pill px-3 py-1 fw-semibold mb-2">
                <i class="bi bi-chat-heart-fill me-1"></i><?= __('What our community says') ?>
            </span>
            <h2 class="h1 fw-bold mb-2"><?= __('Loved by Students & Neighbors') ?></h2>
            <p class="lead text-muted"><?= __('Trusted reviews from users who solved real problems locally') ?></p>
        </div>

        <div class="row g-4">
            <div class="col-md-4">
                <div class="testimonial-card p-4 h-100">
                    <div class="d-flex text-warning mb-3">
                        <i class="bi bi-star-fill"></i>
                        <i class="bi bi-star-fill"></i>
                        <i class="bi bi-star-fill"></i>
                        <i class="bi bi-star-fill"></i>
                        <i class="bi bi-star-fill"></i>
                    </div>
                    <p class="text-muted mb-4 fst-italic">
                        "Needed urgent peer tutoring before my Calculus II midterm. Connected with a senior on WhatsApp in 10 minutes and aced the exam!"
                    </p>
                    <div class="d-flex align-items-center gap-3">
                        <div class="avatar-mini-initials bg-primary text-white">S</div>
                        <div>
                            <h6 class="mb-0 fw-bold">Siti Aisyah</h6>
                            <span class="text-muted small">Engineering Student</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="testimonial-card p-4 h-100">
                    <div class="d-flex text-warning mb-3">
                        <i class="bi bi-star-fill"></i>
                        <i class="bi bi-star-fill"></i>
                        <i class="bi bi-star-fill"></i>
                        <i class="bi bi-star-fill"></i>
                        <i class="bi bi-star-fill"></i>
                    </div>
                    <p class="text-muted mb-4 fst-italic">
                        "My laptop suddenly blue-screened right before assignment submission. A tech peer fixed it on campus the same afternoon. Lifesaver!"
                    </p>
                    <div class="d-flex align-items-center gap-3">
                        <div class="avatar-mini-initials bg-success text-white">H</div>
                        <div>
                            <h6 class="mb-0 fw-bold">Hafiz Ramli</h6>
                            <span class="text-muted small">Business Faculty</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="testimonial-card p-4 h-100">
                    <div class="d-flex text-warning mb-3">
                        <i class="bi bi-star-fill"></i>
                        <i class="bi bi-star-fill"></i>
                        <i class="bi bi-star-fill"></i>
                        <i class="bi bi-star-fill"></i>
                        <i class="bi bi-star-fill"></i>
                    </div>
                    <p class="text-muted mb-4 fst-italic">
                        "As a freelance designer, ProVenture helped me earn extra income without paying 20% platform commissions. 100% recommended!"
                    </p>
                    <div class="d-flex align-items-center gap-3">
                        <div class="avatar-mini-initials bg-info text-white">D</div>
                        <div>
                            <h6 class="mb-0 fw-bold">Danial Lee</h6>
                            <span class="text-muted small">Freelance Designer</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- 8. Frequently Asked Questions (FAQ) Section -->
<section class="py-5 section-faq section-light-bg">
    <div class="container py-4">
        <div class="text-center max-w-700 mx-auto mb-5">
            <span class="badge bg-primary-subtle text-primary rounded-pill px-3 py-1 fw-semibold mb-2">
                <i class="bi bi-question-circle-fill me-1"></i><?= __('Frequently Asked Questions') ?>
            </span>
            <h2 class="h1 fw-bold mb-2"><?= __('Got questions? We have answers to help you get started') ?></h2>
        </div>

        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="accordion accordion-flush modern-accordion" id="cshubFaq">
                    <div class="accordion-item shadow-sm">
                        <h2 class="accordion-header" id="faqHeading1">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faqCollapse1" aria-expanded="false" aria-controls="faqCollapse1">
                                <i class="bi bi-patch-question-fill text-primary me-2"></i><?= __('Is ProVenture completely free to use?') ?>
                            </button>
                        </h2>
                        <div id="faqCollapse1" class="accordion-collapse collapse" aria-labelledby="faqHeading1" data-bs-parent="#cshubFaq">
                            <div class="accordion-body">
                                <?= __('Yes! Registration, browsing, and listing services are 100% free. We take 0% commission from freelancers and clients.') ?>
                            </div>
                        </div>
                    </div>

                    <div class="accordion-item shadow-sm">
                        <h2 class="accordion-header" id="faqHeading2">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faqCollapse2" aria-expanded="false" aria-controls="faqCollapse2">
                                <i class="bi bi-whatsapp text-success me-2"></i><?= __('How do I contact a service provider?') ?>
                            </button>
                        </h2>
                        <div id="faqCollapse2" class="accordion-collapse collapse" aria-labelledby="faqHeading2" data-bs-parent="#cshubFaq">
                            <div class="accordion-body">
                                <?= __('Simply click the "Contact via WhatsApp" button on any service card to initiate a direct chat with the provider.') ?>
                            </div>
                        </div>
                    </div>

                    <div class="accordion-item shadow-sm">
                        <h2 class="accordion-header" id="faqHeading3">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faqCollapse3" aria-expanded="false" aria-controls="faqCollapse3">
                                <i class="bi bi-credit-card-2-front-fill text-info me-2"></i><?= __('How are payments handled?') ?>
                            </button>
                        </h2>
                        <div id="faqCollapse3" class="accordion-collapse collapse" aria-labelledby="faqHeading3" data-bs-parent="#cshubFaq">
                            <div class="accordion-body">
                                <?= __('Payments are handled directly between the client and freelancer (via DuitNow QR, bank transfer, or cash) without any platform deductions.') ?>
                            </div>
                        </div>
                    </div>

                    <div class="accordion-item shadow-sm">
                        <h2 class="accordion-header" id="faqHeading4">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faqCollapse4" aria-expanded="false" aria-controls="faqCollapse4">
                                <i class="bi bi-person-workspace text-warning me-2"></i><?= __('Can I offer services as a student or beginner?') ?>
                            </button>
                        </h2>
                        <div id="faqCollapse4" class="accordion-collapse collapse" aria-labelledby="faqHeading4" data-bs-parent="#cshubFaq">
                            <div class="accordion-body">
                                <?= __('Absolutely! ProVenture is built specifically for campus students and local community members to monetize skills like tutoring, repair, and design.') ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- 9. High-Impact Call-to-Action (CTA) Section -->
<section class="py-5 position-relative section-cta">
    <div class="container py-3">
        <div class="cta-banner-modern text-center position-relative overflow-hidden">
            <div class="cta-glow-circle-1"></div>
            <div class="cta-glow-circle-2"></div>

            <div class="position-relative z-1 max-w-700 mx-auto">
                <span class="badge bg-white text-dark rounded-pill px-3 py-1 fw-bold mb-3 shadow-sm">
                    🚀 <?= __('Connect with Local Talent, Instantly') ?>
                </span>
                <h2 class="display-5 fw-bold mb-3 text-white"><?= __('Ready to Get Started?') ?></h2>
                <p class="lead text-white opacity-90 mb-4">
                    <?= __('Join hundreds of students, freelancers, and local clients in your community today.') ?>
                </p>

                <div class="d-flex gap-3 justify-content-center flex-wrap mb-4">
                    <a href="/cshub/register.php" class="btn btn-light btn-lg rounded-pill px-5 shadow fw-bold cta-main-btn">
                        <i class="bi bi-person-plus-fill me-2 text-primary"></i><?= __('Sign Up Now') ?>
                    </a>
                    <a href="/cshub/login.php" class="btn btn-outline-light btn-lg rounded-pill px-4 fw-semibold">
                        <i class="bi bi-box-arrow-in-right me-2"></i><?= __('Login') ?>
                    </a>
                </div>

                <div class="d-flex justify-content-center gap-4 text-white opacity-75 small flex-wrap">
                    <span><i class="bi bi-check-circle me-1"></i><?= __('No credit card required') ?></span>
                    <span><i class="bi bi-check-circle me-1"></i><?= __('Free forever') ?></span>
                    <span><i class="bi bi-check-circle me-1"></i><?= __('Fast response') ?></span>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Custom Styles for Landing Page Redesign -->
<style>
/* Base Wrapper */
.landing-page-wrap {
    width: 100%;
    overflow-x: hidden;
}

/* Typography Utilities */
.max-w-700 {
    max-width: 700px;
}

.gradient-text {
    background: var(--accent-gradient);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    display: inline-block;
}

/* Hero Ambient Background */
.landing-hero {
    background-color: var(--bg-hero);
    background-image: 
        radial-gradient(circle at 10% 20%, var(--hero-glow-1) 0%, transparent 40%),
        radial-gradient(circle at 90% 80%, var(--hero-glow-2) 0%, transparent 40%);
    border-bottom: 1px solid var(--border-color);
    padding: 3rem 0 4.5rem;
    position: relative;
    overflow: hidden;
}

.hero-ambient-glow {
    position: absolute;
    border-radius: 50%;
    filter: blur(80px);
    pointer-events: none;
}

.hero-ambient-glow-1 {
    width: 350px;
    height: 350px;
    top: -50px;
    left: -50px;
    background: var(--hero-glow-1);
}

.hero-ambient-glow-2 {
    width: 400px;
    height: 400px;
    bottom: -50px;
    right: -50px;
    background: var(--hero-glow-2);
}

/* Hero Badge */
.hero-badge {
    display: inline-flex;
    align-items: center;
    gap: 0.6rem;
    padding: 0.45rem 1.15rem;
    border-radius: 999px;
    background-color: var(--pill-bg);
    color: var(--pill-text);
    border: 1px solid var(--stat-border);
    backdrop-filter: blur(8px);
}

.badge-pulse-dot {
    width: 8px;
    height: 8px;
    border-radius: 50%;
    background-color: var(--whatsapp-green);
    display: inline-block;
    box-shadow: 0 0 0 0 rgba(37, 211, 102, 0.7);
    animation: pulseDot 2s infinite;
}

@keyframes pulseDot {
    0% {
        box-shadow: 0 0 0 0 rgba(37, 211, 102, 0.7);
    }
    70% {
        box-shadow: 0 0 0 10px rgba(37, 211, 102, 0);
    }
    100% {
        box-shadow: 0 0 0 0 rgba(37, 211, 102, 0);
    }
}

/* Hero Title & Lead */
.hero-title {
    color: var(--text-primary);
    line-height: 1.18;
    letter-spacing: -0.03em;
}

.hero-lead {
    color: var(--text-secondary);
    line-height: 1.65;
}

/* Hero Search Card */
.hero-search-card {
    background: var(--glass-card-bg);
    backdrop-filter: blur(16px);
    -webkit-backdrop-filter: blur(16px);
    border: 1px solid var(--glass-card-border);
    border-radius: 20px;
    padding: 0.75rem;
    box-shadow: 0 12px 35px var(--card-shadow);
    transition: all 0.3s ease;
}

.hero-search-input {
    background: transparent !important;
    color: var(--text-primary) !important;
    font-size: 0.95rem;
    padding: 0.75rem 0.5rem;
}

.hero-search-input:focus {
    box-shadow: none !important;
    outline: none !important;
}

.hero-search-btn {
    border-radius: 14px;
    font-weight: 600;
    box-shadow: 0 4px 15px rgba(18, 140, 126, 0.3);
}

.badge-pill-link {
    font-size: 0.78rem;
    font-weight: 500;
    text-decoration: none;
    padding: 0.2rem 0.75rem;
    border-radius: 999px;
    background-color: var(--pill-bg);
    color: var(--pill-text);
    border: 1px solid var(--stat-border);
    transition: all 0.2s ease;
    display: inline-flex;
    align-items: center;
}

.badge-pill-link:hover {
    background-color: var(--pill-hover-bg);
    color: var(--pill-text);
    transform: translateY(-1px);
}

.hero-cta-btn {
    border-radius: 14px;
    box-shadow: 0 6px 20px rgba(18, 140, 126, 0.35);
}

.hero-login-btn {
    border-radius: 14px;
}

/* Hero Showcase Matrix */
.hero-showcase-matrix {
    max-width: 460px;
    position: relative;
    padding: 1rem;
}

.showcase-glow {
    position: absolute;
    width: 280px;
    height: 280px;
    background: var(--accent-gradient);
    filter: blur(60px);
    opacity: 0.25;
    top: 20%;
    left: 20%;
    border-radius: 50%;
    pointer-events: none;
}

.glass-card {
    background: var(--glass-card-bg);
    backdrop-filter: blur(20px);
    -webkit-backdrop-filter: blur(20px);
    border: 1px solid var(--glass-card-border);
    border-radius: 24px;
}

.talent-card-main {
    padding: 1.75rem;
    position: relative;
    z-index: 2;
    transition: transform 0.3s ease;
}

.avatar-circle-gradient {
    width: 48px;
    height: 48px;
    border-radius: 50%;
    background: var(--accent-gradient);
    color: #ffffff;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
    font-size: 1.1rem;
    box-shadow: 0 4px 12px rgba(18, 140, 126, 0.3);
}

.service-preview-box {
    background-color: var(--bg-tertiary);
    border: 1px solid var(--border-color);
}

/* Floating Pills on Hero */
.floating-pill {
    position: absolute;
    z-index: 3;
    padding: 0.65rem 1rem;
    border-radius: 16px;
    display: flex;
    align-items: center;
    gap: 0.75rem;
    animation: floatCard 4s ease-in-out infinite;
}

.floating-pill-top {
    top: -15px;
    right: -20px;
    animation-delay: 0s;
}

.floating-pill-bottom {
    bottom: -20px;
    left: -20px;
    animation-delay: 2s;
}

.floating-pill-accent {
    bottom: 25px;
    right: -25px;
    animation-delay: 1s;
}

.floating-icon-box {
    width: 36px;
    height: 36px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1rem;
}

.floating-avatar-stack {
    display: flex;
}

.avatar-mini {
    width: 24px;
    height: 24px;
    border-radius: 50%;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 0.7rem;
    font-weight: bold;
    border: 2px solid var(--card-bg);
    margin-left: -6px;
}

.avatar-mini:first-child {
    margin-left: 0;
}

@keyframes floatCard {
    0%, 100% {
        transform: translateY(0px);
    }
    50% {
        transform: translateY(-8px);
    }
}

/* Stats Section */
.stats-section {
    margin-top: -2.5rem;
    position: relative;
    z-index: 10;
    background-color: var(--bg-secondary);
}

.stats-bar-card {
    background-color: var(--card-bg);
    border: 1px solid var(--border-color);
    border-radius: 24px;
    padding: 1.75rem 2rem;
    box-shadow: 0 15px 40px var(--card-shadow);
}

.stat-icon-wrapper {
    width: 52px;
    height: 52px;
    border-radius: 16px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.4rem;
    flex-shrink: 0;
}

.stat-number {
    font-size: 1.85rem;
    font-weight: 800;
    line-height: 1.1;
    color: var(--text-primary);
}

.stat-label {
    font-weight: 500;
}

/* Landing Page Section Backgrounds */
.section-featured,
.section-features,
.section-testimonials,
.section-cta {
    background-color: var(--bg-secondary);
}

.section-categories,
.section-how-it-works,
.section-faq,
.section-light-bg {
    background-color: var(--bg-section-alt);
}

.service-card-modern {
    background-color: var(--card-bg);
    border: 1px solid var(--border-color);
    border-radius: 22px;
    display: flex;
    flex-direction: column;
    overflow: hidden;
    transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
    box-shadow: 0 4px 15px var(--card-shadow);
}

.service-card-modern:hover {
    transform: translateY(-6px);
    box-shadow: 0 16px 35px var(--card-shadow-hover);
    border-color: var(--card-hover-border);
}

.service-card-title {
    color: var(--text-primary);
    font-size: 1.1rem;
    line-height: 1.35;
}

.service-card-desc {
    line-height: 1.5;
}

.avatar-mini-initials {
    width: 36px;
    height: 36px;
    border-radius: 50%;
    background-color: var(--pill-bg);
    color: var(--pill-text);
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
    font-size: 0.85rem;
    border: 1px solid var(--stat-border);
}

/* Category Cards */
.category-card-modern {
    background-color: var(--card-bg);
    border: 1px solid var(--border-color);
    border-radius: 20px;
    padding: 1.4rem;
    display: flex;
    align-items: center;
    gap: 1.25rem;
    text-decoration: none;
    color: var(--text-primary);
    transition: all 0.3s ease;
    box-shadow: 0 3px 12px var(--card-shadow);
    height: 100%;
}

.category-card-modern:hover {
    transform: translateY(-5px);
    border-color: var(--primary-color);
    box-shadow: 0 12px 28px var(--card-shadow-hover);
    color: var(--text-primary);
}

.category-icon-box {
    width: 60px;
    height: 60px;
    border-radius: 18px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.category-title {
    color: var(--text-primary);
    font-size: 1.05rem;
}

.category-arrow {
    font-size: 1.35rem;
    color: var(--text-muted);
    transition: transform 0.3s ease, color 0.3s ease;
}

.category-card-modern:hover .category-arrow {
    transform: translateX(5px);
    color: var(--primary-color);
}

/* Feature Cards */
.feature-card-modern {
    background-color: var(--card-bg);
    border: 1px solid var(--border-color);
    border-radius: 22px;
    padding: 2.25rem 1.75rem;
    height: 100%;
    transition: all 0.3s ease;
    box-shadow: 0 4px 15px var(--card-shadow);
}

.feature-card-modern:hover {
    transform: translateY(-6px);
    border-color: var(--primary-color);
    box-shadow: 0 15px 35px var(--card-shadow-hover);
}

.feature-icon-circle {
    width: 58px;
    height: 58px;
    border-radius: 18px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.65rem;
    margin-bottom: 1.5rem;
}

/* How It Works Steps */
.step-card-modern {
    background-color: var(--card-bg);
    border: 1px solid var(--border-color);
    border-radius: 22px;
    height: 100%;
    box-shadow: 0 4px 15px var(--card-shadow);
    transition: all 0.3s ease;
}

.step-card-modern:hover {
    transform: translateY(-5px);
    box-shadow: 0 14px 30px var(--card-shadow-hover);
}

.step-badge-gradient {
    width: 56px;
    height: 56px;
    border-radius: 50%;
    background: var(--accent-gradient);
    color: #ffffff;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
    font-weight: 800;
    box-shadow: 0 6px 18px rgba(18, 140, 126, 0.35);
}

/* Testimonial Cards */
.testimonial-card {
    background-color: var(--card-bg);
    border: 1px solid var(--border-color);
    border-radius: 22px;
    box-shadow: 0 4px 15px var(--card-shadow);
    transition: all 0.3s ease;
}

.testimonial-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 14px 30px var(--card-shadow-hover);
}

/* Accordion FAQ */
.modern-accordion .accordion-item {
    background-color: var(--card-bg);
    border: 1px solid var(--border-color) !important;
    border-radius: 18px !important;
    margin-bottom: 1rem;
    overflow: hidden;
}

.modern-accordion .accordion-button {
    background-color: var(--card-bg);
    color: var(--text-primary);
    font-weight: 600;
    font-size: 1.05rem;
    padding: 1.25rem 1.5rem;
    border: none;
}

.modern-accordion .accordion-button:not(.collapsed) {
    background-color: var(--pill-bg);
    color: var(--pill-text);
    box-shadow: none;
}

.modern-accordion .accordion-button::after {
    filter: var(--icon-filter, none);
}

.modern-accordion .accordion-body {
    color: var(--text-secondary);
    line-height: 1.65;
    padding: 1.25rem 1.5rem;
    border-top: 1px solid var(--border-light);
}

/* CTA Banner */
.cta-banner-modern {
    background: var(--accent-gradient);
    border-radius: 36px;
    padding: 4.5rem 2rem;
    box-shadow: 0 20px 50px rgba(18, 140, 126, 0.35);
    position: relative;
    overflow: hidden;
}

.cta-glow-circle-1 {
    position: absolute;
    width: 320px;
    height: 320px;
    background: rgba(255, 255, 255, 0.15);
    border-radius: 50%;
    top: -100px;
    left: -100px;
    filter: blur(30px);
}

.cta-glow-circle-2 {
    position: absolute;
    width: 250px;
    height: 250px;
    background: rgba(255, 255, 255, 0.12);
    border-radius: 50%;
    bottom: -80px;
    right: -80px;
    filter: blur(30px);
}

.cta-main-btn {
    transition: all 0.3s ease;
}

.cta-main-btn:hover {
    transform: translateY(-3px);
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2) !important;
}

/* Dark mode specific subtle adjustments */
[data-theme="dark"] .landing-page-wrap {
    background-color: #050814 !important;
}

[data-theme="dark"] .stats-section,
[data-theme="dark"] .section-featured,
[data-theme="dark"] .section-features,
[data-theme="dark"] .section-testimonials,
[data-theme="dark"] .section-cta {
    background-color: #050814 !important;
}

[data-theme="dark"] .section-categories,
[data-theme="dark"] .section-how-it-works,
[data-theme="dark"] .section-faq,
[data-theme="dark"] .section-light-bg {
    background-color: #080d1f !important;
}

[data-theme="dark"] .section-featured h2,
[data-theme="dark"] .section-categories h2,
[data-theme="dark"] .section-features h2,
[data-theme="dark"] .section-how-it-works h2,
[data-theme="dark"] .section-testimonials h2,
[data-theme="dark"] .section-faq h2,
[data-theme="dark"] .section-cta h2 {
    color: #ffffff !important;
}

[data-theme="dark"] .section-featured p,
[data-theme="dark"] .section-categories .lead,
[data-theme="dark"] .section-features .lead,
[data-theme="dark"] .section-how-it-works .lead,
[data-theme="dark"] .section-testimonials .lead,
[data-theme="dark"] .section-faq .lead,
[data-theme="dark"] .section-cta .lead {
    color: #94a3b8 !important;
}

[data-theme="dark"] .feature-card-modern h5,
[data-theme="dark"] .step-card-modern h4,
[data-theme="dark"] .testimonial-card h6 {
    color: #ffffff !important;
}

[data-theme="dark"] .feature-card-modern p,
[data-theme="dark"] .step-card-modern p {
    color: #94a3b8 !important;
}

[data-theme="dark"] .testimonial-card p.fst-italic {
    color: #cbd5e1 !important;
}

[data-theme="dark"] .cta-banner-modern {
    background: linear-gradient(135deg, rgba(14, 165, 233, 0.22) 0%, rgba(18, 140, 126, 0.3) 50%, rgba(37, 211, 102, 0.22) 100%), #0c1427 !important;
    border: 1px solid rgba(255, 255, 255, 0.15) !important;
    border-top: 1px solid rgba(255, 255, 255, 0.3) !important;
    box-shadow: 0 25px 60px -15px rgba(0, 0, 0, 0.9), 0 0 50px rgba(18, 140, 126, 0.25) !important;
}

[data-theme="dark"] .cta-banner-modern h2 {
    color: #ffffff !important;
}

[data-theme="dark"] .cta-banner-modern p {
    color: #cbd5e1 !important;
}

[data-theme="dark"] .cta-banner-modern .cta-main-btn {
    background-color: #25D366 !important;
    border-color: #25D366 !important;
    color: #050814 !important;
    font-weight: 700 !important;
}

[data-theme="dark"] .cta-banner-modern .btn-outline-light {
    background-color: rgba(255, 255, 255, 0.08) !important;
    border-color: rgba(255, 255, 255, 0.2) !important;
    color: #ffffff !important;
}

[data-theme="dark"] .modern-accordion .accordion-button::after {
    filter: invert(1);
}

[data-theme="dark"] .landing-hero {
    background-color: #050814;
    background-image: 
        radial-gradient(circle at 12% 25%, rgba(14, 165, 233, 0.28) 0%, transparent 45%),
        radial-gradient(circle at 88% 75%, rgba(18, 140, 126, 0.25) 0%, transparent 45%),
        radial-gradient(circle at 50% 20%, rgba(59, 130, 246, 0.18) 0%, transparent 55%);
    border-bottom: 1px solid rgba(255, 255, 255, 0.06) !important;
}

[data-theme="dark"] .glass-card {
    border-top: 1px solid rgba(255, 255, 255, 0.18) !important;
    box-shadow: 0 25px 60px -15px rgba(0, 0, 0, 0.85), 0 0 45px rgba(14, 165, 233, 0.12) !important;
}

[data-theme="dark"] .stats-bar-card {
    background-color: rgba(13, 21, 38, 0.88) !important;
    backdrop-filter: blur(20px);
    -webkit-backdrop-filter: blur(20px);
    border: 1px solid rgba(255, 255, 255, 0.1) !important;
    border-top: 1px solid rgba(255, 255, 255, 0.2) !important;
    box-shadow: 0 25px 55px -10px rgba(0, 0, 0, 0.9), 0 0 40px rgba(18, 140, 126, 0.1) !important;
}

[data-theme="dark"] .service-card-modern,
[data-theme="dark"] .category-card-modern,
[data-theme="dark"] .feature-card-modern,
[data-theme="dark"] .step-card-modern,
[data-theme="dark"] .testimonial-card {
    background-color: rgba(13, 21, 38, 0.78) !important;
    border: 1px solid rgba(255, 255, 255, 0.08) !important;
    border-top: 1px solid rgba(255, 255, 255, 0.15) !important;
}

[data-theme="dark"] .service-card-modern:hover,
[data-theme="dark"] .category-card-modern:hover,
[data-theme="dark"] .feature-card-modern:hover,
[data-theme="dark"] .step-card-modern:hover,
[data-theme="dark"] .testimonial-card:hover {
    background-color: rgba(16, 25, 48, 0.92) !important;
    border-color: rgba(46, 219, 114, 0.45) !important;
    box-shadow: 0 25px 50px -10px rgba(0, 0, 0, 0.95), 0 0 35px rgba(46, 219, 114, 0.15) !important;
}

[data-theme="dark"] .bg-purple-subtle {
    background-color: rgba(139, 92, 246, 0.18) !important;
}

[data-theme="dark"] .text-purple {
    color: #c084fc !important;
}

[data-theme="dark"] .border-purple-subtle {
    border-color: rgba(139, 92, 246, 0.3) !important;
}

[data-theme="dark"] .bg-orange-subtle {
    background-color: rgba(249, 115, 22, 0.18) !important;
}

[data-theme="dark"] .text-orange {
    color: #fb923c !important;
}

[data-theme="dark"] .border-orange-subtle {
    border-color: rgba(249, 115, 22, 0.3) !important;
}

/* Responsive adjustments */
@media (max-width: 991px) {
    .hero-showcase-matrix {
        max-width: 100%;
        margin-top: 1rem;
    }

    .floating-pill-top {
        top: -10px;
        right: 0px;
    }

    .floating-pill-bottom {
        bottom: -10px;
        left: 0px;
    }
}

@media (max-width: 768px) {
    .landing-hero {
        padding: 2.5rem 0 3.5rem;
    }

    .hero-title {
        font-size: 2.25rem;
    }

    .stats-section {
        margin-top: 0;
        padding-top: 1rem;
    }

    .stats-bar-card {
        padding: 1.25rem;
    }

    .stat-number {
        font-size: 1.5rem;
    }

    .cta-banner-modern {
        padding: 3rem 1.5rem;
        border-radius: 24px;
    }

    .floating-pill {
        display: none;
    }
}
</style>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
