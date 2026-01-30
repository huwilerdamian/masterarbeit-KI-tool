<?php
/**
 * tasks.php
 *
 * Diese Datei bündelt den Datenzugriff und die Logik rund um Tasks.
 * Sie stellt Funktionen bereit, um Tasks zu laden sowie einzelne
 * Tasks anhand der ID abzufragen.
 *
 * Die Datei enthält keine HTML-Ausgabe und keine Seitenlogik,
 * sondern liefert nur Daten für die Verwendung in public/*.
 */

/**
 * Lädt alle Tasks eines Users über task_progress inkl. Progress-Daten.
 */
function tasks(int $userId): array
{
    return db_query(
        'SELECT t.id, t.title, t.prompt_notes, t.position, tp.task_id, tp.state, tp.corrected
         FROM task_progress tp
         INNER JOIN tasks t ON t.id = tp.task_id
         WHERE tp.user_id = :uid
         ORDER BY t.position ASC',
        ['uid' => $userId]
    );
}

/**
 * Legt für einen User fehlende task_progress-Einträge an,
 * damit jeder Task einen Fortschrittseintrag hat.
 */
function ensure_task_progress_for_user(int $userId): void
{
    db_execute(
        'INSERT INTO task_progress (task_id, user_id)
         SELECT t.id, :uid_insert
         FROM tasks t
         LEFT JOIN task_progress tp
           ON tp.task_id = t.id AND tp.user_id = :uid_join
         WHERE tp.task_id IS NULL',
        ['uid_insert' => $userId, 'uid_join' => $userId]
    );
}

/**
 * Lädt den aktuellen Stand eines Users für einen bestimmten Task.
 */
function task_progress_by_task_id_and_user_id(int $taskId, int $userId): array
{
    $rows = db_query(
        'SELECT * FROM task_progress WHERE task_id = :task_id AND user_id = :user_id',
        ['task_id' => $taskId, 'user_id' => $userId]
    );

    return $rows;
}

/**
 * Lädt einen einzelnen Task anhand der ID.
 */
function task_by_id(int $taskId): ?array
{
    $rows = db_query(
        'SELECT * FROM tasks WHERE id = :id LIMIT 1',
        ['id' => $taskId]
    );

    return $rows[0] ?? null;
}

/**
 * Setzt den corrected-Status eines Tasks für einen User.
 */
function update_task_corrected(int $taskId, int $userId, bool $corrected): void
{
    db_execute(
        'UPDATE task_progress
         SET corrected = :corrected
         WHERE task_id = :task_id AND user_id = :user_id',
        [
            'corrected' => $corrected ? 1 : 0,
            'task_id' => $taskId,
            'user_id' => $userId,
        ]
    );
}

/**
 * Setzt den state eines Tasks für einen User.
 */
function update_task_state(int $taskId, int $userId, bool $state): void
{
    db_execute(
        'UPDATE task_progress
         SET state = :state
         WHERE task_id = :task_id AND user_id = :user_id',
        [
            'state' => $state ? 1 : 0,
            'task_id' => $taskId,
            'user_id' => $userId,
        ]
    );
}
