<?php
/**
 * chat.php
 *
 * Diese Datei enthält die Chat-Logik der Anwendung.
 * Sie koordiniert die Verarbeitung von Chat-Nachrichten
 * und nutzt dafür den AI-Service.
 */

/**
 * Speichert eine Chat-Nachricht in der Datenbank.
 */
function save_chat_message(int $userId, int $taskId, string $role, string $content, ?string $filePath = null, ?string $fileName = null): void
{
    db_execute(
        'INSERT INTO chat_messages (user_id, task_id, role, content, file_path, file_name)
         VALUES (:user_id, :task_id, :role, :content, :file_path, :file_name)',
        [
            'user_id' => $userId,
            'task_id' => $taskId,
            'role' => $role,
            'content' => $content,
            'file_path' => $filePath,
            'file_name' => $fileName,
        ]
    );
}

/**
 * Lädt alle Chat-Nachrichten für einen User und Task.
 */
function chat_messages_for_task(int $userId, int $taskId, ?int $limit = null): array
{
    global $config;

    $maxHistory = $limit;
    if ($maxHistory === null) {
        $maxHistory = (int)($config['ai']['max_history'] ?? 20);
    }
    if ($maxHistory < 1) {
        return [];
    }

    return db_query(
        'SELECT role, content, file_path, file_name
         FROM (
           SELECT id, role, content, file_path, file_name
           FROM chat_messages
           WHERE user_id = :user_id AND task_id = :task_id
           ORDER BY id DESC
           LIMIT :limit
         ) AS recent
         ORDER BY id ASC',
        [
            'user_id' => $userId,
            'task_id' => $taskId,
            'limit' => (int)$maxHistory,
        ]
    );
}
