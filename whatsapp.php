<?php
/**
 * whatsapp.php?service_id=X
 * Builds the wa.me Click-to-Chat URL and redirects the client to WhatsApp.
 * (Objective 3 / Section 20)
 * TODO: full implementation belongs to the WhatsApp Integration sprint.
 */

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';

$serviceId = (int) ($_GET['service_id'] ?? 0);

$stmt = $pdo->prepare("
    SELECT services.title, services.availability, users.phone
    FROM services
    LEFT JOIN users ON services.user_id = users.id
    WHERE services.id = ?
");
$stmt->execute([$serviceId]);
$row = $stmt->fetch();

if (!$row || empty($row['phone'])) {
    redirect('/cshub/index.php');
}

// Guard: prevent contact if listing is marked as busy
if (empty($row['availability'])) {
    $_SESSION['flash_error'] = __('This service provider is currently marked as busy and unavailable for new inquiries.');
    redirect('/cshub/service.php?id=' . $serviceId);
}

// TODO: normalize/validate phone number format before building the URL.
$phone = preg_replace('/\D/', '', $row['phone']);
$biz = trim($_GET['biz'] ?? '');
if (!empty($biz)) {
    $message = "Hi, I run a " . $biz . " business and saw your \"" . $row['title'] . "\" listing on ProVenture. I would like to discuss a project.";
} else {
    $message = "Hi, I am interested in your " . $row['title'] . " service on ProVenture.";
}
$url = "https://wa.me/" . $phone . "?text=" . urlencode($message);

redirect($url);
