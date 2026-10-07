<?php
require_once __DIR__ . '/config/database.php';
$stmt = $pdo->prepare("UPDATE services SET portfolio_image = 'portfolio_calculus_tutoring.jpg' WHERE id = 1");
$stmt->execute();
echo "UPDATED_SERVICE_1_OK";


