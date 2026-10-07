<?php
/**
 * freelancer/profile.php
 * Freelancer edits their own name, phone/WhatsApp number, and profile image.
 * TODO: full implementation belongs to the Freelancer CRUD sprint.
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

requireRole('freelancer');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // TODO:
    // 1. Validate name / phone / optional profile image upload
    // 2. UPDATE users SET name = ?, phone = ? WHERE id = ? (prepared statement)
    // 3. Handle image upload per Section 21 if a new profile image is provided
}

$stmt = $pdo->prepare("SELECT name, email, phone FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();

require_once __DIR__ . '/../includes/header.php';
?>

<h1 class="h4 mb-3">Edit Profile</h1>

<form method="post" action="/cshub/freelancer/profile.php" enctype="multipart/form-data" class="col-md-6">
    <div class="mb-3">
        <label class="form-label">Name</label>
        <input type="text" name="name" class="form-control" value="<?php echo e($user['name'] ?? ''); ?>">
    </div>
    <div class="mb-3">
        <label class="form-label">Email</label>
        <input type="email" class="form-control" value="<?php echo e($user['email'] ?? ''); ?>" disabled>
    </div>
    <div class="mb-3">
        <label class="form-label">WhatsApp / Contact Number</label>
        <input type="text" name="phone" class="form-control" value="<?php echo e($user['phone'] ?? ''); ?>">
    </div>
    <button type="submit" class="btn btn-primary">Save Changes</button>
</form>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
