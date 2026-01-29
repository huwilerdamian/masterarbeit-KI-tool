<?php
/**
 * database.php
 *
 * Diese Datei stellt die zentrale Schnittstelle zur Datenbank dar.
 * Sie ist zuständig für den Aufbau und die Rueckgabe einer
 * Datenbankverbindung.
 *
 * Fachliche Logik oder Datenverarbeitung findet hier bewusst nicht statt,
 * um eine klare Trennung zwischen Datenzugriff und Anwendungslogik
 * zu gewähren.
 */

function db(): PDO
{
    global $config;

    // "static" sorgt dafür, dass $pdo seinen Wert zwischen Funktionsaufrufen behält.
    static $pdo = null;

    // Nur wenn noch keine Verbindung existiert, wird sie erstellt.
    if ($pdo === null) {
        if (!isset($config['db'])) {
            throw new RuntimeException('DB config fehlt. init.php geladen?');
        }

        $db = $config['db'];

        foreach (['host','name','user','pass','port'] as $key) {
            if (!array_key_exists($key, $db)) {
                throw new RuntimeException("DB config Key fehlt: {$key}");
            }
        }

        // DSN = Data Source Name, beschreibt die Verbindung.
        // charset=utf8mb4 unterstützt Umlaute + Emoji korrekt.
        $dsn = "mysql:host={$db['host']};port={$db['port']};dbname={$db['name']};charset=utf8mb4";

        // Wichtige PDO-Optionen:
        $options = [
            // Fehler als Exceptions werfen (einfacher zu debuggen).
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            // Ergebniszeilen als assoziatives Array zurückgeben.
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            // Emulierte Prepares deaktivieren (sicherer, echte Prepares).
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];

        // PDO-Instanz erstellen.
        $pdo = new PDO($dsn, $db['user'], $db['pass'], $options);
    }

    return $pdo;
}

/**
 * Führt ein SELECT aus und gibt alle Zeilen zurück.
 *
 * @param string $sql    SQL-Statement mit Platzhaltern (:id, ? etc.)
 * @param array  $params Werte für die Platzhalter.
 * @return array         Ergebniszeilen (assoziative Arrays).
 */
function db_query(string $sql, array $params = []): array
{
    // Statement vorbereiten schützt vor SQL-Injection.
    $stmt = db()->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

/**
 * Führt INSERT/UPDATE/DELETE aus.
 *
 * @return int Anzahl betroffener Zeilen.
 */
function db_execute(string $sql, array $params = []): int
{
    $stmt = db()->prepare($sql);
    $stmt->execute($params);
    return $stmt->rowCount();
}

/**
 * Gibt die ID des zuletzt eingefügten Datensatzes zurück.
 * Funktioniert nur für Tabellen mit AUTO_INCREMENT.
 */
function db_last_insert_id(): string
{
    return db()->lastInsertId();
}
