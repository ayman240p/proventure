<?php
require_once __DIR__ . '/language.php';

/**
 * includes/functions.php
 * Shared helper functions used across ProVenture pages.
 * Feature-specific logic (ratings, WhatsApp URL building, etc.) will be
 * fleshed out in later sprints — this file currently holds the general
 * utilities the rest of the scaffold depends on.
 */

/**
 * Escape a string for safe HTML output.
 */
function e(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

/**
 * Redirect to a given path relative to the site root and stop execution.
 */
function redirect(string $path): void
{
    header("Location: " . $path);
    exit;
}

/**
 * Format a price consistently across the site.
 */
function formatPrice(float $price): string
{
    return "RM " . number_format($price, 2);
}

/**
 * Get a system setting value by key, with fallback default.
 */
function getSystemSetting(PDO $pdo, string $key, string $default = ''): string
{
    static $cache = [];
    if (isset($cache[$key])) {
        return $cache[$key];
    }

    try {
        $stmt = $pdo->prepare("SELECT setting_value FROM system_settings WHERE setting_key = ?");
        $stmt->execute([$key]);
        $val = $stmt->fetchColumn();
        if ($val !== false) {
            $cache[$key] = (string) $val;
            return $cache[$key];
        }
    } catch (PDOException $e) {
        // Table may not exist yet or connection error, return default
    }

    $cache[$key] = $default;
    return $default;
}

/**
 * Set a system setting value by key.
 */
function setSystemSetting(PDO $pdo, string $key, string $value): bool
{
    try {
        $stmt = $pdo->prepare("
            INSERT INTO system_settings (setting_key, setting_value)
            VALUES (?, ?)
            ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)
        ");
        return $stmt->execute([$key, $value]);
    } catch (PDOException $e) {
        return false;
    }
}

