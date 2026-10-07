<?php
/**
 * includes/auth.php
 * Session bootstrap + role-based access helpers.
 * Full login/register/logout logic lives in login.php / register.php /
 * logout.php — this file just centralizes the checks other pages use
 * to guard access.
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Is anyone currently logged in?
 */
function isLoggedIn(): bool
{
    return isset($_SESSION['user_id']);
}

/**
 * Get the logged-in user's role, or null if not logged in.
 */
function currentRole(): ?string
{
    return $_SESSION['role'] ?? null;
}

/**
 * Require the user to be logged in at all. Redirects to login.php otherwise.
 */
function requireLogin(): void
{
    if (!isLoggedIn()) {
        redirect('/cshub/login.php');
    }
}

/**
 * Require the user to be logged in AND have a specific role.
 * Example: requireRole('freelancer'); at the top of freelancer/dashboard.php
 */
function requireRole(string $role): void
{
    requireLogin();
    if (currentRole() !== $role) {
        redirect('/cshub/index.php');
    }
}
