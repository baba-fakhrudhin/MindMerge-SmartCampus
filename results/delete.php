<?php

include('../config/auth.php');
include('../config/db.php');
require_once __DIR__ . '/../shared/services/ResultsService.php';

requirePermission('results', 'delete');

$result_id = (int) ($_GET['result_id'] ?? 0);
$service = new ResultsService($conn);

if ($result_id > 0) {
    $service->deleteResult($result_id);
}

header('Location: index.php?success=deleted');
exit();
