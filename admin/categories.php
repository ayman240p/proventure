<?php
/**
 * admin/categories.php
 * Category management page - view, add, edit, delete categories
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/logger.php';

// Require admin role
requireRole('admin');

$success = '';
$error = '';

// Handle category addition
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_category'])) {
    $name = trim($_POST['name'] ?? '');
    $description = trim($_POST['description'] ?? '');

    if (empty($name)) {
        $error = 'Category name is required.';
    } else {
        try {
            $stmt = $pdo->prepare("INSERT INTO categories (name, description) VALUES (?, ?)");
            $stmt->execute([$name, $description]);

            $categoryId = $pdo->lastInsertId();

            // Log the addition
            logAdminActivity($pdo, 'added_category', 'category', $categoryId, "Added category: $name");

            $success = 'Category added successfully.';
        } catch (PDOException $e) {
            $error = 'Failed to add category.';
        }
    }
}

// Handle category deletion
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $categoryId = (int)$_GET['delete'];

    try {
        // Get category info before deleting
        $stmt = $pdo->prepare("SELECT name FROM categories WHERE id = ?");
        $stmt->execute([$categoryId]);
        $categoryInfo = $stmt->fetch();

        $stmt = $pdo->prepare("DELETE FROM categories WHERE id = ?");
        $stmt->execute([$categoryId]);

        // Log the deletion
        logAdminActivity($pdo, 'deleted_category', 'category', $categoryId, "Deleted category: {$categoryInfo['name']}");

        $success = 'Category deleted successfully.';
    } catch (PDOException $e) {
        $error = 'Failed to delete category. There may be services using this category.';
    }
}

// Get all categories with service counts
$categories = $pdo->query("
    SELECT c.*, COUNT(s.id) as service_count
    FROM categories c
    LEFT JOIN services s ON c.id = s.category_id
    GROUP BY c.id
    ORDER BY c.name
")->fetchAll();

require_once __DIR__ . '/../includes/header.php';
?>

<div class="container py-4">
    <div class="row mb-4">
        <div class="col-md-6">
            <h1 class="h3 mb-1">Category Management</h1>
            <p class="text-muted">Manage service categories on ProVenture</p>
        </div>
        <div class="col-md-6 text-end">
            <a href="/cshub/admin/export.php?type=categories&format=csv" class="btn btn-success me-2">
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

    <div class="row">
        <!-- Add Category Form -->
        <div class="col-lg-4 mb-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0 py-3">
                    <h5 class="mb-0">Add New Category</h5>
                </div>
                <div class="card-body">
                    <form method="post" action="/cshub/admin/categories.php">
                        <div class="mb-3">
                            <label class="form-label">Category Name</label>
                            <input type="text" name="name" class="form-control" placeholder="e.g., Web Development" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Description</label>
                            <textarea name="description" class="form-control" rows="3" placeholder="Brief description of this category"></textarea>
                        </div>
                        <button type="submit" name="add_category" class="btn btn-primary w-100">Add Category</button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Categories List -->
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0 py-3">
                    <h5 class="mb-0">All Categories (<?php echo count($categories); ?>)</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>ID</th>
                                    <th>Name</th>
                                    <th>Description</th>
                                    <th>Services</th>
                                    <th>Created</th>
                                    <th class="text-center">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (count($categories) > 0): ?>
                                    <?php foreach ($categories as $category): ?>
                                        <tr>
                                            <td><?php echo $category['id']; ?></td>
                                            <td><strong><?php echo e($category['name']); ?></strong></td>
                                            <td><?php echo e($category['description'] ?? '-'); ?></td>
                                            <td>
                                                <span class="badge bg-info"><?php echo $category['service_count']; ?> services</span>
                                            </td>
                                            <td class="text-muted small"><?php echo date('M d, Y', strtotime($category['created_at'])); ?></td>
                                            <td class="text-center">
                                                <a href="/cshub/admin/categories.php?delete=<?php echo $category['id']; ?>"
                                                   class="btn btn-sm btn-outline-danger"
                                                   onclick="return confirm('Are you sure you want to delete this category? Services will be uncategorized.');">
                                                    Delete
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="6" class="text-center text-muted py-4">
                                            No categories found. Add your first category above.
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
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
