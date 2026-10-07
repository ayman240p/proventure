<?php
/**
 * admin/settings.php
 * Admin profile and settings page with password change functionality
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/logger.php';

// Require admin role
requireRole('admin');

$success = '';
$error = '';

// Handle password change
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password'])) {
    $currentPassword = $_POST['current_password'] ?? '';
    $newPassword = $_POST['new_password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';

    // Validation
    if (empty($currentPassword)) {
        $error = 'Current password is required.';
    } elseif (empty($newPassword)) {
        $error = 'New password is required.';
    } elseif (strlen($newPassword) < 8) {
        $error = 'New password must be at least 8 characters long.';
    } elseif (!preg_match('/[A-Z]/', $newPassword)) {
        $error = 'New password must contain at least one uppercase letter.';
    } elseif (!preg_match('/[a-z]/', $newPassword)) {
        $error = 'New password must contain at least one lowercase letter.';
    } elseif (!preg_match('/[0-9]/', $newPassword)) {
        $error = 'New password must contain at least one number.';
    } elseif (!preg_match('/[!@#$%^&*(),.?":{}|<>]/', $newPassword)) {
        $error = 'New password must contain at least one special character.';
    } elseif ($newPassword !== $confirmPassword) {
        $error = 'New passwords do not match.';
    } else {
        try {
            // Verify current password
            $stmt = $pdo->prepare("SELECT password FROM users WHERE id = ?");
            $stmt->execute([$_SESSION['user_id']]);
            $user = $stmt->fetch();

            if (!$user || !password_verify($currentPassword, $user['password'])) {
                $error = 'Current password is incorrect.';
            } else {
                // Update password
                $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
                $stmt->execute([$hashedPassword, $_SESSION['user_id']]);

                // Log activity
                logAdminActivity($pdo, 'changed_password', 'user', $_SESSION['user_id'], 'Admin changed their own password');

                $success = 'Password changed successfully!';
            }
        } catch (PDOException $e) {
            $error = 'Failed to change password. Please try again.';
        }
    }
}

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');

    if (empty($name)) {
        $error = 'Name is required.';
    } elseif (empty($email)) {
        $error = 'Email is required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } else {
        try {
            // Check if email is taken by another user
            $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
            $stmt->execute([$email, $_SESSION['user_id']]);

            if ($stmt->fetch()) {
                $error = 'This email is already in use by another account.';
            } else {
                // Update profile
                $stmt = $pdo->prepare("UPDATE users SET name = ?, email = ?, phone = ? WHERE id = ?");
                $stmt->execute([$name, $email, $phone, $_SESSION['user_id']]);

                // Update session name
                $_SESSION['user_name'] = $name;

                // Log activity
                logAdminActivity($pdo, 'updated_profile', 'user', $_SESSION['user_id'], 'Admin updated their profile information');

                $success = 'Profile updated successfully!';
            }
        } catch (PDOException $e) {
            $error = 'Failed to update profile. Please try again.';
        }
    }
}

// Handle moderation settings update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_moderation_settings'])) {
    $autoApproveVal = isset($_POST['auto_approve_services']) && $_POST['auto_approve_services'] === '1' ? '1' : '0';
    $updated = setSystemSetting($pdo, 'auto_approve_services', $autoApproveVal);

    if ($updated) {
        $statusLabel = $autoApproveVal === '1' ? 'Enabled (Instant Publishing)' : 'Disabled (Manual Approval Required)';
        logAdminActivity($pdo, 'updated_settings', 'system', 0, "Set Automatic Service Approval to: {$statusLabel}");
        $success = "System moderation settings updated successfully! Automatic Service Approval is now {$statusLabel}.";
    } else {
        $error = 'Failed to update moderation settings. Please try again.';
    }
}

// Get current admin info
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$admin = $stmt->fetch();

$autoApproveSetting = getSystemSetting($pdo, 'auto_approve_services', '1') === '1';

require_once __DIR__ . '/../includes/header.php';
?>

<div class="container py-4">
    <div class="row mb-4">
        <div class="col-md-6">
            <h1 class="h3 mb-1">Admin Settings</h1>
            <p class="text-muted">Manage your profile and account settings</p>
        </div>
        <div class="col-md-6 text-end">
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
        <!-- Profile Information -->
        <div class="col-lg-6 mb-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0 py-3">
                    <h5 class="mb-0">Profile Information</h5>
                </div>
                <div class="card-body">
                    <form method="post" action="/cshub/admin/settings.php">
                        <div class="mb-3">
                            <label class="form-label">Full Name</label>
                            <input type="text" name="name" class="form-control" value="<?php echo e($admin['name']); ?>" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Email Address</label>
                            <input type="email" name="email" class="form-control" value="<?php echo e($admin['email']); ?>" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Phone Number</label>
                            <input type="text" name="phone" class="form-control" value="<?php echo e($admin['phone']); ?>" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Role</label>
                            <input type="text" class="form-control" value="Administrator" disabled>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Member Since</label>
                            <input type="text" class="form-control" value="<?php echo date('F d, Y', strtotime($admin['created_at'])); ?>" disabled>
                        </div>
                        <button type="submit" name="update_profile" class="btn btn-primary w-100">Update Profile</button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Change Password -->
        <div class="col-lg-6 mb-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0 py-3">
                    <h5 class="mb-0">Change Password</h5>
                </div>
                <div class="card-body">
                    <form method="post" action="/cshub/admin/settings.php" id="passwordForm">
                        <div class="mb-3">
                            <label class="form-label">Current Password</label>
                            <div class="position-relative">
                                <input type="password" name="current_password" id="currentPassword" class="form-control pe-5" required>
                                <button type="button" class="btn btn-link position-absolute end-0 top-50 translate-middle-y" id="toggleCurrent" style="text-decoration: none;">
                                    <span id="eyeCurrent">👁️</span>
                                </button>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">New Password</label>
                            <div class="position-relative">
                                <input type="password" name="new_password" id="newPassword" class="form-control pe-5" minlength="8" required>
                                <button type="button" class="btn btn-link position-absolute end-0 top-50 translate-middle-y" id="toggleNew" style="text-decoration: none;">
                                    <span id="eyeNew">👁️</span>
                                </button>
                            </div>

                            <!-- Password Strength Meter -->
                            <div class="mt-2">
                                <div class="password-strength-bar">
                                    <div id="strengthBar" class="strength-bar-fill"></div>
                                </div>
                                <small id="strengthText" class="text-muted">Password strength: <span id="strengthLevel">-</span></small>
                            </div>

                            <!-- Password Requirements -->
                            <div class="mt-2 password-requirements-mini">
                                <small class="d-block mb-1 fw-bold text-muted">Must have:</small>
                                <div class="requirement-mini" id="req-length">
                                    <span class="req-icon-mini">○</span>
                                    <span class="req-text-mini">8+ chars</span>
                                </div>
                                <div class="requirement-mini" id="req-uppercase">
                                    <span class="req-icon-mini">○</span>
                                    <span class="req-text-mini">A-Z</span>
                                </div>
                                <div class="requirement-mini" id="req-lowercase">
                                    <span class="req-icon-mini">○</span>
                                    <span class="req-text-mini">a-z</span>
                                </div>
                                <div class="requirement-mini" id="req-number">
                                    <span class="req-icon-mini">○</span>
                                    <span class="req-text-mini">0-9</span>
                                </div>
                                <div class="requirement-mini" id="req-special">
                                    <span class="req-icon-mini">○</span>
                                    <span class="req-text-mini">!@#$</span>
                                </div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Confirm New Password</label>
                            <div class="position-relative">
                                <input type="password" name="confirm_password" id="confirmPassword" class="form-control pe-5" minlength="8" required>
                                <button type="button" class="btn btn-link position-absolute end-0 top-50 translate-middle-y" id="toggleConfirm" style="text-decoration: none;">
                                    <span id="eyeConfirm">👁️</span>
                                </button>
                            </div>
                            <small id="passwordMatch" class="text-muted"></small>
                        </div>
                        <button type="submit" name="change_password" class="btn btn-warning w-100">Change Password</button>
                    </form>

                    <hr class="my-4">

                    <h6 class="mb-3">Password Security Tips</h6>
                    <ul class="small text-muted">
                        <li>Use at least 8 characters with mixed case</li>
                        <li>Include numbers and special characters</li>
                        <li>Avoid common words or personal information</li>
                        <li>Never share your password with anyone</li>
                        <li>Use a unique password for ProVenture</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <!-- System & Moderation Settings -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                <div class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <div class="d-flex align-items-center gap-2">
                        <div class="rounded-3 bg-primary bg-opacity-10 p-2 text-primary">
                            <i class="bi bi-sliders fs-5"></i>
                        </div>
                        <div>
                            <h5 class="mb-0 fw-bold">Platform Moderation &amp; Approval Settings</h5>
                            <small class="text-muted">Manage automated publishing rules and community safeguarding policies</small>
                        </div>
                    </div>
                    <div>
                        <?php if ($autoApproveSetting): ?>
                            <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-3 py-2">
                                <i class="bi bi-check-circle-fill me-1"></i> Auto-Approval Active
                            </span>
                        <?php else: ?>
                            <span class="badge bg-warning-subtle text-dark border border-warning-subtle rounded-pill px-3 py-2">
                                <i class="bi bi-shield-lock-fill me-1"></i> Manual Moderation Mode
                            </span>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="card-body p-4">
                    <form method="post" action="/cshub/admin/settings.php">
                        <div class="p-3 bg-light rounded-4 mb-4 border">
                            <div class="form-check form-switch d-flex align-items-center justify-content-between p-0 mb-3">
                                <div>
                                    <label class="form-check-label fw-bold fs-6 mb-1 text-dark" for="autoApproveToggle">
                                        Automatic Micro-Service Approval (Instant Live Publishing)
                                    </label>
                                    <p class="text-muted small mb-0 pe-md-4">
                                        When enabled, all newly submitted and updated micro-services from freelancers are immediately published to the live directory. This prevents platform bottlenecks and ensures uninterrupted service even if administrators are absent.
                                    </p>
                                </div>
                                <input class="form-check-input ms-3" type="checkbox" role="switch" id="autoApproveToggle" 
                                       name="auto_approve_services" value="1" style="width: 3rem; height: 1.6rem; cursor: pointer;"
                                       <?php echo $autoApproveSetting ? 'checked' : ''; ?>>
                            </div>

                            <div class="d-flex align-items-center gap-2 p-2 px-3 rounded-3 bg-white border small text-muted">
                                <i class="bi bi-shield-fill-check text-success fs-5"></i>
                                <div>
                                    <strong>Community Spam Safeguard:</strong> All live listings feature a direct <strong>Report</strong> button. If users flag any listing as spam or malicious, it is immediately escalated to the <a href="/cshub/admin/reports.php" class="fw-semibold text-decoration-none">Reports Center</a> for rapid takedown.
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                            <a href="/cshub/admin/reports.php" class="btn btn-outline-danger btn-sm rounded-pill px-3">
                                <i class="bi bi-flag-fill me-1"></i> View Reported Listings
                            </a>
                            <button type="submit" name="update_moderation_settings" class="btn btn-primary rounded-pill px-4">
                                <i class="bi bi-save me-1"></i> Save Moderation Settings
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Account Statistics -->
    <div class="row">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0 py-3">
                    <h5 class="mb-0">Your Activity</h5>
                </div>
                <div class="card-body">
                    <?php
                    // Get admin's recent activity
                    $recentActivity = $pdo->prepare("
                        SELECT action, target_type, target_id, details, created_at
                        FROM activity_logs
                        WHERE admin_id = ?
                        ORDER BY created_at DESC
                        LIMIT 10
                    ");
                    $recentActivity->execute([$_SESSION['user_id']]);
                    $activities = $recentActivity->fetchAll();
                    ?>

                    <?php if (count($activities) > 0): ?>
                        <div class="table-responsive">
                            <table class="table table-sm mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Action</th>
                                        <th>Target</th>
                                        <th>Details</th>
                                        <th>Time</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($activities as $activity): ?>
                                        <tr>
                                            <td>
                                                <span class="badge bg-secondary">
                                                    <?php echo e(str_replace('_', ' ', ucfirst($activity['action']))); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <?php echo e(ucfirst($activity['target_type'])); ?>
                                                <?php if ($activity['target_id']): ?>
                                                    #<?php echo $activity['target_id']; ?>
                                                <?php endif; ?>
                                            </td>
                                            <td class="small"><?php echo e($activity['details'] ?? '-'); ?></td>
                                            <td class="text-muted small">
                                                <?php echo date('M d, H:i', strtotime($activity['created_at'])); ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <div class="text-center mt-3">
                            <a href="/cshub/admin/activity-logs.php?admin=<?php echo $_SESSION['user_id']; ?>" class="btn btn-sm btn-outline-primary">
                                View All My Activity
                            </a>
                        </div>
                    <?php else: ?>
                        <p class="text-muted text-center mb-0">No activity recorded yet.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Password Strength Styles -->
<style>
.password-strength-bar {
    width: 100%;
    height: 6px;
    background-color: #e9ecef;
    border-radius: 3px;
    overflow: hidden;
}

.strength-bar-fill {
    height: 100%;
    width: 0%;
    transition: all 0.3s ease;
    border-radius: 3px;
}

.strength-weak { background-color: #dc3545; width: 25%; }
.strength-fair { background-color: #ffc107; width: 50%; }
.strength-good { background-color: #17a2b8; width: 75%; }
.strength-strong { background-color: #28a745; width: 100%; }

.password-requirements-mini {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
    padding: 8px;
    background-color: #f8f9fa;
    border-radius: 0.5rem;
}

.requirement-mini {
    display: flex;
    align-items: center;
    gap: 4px;
}

.req-icon-mini {
    font-size: 12px;
    color: #6c757d;
    font-weight: bold;
}

.req-text-mini {
    font-size: 11px;
    color: #6c757d;
}

.requirement-mini.met .req-icon-mini {
    color: #28a745;
}

.requirement-mini.met .req-text-mini {
    color: #28a745;
}

.requirement-mini.met .req-icon-mini::before {
    content: '✓';
}
</style>

<!-- Password Strength JavaScript -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Toggle password visibility
    const toggles = [
        { btn: 'toggleCurrent', input: 'currentPassword', icon: 'eyeCurrent' },
        { btn: 'toggleNew', input: 'newPassword', icon: 'eyeNew' },
        { btn: 'toggleConfirm', input: 'confirmPassword', icon: 'eyeConfirm' }
    ];

    toggles.forEach(t => {
        const btn = document.getElementById(t.btn);
        if (btn) {
            btn.addEventListener('click', function() {
                const input = document.getElementById(t.input);
                const icon = document.getElementById(t.icon);
                const type = input.type === 'password' ? 'text' : 'password';
                input.type = type;
                icon.textContent = type === 'password' ? '👁️' : '🙈';
            });
        }
    });

    const newPasswordInput = document.getElementById('newPassword');
    const confirmPasswordInput = document.getElementById('confirmPassword');
    const strengthBar = document.getElementById('strengthBar');
    const strengthLevel = document.getElementById('strengthLevel');
    const passwordMatch = document.getElementById('passwordMatch');

    const requirements = {
        length: document.getElementById('req-length'),
        uppercase: document.getElementById('req-uppercase'),
        lowercase: document.getElementById('req-lowercase'),
        number: document.getElementById('req-number'),
        special: document.getElementById('req-special')
    };

    function checkPasswordStrength() {
        const password = newPasswordInput.value;

        const checks = {
            length: password.length >= 8,
            uppercase: /[A-Z]/.test(password),
            lowercase: /[a-z]/.test(password),
            number: /[0-9]/.test(password),
            special: /[!@#$%^&*(),.?":{}|<>]/.test(password)
        };

        Object.keys(checks).forEach(key => {
            if (checks[key]) {
                requirements[key].classList.add('met');
            } else {
                requirements[key].classList.remove('met');
            }
        });

        const metCount = Object.values(checks).filter(Boolean).length;
        strengthBar.className = 'strength-bar-fill';

        if (password.length === 0) {
            strengthLevel.textContent = '-';
            strengthLevel.style.color = '#6c757d';
        } else if (metCount <= 2) {
            strengthBar.classList.add('strength-weak');
            strengthLevel.textContent = 'Weak';
            strengthLevel.style.color = '#dc3545';
        } else if (metCount === 3) {
            strengthBar.classList.add('strength-fair');
            strengthLevel.textContent = 'Fair';
            strengthLevel.style.color = '#ffc107';
        } else if (metCount === 4) {
            strengthBar.classList.add('strength-good');
            strengthLevel.textContent = 'Good';
            strengthLevel.style.color = '#17a2b8';
        } else if (metCount === 5) {
            strengthBar.classList.add('strength-strong');
            strengthLevel.textContent = 'Strong';
            strengthLevel.style.color = '#28a745';
        }

        checkPasswordMatch();
    }

    function checkPasswordMatch() {
        const password = newPasswordInput.value;
        const confirmPassword = confirmPasswordInput.value;

        if (confirmPassword.length === 0) {
            passwordMatch.textContent = '';
            return false;
        }

        if (password === confirmPassword) {
            passwordMatch.textContent = '✓ Passwords match';
            passwordMatch.className = 'text-success';
            return true;
        } else {
            passwordMatch.textContent = '✗ Passwords do not match';
            passwordMatch.className = 'text-danger';
            return false;
        }
    }

    newPasswordInput.addEventListener('input', checkPasswordStrength);
    confirmPasswordInput.addEventListener('input', checkPasswordMatch);

    // Form validation
    document.getElementById('passwordForm').addEventListener('submit', function(e) {
        const password = newPasswordInput.value;
        const confirmPassword = confirmPasswordInput.value;

        if (password.length < 8) {
            e.preventDefault();
            alert('New password must be at least 8 characters long.');
            return false;
        }

        if (!/[A-Z]/.test(password)) {
            e.preventDefault();
            alert('New password must contain at least one uppercase letter.');
            return false;
        }

        if (!/[a-z]/.test(password)) {
            e.preventDefault();
            alert('New password must contain at least one lowercase letter.');
            return false;
        }

        if (!/[0-9]/.test(password)) {
            e.preventDefault();
            alert('New password must contain at least one number.');
            return false;
        }

        if (!/[!@#$%^&*(),.?":{}|<>]/.test(password)) {
            e.preventDefault();
            alert('New password must contain at least one special character.');
            return false;
        }

        if (password !== confirmPassword) {
            e.preventDefault();
            alert('Passwords do not match.');
            return false;
        }
    });
});
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
