<?php
require __DIR__ . '/../init.php';
require __DIR__ . '/../src/tasks.php';

header('Content-Type: application/json; charset=utf-8');

$raw = file_get_contents('php://input');
$data = json_decode($raw, true);

if (!is_array($data)) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'Invalid JSON']);
    exit;
}

$taskId = isset($data['task_id']) ? (int)$data['task_id'] : 0;
$state = isset($data['state']) ? (int)$data['state'] : -1;

if ($taskId < 1) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'Invalid task_id']);
    exit;
}

$allowed = [0, 1];
if (!in_array($state, $allowed, true)) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'Invalid state']);
    exit;
}

$userId = 1;
update_task_state($taskId, $userId, $state === 1);

echo json_encode(['ok' => true, 'state' => $state]);
