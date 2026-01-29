<?php
require __DIR__ . '/../init.php';
require __DIR__ . '/../src/tasks.php';
require __DIR__ . '/../src/chat.php';
require __DIR__ . '/../src/ai_service.php';

header('Content-Type: application/json; charset=utf-8');

session_start();

$userId = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : 0;
if ($userId < 1) {
    http_response_code(401);
    echo json_encode(['ok' => false, 'error' => 'Nicht eingeloggt.']);
    exit;
}

$raw = file_get_contents('php://input');
$data = json_decode($raw, true);
if (!is_array($data)) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'Ungültige Anfrage.']);
    exit;
}

$taskId = isset($data['task_id']) ? (int)$data['task_id'] : 0;
$message = trim((string)($data['message'] ?? ''));

if ($taskId < 1 || $message === '') {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'Task oder Nachricht fehlt.']);
    exit;
}

$task = task_by_id($taskId);
if (!$task) {
    http_response_code(404);
    echo json_encode(['ok' => false, 'error' => 'Task nicht gefunden.']);
    exit;
}

try {
    save_chat_message($userId, $taskId, 'user', $message);
    $reply = ai_chat_reply($message);
    if ($reply !== '') {
        save_chat_message($userId, $taskId, 'assistant', $reply);
    }
    echo json_encode(['ok' => true, 'reply' => $reply]);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => $e->getMessage()]);
}
