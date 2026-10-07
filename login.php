<?php
/**
 * login.php
 * Authenticates a user via email + password_verify(), sets session vars.
 * TODO: full implementation belongs to the Authentication sprint (Section 12/13).
 */

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';

$error = '';
$success = '';

// Check for registration success message
if (isset($_SESSION['registration_success'])) {
    $success = $_SESSION['registration_success'];
    unset($_SESSION['registration_success']);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    // Validation
    if (empty($email)) {
        $error = 'Email is required.';
    } elseif (empty($password)) {
        $error = 'Password is required.';
    } else {
        try {
            // Fetch user by email
            $stmt = $pdo->prepare("SELECT id, name, password, role FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['password'])) {
                // Authentication successful
                // Regenerate session ID to prevent session fixation
                session_regenerate_id(true);

                // Set session variables
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['name'];
                $_SESSION['role'] = $user['role'];

                // Redirect based on role
                if ($user['role'] === 'admin') {
                    redirect('/cshub/admin/dashboard.php');
                } elseif ($user['role'] === 'freelancer') {
                    redirect('/cshub/freelancer/dashboard.php');
                } else {
                    redirect('/cshub/client/dashboard.php');
                }
            } else {
                $error = 'Invalid email or password. Please try again.';
            }
        } catch (PDOException $e) {
            $error = 'Login failed. Please try again.';
            // In production, log the actual error: error_log($e->getMessage());
        }
    }
}

$mainContainerClass = 'auth-page-wrap p-0';
require_once __DIR__ . '/includes/header.php';
?>

<div class="auth-ambient-glow auth-glow-1"></div>
<div class="auth-ambient-glow auth-glow-2"></div>

<div class="auth-container">
    <div class="auth-card shadow-lg">
        <h1><?= __('Welcome Back') ?></h1>
        <p class="subtitle"><?= __('Login to access your ProVenture account') ?></p>

        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo e($success); ?></div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo e($error); ?></div>
        <?php endif; ?>

        <form method="post" action="/cshub/login.php">
            <div class="mb-3">
                <label class="form-label"><?= __('Email Address') ?></label>
                <input type="email" name="email" class="form-control" placeholder="<?= __('your.email@example.com') ?>" required autocomplete="email">
            </div>
            <div class="mb-3">
                <label class="form-label"><?= __('Password') ?></label>
                <div class="position-relative">
                    <input type="password" name="password" id="loginPassword" class="form-control pe-5" placeholder="<?= __('Enter your password') ?>" required autocomplete="current-password">
                    <button type="button" class="btn btn-link position-absolute end-0 top-50 translate-middle-y text-decoration-none text-muted pe-3" id="toggleLoginPassword" aria-label="Toggle password visibility">
                        <span id="loginEyeIcon">👁️</span>
                    </button>
                </div>
            </div>
            <button type="submit" class="btn btn-primary w-100 py-2 fw-bold mt-2 shadow-sm"><?= __('Login') ?></button>
        </form>
        <p class="mt-4 text-center text-muted small"><?= __("Don't have an account?") ?> <a href="/cshub/register.php" class="fw-bold text-decoration-none"><?= __('Register here') ?></a></p>
    </div>
</div>

<script>
// Toggle password visibility for login
document.addEventListener('DOMContentLoaded', function() {
    const toggleBtn = document.getElementById('toggleLoginPassword');
    if (toggleBtn) {
        toggleBtn.addEventListener('click', function() {
            const passwordInput = document.getElementById('loginPassword');
            const eyeIcon = document.getElementById('loginEyeIcon');
            const type = passwordInput.type === 'password' ? 'text' : 'password';
            passwordInput.type = type;
            eyeIcon.textContent = type === 'password' ? '👁️' : '🙈';
        });
    }
});
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
