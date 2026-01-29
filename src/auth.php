<?php
/**
 * auth.php
 *
 * Einfache Authentifizierung: prüft Username/Passwort und liefert die User-ID.
 * Erwartet eine Tabelle `users` mit Spalten `id`, `username`, `password_hash`.
 */

function authenticate_user(string $username, string $password): ?int
{
    $rows = db_query(
        'SELECT id, password_hash FROM users WHERE username = :username LIMIT 1',
        ['username' => $username]
    );

    $user = $rows[0] ?? null;
    if (!$user) {
        return null;
    }

    if (!password_verify($password, $user['password_hash'])) {
        return null;
    }

    return (int)$user['id'];
}
