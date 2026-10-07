<?php
/**
 * register.php
 * Registers a new user as either 'client' or 'freelancer'.
 * TODO: full implementation belongs to the Authentication sprint (Section 12/13).
 */

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $phone = trim($_POST['phone'] ?? '');
    $role = $_POST['role'] ?? 'client';

    // Validation
    if (empty($name)) {
        $error = 'Full name is required.';
    } elseif (empty($email)) {
        $error = 'Email is required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } elseif (empty($password)) {
        $error = 'Password is required.';
    } elseif (strlen($password) < 8) {
        $error = 'Password must be at least 8 characters long.';
    } elseif (!preg_match('/[A-Z]/', $password)) {
        $error = 'Password must contain at least one uppercase letter.';
    } elseif (!preg_match('/[a-z]/', $password)) {
        $error = 'Password must contain at least one lowercase letter.';
    } elseif (!preg_match('/[0-9]/', $password)) {
        $error = 'Password must contain at least one number.';
    } elseif (!preg_match('/[!@#$%^&*(),.?":{}|<>]/', $password)) {
        $error = 'Password must contain at least one special character.';
    } elseif (empty($phone)) {
        $error = 'Phone number is required.';
    } elseif (!in_array($role, ['client', 'freelancer'])) {
        $error = 'Invalid role selected.';
    } else {
        try {
            // Check email uniqueness
            $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->execute([$email]);

            if ($stmt->fetch()) {
                $error = 'This email is already registered. Please use a different email or login.';
            } else {
                // Hash password
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

                // Insert new user
                $stmt = $pdo->prepare(
                    "INSERT INTO users (name, email, password, phone, role)
                     VALUES (?, ?, ?, ?, ?)"
                );
                $stmt->execute([$name, $email, $hashedPassword, $phone, $role]);

                // Success - redirect to login
                $_SESSION['registration_success'] = 'Registration successful! Please login to continue.';
                redirect('/cshub/login.php');
            }
        } catch (PDOException $e) {
            $error = 'Registration failed. Please try again.';
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
        <h1><?= __('Join ProVenture') ?></h1>
        <p class="subtitle"><?= __('Create your account to get started') ?></p>

        <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo e($error); ?></div>
        <?php endif; ?>

        <form method="post" action="/cshub/register.php" id="registerForm">
            <div class="mb-3">
                <label class="form-label"><?= __('Full Name') ?></label>
                <input type="text" name="name" id="name" class="form-control" placeholder="<?= __('John Doe') ?>" required autocomplete="name">
            </div>
            <div class="mb-3">
                <label class="form-label"><?= __('Email Address') ?></label>
                <input type="email" name="email" id="email" class="form-control" placeholder="<?= __('your.email@example.com') ?>" required autocomplete="email">
            </div>
            <div class="mb-3">
                <label class="form-label"><?= __('Phone Number (WhatsApp)') ?></label>
                <input type="text" name="phone" id="phone" class="form-control" placeholder="<?= __('e.g. 601XXXXXXXX') ?>" required autocomplete="tel">
            </div>
            <div class="mb-3">
                <label class="form-label"><?= __('Password') ?></label>
                <div class="position-relative">
                    <input type="password" name="password" id="password" class="form-control pe-5" placeholder="<?= __('Create a strong password') ?>" required autocomplete="new-password">
                    <button type="button" class="btn btn-link position-absolute end-0 top-50 translate-middle-y text-decoration-none text-muted pe-3" id="togglePassword" aria-label="Toggle password visibility">
                        <span id="eyeIcon">👁️</span>
                    </button>
                </div>

                <!-- Password Strength Meter -->
                <div class="mt-2">
                    <div class="password-strength-bar">
                        <div id="strengthBar" class="strength-bar-fill"></div>
                    </div>
                    <small id="strengthText" class="text-muted"><?= __('Password strength:') ?> <span id="strengthLevel" class="fw-bold">-</span></small>
                </div>

                <!-- Password Requirements -->
                <div class="mt-3 password-requirements">
                    <small class="d-block mb-2 fw-bold text-muted"><?= __('Password must contain:') ?></small>
                    <div class="requirement" id="req-length">
                        <span class="req-icon">○</span>
                        <span class="req-text"><?= __('At least 8 characters') ?></span>
                    </div>
                    <div class="requirement" id="req-uppercase">
                        <span class="req-icon">○</span>
                        <span class="req-text"><?= __('One uppercase letter (A-Z)') ?></span>
                    </div>
                    <div class="requirement" id="req-lowercase">
                        <span class="req-icon">○</span>
                        <span class="req-text"><?= __('One lowercase letter (a-z)') ?></span>
                    </div>
                    <div class="requirement" id="req-number">
                        <span class="req-icon">○</span>
                        <span class="req-text"><?= __('One number (0-9)') ?></span>
                    </div>
                    <div class="requirement" id="req-special">
                        <span class="req-icon">○</span>
                        <span class="req-text"><?= __('One special character (!@#$%^&*)') ?></span>
                    </div>
                </div>
            </div>
            <div class="mb-3">
                <label class="form-label"><?= __('Confirm Password') ?></label>
                <div class="position-relative">
                    <input type="password" name="confirm_password" id="confirmPassword" class="form-control pe-5" placeholder="<?= __('Re-enter your password') ?>" required autocomplete="new-password">
                    <button type="button" class="btn btn-link position-absolute end-0 top-50 translate-middle-y text-decoration-none text-muted pe-3" id="toggleConfirmPassword" aria-label="Toggle confirm password visibility">
                        <span id="eyeIconConfirm">👁️</span>
                    </button>
                </div>
                <small id="passwordMatch" class="text-muted d-block mt-1"></small>
            </div>
            <div class="mb-3">
                <label class="form-label"><?= __('I am a:') ?></label>
                <select name="role" class="form-select">
                    <option value="client"><?= __('Client - Looking for services') ?></option>
                    <option value="freelancer"><?= __('Freelancer - Offering services') ?></option>
                </select>
            </div>
            <button type="submit" class="btn btn-primary w-100 py-2 fw-bold mt-2 shadow-sm" id="submitBtn"><?= __('Create Account') ?></button>
        </form>
        <p class="mt-4 text-center text-muted small"><?= __('Already have an account?') ?> <a href="/cshub/login.php" class="fw-bold text-decoration-none"><?= __('Login here') ?></a></p>
    </div>
</div>

<!-- Password Strength Styles -->
<style>
.password-strength-bar {
    width: 100%;
    height: 6px;
    background-color: var(--border-color, #e9ecef);
    border-radius: 3px;
    overflow: hidden;
}

.strength-bar-fill {
    height: 100%;
    width: 0%;
    transition: all 0.3s ease;
    border-radius: 3px;
}

.strength-weak {
    background-color: #dc3545;
    width: 25%;
}

.strength-fair {
    background-color: #ffc107;
    width: 50%;
}

.strength-good {
    background-color: #17a2b8;
    width: 75%;
}

.strength-strong {
    background-color: #28a745;
    width: 100%;
}

.password-requirements {
    padding: 12px;
    background-color: var(--bg-tertiary, #f8f9fa);
    border: 1px solid var(--border-color, #dee2e6);
    border-radius: 12px;
}

.requirement {
    display: flex;
    align-items: center;
    gap: 8px;
    margin-bottom: 6px;
    color: var(--text-secondary, #6c757d);
    font-size: 0.85rem;
    transition: all 0.2s ease;
}

.requirement:last-child {
    margin-bottom: 0;
}

.req-icon {
    font-size: 14px;
    color: #6c757d;
    font-weight: bold;
}

.req-text {
    font-size: 13px;
    color: #6c757d;
}

.requirement.met .req-icon {
    color: #28a745;
}

.requirement.met .req-text {
    color: #28a745;
}

.requirement.met .req-icon::before {
    content: '✓';
}
</style>

<!-- Password Strength JavaScript -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const passwordInput = document.getElementById('password');
    const confirmPasswordInput = document.getElementById('confirmPassword');
    const strengthBar = document.getElementById('strengthBar');
    const strengthLevel = document.getElementById('strengthLevel');
    const submitBtn = document.getElementById('submitBtn');
    const passwordMatch = document.getElementById('passwordMatch');

    // Toggle password visibility
    document.getElementById('togglePassword').addEventListener('click', function() {
        const type = passwordInput.type === 'password' ? 'text' : 'password';
        passwordInput.type = type;
        document.getElementById('eyeIcon').textContent = type === 'password' ? '👁️' : '🙈';
    });

    document.getElementById('toggleConfirmPassword').addEventListener('click', function() {
        const type = confirmPasswordInput.type === 'password' ? 'text' : 'password';
        confirmPasswordInput.type = type;
        document.getElementById('eyeIconConfirm').textContent = type === 'password' ? '👁️' : '🙈';
    });

    const requirements = {
        length: document.getElementById('req-length'),
        uppercase: document.getElementById('req-uppercase'),
        lowercase: document.getElementById('req-lowercase'),
        number: document.getElementById('req-number'),
        special: document.getElementById('req-special')
    };

    function checkPasswordStrength() {
        const password = passwordInput.value;

        // Check requirements
        const checks = {
            length: password.length >= 8,
            uppercase: /[A-Z]/.test(password),
            lowercase: /[a-z]/.test(password),
            number: /[0-9]/.test(password),
            special: /[!@#$%^&*(),.?":{}|<>]/.test(password)
        };

        // Update requirement indicators
        Object.keys(checks).forEach(key => {
            if (checks[key]) {
                requirements[key].classList.add('met');
            } else {
                requirements[key].classList.remove('met');
            }
        });

        // Calculate strength
        const metCount = Object.values(checks).filter(Boolean).length;

        // Remove all strength classes
        strengthBar.className = 'strength-bar-fill';

        // Update strength bar and text
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

        // Check if all requirements are met
        const allMet = Object.values(checks).every(Boolean);

        // Check password match
        checkPasswordMatch();

        return allMet;
    }

    function checkPasswordMatch() {
        const password = passwordInput.value;
        const confirmPassword = confirmPasswordInput.value;

        if (confirmPassword.length === 0) {
            passwordMatch.textContent = '';
            passwordMatch.className = 'text-muted';
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

    passwordInput.addEventListener('input', checkPasswordStrength);
    confirmPasswordInput.addEventListener('input', checkPasswordMatch);

    // Form validation on submit
    document.getElementById('registerForm').addEventListener('submit', function(e) {
        const password = passwordInput.value;
        const confirmPassword = confirmPasswordInput.value;

        if (password.length < 8) {
            e.preventDefault();
            alert('Password must be at least 8 characters long.');
            return false;
        }

        if (!/[A-Z]/.test(password)) {
            e.preventDefault();
            alert('Password must contain at least one uppercase letter.');
            return false;
        }

        if (!/[a-z]/.test(password)) {
            e.preventDefault();
            alert('Password must contain at least one lowercase letter.');
            return false;
        }

        if (!/[0-9]/.test(password)) {
            e.preventDefault();
            alert('Password must contain at least one number.');
            return false;
        }

        if (!/[!@#$%^&*(),.?":{}|<>]/.test(password)) {
            e.preventDefault();
            alert('Password must contain at least one special character.');
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

<?php require_once __DIR__ . '/includes/footer.php'; ?>
