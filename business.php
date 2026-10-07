<?php
// business.php - ProVenture For Business: Dedicated Industry-Filtered Freelancer Solutions
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';

$pageTitle = 'For Business & Startups - Hire Relevant Freelancers | ProVenture';

// Industry & Business Solutions Matrix
$industries = [
    'fnb' => [
        'id' => 'fnb',
        'title' => 'Food & Beverage',
        'short_title' => 'F&B',
        'icon' => 'bi-cup-hot-fill',
        'emoji' => '🍽️',
        'badge' => 'F&B Top Pick',
        'desc' => 'Cafes, Restaurants, Cloud Kitchens & Food Trucks',
        'subtasks' => [
            'menu' => [
                'label' => 'Menu & Poster Design',
                'keywords' => ['menu', 'poster', 'flyer', 'carousels', 'design', 'canva'],
                'tag' => 'Menu & Posters'
            ],
            'photo' => [
                'label' => 'Food & Venue Photography',
                'keywords' => ['photo', 'photoshoot', 'portrait', 'event'],
                'tag' => 'Food Photography'
            ],
            'runner' => [
                'label' => 'Supplies & Food Delivery Runners',
                'keywords' => ['runner', 'dispatch', 'food', 'parcel', 'delivery'],
                'tag' => 'Urgent Dispatch'
            ],
            'tech' => [
                'label' => 'Cafe Wi-Fi & POS Network Setup',
                'keywords' => ['wi-fi', 'network', 'booster', 'computer', 'malware'],
                'tag' => 'Wi-Fi & Tech'
            ],
            'branding' => [
                'label' => 'Logo & Brand Identity Kit',
                'keywords' => ['logo', 'brand', 'identity'],
                'tag' => 'Branding'
            ]
        ],
        'default_keywords' => ['menu', 'poster', 'flyer', 'logo', 'photo', 'runner', 'dispatch', 'wi-fi', 'design']
    ],
    'retail' => [
        'id' => 'retail',
        'title' => 'Retail & E-Commerce',
        'short_title' => 'Retail',
        'icon' => 'bi-bag-check-fill',
        'emoji' => '🛍️',
        'badge' => 'Retail Ready',
        'desc' => 'Boutiques, Online Stores, Grocers & Merchandisers',
        'subtasks' => [
            'product-photo' => [
                'label' => 'Product Photography & Staging',
                'keywords' => ['photo', 'photoshoot', 'portrait'],
                'tag' => 'Product Photos'
            ],
            'branding' => [
                'label' => 'Packaging, Labels & Brand Identity',
                'keywords' => ['logo', 'brand', 'identity', 'poster'],
                'tag' => 'Packaging & Logo'
            ],
            'web' => [
                'label' => 'E-Commerce Store & Web App Setup',
                'keywords' => ['web', 'app', 'database', 'php', 'development'],
                'tag' => 'Online Store Web'
            ],
            'runner' => [
                'label' => 'Local Delivery & Parcel Runner',
                'keywords' => ['runner', 'dispatch', 'parcel', 'delivery'],
                'tag' => 'Customer Deliveries'
            ],
            'tech' => [
                'label' => 'Store POS, SSD & PC Optimization',
                'keywords' => ['laptop', 'reformatting', 'ssd', 'thermal', 'windows'],
                'tag' => 'Store IT Hardware'
            ]
        ],
        'default_keywords' => ['product', 'photo', 'brand', 'logo', 'web', 'runner', 'parcel', 'laptop', 'poster']
    ],
    'corporate' => [
        'id' => 'corporate',
        'title' => 'Corporate & Offices',
        'short_title' => 'Corporate',
        'icon' => 'bi-building-fill',
        'emoji' => '🏢',
        'badge' => 'Corporate Tier',
        'desc' => 'Agencies, Consultancies, Professional Practices & Startups',
        'subtasks' => [
            'slides' => [
                'label' => 'Pitch Decks & Presentation Slides',
                'keywords' => ['pitch', 'deck', 'presentation', 'slide', 'canva'],
                'tag' => 'Pitch Decks'
            ],
            'it-support' => [
                'label' => 'Workstation Maintenance, SSD & Malware',
                'keywords' => ['laptop', 'reformatting', 'ssd', 'thermal', 'malware', 'windows'],
                'tag' => 'Office IT Care'
            ],
            'web' => [
                'label' => 'Custom Web Apps & Database Review',
                'keywords' => ['web', 'app', 'database', 'architecture', 'php'],
                'tag' => 'Custom Software'
            ],
            'runner' => [
                'label' => 'Plotting, Document Dispatch & Binding',
                'keywords' => ['runner', 'dispatch', 'printing', 'binding', 'parcel'],
                'tag' => 'Document Runner'
            ],
            'photo' => [
                'label' => 'Corporate Annual Dinner & Team Headshots',
                'keywords' => ['dinner', 'event', 'society', 'photo', 'portrait'],
                'tag' => 'Event & Team Photos'
            ]
        ],
        'default_keywords' => ['pitch', 'deck', 'presentation', 'slide', 'laptop', 'reformatting', 'ssd', 'malware', 'web', 'database', 'runner', 'printing', 'photo']
    ],
    'events' => [
        'id' => 'events',
        'title' => 'Events & Entertainment',
        'short_title' => 'Events',
        'icon' => 'bi-calendar-event-fill',
        'emoji' => '🎉',
        'badge' => 'Event Ready',
        'desc' => 'Company Dinners, Conferences, Pop-Ups & Launches',
        'subtasks' => [
            'photo' => [
                'label' => 'Event & Gala Photography Packages',
                'keywords' => ['dinner', 'society', 'event', 'photo', 'photoshoot'],
                'tag' => 'Event Coverage'
            ],
            'graphics' => [
                'label' => 'Banners, Flyers, Carousels & Tickets',
                'keywords' => ['poster', 'flyer', 'carousels', 'design'],
                'tag' => 'Event Marketing'
            ],
            'runner' => [
                'label' => 'Large Format Printing & Errand Runners',
                'keywords' => ['printing', 'plotting', 'binding', 'runner', 'dispatch'],
                'tag' => 'Print & Errands'
            ],
            'tech' => [
                'label' => 'Event Wi-Fi & Sound Connectivity',
                'keywords' => ['wi-fi', 'network', 'booster', 'troubleshooting'],
                'tag' => 'Network & Tech'
            ]
        ],
        'default_keywords' => ['dinner', 'society', 'event', 'photo', 'photoshoot', 'poster', 'flyer', 'printing', 'runner', 'wi-fi']
    ],
    'home_services' => [
        'id' => 'home_services',
        'title' => 'Home & Facilities',
        'short_title' => 'Facilities',
        'icon' => 'bi-tools',
        'emoji' => '🛠️',
        'badge' => 'Facility Care',
        'desc' => 'Property Managers, Co-Working Spaces, Maintenance',
        'subtasks' => [
            'it-hardware' => [
                'label' => 'Office Hardware, Laptop & Screen Repairs',
                'keywords' => ['laptop', 'reformatting', 'smartphone', 'screen', 'battery', 'thermal'],
                'tag' => 'Hardware Repair'
            ],
            'networking' => [
                'label' => 'Facility Wi-Fi & Network Booster',
                'keywords' => ['wi-fi', 'network', 'booster', 'troubleshooting'],
                'tag' => 'Facility Wi-Fi'
            ],
            'flyers' => [
                'label' => 'Contractor Banners & Promotional Flyers',
                'keywords' => ['poster', 'flyer', 'design', 'carousels'],
                'tag' => 'Signage & Flyers'
            ],
            'dispatch' => [
                'label' => 'Tools, Parts & Urgent Parcel Dispatch',
                'keywords' => ['runner', 'parcel', 'dispatch', 'delivery'],
                'tag' => 'Parts Dispatch'
            ]
        ],
        'default_keywords' => ['laptop', 'reformatting', 'smartphone', 'wi-fi', 'network', 'poster', 'flyer', 'runner', 'parcel']
    ]
];

