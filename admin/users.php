<?php
/**
 * admin/users.php
 * User management page - view, edit, delete users
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/logger.php';

// Require admin role
requireRole('admin');

$success = '';
$error = '';

// Check for bulk action results
if (isset($_SESSION['bulk_success'])) {
    $success = $_SESSION['bulk_success'];
    unset($_SESSION['bulk_success']);
}
if (isset($_SESSION['bulk_error'])) {
    $error = $_SESSION['bulk_error'];
    unset($_SESSION['bulk_error']);
}

// Handle user deletion
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $userId = (int)$_GET['delete'];

    // Prevent admin from deleting themselves
    if ($userId === $_SESSION['user_id']) {
        $error = 'You cannot delete your own account.';
    } else {
        try {
            // Get user info before deleting for logging
            $stmt = $pdo->prepare("SELECT name, email FROM users WHERE id = ?");
            $stmt->execute([$userId]);
            $userInfo = $stmt->fetch();

            $stmt = $pdo->prepare("DELETE FROM users WHERE id = ? AND role != 'admin'");
            $stmt->execute([$userId]);

            // Log the deletion
            logAdminActivity($pdo, 'deleted_user', 'user', $userId, "Deleted user: {$userInfo['name']} ({$userInfo['email']})");

            $success = 'User deleted successfully.';
        } catch (PDOException $e) {
            $error = 'Failed to delete user. They may have active services or reviews.';
        }
    }
}

// Get all users with search and filter
$search = $_GET['search'] ?? '';
$roleFilter = $_GET['role'] ?? '';

$query = "SELECT id, name, email, phone, role, created_at FROM users WHERE 1=1";
$params = [];

if (!empty($search)) {
    $query .= " AND (name LIKE ? OR email LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

if (!empty($roleFilter) && $roleFilter !== 'all') {
    $query .= " AND role = ?";
    $params[] = $roleFilter;
}

$query .= " ORDER BY created_at DESC";

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$users = $stmt->fetchAll();

require_once __DIR__ . '/../includes/header.php';
?>

<div class="container py-4">
    <div class="row mb-4">
        <div class="col-md-6">
            <h1 class="h3 mb-1">User Management</h1>
            <p class="text-muted">Manage all registered users on ProVenture</p>
        </div>
        <div class="col-md-6 text-end">
            <a href="/cshub/admin/export.php?type=users&format=csv" class="btn btn-success me-2">
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

    <!-- Search and Filter -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <form method="get" action="/cshub/admin/users.php" class="row g-3">
                <div class="col-md-6">
                    <input type="text" name="search" class="form-control" placeholder="Search by name or email..." value="<?php echo e($search); ?>">
                </div>
                <div class="col-md-3">
                    <select name="role" class="form-select">
                        <option value="all" <?php echo $roleFilter === 'all' ? 'selected' : ''; ?>>All Roles</option>
                        <option value="client" <?php echo $roleFilter === 'client' ? 'selected' : ''; ?>>Clients</option>
                        <option value="freelancer" <?php echo $roleFilter === 'freelancer' ? 'selected' : ''; ?>>Freelancers</option>
                        <option value="admin" <?php echo $roleFilter === 'admin' ? 'selected' : ''; ?>>Admins</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-primary w-100">Search</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Bulk Actions Form -->
    <form id="bulkForm" method="post" action="/cshub/admin/bulk-actions.php">
        <input type="hidden" name="type" value="users">
        <input type="hidden" name="action" id="bulkAction" value="">

        <div class="card border-0 shadow-sm mb-3">
            <div class="card-body py-2">
                <div class="d-flex align-items-center gap-2">
                    <input type="checkbox" id="selectAll" class="form-check-input">
                    <label for="selectAll" class="form-check-label small">Select All</label>
                    <span class="text-muted small mx-2">|</span>
                    <button type="button" class="btn btn-sm btn-danger" onclick="submitBulk('delete')">
                        Delete Selected
                    </button>
                    <span class="text-muted small ms-auto" id="selectedCount">0 selected</span>
                </div>
            </div>
        </div>

    <!-- Users Table -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white border-0 py-3">
            <h5 class="mb-0">All Users (<?php echo count($users); ?>)</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th width="30"><input type="checkbox" class="form-check-input"></th>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Role</th>
                            <th>Joined</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($users) > 0): ?>
                            <?php foreach ($users as $user): ?>
                                <tr>
                                    <td>
                                        <?php if ($user['role'] !== 'admin' || $user['id'] !== $_SESSION['user_id']): ?>
                                            <input type="checkbox" name="ids[]" value="<?php echo $user['id']; ?>" class="form-check-input bulk-checkbox">
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo $user['id']; ?></td>
                                    <td><?php echo e($user['name']); ?></td>
                                    <td><?php echo e($user['email']); ?></td>
                                    <td><?php echo e($user['phone']); ?></td>
                                    <td>
                                        <?php
                                        $badgeClass = 'primary';
                                        if ($user['role'] === 'freelancer') $badgeClass = 'success';
                                        if ($user['role'] === 'admin') $badgeClass = 'danger';
                                        ?>
                                        <span class="badge bg-<?php echo $badgeClass; ?>">
                                            <?php echo ucfirst($user['role']); ?>
                                        </span>
                                    </td>
                                    <td class="text-muted small"><?php echo date('M d, Y', strtotime($user['created_at'])); ?></td>
                                    <td class="text-center">
                                        <?php if ($user['role'] !== 'admin' || $user['id'] !== $_SESSION['user_id']): ?>
                                            <a href="/cshub/admin/users.php?delete=<?php echo $user['id']; ?>"
                                               class="btn btn-sm btn-outline-danger"
                                               onclick="return confirm('Are you sure you want to delete this user? This action cannot be undone.');">
                                                Delete
                                            </a>
                                        <?php else: ?>
                                            <span class="text-muted small">You</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="8" class="text-center text-muted py-4">
                                    No users found matching your search criteria.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    </form>

    <!-- User Statistics -->
    <div class="row mt-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <?php
                    $clientCount = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'client'")->fetchColumn();
                    ?>
                    <h2 class="text-primary"><?php echo $clientCount; ?></h2>
                    <p class="text-muted mb-0">Total Clients</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <?php
                    $freelancerCount = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'freelancer'")->fetchColumn();
                    ?>
                    <h2 class="text-success"><?php echo $freelancerCount; ?></h2>
                    <p class="text-muted mb-0">Total Freelancers</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <?php
                    $adminCount = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'admin'")->fetchColumn();
                    ?>
                    <h2 class="text-danger"><?php echo $adminCount; ?></h2>
                    <p class="text-muted mb-0">Total Admins</p>
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

<script>
// Bulk actions JavaScript
document.getElementById('selectAll').addEventListener('change', function() {
    const checkboxes = document.querySelectorAll('.bulk-checkbox');
    checkboxes.forEach(cb => cb.checked = this.checked);
    updateSelectedCount();
});

document.querySelectorAll('.bulk-checkbox').forEach(cb => {
    cb.addEventListener('change', updateSelectedCount);
});

function updateSelectedCount() {
    const checked = document.querySelectorAll('.bulk-checkbox:checked').length;
    document.getElementById('selectedCount').textContent = checked + ' selected';
}

function submitBulk(action) {
    const checked = document.querySelectorAll('.bulk-checkbox:checked');
    if (checked.length === 0) {
        alert('Please select at least one user.');
        return;
    }

    if (confirm(`Are you sure you want to ${action} ${checked.length} user(s)?`)) {
        document.getElementById('bulkAction').value = action;
        document.getElementById('bulkForm').submit();
    }
}
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
