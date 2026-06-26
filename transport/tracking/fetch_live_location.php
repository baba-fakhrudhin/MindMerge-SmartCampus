<?php

require_once '../../config/auth.php';
require_once '../../config/db.php';
require_once '../../shared/services/TransportService.php';

header('Content-Type: application/json');

if (empty($_SESSION['user'])) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$service = new TransportService($conn);
$userId = (int) ($_SESSION['user']['id'] ?? 0);
$role = strtolower($_SESSION['user']['role'] ?? '');
$busId = isset($_GET['bus_id']) ? (int) $_GET['bus_id'] : 0;

if ($busId > 0) {
    if (!$service->userCanViewBus($userId, $role, $busId)) {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'You do not have access to this bus']);
        exit;
    }

    $bus = $service->getLiveBus($busId);

    echo json_encode([
        'success' => true,
        'data' => $bus,
        'bus_id' => $bus['bus_id'] ?? null,
        'latitude' => $bus['latitude'] ?? null,
        'longitude' => $bus['longitude'] ?? null,
        'status' => $bus['status'] ?? 'not_started',
        'updated_at' => $bus['updated_at'] ?? null,
        'stops' => $bus['stops'] ?? [],
        'progress' => $bus['progress'] ?? null,
        'timestamp' => date('Y-m-d H:i:s'),
    ]);
    exit;
}

if ($role !== 'admin') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'A bus selection is required']);
    exit;
}

$buses = $service->getLiveBuses();

echo json_encode([
    'success' => true,
    'count' => count($buses),
    'data' => $buses,
    'timestamp' => date('Y-m-d H:i:s'),
]);
exit;