// Current filter states
$activeIndustryKey = $_GET['industry'] ?? 'fnb';
if (!isset($industries[$activeIndustryKey]) && $activeIndustryKey !== 'all') {
    $activeIndustryKey = 'fnb';
}

$activeSubtaskKey = $_GET['subtask'] ?? '';
$searchQuery = trim($_GET['q'] ?? '');
$minPrice = $_GET['min_price'] ?? '';
$maxPrice = $_GET['max_price'] ?? '';
$sortBy = $_GET['sort'] ?? 'recommended';

$currentIndustry = ($activeIndustryKey !== 'all') ? $industries[$activeIndustryKey] : null;

// Build Filter SQL Query
$where = ["services.availability = 1", "services.status = 'approved'"];
$params = [];

// 1. Industry / Subtask Keyword Filter
if ($currentIndustry) {
    if (!empty($activeSubtaskKey) && isset($currentIndustry['subtasks'][$activeSubtaskKey])) {
        // Specific subtask keywords
        $kwList = $currentIndustry['subtasks'][$activeSubtaskKey]['keywords'];
        $kwConditions = [];
        foreach ($kwList as $kw) {
            $kwConditions[] = "services.title LIKE ? OR services.description LIKE ? OR categories.name LIKE ?";
            $params[] = "%{$kw}%";
            $params[] = "%{$kw}%";
            $params[] = "%{$kw}%";
        }
        if (!empty($kwConditions)) {
            $where[] = "(" . implode(' OR ', $kwConditions) . ")";
        }
    } else {
        // General industry default keywords
        $kwList = $currentIndustry['default_keywords'];
        $kwConditions = [];
        foreach ($kwList as $kw) {
            $kwConditions[] = "services.title LIKE ? OR services.description LIKE ? OR categories.name LIKE ?";
            $params[] = "%{$kw}%";
            $params[] = "%{$kw}%";
            $params[] = "%{$kw}%";
        }
        if (!empty($kwConditions)) {
            $where[] = "(" . implode(' OR ', $kwConditions) . ")";
        }
    }
}

