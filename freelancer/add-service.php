<?php
/**
 * freelancer/add-service.php
 * CREATE step of Service CRUD (Objective 4 / Section 14).
 * All newly added services start with status = 'pending' awaiting Admin approval.
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

requireRole('freelancer');

$categories = $pdo->query("SELECT id, name FROM categories ORDER BY name")->fetchAll();

$errors = [];
$title = '';
$description = '';
$categoryId = '';
$price = '';

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

    // 2. Handle optional portfolio image upload
    $portfolioImage = null;
    if (isset($_FILES['portfolio_image']) && $_FILES['portfolio_image']['error'] !== UPLOAD_ERR_NO_FILE) {
        $file = $_FILES['portfolio_image'];

        if ($file['error'] !== UPLOAD_ERR_OK) {
            $errors[] = 'Failed to upload portfolio image. Please try again.';
        } else {
            // Check file size (max 3MB)
            if ($file['size'] > 3 * 1024 * 1024) {
                $errors[] = 'Portfolio image must be smaller than 3MB.';
            }

            // Check file extension
            $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            $allowedExts = ['jpg', 'jpeg', 'png', 'webp'];
            if (!in_array($ext, $allowedExts, true)) {
                $errors[] = 'Only JPG, JPEG, PNG, and WebP image formats are supported.';
            }

            // Check MIME type using finfo
            if (empty($errors)) {
                $finfo = finfo_open(FILEINFO_MIME_TYPE);
                $mime = finfo_file($finfo, $file['tmp_name']);
                finfo_close($finfo);

                $allowedMimes = ['image/jpeg', 'image/png', 'image/webp'];
                if (!in_array($mime, $allowedMimes, true)) {
                    $errors[] = 'Invalid image file type.';
                }
            }

            // Move uploaded file to uploads/portfolio/
            if (empty($errors)) {
                $uploadDir = __DIR__ . '/../uploads/portfolio/';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0755, true);
                }

                $filename = 'portfolio_' . bin2hex(random_bytes(10)) . '.' . $ext;
                $destination = $uploadDir . $filename;

                if (move_uploaded_file($file['tmp_name'], $destination)) {
                    $portfolioImage = $filename;
                } else {
                    $errors[] = 'Could not save the uploaded image.';
                }
            }
        }
    }

    // 3. Save to database if no errors
    if (empty($errors)) {
        try {
            $autoApprove = getSystemSetting($pdo, 'auto_approve_services', '1') === '1';
            $initialStatus = $autoApprove ? 'approved' : 'pending';

            $stmt = $pdo->prepare("
                INSERT INTO services (user_id, category_id, title, description, price, portfolio_image, availability, status)
                VALUES (?, ?, ?, ?, ?, ?, 1, ?)
            ");
            $stmt->execute([
                $_SESSION['user_id'],
                $categoryId,
                $title,
                $description,
                $price,
                $portfolioImage,
                $initialStatus
            ]);

            if ($autoApprove) {
                $_SESSION['flash_success'] = 'Service created and published immediately! Your service is now live on the public directory.';
            } else {
                $_SESSION['flash_success'] = 'Service created successfully! It has been submitted for admin approval and will appear on the public directory once verified.';
            }
            redirect('/cshub/freelancer/dashboard.php');
        } catch (PDOException $e) {
            $errors[] = 'Database error: Unable to save service. Please try again.';
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
                    <h1 class="h3 fw-bold mb-1">Add New Service</h1>
                    <p class="text-muted small mb-0">List a new micro-service to offer to the ProVenture community.</p>
                </div>
                <a href="/cshub/freelancer/dashboard.php" class="btn btn-outline-secondary btn-sm rounded-pill px-3">
                    <i class="bi bi-arrow-left me-1"></i> Back to Dashboard
                </a>
            </div>

            <!-- Admin Approval Policy Notice -->
            <?php if ($autoApproveEnabled): ?>
                <div class="alert alert-success border-0 rounded-4 shadow-sm mb-4 d-flex align-items-start gap-3">
                    <i class="bi bi-lightning-charge-fill fs-3 text-success flex-shrink-0"></i>
                    <div>
                        <h6 class="fw-bold mb-1">Instant Publishing Active</h6>
                        <p class="small text-muted mb-0">
                            Automatic approval is enabled. Your micro-service will be published immediately to the public directory and available to clients right after submission.
                        </p>
                    </div>
                </div>
            <?php else: ?>
                <div class="alert alert-info border-0 rounded-4 shadow-sm mb-4 d-flex align-items-start gap-3">
                    <i class="bi bi-shield-check fs-3 text-info flex-shrink-0"></i>
                    <div>
                        <h6 class="fw-bold mb-1">Moderation &amp; Community Safety</h6>
                        <p class="small text-muted mb-0">
                            To protect users and maintain high service quality, every newly submitted service is reviewed by an administrator before appearing on the public <strong>Browse Services</strong> directory. You can check the approval status on your dashboard.
                        </p>
                    </div>
                </div>
            <?php endif; ?>

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
                <form method="post" action="/cshub/freelancer/add-service.php" enctype="multipart/form-data">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Service Title <span class="text-danger">*</span></label>
                        <input type="text" name="title" class="form-control rounded-3" value="<?php echo e($title); ?>" placeholder="e.g., Python &amp; Calculus 1-on-1 Peer Tutoring" maxlength="150" required>
                        <div class="form-text small">Use a clear, concise title describing what you offer (max 150 chars).</div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Category <span class="text-danger">*</span></label>
                            <select name="category_id" class="form-select rounded-3" required>
                                <option value="">Select a Category</option>
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
                                <input type="number" step="0.01" min="0.01" name="price" class="form-control rounded-end-3" value="<?php echo e((string)$price); ?>" placeholder="25.00" required>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Description &amp; Deliverables <span class="text-danger">*</span></label>
                        <textarea name="description" class="form-control rounded-3" rows="5" placeholder="Describe what is included, your experience, requirements, turnaround time, and session details..." required><?php echo e($description); ?></textarea>
                        <div class="form-text small">Minimum 10 characters. Detailed descriptions attract more clients.</div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-bold">Portfolio / Showcase Image <span class="text-muted small fw-normal">(Optional)</span></label>
                        <input type="file" name="portfolio_image" class="form-control rounded-3" accept=".jpg,.jpeg,.png,.webp">
                        <div class="form-text small">Accepted formats: JPG, PNG, WebP (Max 3MB). An attractive image helps clients choose your service.</div>
                    </div>

                    <div class="d-flex align-items-center justify-content-end gap-2 pt-2 border-top">
                        <a href="/cshub/freelancer/dashboard.php" class="btn btn-light rounded-pill px-4">Cancel</a>
                        <button type="submit" class="btn btn-primary rounded-pill px-4 fw-semibold">
                            <i class="bi bi-cloud-arrow-up-fill me-1"></i> Submit for Approval
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
