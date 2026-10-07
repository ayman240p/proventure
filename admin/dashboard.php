<?php
/**
 * admin/dashboard.php
 * Admin dashboard with user management, service oversight, and statistics
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

// Require admin role
requireRole('admin');

// Get statistics
$statsQuery = "
    SELECT
        (SELECT COUNT(*) FROM users WHERE role = 'client') as total_clients,
        (SELECT COUNT(*) FROM users WHERE role = 'freelancer') as total_freelancers,
        (SELECT COUNT(*) FROM services) as total_services,
        (SELECT COUNT(*) FROM services WHERE availability = 1) as active_services,
        (SELECT COUNT(*) FROM services WHERE status = 'pending') as pending_services,
        (SELECT COUNT(*) FROM service_reports WHERE status = 'pending') as pending_reports,
        (SELECT COUNT(*) FROM reviews) as total_reviews,
        (SELECT COUNT(*) FROM categories) as total_categories
";
$stats = $pdo->query($statsQuery)->fetch();

// Get recent users
$recentUsers = $pdo->query("
    SELECT id, name, email, role, created_at
    FROM users
    WHERE role != 'admin'
    ORDER BY created_at DESC
    LIMIT 5
")->fetchAll();

// Get recent services
$recentServices = $pdo->query("
    SELECT s.id, s.title, s.price, s.availability, s.status, u.name as provider_name, c.name as category_name, s.created_at
    FROM services s
    JOIN users u ON s.user_id = u.id
    LEFT JOIN categories c ON s.category_id = c.id
    ORDER BY s.created_at DESC
    LIMIT 5
")->fetchAll();

require_once __DIR__ . '/../includes/header.php';
?>

<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-12">
            <h1 class="h3 mb-1">Admin Dashboard</h1>
            <p class="text-muted">Welcome back, <?php echo e($_SESSION['user_name']); ?>! Here's an overview of ProVenture.</p>
        </div>
    </div>

    <!-- Reported Listings Alert -->
    <?php if (!empty($stats['pending_reports']) && $stats['pending_reports'] > 0): ?>
        <div class="alert alert-danger border-0 rounded-4 shadow-sm mb-4 d-flex align-items-center justify-content-between flex-wrap gap-2">
            <div class="d-flex align-items-center gap-3">
                <span class="badge bg-danger text-white fs-6 rounded-pill px-3 py-2">
                    <i class="bi bi-flag-fill me-1"></i> <?php echo $stats['pending_reports']; ?> Reported
                </span>
                <div>
                    <strong class="d-block text-danger">Community Spam / Malicious Alerts Awaiting Review</strong>
                    <span class="small text-muted">Users have flagged live listings for potential spam, malware links, or inappropriate content.</span>
                </div>
            </div>
            <a href="/cshub/admin/reports.php" class="btn btn-danger btn-sm rounded-pill px-3">
                Inspect Reports <i class="bi bi-arrow-right ms-1"></i>
            </a>
        </div>
    <?php endif; ?>

    <!-- Pending Moderation Alert -->
    <?php if ($stats['pending_services'] > 0): ?>
        <div class="alert alert-warning border-0 rounded-4 shadow-sm mb-4 d-flex align-items-center justify-content-between flex-wrap gap-2">
            <div class="d-flex align-items-center gap-3">
                <span class="badge bg-warning text-dark fs-6 rounded-pill px-3 py-2">
                    <i class="bi bi-clock-history me-1"></i> <?php echo $stats['pending_services']; ?> Pending
                </span>
                <div>
                    <strong class="d-block">Services Awaiting Admin Moderation</strong>
                    <span class="small text-muted">New micro-services submitted by freelancers require review before going live to clients.</span>
                </div>
            </div>
            <a href="/cshub/admin/services.php?status=pending" class="btn btn-dark btn-sm rounded-pill px-3">
                Review Queue <i class="bi bi-arrow-right ms-1"></i>
            </a>
        </div>
    <?php endif; ?>

    <!-- Statistics Cards -->
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-muted mb-1 small">Total Clients</p>
                            <h3 class="mb-0"><?php echo $stats['total_clients']; ?></h3>
                        </div>
                        <div class="bg-primary bg-opacity-10 p-3 rounded">
                            <svg width="24" height="24" fill="currentColor" class="text-primary">
                                <use href="#icon-users"/>
                            </svg>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-muted mb-1 small">Total Freelancers</p>
                            <h3 class="mb-0"><?php echo $stats['total_freelancers']; ?></h3>
                        </div>
                        <div class="bg-success bg-opacity-10 p-3 rounded">
                            <svg width="24" height="24" fill="currentColor" class="text-success">
                                <use href="#icon-briefcase"/>
                            </svg>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-muted mb-1 small">Total Services</p>
                            <h3 class="mb-0"><?php echo $stats['total_services']; ?></h3>
                            <small class="text-success"><?php echo $stats['active_services']; ?> active</small>
                        </div>
                        <div class="bg-info bg-opacity-10 p-3 rounded">
                            <svg width="24" height="24" fill="currentColor" class="text-info">
                                <use href="#icon-grid"/>
                            </svg>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <!-- Recent Users -->
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0 d-flex justify-content-between align-items-center py-3">
                    <h5 class="mb-0">Recent Users</h5>
                    <a href="/cshub/admin/users.php" class="btn btn-sm btn-outline-primary">View All</a>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Role</th>
                                    <th>Joined</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (count($recentUsers) > 0): ?>
                                    <?php foreach ($recentUsers as $user): ?>
                                        <tr>
                                            <td><?php echo e($user['name']); ?></td>
                                            <td><?php echo e($user['email']); ?></td>
                                            <td>
                                                <span class="badge bg-<?php echo $user['role'] === 'freelancer' ? 'success' : 'primary'; ?>">
                                                    <?php echo ucfirst($user['role']); ?>
                                                </span>
                                            </td>
                                            <td class="text-muted small"><?php echo date('M d, Y', strtotime($user['created_at'])); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="4" class="text-center text-muted py-4">No users yet</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Services -->
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0 d-flex justify-content-between align-items-center py-3">
                    <h5 class="mb-0">Recent Services</h5>
                    <a href="/cshub/admin/services.php" class="btn btn-sm btn-outline-primary">View All</a>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Service</th>
                                    <th>Provider</th>
                                    <th>Moderation</th>
                                    <th>Availability</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (count($recentServices) > 0): ?>
                                    <?php foreach ($recentServices as $service): ?>
                                        <tr>
                                            <td>
                                                <div><?php echo e($service['title']); ?></div>
                                                <small class="text-muted"><?php echo e($service['category_name'] ?? 'Uncategorized'); ?></small>
                                            </td>
                                            <td><?php echo e($service['provider_name']); ?></td>
                                            <td>
                                                <?php if ($service['status'] === 'pending'): ?>
                                                    <span class="badge bg-warning text-dark"><i class="bi bi-clock-history me-1"></i>Pending</span>
                                                <?php elseif ($service['status'] === 'approved'): ?>
                                                    <span class="badge bg-success">Approved</span>
                                                <?php else: ?>
                                                    <span class="badge bg-danger">Rejected</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php if ($service['availability']): ?>
                                                    <span class="badge bg-success-subtle text-success">Available</span>
                                                <?php else: ?>
                                                    <span class="badge bg-secondary-subtle text-secondary">Busy</span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="4" class="text-center text-muted py-4">No services yet</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0 py-3">
                    <h5 class="mb-0">Quick Actions</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <a href="/cshub/admin/users.php" class="btn btn-outline-primary w-100 py-3">
                                <svg width="20" height="20" fill="currentColor" class="mb-2">
                                    <use href="#icon-users"/>
                                </svg>
                                <div>Manage Users</div>
                            </a>
                        </div>
                        <div class="col-md-3">
                            <a href="/cshub/admin/services.php" class="btn btn-outline-success w-100 py-3">
                                <svg width="20" height="20" fill="currentColor" class="mb-2">
                                    <use href="#icon-grid"/>
                                </svg>
                                <div>Manage Services</div>
                            </a>
                        </div>
                        <div class="col-md-3">
                            <a href="/cshub/admin/categories.php" class="btn btn-outline-info w-100 py-3">
                                <svg width="20" height="20" fill="currentColor" class="mb-2">
                                    <use href="#icon-tag"/>
                                </svg>
                                <div>Manage Categories</div>
                            </a>
                        </div>
                        <div class="col-md-3">
                            <a href="/cshub/admin/reviews.php" class="btn btn-outline-warning w-100 py-3">
                                <svg width="20" height="20" fill="currentColor" class="mb-2">
                                    <use href="#icon-star"/>
                                </svg>
                                <div>Manage Reviews</div>
                            </a>
                        </div>
                    </div>
                    <div class="row g-3 mt-2">
                        <div class="col-md-3">
                            <a href="/cshub/admin/reports.php" class="btn btn-outline-danger w-100 py-3 position-relative">
                                <i class="bi bi-flag-fill fs-4 d-block mb-1"></i>
                                <div>Community Reports</div>
                                <?php if (!empty($stats['pending_reports']) && $stats['pending_reports'] > 0): ?>
                                    <span class="badge bg-danger rounded-pill mt-1"><?php echo $stats['pending_reports']; ?> pending</span>
                                <?php endif; ?>
                            </a>
                        </div>
                        <div class="col-md-3">
                            <a href="/cshub/admin/activity-logs.php" class="btn btn-outline-secondary w-100 py-3">
                                <svg width="20" height="20" fill="currentColor" class="mb-2">
                                    <use href="#icon-activity"/>
                                </svg>
                                <div>Activity Logs</div>
                            </a>
                        </div>
                        <div class="col-md-3">
                            <a href="/cshub/admin/settings.php" class="btn btn-outline-secondary w-100 py-3">
                                <svg width="20" height="20" fill="currentColor" class="mb-2">
                                    <use href="#icon-settings"/>
                                </svg>
                                <div>Settings</div>
                            </a>
                        </div>
                        <div class="col-md-3">
                            <a href="/cshub/index.php" class="btn btn-outline-secondary w-100 py-3" target="_blank">
                                <svg width="20" height="20" fill="currentColor" class="mb-2">
                                    <use href="#icon-external"/>
                                </svg>
                                <div>View Site</div>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- SVG Icons -->
<svg style="display: none;">
    <symbol id="icon-users" viewBox="0 0 24 24">
        <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
        <circle cx="9" cy="7" r="4"></circle>
        <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
        <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
    </symbol>
    <symbol id="icon-briefcase" viewBox="0 0 24 24">
        <rect x="2" y="7" width="20" height="14" rx="2" ry="2"></rect>
        <path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"></path>
    </symbol>
    <symbol id="icon-grid" viewBox="0 0 24 24">
        <rect x="3" y="3" width="7" height="7"></rect>
        <rect x="14" y="3" width="7" height="7"></rect>
        <rect x="14" y="14" width="7" height="7"></rect>
        <rect x="3" y="14" width="7" height="7"></rect>
    </symbol>
    <symbol id="icon-tag" viewBox="0 0 24 24">
        <path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"></path>
        <line x1="7" y1="7" x2="7.01" y2="7"></line>
    </symbol>
    <symbol id="icon-star" viewBox="0 0 24 24">
        <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon>
    </symbol>
    <symbol id="icon-activity" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" fill="none">
        <polyline points="22 12 18 12 15 21 9 3 6 12 2 12"></polyline>
    </symbol>
    <symbol id="icon-settings" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" fill="none">
        <circle cx="12" cy="12" r="3"></circle>
        <path d="M12 1v6m0 6v6m-8-7h6m6 0h6m-15.364 8.364l4.243-4.243m4.242-4.242l4.243-4.243m-12.728 0l4.243 4.243m4.242 4.242l4.243 4.243"></path>
    </symbol>
    <symbol id="icon-external" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" fill="none">
        <path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"></path>
        <polyline points="15 3 21 3 21 9"></polyline>
        <line x1="10" y1="14" x2="21" y2="3"></line>
    </symbol>
    <symbol id="icon-logout" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" fill="none">
        <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
        <polyline points="16 17 21 12 16 7"></polyline>
        <line x1="21" y1="12" x2="9" y2="12"></line>
    </symbol>
</svg>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
