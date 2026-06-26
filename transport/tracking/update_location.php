<?php

require_once '../../config/auth.php';
require_once '../../config/db.php';
require_once '../../shared/services/TransportService.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user']) || strtolower($_SESSION['user']['role'] ?? '') !== 'driver') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$latitude = isset($_POST['latitude']) && $_POST['latitude'] !== ''
    ? (float) $_POST['latitude']
    : null;
$longitude = isset($_POST['longitude']) && $_POST['longitude'] !== ''
    ? (float) $_POST['longitude']
    : null;
$status = strtolower(trim($_POST['status'] ?? 'running'));

$service = new TransportService($conn);
$result = $service->updateDriverLocation((int) $_SESSION['user']['id'], $latitude, $longitude, $status);

if (!$result['success']) {
    http_response_code(422);
}

echo json_encode($result);
exit;
