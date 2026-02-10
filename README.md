# Masterarbeit KI-Tool

Web-App zur Aufgabenverwaltung mit KI-gestütztem Chat pro Aufgabe. Lehrpersonen/Schüler:innen können Aufgaben nach Teil (A, B, …) sehen, Status setzen (gelöst/korrigiert) und bei Bedarf Hilfe per Chat abrufen. Optional lassen sich Aufgaben- und Lösungsdateien anzeigen und in einen OpenAI Vector Store einhängen.

## Features
- Aufgabenübersicht nach Gruppen (Teil A, B, …)
- Status pro Aufgabe (Gelöst, Korrigiert, Hilfe)
- Chat pro Aufgabe mit Dateianhängen
- Anzeige von Aufgaben- und Lösungsdateien (PDF/Bild)
- Optional: Vector-Store-Anbindung für Datei-Suche

## Anforderungen
- PHP 8.1+
- MySQL/MariaDB
- OpenAI API Key (für den Chat)

## Setup
1. `.env` im Projektroot anlegen (wird in `init.php` geladen).
2. Datenbankzugang und OpenAI-Konfiguration eintragen.
3. Lokalen PHP-Server starten.

### Beispiel `.env`
```dotenv
DB_HOST=127.0.0.1
DB_PORT=3306
DB_NAME=masterarbeit
DB_USER=root
DB_PASS=secret

OPENAI_API_KEY=your_key_here
OPENAI_MODEL=gpt-4.1-mini
OPENAI_MAX_HISTORY=20
OPENAI_TEMPERATURE=0.3
OPENAI_VECTOR_STORE_ID=
SYSTEM_PROMPT=
```

### Starten (lokal)
```bash
php -S localhost:8000 -t public
```
Danach `http://localhost:8000` öffnen.

## Wichtige Tabellen (Datenbank)
Die App erwartet mindestens folgende Tabellen:
- `users` (z. B. `id`, `username`, `password_hash`)
- `tasks`
- `task_progress`
- `task_files`
- `chat_messages`

Die genauen Spalten können aus den SQL-Queries in `src/` abgeleitet werden.

## Projektstruktur (Auszug)
- `public/` – Public Web Root (Screens, Assets)
- `src/` – Business-Logik und DB-Zugriff
- `config/` – App- und DB-Konfiguration

## Hinweise
- Der Vector-Store ist optional. Wenn `OPENAI_VECTOR_STORE_ID` leer ist, wird der Upload übersprungen.
- Für den Chat muss der API Key gesetzt sein (`OPENAI_API_KEY`).
