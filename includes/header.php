<?php
/**
 * includes/header.php
 * Shared top of every page: doctype, nav bar, session-aware links.
 * Requires includes/auth.php to already be loaded by the calling page.
 */
$styleVer = file_exists(__DIR__ . '/../assets/css/style.css') ? filemtime(__DIR__ . '/../assets/css/style.css') : time();
$themeVer = file_exists(__DIR__ . '/../assets/css/theme.css') ? filemtime(__DIR__ . '/../assets/css/theme.css') : time();
?>
<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="color-scheme" content="light dark">
    <title><?= !empty($pageTitle) ? e($pageTitle) : 'ProVenture | Professional Freelance & Micro-Services' ?></title>
    <script>
    (function() {
        var savedTheme = localStorage.getItem('cshub-theme');
        var theme = isDark ? 'dark' : 'light';
        document.documentElement.setAttribute('data-theme', theme);
        document.documentElement.setAttribute('data-bs-theme', theme);
    })();
    </script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="/cshub/assets/css/style.css?v=<?= $styleVer ?>">
    <link rel="stylesheet" href="/cshub/assets/css/theme.css?v=<?= $themeVer ?>">
</head>
<body class="d-flex flex-column min-vh-100">

<nav class="navbar navbar-expand-lg border-bottom">
    <div class="container">
        <a class="navbar-brand fw-bold" href="/cshub/index.php"><i class="bi bi-briefcase-fill text-primary me-2"></i>ProVenture</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navMain">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navMain">
            <ul class="navbar-nav ms-auto align-items-lg-center">
                <li class="nav-item"><a class="nav-link" href="/cshub/index.php"><?= __('Browse Services') ?></a></li>

                <?php if (isLoggedIn()): ?>
                    <?php if (currentRole() === 'admin'): ?>
                        <li class="nav-item"><a class="nav-link" href="/cshub/admin/dashboard.php"><?= __('Admin Dashboard') ?></a></li>
                    <?php elseif (currentRole() === 'freelancer'): ?>
                        <li class="nav-item"><a class="nav-link" href="/cshub/freelancer/dashboard.php"><?= __('My Dashboard') ?></a></li>
                    <?php else: ?>
                        <li class="nav-item"><a class="nav-link" href="/cshub/client/dashboard.php"><?= __('My Account') ?></a></li>
                    <?php endif; ?>
                    <li class="nav-item"><a class="nav-link" href="/cshub/logout.php"><?= __('Logout') ?></a></li>
                <?php else: ?>
                    <li class="nav-item"><a class="nav-link" href="/cshub/login.php"><?= __('Login') ?></a></li>
                    <li class="nav-item"><a class="nav-link" href="/cshub/register.php"><?= __('Register') ?></a></li>
                <?php endif; ?>

                <!-- Language Switcher -->
                <li class="nav-item dropdown ms-lg-2">
                    <a class="nav-link dropdown-toggle" href="#" id="langDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <?= (isset($_SESSION['lang']) && $_SESSION['lang'] === 'ms') ? 'BM' : 'EN' ?>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="langDropdown">
                        <li><a class="dropdown-item" href="/cshub/switch_lang.php?lang=en">English</a></li>
                        <li><a class="dropdown-item" href="/cshub/switch_lang.php?lang=ms">Bahasa Melayu</a></li>
                    </ul>
                </li>

                <!-- Theme Toggle Button -->
                <li class="nav-item ms-lg-2">
                    <button id="themeToggle" class="btn btn-sm btn-outline-secondary theme-toggle" aria-label="Toggle theme">
                        <span class="theme-icon">🌙</span>
                    </button>
                </li>
            </ul>
        </div>
    </div>
</nav>

<main class="<?php echo htmlspecialchars($mainContainerClass ?? 'container py-4'); ?> flex-grow-1">
