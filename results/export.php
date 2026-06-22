<?php

include('../config/auth.php');
include('../config/db.php');
require_once __DIR__ . '/../shared/services/ResultsService.php';

requirePermission('results', 'view');

$result_id = (int) ($_GET['result_id'] ?? 0);
$format = strtolower($_GET['format'] ?? 'csv');
$service = new ResultsService($conn);

if ($result_id <= 0) {
    header('Location: index.php');
    exit();
}

$result = $service->getResultById($result_id);

if (!$result) {
    header('Location: index.php');
    exit();
}

$filename = preg_replace('/[^A-Za-z0-9_-]/', '_', ($result['exam_code'] ?? 'result_' . $result_id)) . '_' . date('Ymd');

if ($format === 'csv') {
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="' . $filename . '.csv"');
    echo $service->exportEntriesCsv($result_id);
    exit();
}

if ($format === 'xls' || $format === 'excel') {
    header('Content-Type: application/vnd.ms-excel');
    header('Content-Disposition: attachment; filename="' . $filename . '.xls"');
    echo $service->exportEntriesExcelHtml($result_id);
    exit();
}

if ($format === 'pdf') {
    header('Content-Type: application/pdf');
    header('Content-Disposition: attachment; filename="' . $filename . '.pdf"');
    echo $service->exportEntriesPdf($result_id);
    exit();
}

header('Location: view.php?result_id=' . $result_id);
exit();
