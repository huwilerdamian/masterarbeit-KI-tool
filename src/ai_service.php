<?php
/**
 * ai_service.php
 *
 * Diese Datei enthält die fachliche Logik zur Interaktion mit der KI API.
 * Sie ist zuständig für den Aufbau der Prompts, den API Aufruf sowie
 * die Aufbereitung der KI Antworten.
 *
 * Die Funktionen in dieser Datei sind bewusst vom Controller getrennt,
 * um die Logik klar zu kapseln und wiederverwendbar zu halten.
 */

/**
 * Baut einen KI-Request aus Message/History und liefert die Text-Antwort.
 */
function ai_chat_reply(string $message, array $history = [], ?string $imageDataUri = null, ?int $taskId = null, ?string $instructions = null): string
{
    global $config;

    $apiKey = $config['ai']['api_key'] ?? '';
    if ($apiKey === '') {
        throw new RuntimeException('OPENAI_API_KEY fehlt.');
    }

    $model = $config['ai']['model'] ?? 'gpt-4.1-mini';

    $input = $message;
    $hasImage = $imageDataUri !== null && $imageDataUri !== '';
    if (!empty($history) || $hasImage) {
        $messages = normalize_history_messages($history);
        $contentParts = [];
        if ($message !== '') {
            $contentParts[] = ['type' => 'input_text', 'text' => $message];
        }
        if ($hasImage) {
            $contentParts[] = ['type' => 'input_image', 'image_url' => $imageDataUri];
        }
        $messages[] = ['role' => 'user', 'content' => $contentParts];
        $input = $messages;
    }

    $payloadData = [
        'model' => $model,
        'input' => $input,
    ];
    $temperature = $config['ai']['temperature'] ?? null;
    if (is_numeric($temperature)) {
        $temperatureValue = (float)$temperature;
        if ($temperatureValue >= 0 && $temperatureValue <= 2) {
            $payloadData['temperature'] = $temperatureValue;
        }
    }
    $systemPrompt = getenv('SYSTEM_PROMPT');
    $systemPrompt = is_string($systemPrompt) ? trim($systemPrompt) : '';
    $extraInstructions = is_string($instructions) ? trim($instructions) : '';
    if ($systemPrompt !== '' || $extraInstructions !== '') {
        $combined = $systemPrompt;
        if ($extraInstructions !== '') {
            $combined = $combined !== '' ? ($combined . "\n\n" . $extraInstructions) : $extraInstructions;
        }
        $payloadData['instructions'] = $combined;
    }

    $vectorStoreId = $config['ai']['vector_store_id'] ?? '';
    if ($taskId !== null && $taskId > 0 && $vectorStoreId !== '') {
        $payloadData['tools'] = [[
            'type' => 'file_search',
            'vector_store_ids' => [$vectorStoreId],
            'filters' => [
                'type' => 'in',
                'key' => 'task_id',
                'value' => [(int)$taskId, 0],
            ],
        ]];
    }

    $payload = json_encode($payloadData);

    $ch = curl_init('https://api.openai.com/v1/responses');
    curl_setopt_array($ch, [
        CURLOPT_POST => true,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => [
            'Authorization: Bearer ' . $apiKey,
            'Content-Type: application/json',
        ],
        CURLOPT_POSTFIELDS => $payload,
    ]);

    $raw = curl_exec($ch);
    if ($raw === false) {
        $err = curl_error($ch);
        throw new RuntimeException('OpenAI Request fehlgeschlagen: ' . $err);
    }

    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

    $data = json_decode($raw, true);
    if (!is_array($data)) {
        throw new RuntimeException('Ungültige OpenAI Antwort.');
    }

    if ($httpCode >= 400) {
        $msg = $data['error']['message'] ?? 'OpenAI Fehler.';
        throw new RuntimeException($msg);
    }

    return extract_openai_text($data);
}

/**
 * Führt einen generischen OpenAI API-Request aus und gibt das JSON als Array zurück.
 */
