<?php
/**
 * report_service.php
 * Endpoint for reporting listings (spam, malicious, misleading, inappropriate).
 * Accessible to all users (logged-in clients, freelancers, and guests).
 */

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';

$isAjax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest')
    || (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    if ($isAjax) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
        exit;
    }
    redirect('/cshub/index.php');
}

$serviceId = isset($_POST['service_id']) ? (int)$_POST['service_id'] : 0;
$reason = trim($_POST['reason'] ?? 'spam');
$details = trim($_POST['details'] ?? '');
$redirectUrl = $_POST['redirect_url'] ?? ('/cshub/service.php?id=' . $serviceId);

// Basic sanitize redirect url to prevent open redirect
if (!str_starts_with($redirectUrl, '/cshub/')) {
    $redirectUrl = '/cshub/service.php?id=' . $serviceId;
}

$validReasons = ['spam', 'malicious', 'misleading', 'inappropriate', 'other'];
if (!in_array($reason, $validReasons, true)) {
    $reason = 'spam';
}

if ($serviceId <= 0) {
    if ($isAjax) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Invalid service selected.']);
        exit;
    }
    $_SESSION['flash_error'] = 'Invalid service selected.';
    redirect('/cshub/index.php');
}

try {
    // 1. Check service existence
    $stmt = $pdo->prepare("SELECT id, title FROM services WHERE id = ?");
    $stmt->execute([$serviceId]);
    $service = $stmt->fetch();

    if (!$service) {
        if ($isAjax) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Service not found.']);
            exit;
        }
        $_SESSION['flash_error'] = 'The listing you are reporting does not exist.';
        redirect('/cshub/index.php');
    }

    // 2. Identify reporter
    $reporterId = null;
    $reporterName = null;
    $reporterEmail = null;

    if (isLoggedIn()) {
        $reporterId = (int)$_SESSION['user_id'];
        $userStmt = $pdo->prepare("SELECT name, email FROM users WHERE id = ?");
        $userStmt->execute([$reporterId]);
        $user = $userStmt->fetch();
        if ($user) {
            $reporterName = $user['name'];
            $reporterEmail = $user['email'];
        }
    } else {
        $reporterName = trim($_POST['reporter_name'] ?? 'Guest User');
        $reporterEmail = trim($_POST['reporter_email'] ?? '');
    }

    // 3. Prevent duplicate duplicate spam reports within 2 minutes from same user or email
    if ($reporterId !== null) {
        $checkStmt = $pdo->prepare("
            SELECT id FROM service_reports 
            WHERE service_id = ? AND reporter_id = ? AND created_at > DATE_SUB(NOW(), INTERVAL 2 MINUTE)
        ");
        $checkStmt->execute([$serviceId, $reporterId]);
    } else {
        $checkStmt = $pdo->prepare("
            SELECT id FROM service_reports 
            WHERE service_id = ? AND reporter_email = ? AND created_at > DATE_SUB(NOW(), INTERVAL 2 MINUTE)
        ");
        $checkStmt->execute([$serviceId, $reporterEmail]);
    }

    if ($checkStmt->fetch()) {
        $msg = 'You have already submitted a recent report for this service. Our administration team is reviewing it.';
        if ($isAjax) {
            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'message' => $msg]);
            exit;
        }
        $_SESSION['flash_success'] = $msg;
        redirect($redirectUrl);
    }

    // 4. Insert report
    $insertStmt = $pdo->prepare("
        INSERT INTO service_reports (service_id, reporter_id, reporter_name, reporter_email, reason, details, status)
        VALUES (?, ?, ?, ?, ?, ?, 'pending')
    ");
    $insertStmt->execute([
        $serviceId,
        $reporterId,
        $reporterName ?: 'Anonymous',
        $reporterEmail,
        $reason,
        $details
    ]);

    $successMsg = 'Thank you for reporting this listing. Our admin team will inspect it for spam or malicious content.';
    if ($isAjax) {
        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'message' => $successMsg]);
        exit;
    }

    $_SESSION['flash_success'] = $successMsg;
    redirect($redirectUrl);

} catch (PDOException $e) {
    if ($isAjax) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'An error occurred while submitting your report.']);
        exit;
    }
    $_SESSION['flash_error'] = 'An error occurred while submitting your report. Please try again.';
    redirect($redirectUrl);
}
