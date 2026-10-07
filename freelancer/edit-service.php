<?php
/**
 * freelancer/edit-service.php?id=X
 * UPDATE step of Service CRUD (Objective 4 / Section 14).
 * Enforces ownership verification and re-submits for admin review if updated.
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

requireRole('freelancer');

$id = (int) ($_GET['id'] ?? 0);

$stmt = $pdo->prepare("SELECT * FROM services WHERE id = ? AND user_id = ?");
$stmt->execute([$id, $_SESSION['user_id']]);
$service = $stmt->fetch();

if (!$service) {
    $_SESSION['flash_error'] = 'Service not found or you do not have permission to edit it.';
    redirect('/cshub/freelancer/dashboard.php');
}

$categories = $pdo->query("SELECT id, name FROM categories ORDER BY name")->fetchAll();

$errors = [];
$title = $service['title'];
$description = $service['description'];
$categoryId = (int) $service['category_id'];
$price = (float) $service['price'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $categoryId = (int) ($_POST['category_id'] ?? 0);
    $priceInput = trim($_POST['price'] ?? '');

    // 1. Validation
    if (empty($title)) {
        $errors[] = 'Service title is required.';
    } elseif (mb_strlen($title) < 3 || mb_strlen($title) > 150) {
        $errors[] = 'Service title must be between 3 and 150 characters.';
    }

    if (empty($description)) {
        $errors[] = 'Service description is required.';
    } elseif (mb_strlen($description) < 10) {
        $errors[] = 'Description must be at least 10 characters long.';
    }

    $validCategory = false;
    foreach ($categories as $cat) {
        if ((int)$cat['id'] === $categoryId) {
            $validCategory = true;
            break;
        }
    }
    if (!$validCategory) {
        $errors[] = 'Please select a valid service category.';
    }

    if ($priceInput === '' || !is_numeric($priceInput) || (float)$priceInput <= 0) {
        $errors[] = 'Please enter a valid price greater than RM 0.00.';
    }
    $price = (float) $priceInput;

    // 2. Handle optional replacement portfolio image
    $newPortfolioImage = $service['portfolio_image'];
    if (isset($_FILES['portfolio_image']) && $_FILES['portfolio_image']['error'] !== UPLOAD_ERR_NO_FILE) {
        $file = $_FILES['portfolio_image'];

        if ($file['error'] !== UPLOAD_ERR_OK) {
            $errors[] = 'Failed to upload new portfolio image. Please try again.';
        } else {
            if ($file['size'] > 3 * 1024 * 1024) {
                $errors[] = 'Portfolio image must be smaller than 3MB.';
            }

            $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            $allowedExts = ['jpg', 'jpeg', 'png', 'webp'];
            if (!in_array($ext, $allowedExts, true)) {
                $errors[] = 'Only JPG, JPEG, PNG, and WebP image formats are supported.';
            }

            if (empty($errors)) {
                $finfo = finfo_open(FILEINFO_MIME_TYPE);
                $mime = finfo_file($finfo, $file['tmp_name']);
                finfo_close($finfo);

                $allowedMimes = ['image/jpeg', 'image/png', 'image/webp'];
                if (!in_array($mime, $allowedMimes, true)) {
                    $errors[] = 'Invalid image file type.';
                }
            }

            if (empty($errors)) {
                $uploadDir = __DIR__ . '/../uploads/portfolio/';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0755, true);
                }

                $filename = 'portfolio_' . bin2hex(random_bytes(10)) . '.' . $ext;
                $destination = $uploadDir . $filename;

                if (move_uploaded_file($file['tmp_name'], $destination)) {
                    // Delete old file if it existed
                    if (!empty($service['portfolio_image'])) {
                        $oldFile = $uploadDir . $service['portfolio_image'];
                        if (file_exists($oldFile)) {
                            @unlink($oldFile);
                        }
                    }
                    $newPortfolioImage = $filename;
                } else {
                    $errors[] = 'Could not save the replacement image.';
                }
            }
        }
    }

    // 3. Update database if no errors
    if (empty($errors)) {
        try {
            $autoApprove = getSystemSetting($pdo, 'auto_approve_services', '1') === '1';
            $newStatus = $autoApprove ? 'approved' : 'pending';

            $stmt = $pdo->prepare("
                UPDATE services
                SET category_id = ?, title = ?, description = ?, price = ?, portfolio_image = ?, status = ?
                WHERE id = ? AND user_id = ?
            ");
            $stmt->execute([
                $categoryId,
                $title,
                $description,
                $price,
                $newPortfolioImage,
                $newStatus,
                $id,
                $_SESSION['user_id']
            ]);

            if ($autoApprove) {
                $_SESSION['flash_success'] = 'Service updated successfully! Your updates are published live immediately.';
            } else {
                $_SESSION['flash_success'] = 'Service updated successfully! Changes have been submitted for admin review.';
            }
            redirect('/cshub/freelancer/dashboard.php');
        } catch (PDOException $e) {
            $errors[] = 'Database error: Unable to update service.';
        }
    }
}

$autoApproveEnabled = getSystemSetting($pdo, 'auto_approve_services', '1') === '1';

require_once __DIR__ . '/../includes/header.php';
?>

<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="d-flex align-items-center justify-content-between mb-4">
                <div>
                    <h1 class="h3 fw-bold mb-1">Edit Service</h1>
                    <p class="text-muted small mb-0">Update your service details or portfolio image.</p>
                </div>
                <a href="/cshub/freelancer/dashboard.php" class="btn btn-outline-secondary btn-sm rounded-pill px-3">
                    <i class="bi bi-arrow-left me-1"></i> Back to Dashboard
                </a>
            </div>

            <!-- Current Status Callout -->
            <div class="card border-0 shadow-sm rounded-4 p-3 mb-4 bg-light">
                <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                    <div class="d-flex align-items-center gap-2">
                        <span class="text-muted small">Current Moderation Status:</span>
                        <?php if ($service['status'] === 'approved'): ?>
                            <span class="badge bg-success rounded-pill px-3 py-1"><i class="bi bi-check-circle me-1"></i>Approved (Live)</span>
                        <?php elseif ($service['status'] === 'rejected'): ?>
                            <span class="badge bg-danger rounded-pill px-3 py-1"><i class="bi bi-x-circle me-1"></i>Rejected</span>
                        <?php else: ?>
                            <span class="badge bg-warning text-dark rounded-pill px-3 py-1"><i class="bi bi-clock-history me-1"></i>Pending Admin Approval</span>
                        <?php endif; ?>
                    </div>
                    <div class="text-muted small">
                        <?php if ($autoApproveEnabled): ?>
                            <i class="bi bi-check-circle-fill text-success me-1"></i> Instant Publishing: Saving changes will keep your updates live immediately.
                        <?php else: ?>
                            <i class="bi bi-info-circle me-1"></i> Saving changes will re-submit this service for admin review.
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <?php if (!empty($errors)): ?>
                <div class="alert alert-danger rounded-4 shadow-sm mb-4">
                    <h6 class="fw-bold mb-2"><i class="bi bi-exclamation-triangle-fill me-1"></i> Please correct the following:</h6>
                    <ul class="mb-0 ps-3">
                        <?php foreach ($errors as $err): ?>
                            <li><?php echo e($err); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <div class="card border-0 shadow-sm rounded-4 p-4">
                <form method="post" action="/cshub/freelancer/edit-service.php?id=<?php echo (int) $service['id']; ?>" enctype="multipart/form-data">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Service Title <span class="text-danger">*</span></label>
                        <input type="text" name="title" class="form-control rounded-3" value="<?php echo e($title); ?>" maxlength="150" required>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Category <span class="text-danger">*</span></label>
                            <select name="category_id" class="form-select rounded-3" required>
                                <?php foreach ($categories as $cat): ?>
                                    <option value="<?php echo (int) $cat['id']; ?>" <?php echo ($categoryId === (int)$cat['id']) ? 'selected' : ''; ?>>
                                        <?php echo e($cat['name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Starting Price (RM) <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text bg-light fw-bold">RM</span>
                                <input type="number" step="0.01" min="0.01" name="price" class="form-control rounded-end-3" value="<?php echo e((string)$price); ?>" required>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Description &amp; Deliverables <span class="text-danger">*</span></label>
                        <textarea name="description" class="form-control rounded-3" rows="5" required><?php echo e($description); ?></textarea>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-bold">Portfolio / Showcase Image</label>
                        <?php if (!empty($service['portfolio_image'])): ?>
                            <div class="mb-2 d-flex align-items-center gap-3">
                                <img src="/cshub/uploads/portfolio/<?php echo e($service['portfolio_image']); ?>" alt="Current portfolio image" class="rounded-3 border" style="width: 90px; height: 65px; object-fit: cover;">
                                <span class="small text-muted">Current image: <code><?php echo e($service['portfolio_image']); ?></code></span>
                            </div>
                        <?php endif; ?>
                        <input type="file" name="portfolio_image" class="form-control rounded-3" accept=".jpg,.jpeg,.png,.webp">
                        <div class="form-text small">Leave empty to keep the existing image. Uploading a new file will replace it (Max 3MB).</div>
                    </div>

                    <div class="d-flex align-items-center justify-content-end gap-2 pt-2 border-top">
                        <a href="/cshub/freelancer/dashboard.php" class="btn btn-light rounded-pill px-4">Cancel</a>
                        <button type="submit" class="btn btn-primary rounded-pill px-4 fw-semibold">
                            <i class="bi bi-check2-circle me-1"></i> Save &amp; Re-submit for Approval
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
