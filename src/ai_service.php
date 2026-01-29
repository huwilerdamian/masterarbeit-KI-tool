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

function ai_chat_reply(string $message): string
{
    global $config;

    $apiKey = $config['ai']['api_key'] ?? '';
    if ($apiKey === '') {
        throw new RuntimeException('OPENAI_API_KEY fehlt.');
    }

    $model = $config['ai']['model'] ?? 'gpt-4.1-mini';

    $payload = json_encode([
        'model' => $model,
        'input' => $message,
    ]);

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
