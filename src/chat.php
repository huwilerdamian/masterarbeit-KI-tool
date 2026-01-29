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
function save_chat_message(int $userId, int $taskId, string $role, string $content): void
{
    db_execute(
        'INSERT INTO chat_messages (user_id, task_id, role, content)
         VALUES (:user_id, :task_id, :role, :content)',
        [
            'user_id' => $userId,
            'task_id' => $taskId,
            'role' => $role,
            'content' => $content,
        ]
    );
}
