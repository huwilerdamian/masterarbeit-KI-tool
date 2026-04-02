<?php
require __DIR__ . '/../init.php';
require __DIR__ . '/../src/tasks.php';

session_start();

header('Content-Type: application/json; charset=utf-8');

$raw = file_get_contents('php://input');
$data = json_decode($raw, true);

if (!is_array($data)) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'Invalid JSON']);
    exit;
}

$taskProgressId = isset($data['task_progress_id']) ? (int)$data['task_progress_id'] : 0;
$userId = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : 0;

if ($userId < 1) {
    http_response_code(401);
    echo json_encode(['ok' => false, 'error' => 'Unauthorized']);
    exit;
}

if ($taskProgressId < 1) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'Invalid task_progress_id']);
    exit;
}

$solutionViewCount = increment_solution_view_count($taskProgressId, $userId);

echo json_encode([
    'ok' => true,
    'solution_view_count' => $solutionViewCount,
]);
