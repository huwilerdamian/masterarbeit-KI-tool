<?php
/**
 * task_files.php
 *
 * Hilfsfunktionen für Task-Dateien und Vector-Store Uploads.
 */

/**
 * Lädt die Task-Dateien für einen Task.
 */
function task_files_for_task(int $taskId): array
{
    return db_query(
        'SELECT id, task_id, `TYPE` AS type, file_path, file_id, vector_store_file_id
         FROM task_files
         WHERE task_id = :task_id
            OR task_id IS NULL',
        [
            'task_id' => $taskId,
        ]
    );
}

/**
 * Stellt sicher, dass alle Task-Dateien im Vector Store vorhanden sind.
 */
function ensure_task_files_in_vector_store(int $taskId, int $userId): void
{
    global $config;

    $vectorStoreId = $config['ai']['vector_store_id'] ?? '';
    if ($vectorStoreId === '') {
        return;
    }

    $files = task_files_for_task($taskId);
    if (empty($files)) {
        return;
    }

    foreach ($files as $file) {
        $vectorStoreFileId = $file['vector_store_file_id'] ?? '';
        if (is_string($vectorStoreFileId) && $vectorStoreFileId !== '') {
            continue;
        }

        $relativePath = (string)($file['file_path'] ?? '');
        if ($relativePath === '') {
            continue;
        }

        $absolutePath = realpath(__DIR__ . '/../public/' . ltrim($relativePath, '/'));
        if ($absolutePath === false || !is_file($absolutePath)) {
            continue;
        }

        $fileId = (string)($file['file_id'] ?? '');
        if ($fileId === '') {
            $fileId = openai_upload_file($absolutePath);
        }

        $isGlobal = !array_key_exists('task_id', $file) || $file['task_id'] === null;
        $taskAttributeId = $isGlobal ? 0 : (int)$file['task_id'];

        $attributes = [
            'task_id' => $taskAttributeId,
            'is_global' => $isGlobal,
            'task_file_id' => (int)$file['id'],
            'type' => (string)($file['type'] ?? ''),
            'user_id' => $userId,
        ];

        $vsFileId = openai_attach_file_to_vector_store($fileId, $attributes);

        db_execute(
            'UPDATE task_files
             SET file_id = :file_id,
                 vector_store_file_id = :vector_store_file_id,
                 uploaded_to_vector_store_at = NOW()
             WHERE id = :id',
            [
                'file_id' => $fileId,
                'vector_store_file_id' => $vsFileId,
                'id' => (int)$file['id'],
            ]
        );
    }
}