function openai_api_request(string $method, string $url, array $headers = [], $body = null): array
{
    global $config;

    $apiKey = $config['ai']['api_key'] ?? '';
    if ($apiKey === '') {
        throw new RuntimeException('OPENAI_API_KEY fehlt.');
    }

    $ch = curl_init($url);
    $baseHeaders = [
        'Authorization: Bearer ' . $apiKey,
    ];
    $allHeaders = array_merge($baseHeaders, $headers);

    curl_setopt_array($ch, [
        CURLOPT_CUSTOMREQUEST => $method,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => $allHeaders,
        CURLOPT_POSTFIELDS => $body,
    ]);

    $raw = curl_exec($ch);
    if ($raw === false) {
        $err = curl_error($ch);
        throw new RuntimeException('OpenAI Request fehlgeschlagen: ' . $err);
    }

    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $data = json_decode($raw, true);
    if (!is_array($data)) {
        throw new RuntimeException('Ungültige OpenAI Antwort.');
    }
    if ($httpCode >= 400) {
        $msg = $data['error']['message'] ?? 'OpenAI Fehler.';
        throw new RuntimeException($msg);
    }

    return $data;
}

/**
 * Lädt eine lokale Datei zur OpenAI Files API hoch und gibt die File-ID zurück.
 */
function openai_upload_file(string $absolutePath): string
{
    if (!is_file($absolutePath)) {
        throw new RuntimeException('Datei nicht gefunden: ' . $absolutePath);
    }

    $data = openai_api_request(
        'POST',
        'https://api.openai.com/v1/files',
        [],
        [
            'purpose' => 'assistants',
            'file' => new CURLFile($absolutePath),
        ]
    );

    return (string)($data['id'] ?? '');
}

/**
 * Hängt eine File-ID an den konfigurierten Vector Store und gibt die VS-File-ID zurück.
 */
function openai_attach_file_to_vector_store(string $fileId, array $attributes = []): string
{
    global $config;

    $vectorStoreId = $config['ai']['vector_store_id'] ?? '';
    if ($vectorStoreId === '') {
        throw new RuntimeException('OPENAI_VECTOR_STORE_ID fehlt.');
    }

    $payload = [
        'file_id' => $fileId,
    ];
    if (!empty($attributes)) {
        $payload['attributes'] = $attributes;
    }

    $data = openai_api_request(
        'POST',
        'https://api.openai.com/v1/vector_stores/' . $vectorStoreId . '/files',
        ['Content-Type: application/json'],
        json_encode($payload)
    );

    return (string)($data['id'] ?? '');
}

/**
 * Extrahiert den Antwort-Text aus unterschiedlichen OpenAI Response-Formaten.
 */
function extract_openai_text(array $data): string
{
    if (isset($data['output_text']) && is_string($data['output_text'])) {
        return $data['output_text'];
    }

    if (isset($data['output']) && is_array($data['output'])) {
        foreach ($data['output'] as $item) {
            if (!isset($item['content']) || !is_array($item['content'])) {
                continue;
            }
            foreach ($item['content'] as $content) {
                if (($content['type'] ?? '') === 'output_text') {
                    return (string)($content['text'] ?? '');
                }
            }
        }
    }

    if (isset($data['choices'][0]['message']['content'])) {
        return (string)$data['choices'][0]['message']['content'];
    }

    return '';
}

/**
 * Normalisiert ein History-Array zu einem Messages-Format.
 * Erwartet Einträge mit Schlüsseln 'role' und 'content'.
 */
function normalize_history_messages(array $history): array
{
    $messages = [];
    foreach ($history as $item) {
        if (!is_array($item)) {
            continue;
        }
        $role = $item['role'] ?? '';
        $content = $item['content'] ?? '';
        if (!is_string($role) || !is_string($content)) {
            continue;
        }
        if ($role === '' || $content === '') {
            continue;
        }
        $messages[] = ['role' => $role, 'content' => $content];
    }
    return $messages;
}