// 2. User Free Text Search Filter
if (!empty($searchQuery)) {
    $where[] = "(services.title LIKE ? OR services.description LIKE ? OR categories.name LIKE ? OR users.name LIKE ?)";
    $params[] = "%{$searchQuery}%";
    $params[] = "%{$searchQuery}%";
    $params[] = "%{$searchQuery}%";
    $params[] = "%{$searchQuery}%";
}

// 3. Price Filters
if (!empty($minPrice) && is_numeric($minPrice)) {
    $where[] = "services.price >= ?";
    $params[] = (float)$minPrice;
}
if (!empty($maxPrice) && is_numeric($maxPrice)) {
    $where[] = "services.price <= ?";
    $params[] = (float)$maxPrice;
}

// 4. Sort Order
$orderClause = "avg_rating DESC, review_count DESC, services.id DESC";
switch ($sortBy) {
    case 'price_asc':
        $orderClause = "services.price ASC";
        break;
    case 'price_desc':
        $orderClause = "services.price DESC";
        break;
    case 'newest':
        $orderClause = "services.created_at DESC";
        break;
    case 'rating':
        $orderClause = "avg_rating DESC, review_count DESC";
        break;
    default:
        $orderClause = "avg_rating DESC, review_count DESC, services.id DESC";
        break;
}

// Fetch matching services with ratings
$sql = "SELECT services.*, 
               categories.name AS category_name, 
               users.name AS provider_name,
               users.phone AS provider_phone,
               COALESCE(AVG(reviews.rating), 0) AS avg_rating,
               COUNT(reviews.id) AS review_count
        FROM services
        LEFT JOIN categories ON services.category_id = categories.id
        LEFT JOIN users ON services.user_id = users.id
        LEFT JOIN reviews ON services.id = reviews.service_id
        WHERE " . implode(' AND ', $where) . "
        GROUP BY services.id
        ORDER BY {$orderClause}";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$services = $stmt->fetchAll(PDO::FETCH_ASSOC);

