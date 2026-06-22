<?php

include('../config/auth.php');
include('../config/db.php');
require_once __DIR__ . '/../shared/services/ResultsService.php';

requirePermission('results', 'edit');

$result_id = (int) ($_GET['result_id'] ?? 0);
$action = $_GET['action'] ?? 'publish';
$service = new ResultsService($conn);

if ($result_id <= 0) {
    header('Location: index.php');
    exit();
}

if ($action === 'unpublish') {
    $service->unpublishResult($result_id);
    header('Location: view.php?result_id=' . $result_id . '&success=unpublished');
} else {
    $service->publishResult($result_id);
    header('Location: view.php?result_id=' . $result_id . '&success=published');
}

exit();
