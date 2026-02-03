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

$contentType = $_SERVER['CONTENT_TYPE'] ?? '';
$isJson = str_starts_with($contentType, 'application/json');

$taskId = 0;
$message = '';
$uploadedFile = null;

if ($isJson) {
    $raw = file_get_contents('php://input');
    $data = json_decode($raw, true);
    if (!is_array($data)) {
        http_response_code(400);
        echo json_encode(['ok' => false, 'error' => 'Ungültige Anfrage.']);
        exit;
    }
    $taskId = isset($data['task_id']) ? (int)$data['task_id'] : 0;
    $message = trim((string)($data['message'] ?? ''));
} else {
    $taskId = isset($_POST['task_id']) ? (int)$_POST['task_id'] : 0;
    $message = trim((string)($_POST['message'] ?? ''));
    if (isset($_FILES['file']) && is_array($_FILES['file']) && ($_FILES['file']['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE) {
        $uploadedFile = $_FILES['file'];
    }
}

if ($taskId < 1 || ($message === '' && $uploadedFile === null)) {
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
    $filePath = null;
    $fileName = null;
    $imageDataUri = null;

    if ($uploadedFile !== null && $uploadedFile['error'] !== UPLOAD_ERR_NO_FILE) {
        if ($uploadedFile['error'] !== UPLOAD_ERR_OK) {
            $uploadErrorMessages = [
                UPLOAD_ERR_INI_SIZE => 'Die Datei ist zu groß (upload_max_filesize).',
                UPLOAD_ERR_FORM_SIZE => 'Die Datei ist zu groß (FORM_SIZE).',
                UPLOAD_ERR_PARTIAL => 'Die Datei wurde nur teilweise hochgeladen.',
                UPLOAD_ERR_NO_FILE => 'Es wurde keine Datei hochgeladen.',
                UPLOAD_ERR_NO_TMP_DIR => 'Kein temporäres Verzeichnis für Uploads vorhanden.',
                UPLOAD_ERR_CANT_WRITE => 'Die Datei konnte nicht auf die Festplatte geschrieben werden.',
                UPLOAD_ERR_EXTENSION => 'Der Upload wurde durch eine PHP-Erweiterung gestoppt.',
            ];
            $msg = $uploadErrorMessages[$uploadedFile['error']] ?? 'Unbekannter Upload-Fehler.';
            http_response_code(400);
            echo json_encode(['ok' => false, 'error' => $msg]);
            exit;
        }

        $allowedMime = [
            'image/jpeg',
            'image/png',
        ];

        $maxSize = 20 * 1024 * 1024;
        if ($uploadedFile['size'] > $maxSize) {
            http_response_code(400);
            echo json_encode(['ok' => false, 'error' => 'Datei ist zu groß.']);
            exit;
        }

        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mime = $finfo->file($uploadedFile['tmp_name']) ?: '';
        if (!in_array($mime, $allowedMime, true)) {
            http_response_code(400);
            echo json_encode(['ok' => false, 'error' => 'Nur Bilder sind erlaubt.']);
            exit;
        }

        $today = date('Y-m-d');
        $uploadDir = __DIR__ . '/uploads/chat/' . $today;
        if (!is_dir($uploadDir) && !mkdir($uploadDir, 0755, true)) {
            http_response_code(500);
            echo json_encode(['ok' => false, 'error' => 'Upload-Ordner konnte nicht erstellt werden.']);
            exit;
        }

        $originalName = $uploadedFile['name'] ?? 'file';
        $ext = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
        $safeName = preg_replace('/[^a-zA-Z0-9._-]/', '_', pathinfo($originalName, PATHINFO_FILENAME));
        $random = bin2hex(random_bytes(8));
        $fileName = $safeName !== '' ? $safeName . '_' . $random : $random;
        if ($ext !== '') {
            $fileName .= '.' . $ext;
        }

        $imageData = file_get_contents($uploadedFile['tmp_name']);
        if ($imageData === false) {
            http_response_code(500);
            echo json_encode(['ok' => false, 'error' => 'Upload fehlgeschlagen.']);
            exit;
        }
        $imageDataUri = 'data:' . $mime . ';base64,' . base64_encode($imageData);

        $destination = $uploadDir . '/' . $fileName;
        if (!move_uploaded_file($uploadedFile['tmp_name'], $destination)) {
            http_response_code(500);
            echo json_encode(['ok' => false, 'error' => 'Upload fehlgeschlagen.']);
            exit;
        }

        $filePath = 'uploads/chat/' . $today . '/' . $fileName;
        $fileName = $originalName;
    }

    save_chat_message($userId, $taskId, 'user', $message, $filePath, $fileName);
    $history = chat_messages_for_task($userId, $taskId);
    $promptNotes = is_string($task['prompt_notes'] ?? null) ? trim((string)$task['prompt_notes']) : '';
    $reply = ai_chat_reply($message, $history, $imageDataUri ?? null, $taskId, $promptNotes);
    if ($reply !== '') {
        save_chat_message($userId, $taskId, 'assistant', $reply);
    }
    echo json_encode(['ok' => true, 'reply' => $reply]);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => $e->getMessage()]);
}