require_once __DIR__ . '/includes/header.php';
?>

<div class="container py-4">
    <!-- Breadcrumbs -->
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb small">
            <li class="breadcrumb-item"><a href="/cshub/index.php" class="text-decoration-none">Home</a></li>
            <li class="breadcrumb-item active" aria-current="page">For Business</li>
            <?php if ($currentIndustry): ?>
                <li class="breadcrumb-item active" aria-current="page"><?= htmlspecialchars($currentIndustry['title']) ?></li>
            <?php endif; ?>
        </ol>
    </nav>

    <!-- Hero Header -->
    <div class="business-hero p-4 p-md-5 mb-5 rounded-4 border shadow-sm">
        <div class="row align-items-center g-4">
            <div class="col-lg-8">
                <span class="badge bg-primary text-white rounded-pill px-3 py-2 mb-3 fw-bold shadow-sm">
                    <i class="bi bi-briefcase-fill me-1"></i> ProVenture for Business
                </span>
                <h1 class="display-6 fw-bold mb-3">
                    Hire Vetted Freelancers Tailored to Your Business
                </h1>
                <p class="lead text-secondary mb-4" style="max-width: 680px; font-size: 1.05rem;">
                    Whether you run an F&amp;B cafe needing printed menus, a boutique needing product photography, or an office needing IT support &mdash; find local talent with transparent rates, zero agency commissions, and instant WhatsApp contact.
                </p>

                <!-- Search Input with live submit -->
                <form action="/cshub/business.php" method="GET" class="d-flex flex-column flex-sm-row gap-2" style="max-width: 600px;">
                    <input type="hidden" name="industry" value="<?= htmlspecialchars($activeIndustryKey) ?>">
                    <?php if (!empty($activeSubtaskKey)): ?>
                        <input type="hidden" name="subtask" value="<?= htmlspecialchars($activeSubtaskKey) ?>">
                    <?php endif; ?>
                    <div class="input-group">
                        <span class="input-group-text business-search-addon border-end-0"><i class="bi bi-search text-muted"></i></span>
                        <input type="text" name="q" class="form-control border-start-0" placeholder="e.g. Menu design, food photography, Wi-Fi..." value="<?= htmlspecialchars($searchQuery) ?>">
                    </div>
                    <button type="submit" class="btn btn-primary rounded-pill px-4 fw-semibold flex-shrink-0">
                        Search Talent
                    </button>
                </form>
            </div>
            <div class="col-lg-4 text-center d-none d-lg-block">
                <div class="p-4 rounded-4 business-advantage-card border shadow-sm text-start">
                    <h6 class="fw-bold text-primary mb-2"><i class="bi bi-patch-check-fill text-success me-1"></i> The Business Advantage</h6>
                    <ul class="list-unstyled small text-muted mb-0 d-flex flex-column gap-2">
                        <li><i class="bi bi-check-circle-fill text-success me-1"></i> <strong>Zero Platform Cut:</strong> Keep 100% of your budget.</li>
                        <li><i class="bi bi-check-circle-fill text-success me-1"></i> <strong>Direct WhatsApp:</strong> Fast turnaround times.</li>
                        <li><i class="bi bi-check-circle-fill text-success me-1"></i> <strong>Itemized Rates:</strong> Upfront starting prices.</li>
                        <li><i class="bi bi-check-circle-fill text-success me-1"></i> <strong>Local Proximity:</strong> On-site or remote work.</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <!-- Step 1: Select Industry -->
    <div class="mb-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="fw-bold mb-0 text-uppercase" style="font-size: 0.85rem; letter-spacing: 0.08em; color: var(--text-secondary, #64748b);">
                <i class="bi bi-grid-fill text-primary me-1"></i> Step 1: Select Your Business Type
            </h5>
            <a href="/cshub/business.php?industry=all" class="text-decoration-none small fw-semibold <?= $activeIndustryKey === 'all' ? 'text-primary' : 'text-muted' ?>">
                Show All Businesses &rarr;
            </a>
        </div>

        <div class="row g-3">
            <?php foreach ($industries as $key => $ind): ?>
                <?php $isActive = ($activeIndustryKey === $key); ?>
                <div class="col-6 col-md-4 col-lg-2-4" style="flex: 0 0 auto; width: 20%;" class="industry-col">
                    <a href="/cshub/business.php?industry=<?= $key ?>" class="industry-card <?= $isActive ? 'active' : '' ?>">
                        <span class="fs-4"><?= $ind['emoji'] ?></span>
                        <div>
                            <strong class="d-block text-truncate" style="font-size: 0.88rem;"><?= htmlspecialchars($ind['title']) ?></strong>
                            <span class="text-muted d-block" style="font-size: 0.72rem;"><?= htmlspecialchars($ind['desc']) ?></span>
                        </div>
                    </a>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Step 2: Specific Business Task / Need Filter -->
    <?php if ($currentIndustry): ?>
        <div class="p-3 rounded-4 business-subtasks-card border shadow-sm mb-4">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2 mb-2">
                <div class="d-flex align-items-center gap-2">
                    <span class="fs-5"><?= $currentIndustry['emoji'] ?></span>
                    <strong class="fw-bold text-dark dark-text-light" style="font-size: 0.92rem;">
                        Common Freelancer Needs for <?= htmlspecialchars($currentIndustry['title']) ?>:
                    </strong>
                </div>
                <?php if (!empty($activeSubtaskKey) || !empty($searchQuery)): ?>
                    <a href="/cshub/business.php?industry=<?= $activeIndustryKey ?>" class="btn btn-sm btn-link text-decoration-none text-muted p-0">
                        <i class="bi bi-x-circle me-1"></i>Reset Tasks
                    </a>
                <?php endif; ?>
            </div>

            <div class="d-flex flex-wrap gap-2 pt-1">
                <a href="/cshub/business.php?industry=<?= $activeIndustryKey ?>" class="subtask-pill <?= empty($activeSubtaskKey) ? 'active' : '' ?>">
                    <i class="bi bi-stars"></i> All <?= htmlspecialchars($currentIndustry['short_title']) ?> Talent
                </a>
                <?php foreach ($currentIndustry['subtasks'] as $subKey => $sub): ?>
                    <?php $isSubActive = ($activeSubtaskKey === $subKey); ?>
                    <a href="/cshub/business.php?industry=<?= $activeIndustryKey ?>&subtask=<?= $subKey ?>" class="subtask-pill <?= $isSubActive ? 'active' : '' ?>">
                        <span><?= htmlspecialchars($sub['label']) ?></span>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>

    <!-- Secondary Filters Bar (Budget, Sort & Live Count) -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4 pb-3 border-bottom">
        <div class="d-flex align-items-center gap-2">
            <h2 class="h5 fw-bold mb-0">
                <?= count($services) ?> Freelancer<?= count($services) === 1 ? '' : 's' ?> Found
            </h2>
            <?php if ($currentIndustry): ?>
                <span class="badge bg-primary-subtle text-primary rounded-pill">
                    <?= $currentIndustry['emoji'] ?> <?= htmlspecialchars($currentIndustry['title']) ?>
                </span>
            <?php endif; ?>
            <?php if (!empty($activeSubtaskKey) && isset($currentIndustry['subtasks'][$activeSubtaskKey])): ?>
                <span class="badge bg-success-subtle text-success rounded-pill">
                    <?= htmlspecialchars($currentIndustry['subtasks'][$activeSubtaskKey]['tag']) ?>
                </span>
            <?php endif; ?>
        </div>

        <!-- Filter controls -->
        <form action="/cshub/business.php" method="GET" class="d-flex flex-wrap align-items-center gap-2">
            <input type="hidden" name="industry" value="<?= htmlspecialchars($activeIndustryKey) ?>">
            <?php if (!empty($activeSubtaskKey)): ?>
                <input type="hidden" name="subtask" value="<?= htmlspecialchars($activeSubtaskKey) ?>">
            <?php endif; ?>
            <?php if (!empty($searchQuery)): ?>
                <input type="hidden" name="q" value="<?= htmlspecialchars($searchQuery) ?>">
            <?php endif; ?>

            <div class="input-group input-group-sm" style="max-width: 170px;">
                <span class="input-group-text">Max RM</span>
                <input type="number" name="max_price" class="form-control" placeholder="Budget" value="<?= htmlspecialchars($maxPrice) ?>">
            </div>

            <select name="sort" class="form-select form-select-sm" style="width: auto;" onchange="this.form.submit();">
                <option value="recommended" <?= $sortBy === 'recommended' ? 'selected' : '' ?>>Recommended</option>
                <option value="rating" <?= $sortBy === 'rating' ? 'selected' : '' ?>>Highest Rated</option>
                <option value="price_asc" <?= $sortBy === 'price_asc' ? 'selected' : '' ?>>Price: Low to High</option>
                <option value="price_desc" <?= $sortBy === 'price_desc' ? 'selected' : '' ?>>Price: High to Low</option>
                <option value="newest" <?= $sortBy === 'newest' ? 'selected' : '' ?>>Newest First</option>
            </select>

            <button type="submit" class="btn btn-sm btn-outline-secondary rounded-pill px-3">
                Apply
            </button>
            <?php if (!empty($maxPrice) || $sortBy !== 'recommended'): ?>
                <a href="/cshub/business.php?industry=<?= $activeIndustryKey ?><?= !empty($activeSubtaskKey) ? '&subtask=' . $activeSubtaskKey : '' ?>" class="btn btn-sm btn-link text-muted p-0">Clear</a>
            <?php endif; ?>
        </form>
    </div>

    <!-- Results Service Grid -->
    <div class="row g-4 mb-5">
        <?php if (empty($services)): ?>
            <div class="col-12">
                <div class="p-5 text-center business-empty-card border rounded-4 shadow-sm">
                    <i class="bi bi-building-exclamation display-4 text-primary d-block mb-3"></i>
                    <h4 class="fw-bold mb-2">No exact freelancer matches found</h4>
                    <p class="text-muted mx-auto mb-4" style="max-width: 480px;">
                        Try removing budget caps or clearing the subtask filter to browse all available business providers.
                    </p>
                    <a href="/cshub/business.php?industry=<?= $activeIndustryKey ?>" class="btn btn-primary rounded-pill px-4">
                        Reset Filters for <?= htmlspecialchars($currentIndustry['title'] ?? 'Business') ?>
                    </a>
                </div>
            </div>
        <?php endif; ?>

        <?php foreach ($services as $srv): ?>
            <?php
            $stars = round((float)$srv['avg_rating'], 1);
            $reviews = (int)$srv['review_count'];
            $bizContext = $currentIndustry ? $currentIndustry['title'] : 'Business';
            ?>
            <div class="col-md-6 col-lg-4">
                <div class="card h-100 shadow-sm border service-card rounded-4 p-4 d-flex flex-column justify-content-between">
                    <div>
                        <!-- Header & Badges -->
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <span class="badge bg-primary-subtle text-primary border rounded-pill px-2 py-1 small fw-semibold">
                                <?= htmlspecialchars($srv['category_name'] ?? 'Micro-Service') ?>
                            </span>
                            <?php if ($currentIndustry): ?>
                                <span class="badge bg-warning-subtle text-warning-emphasis border rounded-pill px-2 py-1 small">
                                    <?= $currentIndustry['emoji'] ?> <?= htmlspecialchars($currentIndustry['badge']) ?>
                                </span>
                            <?php else: ?>
                                <span class="badge bg-success-subtle text-success rounded-pill px-2 py-1 small">
                                    ● Available
                                </span>
                            <?php endif; ?>
                        </div>

                        <!-- Title -->
                        <h5 class="fw-bold mb-2 mt-2" style="font-size: 1.1rem; line-height: 1.35;">
                            <?= htmlspecialchars($srv['title']) ?>
                        </h5>

                        <!-- Description Snippet -->
                        <p class="text-secondary small mb-3" style="line-height: 1.6;">
                            <?= htmlspecialchars(mb_strimwidth($srv['description'] ?? '', 0, 110, '...')) ?>
                        </p>
                    </div>

                    <!-- Bottom Pricing & WhatsApp Actions -->
                    <div>
                        <div class="d-flex align-items-center justify-content-between pt-3 border-top mb-3">
                            <div>
                                <span class="text-muted small d-block" style="font-size: 0.75rem;">
                                    <i class="bi bi-person-check-fill text-success me-1"></i><?= htmlspecialchars($srv['provider_name'] ?? 'Provider') ?>
                                </span>
                                <?php if ($reviews > 0): ?>
                                    <span class="text-warning small fw-bold d-flex align-items-center gap-1">
                                        <i class="bi bi-star-fill"></i> <?= number_format($stars, 1) ?>
                                        <span class="text-muted fw-normal">(<?= $reviews ?>)</span>
                                    </span>
                                <?php else: ?>
                                    <span class="text-muted small">New Provider</span>
                                <?php endif; ?>
                            </div>
                            <div class="text-end">
                                <span class="text-muted small d-block" style="font-size: 0.72rem;">Starting at</span>
                                <span class="fw-bold fs-5 text-primary"><?= formatPrice((float)$srv['price']) ?></span>
                            </div>
                        </div>

                        <div class="d-flex gap-2 align-items-center">
                            <button type="button" class="btn btn-sm btn-outline-danger rounded-circle p-0 d-flex align-items-center justify-content-center flex-shrink-0"
                                    style="width: 32px; height: 32px;"
                                    data-bs-toggle="modal" data-bs-target="#reportServiceModal"
                                    data-service-id="<?= (int)$srv['id'] ?>"
                                    data-service-title="<?= htmlspecialchars($srv['title'], ENT_QUOTES, 'UTF-8') ?>"
                                    title="Report this listing">
                                <i class="bi bi-flag-fill" style="font-size: 0.75rem;"></i>
                            </button>
                            <a href="/cshub/service.php?id=<?= (int)$srv['id'] ?>" class="btn btn-sm btn-outline-secondary rounded-pill px-3 flex-grow-1">
                                Details
                            </a>
                            <a href="/cshub/whatsapp.php?service_id=<?= (int)$srv['id'] ?>&biz=<?= urlencode($bizContext) ?>" target="_blank" class="btn btn-sm btn-success rounded-pill px-3 fw-semibold shadow-sm d-inline-flex align-items-center gap-1">
                                <i class="bi bi-whatsapp"></i> WhatsApp
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- Business Inquiry Banner -->
    <div class="card rounded-4 border p-4 p-md-5 text-center text-white mb-4" style="background: linear-gradient(135deg, #0b3b36 0%, #128C7E 100%);">
        <h3 class="fw-bold mb-2">Need a Specific Custom Team or Urgent Request?</h3>
        <p class="mx-auto mb-4" style="max-width: 600px; opacity: 0.92;">
            If your business has tailored specifications (e.g. bulk menu design, full store Wi-Fi rollout, recurring daily deliveries), chat directly with our 24/7 AI Assistant to get instant talent matches.
        </p>
        <div class="d-flex justify-content-center gap-3 flex-wrap">
            <button type="button" class="btn btn-light rounded-pill px-4 fw-bold text-primary shadow" onclick="document.getElementById('cshubChatToggle').click();">
                <i class="bi bi-robot me-1"></i> Ask 24/7 AI for Recommendations
            </button>
            <a href="/cshub/register.php?role=freelancer" class="btn btn-outline-light rounded-pill px-4 fw-semibold">
                Register as Business Freelancer
            </a>
        </div>
    </div>
</div>

<style>
/* Responsive industry columns */
@media (max-width: 991.98px) {
    .industry-col {
        width: 33.333% !important;
    }
}
@media (max-width: 575.98px) {
    .industry-col {
        width: 50% !important;
    }
}
</style>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
