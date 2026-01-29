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

function tasks(): array
{
    return db_query(
        'SELECT id, title, prompt_notes, position FROM tasks ORDER BY position ASC'
    );
}

function task_by_id(int $taskId): ?array
{
    $rows = db_query(
        'SELECT * FROM tasks WHERE id = :id LIMIT 1',
        ['id' => $taskId]
    );

    return $rows[0] ?? null;
}
