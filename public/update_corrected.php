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
$corrected = $data['corrected'] ?? null;

if ($taskId < 1) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'Invalid task_id']);
    exit;
}

if ($corrected === true || $corrected === false) {
    $correctedBool = $corrected;
} elseif ($corrected === 1 || $corrected === 0 || $corrected === '1' || $corrected === '0') {
    $correctedBool = (bool)$corrected;
} else {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'Invalid corrected']);
    exit;
}

$userId = 1;
update_task_corrected($taskId, $userId, $correctedBool);

echo json_encode(['ok' => true, 'corrected' => $correctedBool]);
