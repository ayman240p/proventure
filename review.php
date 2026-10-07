<?php
/**
 * review.php
 * Handles review submission (POST) from an authenticated client.
 * (Objective 2 / Section 18)
 * TODO: full implementation belongs to the Reviews/Ratings sprint.
 */

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';

requireLogin();

if (currentRole() !== 'client') {
    redirect('/cshub/index.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $serviceId = (int) ($_POST['service_id'] ?? 0);
    $rating = (int) ($_POST['rating'] ?? 0);
    $reviewText = trim($_POST['review_text'] ?? '');

    if ($serviceId > 0 && $rating >= 1 && $rating <= 5) {
        $check = $pdo->prepare("SELECT id FROM services WHERE id = ?");
        $check->execute([$serviceId]);
        if ($check->fetch()) {
            $stmt = $pdo->prepare("
                INSERT INTO reviews (service_id, client_id, rating, review_text, created_at)
                VALUES (?, ?, ?, ?, NOW())
            ");
            $stmt->execute([$serviceId, $_SESSION['user_id'], $rating, $reviewText]);
        }
    }

    redirect('/cshub/service.php?id=' . $serviceId);
}

redirect('/cshub/index.php');
